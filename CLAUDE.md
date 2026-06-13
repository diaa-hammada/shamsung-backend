# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

```bash
# Setup (first time)
composer run-script setup       # Install deps, generate app key, migrate DB, build frontend

# Development
composer run dev                # Concurrent: Laravel server + queue + log tail + Vite
php artisan serve               # Laravel server only

# Testing
composer test                   # Clears config cache then runs PHPUnit
php artisan test --filter=TestClassName

# Linting
./vendor/bin/pint               # Laravel Pint code style fixer

# Seed admin account
php artisan db:seed --class=AdminSeeder
```

## Architecture

**Laravel 12 REST API** — no Blade views. All routes under `/api/v1/` in `routes/api.php`.
Database: **MySQL** in production/dev (XAMPP). Migrations use `DB::statement('ALTER TABLE ... MODIFY COLUMN ...')` for MySQL-specific changes — never use `$table->change()`.

---

## Auth System

Four separate Authenticatable models with Sanctum tokens:

| Role             | Model                        | Login method     | Route prefix          |
|------------------|------------------------------|------------------|-----------------------|
| Customer         | `App\Models\Customer`        | OTP (SMS)        | `/api/v1/customer`    |
| Technician       | `App\Models\Technician`      | OTP (SMS)        | `/api/v1/technician`  |
| Delivery Worker  | `App\Models\DeliveryWorker`  | OTP (SMS)        | `/api/v1/delivery`    |
| Admin            | `App\Models\Admin`           | Email + Password | `/api/v1/admin`       |

### Customer Auth Flow (3 steps)
```
POST /customer/send-otp    { phone }          → sends OTP via Traccar SMS API
POST /customer/verify-otp  { phone, code }    → new user: returns is_new_user=true
                                               → existing: returns token
POST /customer/register    { first_name, last_name, phone, email?, birthdate? }
                                               → protected: phone must have phone_verified=true OTP record
```

### Technician Auth Flow (2 steps)
```
POST /technician/send-otp   { phone }         → phone must exist in technicians table
POST /technician/verify-otp { phone, code }   → returns token + technician{shop}
                                               → 404 if not registered, 403 if is_active=false
```

### Admin Auth
```
POST /admin/login  { email, password }         → standard email/password
```

### OTP Service (`App\Services\OtpService`)
- Rate limit: 3 OTPs per phone per 60 seconds → 429
- OTP expires: 10 minutes
- Register window: 10 minutes after phone_verified
- SMS provider: Traccar API (`https://www.traccar.org/sms/`)
- Token pattern: `$model->createToken('{role}_token')->plainTextToken`

### Test Accounts (hardcoded bypasses in OtpService)
| Purpose         | Phone           | OTP   |
|-----------------|-----------------|-------|
| Apple Review    | +12345678900    | 12345 |
| Dev/Test        | +963999999999   | 99999 |
| Admin           | admin@shamsung.com | password123 (seed) |

---

## Controller Organization

```
app/Http/Controllers/Api/V1/
├── CustomerAuthController.php        # OTP register/login/logout/profile/delete
├── TechnicianAuthController.php      # OTP login/logout/profile
├── MaintenanceRequestController.php  # Customer: CRUD + approve/reject/cancel/parts
├── ShopController.php                # Customer: nearest shop (Haversine)
├── AccessoryController.php           # Customer: browse accessories
├── CartController.php                # Customer: cart CRUD
├── OrderController.php               # Customer: checkout + order history
├── Customer/
│   └── ConsultationController.php    # Customer: AI or technician consultation
├── Technician/
│   ├── MaintenanceRequestController.php  # Tech: list/show/status/diagnose
│   ├── SparePartController.php           # Tech: view shop spare parts
│   └── ConsultationController.php        # Tech: list pending + reply
└── Admin/
    ├── AdminAuthController.php       # Email/password login
    ├── ShopController.php            # CRUD shops + image upload
    ├── TechnicianController.php      # CRUD technicians (no password)
    ├── SparePartController.php       # List inventory + stock requests
    └── AccessoryController.php       # CRUD accessories + image upload
```

---

## Maintenance Request — Status Machine

```
[Customer] POST /maintenance-requests
    → status: "pending"

[Technician] POST /maintenance-requests/{id}/status  { status: "under_inspection" }
    → status: "under_inspection"

[Technician] POST /maintenance-requests/{id}/diagnose  { estimated_days, parts:[{spare_part_id,quantity,is_required}] }
    → status: "waiting_customer_approval"
    → customer_status: "pending_approval"
    → creates MaintenanceRequestPart rows (name+price copied from SparePart catalog)

[Customer] GET /maintenance-requests/{id}/parts
    → shows { estimated_days, parts:[{id,name,price,quantity,is_required,is_selected}] }

[Customer] POST /maintenance-requests/{id}/approve  { selected_parts:[ids], payment_method }
    → validates required parts are all selected
    → decrements spare_parts.stock_quantity for each selected part
    → calculates estimated_cost = sum(price × quantity) of selected parts
    → status: "approved",  customer_status: "approved"

[Customer] POST /maintenance-requests/{id}/reject  { rejection_reason? }
    → status: "cancelled",  customer_status: "rejected"

[Customer] POST /maintenance-requests/{id}/cancel
    → only if status="pending"
    → status: "cancelled"

[Technician] POST /maintenance-requests/{id}/status  { status: "completed" }
    → status: "completed"
```

**Valid statuses:** `pending`, `under_inspection`, `waiting_customer_approval`, `approved`, `completed`, `cancelled`

**UpdateStatusRequest** (technician manual): `in:under_inspection,completed`
> Note: `pending_approval` is a legacy value in UpdateStatusRequest — remove it, it's unused.

**payment_method values:** `cash_on_delivery`, `pay_after_service`

---

## Key Model Behaviors

- **MaintenanceRequest**: Auto-generates `SHM-{8 chars}` tracking number on creation via `boot()`.
- **MaintenanceRequestPart**: Has both `spare_part_id` (FK, nullable) and `name`/`price` (denormalized from catalog at diagnose time). `unit_price` is nullable (legacy column from old flow).
- **Order**: Auto-generates `ORD-*` order number on creation. `checkout()` groups cart items by shop, creates one order per shop, decrements accessory stock — all in `DB::transaction()`.
- **Shop**: Stores `latitude`/`longitude`. `ShopService` uses Haversine formula for nearest-shop queries.
- **Consultation**: `technician_id` nullable for AI type. `consultation_type`: `technician|ai`. AI calls Gemini API (tries `gemini-2.5-flash-lite` then `gemini-flash-latest`, cycles through comma-separated `GEMINI_API_KEYS`).
- **Technician**: No password — OTP only. `password` column is nullable. `is_active` flag checked at OTP verify.
- **SparePart**: `stock_quantity` decrements when customer approves diagnosis (inside approve transaction).

---

## Response Convention

```json
{ "message": "...", "data": { ... } }
```
List endpoints return `data` as array. Errors follow the same pattern. HTTP codes: 200, 201, 400, 401, 403, 404, 422, 429.

---

## Validation

Form Request classes live in `app/Http/Requests/Api/V1/`. `authorize()` always returns `true`.

All controllers use Form Request classes. `authorize()` always returns `true`.

---

## File Uploads

- Shop images → `storage/app/public/shops/` via `Storage::disk('public')`
- Accessory images → `storage/app/public/accessories/`
- Consultation images → `storage/app/public/consultations/`
- All served via `url('storage/...')`

---

## Completed Features

| FRs | Feature | Notes |
|-----|---------|-------|
| Auth | Customer OTP register/login | 3-step flow with phone_verified guard |
| Auth | Technician OTP login | 2-step, is_active check |
| Auth | Admin email/password login | — |
| FR-3–8 | Customer maintenance requests | Submit, list, detail, cancel |
| FR-10–11 | Technician diagnosis + customer approval | Parts from spare_parts catalog, stock decrements on approve |
| FR-16–20 | Accessories store + cart + checkout | Cart grouped by shop → one order per shop |
| FR-21 | Customer order history | — |
| FR-22–23 | AI + Technician consultations | Gemini API with multi-key/model fallback |
| FR-25 | Find nearest shops | Haversine formula in ShopService |
| FR-28 | Admin shop management | CRUD + image upload |
| FR-29–30 | Spare parts inventory + stock requests | Full CRUD + stock requests → approve increments quantity |
| FR-31 | Admin accessories management | CRUD + image upload |
| Admin | Technician management | CRUD, no password — OTP only |
| Admin | Delivery worker management | CRUD, no password — OTP only |
| Delivery | Delivery worker auth | 2-step OTP, is_active check |
| Delivery | Delivery request flow | Accept/reject → on_the_way → arrived → confirm + cash collection |
| Customer | Customer delivery tracking | List + detail of own deliveries |
| Admin | Delivery management | Create delivery requests + list all |

---

## Firebase FCM Push Notifications

Credentials file: `storage/app/firebase-credentials.json` (service account, project `shamsoung-d2d58`).
No external package — `FcmService` uses PHP OpenSSL + Laravel Http to implement the FCM HTTP v1 API directly.
Access token cached for 55 minutes (`fcm_access_token` cache key). All FCM calls are fire-and-forget (log on failure, never break the request).

### FCM Token Registration
```
POST /customer/fcm-token    { fcm_token }    → requires auth:sanctum
POST /technician/fcm-token  { fcm_token }    → requires auth:sanctum
POST /delivery/fcm-token    { fcm_token }    → requires auth:sanctum
```
`fcm_token` is a nullable string column on both `customers` and `technicians` tables.

### Notification Matrix

| Trigger | Actor | Recipient | Title |
|---------|-------|-----------|-------|
| Customer submits maintenance request | Customer | All shop technicians | New Maintenance Request |
| Customer cancels request | Customer | All shop technicians | Request Cancelled |
| Customer approves diagnosis | Customer | All shop technicians | Diagnosis Approved |
| Customer rejects diagnosis | Customer | All shop technicians | Diagnosis Rejected |
| Customer creates technician consultation | Customer | All active technicians | New Consultation Request |
| Technician sets `under_inspection` | Technician | Customer | Request Under Inspection |
| Technician submits diagnosis | Technician | Customer | Diagnosis Ready |
| Technician sets `completed` | Technician | Customer | Repair Completed |
| Technician replies to consultation | Technician | Customer | Consultation Answered |
| Admin activates technician | Admin | Technician | Account Activated |
| Admin deactivates technician | Admin | Technician | Account Deactivated |
| Admin activates delivery worker | Admin | Delivery Worker | Account Activated |
| Admin deactivates delivery worker | Admin | Delivery Worker | Account Deactivated |
| Admin creates delivery request | Admin | Customer | تم إنشاء طلب التوصيل |
| Delivery worker accepts request | Delivery Worker | Customer | عامل التوصيل في الطريق إليك |
| Delivery worker sets `on_the_way` | Delivery Worker | Customer | عامل التوصيل في الطريق |
| Delivery worker sets `arrived` | Delivery Worker | Customer | عامل التوصيل وصل |
| Delivery worker confirms delivery | Delivery Worker | Customer + Admin | تم التوصيل بنجاح |
| Delivery worker collects cash | Delivery Worker | Admin | Cash Collected |

All FCM `data` payloads include `type` (`maintenance_request` / `consultation` / `account_status` / `delivery`) and `id` for client-side routing.

---

## Pending / Known Issues

No open issues.

---

## Migrations Timeline

```
Base tables (2026-05-14):  customers, technicians, shops, maintenance_requests,
                            accessories, cart_items, orders, order_items
Extensions (2026-05-15):   add shop_id to technicians, change status column,
                            create spare_parts, create maintenance_request_parts,
                            add estimated_days to maintenance_requests
Extensions (2026-05-16):   consultations, admins, shop image+rating, stock_requests
OTP system (2026-06-08):   create otps, customers.password nullable,
                            add phone_verified to otps
FR-10/11   (2026-06-08):   add customer_status/rejection_reason/payment_method to maintenance_requests,
                            add name/price/is_required/is_selected to maintenance_request_parts,
                            spare_part_id nullable in maintenance_request_parts
Fixes      (2026-06-08):   orders.payment_method ENUM → cash_on_delivery/pay_after_service,
                            technicians.password nullable,
                            maintenance_request_parts.unit_price nullable
Delivery   (2026-06-10):   create delivery_workers table
Cleanup    (2026-06-11):   drop unit_price from maintenance_request_parts
```
