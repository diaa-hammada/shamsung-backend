<?php

namespace App\Http\Controllers;

use Mpdf\Mpdf;

class DocumentationController extends Controller
{
    public function generate()
    {
        $html = $this->buildHtml();

        $mpdf = new Mpdf([
            'mode'          => 'utf-8',
            'format'        => 'A4',
            'margin_top'    => 20,
            'margin_bottom' => 20,
            'margin_left'   => 15,
            'margin_right'  => 15,
            'default_font'  => 'dejavusans',
            'direction'     => 'rtl',
        ]);

        $mpdf->SetTitle('توثيق مشروع شامسونج');
        $mpdf->SetAuthor('Shamsung Team');
        $mpdf->WriteHTML($html);
        $mpdf->Output('Shamsung_API_Documentation.pdf', 'D');
    }

    private function buildHtml(): string
    {
        return <<<HTML
<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
<meta charset="UTF-8">
<style>
* { font-family: 'dejavusans', sans-serif; }
body { direction: rtl; font-size: 10pt; color: #1a1a1a; line-height: 1.6; }
h1 { font-size: 22pt; color: #1e3a5f; border-bottom: 3px solid #1e3a5f; padding-bottom: 8px; margin-top: 20px; }
h2 { font-size: 16pt; color: #2563ab; border-bottom: 2px solid #2563ab; padding-bottom: 5px; margin-top: 18px; background: #f0f6ff; padding: 6px 10px; }
h3 { font-size: 13pt; color: #1e3a5f; margin-top: 14px; border-right: 4px solid #2563ab; padding-right: 8px; }
h4 { font-size: 11pt; color: #374151; margin-top: 10px; }
p { margin: 5px 0; font-size: 10pt; }
table { width: 100%; border-collapse: collapse; margin: 10px 0; font-size: 9pt; }
th { background: #1e3a5f; color: #fff; padding: 7px 8px; text-align: right; }
td { padding: 6px 8px; border: 1px solid #d1d5db; vertical-align: top; }
tr:nth-child(even) td { background: #f8fafc; }
.code { background: #f1f5f9; border: 1px solid #cbd5e1; padding: 8px; font-size: 8.5pt; direction: ltr; text-align: left; border-radius: 4px; margin: 6px 0; font-family: 'dejavusans', monospace; }
.badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 8pt; font-weight: bold; }
.badge-green { background: #d1fae5; color: #065f46; }
.badge-blue  { background: #dbeafe; color: #1e40af; }
.badge-red   { background: #fee2e2; color: #991b1b; }
.badge-yellow{ background: #fef9c3; color: #854d0e; }
.section-box { border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px 14px; margin: 8px 0; background: #fafafa; }
.cover { text-align: center; padding: 60px 20px; }
.cover h1 { font-size: 28pt; border: none; color: #1e3a5f; }
.cover h2 { font-size: 16pt; border: none; background: none; color: #64748b; }
.toc-item { padding: 3px 0; border-bottom: 1px dotted #cbd5e1; }
.method-get    { background:#d1fae5; color:#065f46; padding:2px 6px; border-radius:4px; font-size:8pt; font-weight:bold; }
.method-post   { background:#dbeafe; color:#1e40af; padding:2px 6px; border-radius:4px; font-size:8pt; font-weight:bold; }
.method-put    { background:#fef9c3; color:#854d0e; padding:2px 6px; border-radius:4px; font-size:8pt; font-weight:bold; }
.method-delete { background:#fee2e2; color:#991b1b; padding:2px 6px; border-radius:4px; font-size:8pt; font-weight:bold; }
.auth-tag { background:#f3e8ff; color:#6b21a8; padding:2px 6px; border-radius:4px; font-size:7.5pt; }
.page-break { page-break-after: always; }
.info-box { background:#eff6ff; border-right:4px solid #2563ab; padding:8px 12px; margin:8px 0; }
.warn-box { background:#fff7ed; border-right:4px solid #ea580c; padding:8px 12px; margin:8px 0; }
</style>
</head>
<body>

<!-- غلاف -->
<div class="cover">
  <br><br>
  <h1>🔧 مشروع شامسونج</h1>
  <h2>توثيق شامل للباك إند</h2>
  <p style="font-size:12pt; color:#64748b;">Laravel 12 REST API</p>
  <br>
  <table style="width:60%; margin:auto; border:2px solid #1e3a5f;">
    <tr><th>الإصدار</th><td>1.0.0</td></tr>
    <tr><th>المنصة</th><td>Laravel 12 + PHP 8.2+</td></tr>
    <tr><th>قاعدة البيانات</th><td>MySQL</td></tr>
    <tr><th>المصادقة</th><td>Laravel Sanctum</td></tr>
    <tr><th>رابط الإنتاج</th><td>https://shamsung.haderin.sy/api/v1</td></tr>
  </table>
</div>

<div class="page-break"></div>

<!-- فهرس المحتويات -->
<h1>فهرس المحتويات</h1>
<div class="section-box">
  <div class="toc-item">1. نظرة عامة على المشروع</div>
  <div class="toc-item">2. هيكل المجلدات</div>
  <div class="toc-item">3. نظام المصادقة - الأدوار الأربعة</div>
  <div class="toc-item">4. النماذج (Models) - 18 نموذج</div>
  <div class="toc-item">5. الخدمات (Services) - OtpService / FcmService / ShopService</div>
  <div class="toc-item">6. المتحكمات (Controllers) - جميع الـ endpoints</div>
  <div class="toc-item">7. جدول كل الـ Routes</div>
  <div class="toc-item">8. Form Requests - قواعد التحقق</div>
  <div class="toc-item">9. آلة حالات طلب الصيانة</div>
  <div class="toc-item">10. نظام الإشعارات FCM</div>
  <div class="toc-item">11. جداول قاعدة البيانات (Migrations)</div>
  <div class="toc-item">12. اتفاقية الاستجابات</div>
  <div class="toc-item">13. حسابات الاختبار</div>
</div>

<div class="page-break"></div>

<!-- ===== 1. نظرة عامة ===== -->
<h1>1. نظرة عامة على المشروع</h1>
<p>مشروع شامسونج هو <strong>REST API</strong> مبني بـ <strong>Laravel 12</strong>، يخدم تطبيقات الجوال (iOS/Android) ولوحة التحكم. لا توجد أي Blade views - كل شيء JSON.</p>

<div class="info-box">
  <strong>Base URL للإنتاج:</strong> <code>https://shamsung.haderin.sy/api/v1</code><br>
  <strong>Base URL المحلي:</strong> <code>http://127.0.0.1:8000/api/v1</code>
</div>

<h3>الأدوار في النظام</h3>
<table>
  <tr><th>الدور</th><th>النموذج</th><th>طريقة الدخول</th><th>بادئة الـ Routes</th></tr>
  <tr><td>العميل (Customer)</td><td>App\Models\Customer</td><td>OTP عبر SMS</td><td>/api/v1/customer</td></tr>
  <tr><td>الفني (Technician)</td><td>App\Models\Technician</td><td>OTP عبر SMS</td><td>/api/v1/technician</td></tr>
  <tr><td>عامل التوصيل (DeliveryWorker)</td><td>App\Models\DeliveryWorker</td><td>OTP عبر SMS</td><td>/api/v1/delivery</td></tr>
  <tr><td>المدير (Admin)</td><td>App\Models\Admin</td><td>Email + Password</td><td>/api/v1/admin</td></tr>
</table>

<h3>التقنيات المستخدمة</h3>
<table>
  <tr><th>التقنية</th><th>الاستخدام</th></tr>
  <tr><td>Laravel Sanctum</td><td>إصدار API tokens للمصادقة</td></tr>
  <tr><td>mPDF</td><td>توليد ملفات PDF</td></tr>
  <tr><td>Firebase FCM</td><td>إشعارات Push للجوال</td></tr>
  <tr><td>Gemini AI</td><td>الاستشارات الذكية</td></tr>
  <tr><td>Traccar SMS API</td><td>إرسال رسائل OTP</td></tr>
  <tr><td>MySQL</td><td>قاعدة البيانات</td></tr>
  <tr><td>Laravel Pint</td><td>تنسيق الكود تلقائياً</td></tr>
</table>

<div class="page-break"></div>

<!-- ===== 2. هيكل المجلدات ===== -->
<h1>2. هيكل المجلدات</h1>
<div class="code">app/
├── Http/
│   ├── Controllers/
│   │   ├── DocumentationController.php       ← يولّد هذا الملف
│   │   └── Api/V1/
│   │       ├── CustomerAuthController.php    ← تسجيل دخول العملاء بـ OTP
│   │       ├── TechnicianAuthController.php  ← تسجيل دخول الفنيين
│   │       ├── DeliveryAuthController.php    ← تسجيل دخول عمال التوصيل
│   │       ├── UnifiedAuthController.php     ← دخول Admin+Technician بـ email/pass
│   │       ├── MaintenanceRequestController.php ← طلبات الصيانة (العميل)
│   │       ├── ShopController.php            ← إيجاد أقرب صالة
│   │       ├── AccessoryController.php       ← تصفح الملحقات
│   │       ├── CartController.php            ← السلة
│   │       ├── OrderController.php           ← الدفع والطلبات
│   │       ├── Admin/
│   │       │   ├── AdminAuthController.php
│   │       │   ├── AdminDashboardController.php
│   │       │   ├── AdminMaintenanceRequestController.php
│   │       │   ├── AdminOrderController.php
│   │       │   ├── AdminDeliveryController.php
│   │       │   ├── AdminNotificationController.php
│   │       │   ├── ShopController.php
│   │       │   ├── TechnicianController.php
│   │       │   ├── SparePartController.php
│   │       │   ├── AccessoryController.php
│   │       │   └── DeliveryWorkerController.php
│   │       ├── Technician/
│   │       │   ├── MaintenanceRequestController.php
│   │       │   ├── SparePartController.php
│   │       │   ├── ConsultationController.php
│   │       │   └── NotificationController.php
│   │       ├── Customer/
│   │       │   ├── ConsultationController.php
│   │       │   └── DeliveryController.php
│   │       └── Delivery/
│   │           └── DeliveryController.php
│   └── Requests/Api/V1/        ← Form Requests للتحقق
├── Models/                      ← 18 نموذج
├── Services/
│   ├── OtpService.php
│   ├── FcmService.php
│   └── ShopService.php
routes/
├── api.php     ← كل الـ API routes
└── web.php     ← routes التشغيل والتوثيق
storage/app/public/
├── shops/          ← صور الصالات
├── accessories/    ← صور الملحقات
└── consultations/  ← صور الاستشارات
storage/app/
└── firebase-credentials.json   ← مفاتيح FCM السرية</div>

<div class="page-break"></div>

<!-- ===== 3. نظام المصادقة ===== -->
<h1>3. نظام المصادقة</h1>

<h2>3.1 دخول العملاء - OTP (3 خطوات)</h2>
<div class="section-box">
<p><strong>الخطوة 1:</strong> العميل يرسل رقم هاتفه</p>
<div class="code">POST /api/v1/customer/send-otp
Body: { "phone": "+963912345678" }
→ يرسل SMS بكود 5 أرقام صالح 10 دقائق</div>

<p><strong>الخطوة 2:</strong> التحقق من الكود</p>
<div class="code">POST /api/v1/customer/verify-otp
Body: { "phone": "+963912345678", "code": "12345" }

// مستخدم جديد:
→ { "data": { "is_new_user": true, "phone": "+963912345678" } }

// مستخدم موجود:
→ { "data": { "customer": {...}, "token": "1|abc123..." } }</div>

<p><strong>الخطوة 3:</strong> تسجيل مستخدم جديد فقط</p>
<div class="code">POST /api/v1/customer/register
Headers: Authorization: Bearer {token}  (غير مطلوب - يُتحقق من phone_verified بالـ OTP)
Body: { "first_name":"أحمد", "last_name":"محمد", "phone":"+963912345678",
        "email":"a@a.com", "birthdate":"1995-01-15" }
→ { "data": { "customer": {...}, "token": "2|xyz..." } }</div>
</div>

<h2>3.2 دخول الفنيين - OTP (خطوتان)</h2>
<div class="section-box">
<div class="info-box">الفني يجب أن يكون مسجلاً مسبقاً من قِبل الأدمن، ويجب أن يكون is_active = true</div>
<div class="code">POST /api/v1/technician/send-otp   → { phone }
POST /api/v1/technician/verify-otp  → { phone, code }
// نجاح: { "data": { "technician": {..., "shop": {id,name}}, "token": "..." } }
// هاتف غير موجود: 404
// حساب موقوف:    403</div>
</div>

<h2>3.3 دخول عمال التوصيل - OTP (خطوتان)</h2>
<div class="section-box">
<div class="code">POST /api/v1/delivery/send-otp
POST /api/v1/delivery/verify-otp
// نفس منطق الفني تماماً</div>
</div>

<h2>3.4 دخول الأدمن - Email/Password</h2>
<div class="section-box">
<div class="code">POST /api/v1/auth/login
Body: { "email": "admin@shamsung.com", "password": "password123" }
→ { "data": { "role": "admin", "user": {...}, "token": "..." } }

// نفس الـ endpoint للفني بـ email (إذا عنده email/password):
→ { "data": { "role": "technician", "user": {..., "shop":{...}}, "token": "..." } }</div>
</div>

<h2>3.5 استخدام الـ Token</h2>
<div class="section-box">
<p>كل endpoint محمي يحتاج هذا الـ Header:</p>
<div class="code">Authorization: Bearer {token}
Accept: application/json</div>
<div class="warn-box">الـ tokens لا تنتهي صلاحيتها تلقائياً (expiration = null في sanctum.php). تُلغى فقط عند تسجيل الخروج.</div>
</div>

<div class="page-break"></div>

<!-- ===== 4. النماذج ===== -->
<h1>4. النماذج (Models)</h1>

<h2>4.1 نموذج العميل - Customer</h2>
<div class="code">الجدول: customers
Traits: HasApiTokens, Notifiable</div>
<table>
  <tr><th>الحقل</th><th>النوع</th><th>الوصف</th></tr>
  <tr><td>id</td><td>bigint PK</td><td>المعرف</td></tr>
  <tr><td>first_name</td><td>string</td><td>الاسم الأول</td></tr>
  <tr><td>last_name</td><td>string</td><td>الاسم الأخير</td></tr>
  <tr><td>email</td><td>string unique nullable</td><td>البريد الإلكتروني</td></tr>
  <tr><td>password</td><td>string nullable hashed</td><td>كلمة المرور (مشفرة bcrypt)</td></tr>
  <tr><td>phone</td><td>string unique</td><td>رقم الهاتف +963...</td></tr>
  <tr><td>birthdate</td><td>date nullable</td><td>تاريخ الميلاد</td></tr>
  <tr><td>fcm_token</td><td>string nullable</td><td>رمز Firebase للإشعارات</td></tr>
  <tr><td>created_at / updated_at</td><td>timestamp</td><td>تلقائي</td></tr>
</table>
<p><strong>العلاقات:</strong> hasMany(MaintenanceRequest) | hasMany(CartItem) | hasMany(Order)</p>

<h2>4.2 نموذج الفني - Technician</h2>
<div class="code">الجدول: technicians
Traits: HasApiTokens, Notifiable</div>
<table>
  <tr><th>الحقل</th><th>النوع</th><th>الوصف</th></tr>
  <tr><td>id</td><td>bigint PK</td><td>المعرف</td></tr>
  <tr><td>shop_id</td><td>bigint FK nullable</td><td>الصالة التابع لها</td></tr>
  <tr><td>first_name / last_name</td><td>string</td><td>الاسم</td></tr>
  <tr><td>email</td><td>string unique nullable</td><td>البريد (اختياري)</td></tr>
  <tr><td>phone</td><td>string unique</td><td>+963...</td></tr>
  <tr><td>password</td><td>string nullable hashed</td><td>مطلوب للدخول بـ email</td></tr>
  <tr><td>specialization</td><td>string</td><td>التخصص</td></tr>
  <tr><td>experience</td><td>string</td><td>سنوات الخبرة</td></tr>
  <tr><td>is_active</td><td>boolean default:true</td><td>حالة الحساب</td></tr>
  <tr><td>fcm_token</td><td>string nullable</td><td>رمز Firebase</td></tr>
  <tr><td>birthdate</td><td>date nullable</td><td>تاريخ الميلاد</td></tr>
</table>
<p><strong>العلاقات:</strong> belongsTo(Shop)</p>

<h2>4.3 نموذج عامل التوصيل - DeliveryWorker</h2>
<div class="code">الجدول: delivery_workers
Traits: HasApiTokens, Notifiable</div>
<p>نفس حقول Technician تقريباً (shop_id, first_name, last_name, email, phone, password nullable, specialization, experience, is_active, fcm_token, birthdate)</p>
<p><strong>العلاقات:</strong> belongsTo(Shop) | hasMany(Delivery)</p>

<h2>4.4 نموذج الأدمن - Admin</h2>
<div class="code">الجدول: admins
Traits: HasApiTokens</div>
<table>
  <tr><th>الحقل</th><th>النوع</th><th>الوصف</th></tr>
  <tr><td>id</td><td>bigint PK</td><td>المعرف</td></tr>
  <tr><td>name</td><td>string</td><td>الاسم</td></tr>
  <tr><td>email</td><td>string unique</td><td>البريد</td></tr>
  <tr><td>password</td><td>string hashed</td><td>مشفر</td></tr>
  <tr><td>fcm_token</td><td>string nullable</td><td>رمز Firebase</td></tr>
</table>

<h2>4.5 نموذج الصالة - Shop</h2>
<div class="code">الجدول: shops</div>
<table>
  <tr><th>الحقل</th><th>النوع</th><th>الوصف</th></tr>
  <tr><td>name</td><td>string</td><td>اسم الصالة</td></tr>
  <tr><td>address</td><td>string</td><td>العنوان</td></tr>
  <tr><td>phone</td><td>string</td><td>رقم الهاتف</td></tr>
  <tr><td>image_path</td><td>string nullable</td><td>مسار الصورة</td></tr>
  <tr><td>rating</td><td>decimal(3,2)</td><td>التقييم</td></tr>
  <tr><td>latitude</td><td>decimal</td><td>خط العرض</td></tr>
  <tr><td>longitude</td><td>decimal</td><td>خط الطول</td></tr>
  <tr><td>is_active</td><td>boolean</td><td>هل الصالة نشطة</td></tr>
</table>
<p><strong>العلاقات:</strong> hasMany(MaintenanceRequest)</p>

<h2>4.6 نموذج طلب الصيانة - MaintenanceRequest</h2>
<div class="code">الجدول: maintenance_requests</div>
<div class="info-box">
  <strong>سلوك تلقائي:</strong> عند إنشاء طلب جديد (boot → creating)، يُولَّد رقم تتبع تلقائياً بالصيغة: <code>SHM-XXXXXXXX</code> (8 أحرف عشوائية).
</div>
<table>
  <tr><th>الحقل</th><th>النوع</th><th>الوصف</th></tr>
  <tr><td>tracking_number</td><td>string unique</td><td>SHM-XXXXXXXX</td></tr>
  <tr><td>customer_id</td><td>FK → customers</td><td>العميل صاحب الطلب</td></tr>
  <tr><td>shop_id</td><td>FK → shops</td><td>الصالة المستقبِلة</td></tr>
  <tr><td>device_model</td><td>string</td><td>موديل الجهاز</td></tr>
  <tr><td>problem_description</td><td>text</td><td>وصف المشكلة</td></tr>
  <tr><td>status</td><td>string</td><td>pending|under_inspection|waiting_customer_approval|approved|completed|cancelled</td></tr>
  <tr><td>customer_status</td><td>string nullable</td><td>pending_approval|approved|rejected</td></tr>
  <tr><td>rejection_reason</td><td>string nullable</td><td>سبب الرفض</td></tr>
  <tr><td>payment_method</td><td>string nullable</td><td>cash_on_delivery|pay_after_service</td></tr>
  <tr><td>estimated_cost</td><td>decimal nullable</td><td>التكلفة التقديرية (تُحسب عند الموافقة)</td></tr>
  <tr><td>estimated_days</td><td>integer nullable</td><td>أيام الإصلاح التقديرية</td></tr>
</table>
<p><strong>العلاقات:</strong> belongsTo(Customer) | belongsTo(Shop) | hasMany(MaintenanceRequestPart)</p>

<h2>4.7 نموذج قطع الطلب - MaintenanceRequestPart</h2>
<div class="code">الجدول: maintenance_request_parts</div>
<table>
  <tr><th>الحقل</th><th>النوع</th><th>الوصف</th></tr>
  <tr><td>maintenance_request_id</td><td>FK</td><td>رقم الطلب</td></tr>
  <tr><td>spare_part_id</td><td>FK nullable</td><td>القطعة الأصلية (من الكتالوج)</td></tr>
  <tr><td>name</td><td>string</td><td>اسم القطعة (منسوخ من الكتالوج عند التشخيص)</td></tr>
  <tr><td>price</td><td>decimal</td><td>السعر (منسوخ من الكتالوج)</td></tr>
  <tr><td>quantity</td><td>integer</td><td>الكمية</td></tr>
  <tr><td>is_required</td><td>boolean</td><td>هل القطعة إلزامية للإصلاح</td></tr>
  <tr><td>is_selected</td><td>boolean</td><td>هل اختارها العميل</td></tr>
</table>
<p><strong>العلاقات:</strong> belongsTo(SparePart)</p>

<h2>4.8 نموذج قطع الغيار - SparePart</h2>
<div class="code">الجدول: spare_parts</div>
<table>
  <tr><th>الحقل</th><th>الوصف</th></tr>
  <tr><td>shop_id FK</td><td>الصالة المالكة</td></tr>
  <tr><td>name</td><td>اسم القطعة</td></tr>
  <tr><td>price</td><td>السعر</td></tr>
  <tr><td>stock_quantity</td><td>الكمية في المخزون (تنخفض عند موافقة العميل)</td></tr>
</table>

<h2>4.9 نموذج طلب المخزون - StockRequest</h2>
<div class="code">الجدول: stock_requests</div>
<table>
  <tr><th>الحقل</th><th>الوصف</th></tr>
  <tr><td>shop_id FK</td><td>الصالة الطالبة</td></tr>
  <tr><td>spare_part_id FK</td><td>القطعة المطلوبة</td></tr>
  <tr><td>quantity</td><td>الكمية المطلوبة</td></tr>
  <tr><td>status</td><td>pending | approved | rejected</td></tr>
</table>
<p>عند الموافقة: تزداد <code>stock_quantity</code> بالكمية المطلوبة.</p>

<h2>4.10 نموذج الملحق - Accessory</h2>
<div class="code">الجدول: accessories</div>
<table>
  <tr><th>الحقل</th><th>الوصف</th></tr>
  <tr><td>shop_id FK</td><td>الصالة المالكة</td></tr>
  <tr><td>name</td><td>اسم المنتج</td></tr>
  <tr><td>description</td><td>الوصف</td></tr>
  <tr><td>price</td><td>السعر</td></tr>
  <tr><td>stock_quantity</td><td>الكمية (تنخفض عند checkout)</td></tr>
  <tr><td>image_url</td><td>رابط الصورة</td></tr>
  <tr><td>is_active</td><td>هل المنتج متاح</td></tr>
</table>

<h2>4.11 نموذج الطلبية - Order</h2>
<div class="code">الجدول: orders</div>
<div class="info-box">سلوك تلقائي: يُولَّد order_number بالصيغة ORD-XXXXXXXX عند الإنشاء</div>
<table>
  <tr><th>الحقل</th><th>الوصف</th></tr>
  <tr><td>order_number</td><td>ORD-XXXXXXXX (فريد)</td></tr>
  <tr><td>customer_id FK</td><td>العميل</td></tr>
  <tr><td>shop_id FK</td><td>الصالة</td></tr>
  <tr><td>total_amount</td><td>المبلغ الإجمالي</td></tr>
  <tr><td>payment_method</td><td>cash_on_delivery | pay_after_service</td></tr>
  <tr><td>status</td><td>pending | confirmed | delivered | cancelled</td></tr>
</table>
<p><strong>العلاقات:</strong> belongsTo(Customer) | belongsTo(Shop) | hasMany(OrderItem)</p>

<h2>4.12 نموذج عنصر الطلبية - OrderItem</h2>
<table>
  <tr><th>الحقل</th><th>الوصف</th></tr>
  <tr><td>order_id FK</td><td>الطلبية</td></tr>
  <tr><td>accessory_id FK</td><td>المنتج</td></tr>
  <tr><td>quantity</td><td>الكمية</td></tr>
  <tr><td>unit_price</td><td>السعر وقت الشراء (snapshot)</td></tr>
</table>

<h2>4.13 نموذج عنصر السلة - CartItem</h2>
<table>
  <tr><th>الحقل</th><th>الوصف</th></tr>
  <tr><td>customer_id FK</td><td>العميل</td></tr>
  <tr><td>accessory_id FK</td><td>المنتج</td></tr>
  <tr><td>quantity</td><td>الكمية (تتجمع إذا أضاف نفس المنتج مرتين)</td></tr>
</table>

<h2>4.14 نموذج الاستشارة - Consultation</h2>
<table>
  <tr><th>الحقل</th><th>الوصف</th></tr>
  <tr><td>customer_id FK</td><td>العميل</td></tr>
  <tr><td>technician_id FK nullable</td><td>الفني (null إذا كانت استشارة AI)</td></tr>
  <tr><td>consultation_type</td><td>technician | ai</td></tr>
  <tr><td>message</td><td>رسالة العميل</td></tr>
  <tr><td>image_path</td><td>صورة مرفقة (اختياري)</td></tr>
  <tr><td>reply</td><td>رد الفني أو AI</td></tr>
  <tr><td>status</td><td>pending | answered | ai_answered</td></tr>
</table>

<h2>4.15 نموذج التوصيل - Delivery</h2>
<table>
  <tr><th>الحقل</th><th>الوصف</th></tr>
  <tr><td>type</td><td>device_pickup | device_dropoff | accessory_delivery</td></tr>
  <tr><td>delivery_worker_id FK nullable</td><td>العامل المعيّن</td></tr>
  <tr><td>customer_id FK</td><td>العميل</td></tr>
  <tr><td>shop_id FK</td><td>الصالة</td></tr>
  <tr><td>maintenance_request_id FK nullable</td><td>طلب الصيانة (إن وجد)</td></tr>
  <tr><td>order_id FK nullable</td><td>الطلبية (إن وجدت)</td></tr>
  <tr><td>status</td><td>pending|accepted|on_the_way|arrived|picked_up|in_transit|delivered|failed|rejected</td></tr>
  <tr><td>payment_method</td><td>cash_on_delivery | prepaid</td></tr>
  <tr><td>notes</td><td>ملاحظات</td></tr>
  <tr><td>estimated_time</td><td>وقت التوصيل التقديري</td></tr>
  <tr><td>confirmation_code</td><td>كود التأكيد</td></tr>
  <tr><td>confirmation_image_path</td><td>صورة التأكيد</td></tr>
  <tr><td>confirmed_at</td><td>وقت التأكيد</td></tr>
  <tr><td>cash_collected</td><td>boolean - هل جُمع الكاش</td></tr>
  <tr><td>cash_amount</td><td>المبلغ المحصّل</td></tr>
</table>

<h2>4.16 نموذج الـ OTP</h2>
<table>
  <tr><th>الحقل</th><th>الوصف</th></tr>
  <tr><td>phone</td><td>رقم الهاتف</td></tr>
  <tr><td>code</td><td>5 أرقام عشوائية</td></tr>
  <tr><td>is_used</td><td>boolean - هل استُخدم</td></tr>
  <tr><td>phone_verified</td><td>boolean - هل تم التحقق (للمستخدمين الجدد)</td></tr>
  <tr><td>expires_at</td><td>وقت الانتهاء (10 دقائق)</td></tr>
</table>

<h2>4.17 نموذج الإشعار - Notification</h2>
<table>
  <tr><th>الحقل</th><th>الوصف</th></tr>
  <tr><td>admin_id FK nullable</td><td>الأدمن المستقبِل</td></tr>
  <tr><td>technician_id FK nullable</td><td>الفني المستقبِل</td></tr>
  <tr><td>type</td><td>نوع الإشعار (نص)</td></tr>
  <tr><td>title</td><td>عنوان الإشعار</td></tr>
  <tr><td>body</td><td>نص الإشعار</td></tr>
  <tr><td>data</td><td>JSON - بيانات إضافية (id, type)</td></tr>
  <tr><td>is_read</td><td>boolean</td></tr>
</table>

<h2>4.18 نموذج User</h2>
<div class="warn-box">هذا النموذج الافتراضي من Laravel ولا يُستخدم في التطبيق. الأدوار الأربعة لها نماذج منفصلة.</div>

<div class="page-break"></div>

<!-- ===== 5. الخدمات ===== -->
<h1>5. الخدمات (Services)</h1>

<h2>5.1 OtpService - خدمة رمز التحقق</h2>
<div class="code">الملف: app/Services/OtpService.php</div>

<h3>sendOtp(string $phone): bool</h3>
<div class="section-box">
<p><strong>الغرض:</strong> إنشاء وإرسال رمز OTP للهاتف المحدد.</p>
<p><strong>الخطوات:</strong></p>
<ol>
  <li>يتحقق من Rate Limiting: إذا طُلب أكثر من <strong>3 رسائل في 60 ثانية</strong> للهاتف نفسه → يرجع false</li>
  <li>يولّد كود عشوائي 5 أرقام: <code>random_int(10000, 99999)</code></li>
  <li>يحفظ السجل في جدول <code>otps</code> مع <code>expires_at = now() + 10 دقائق</code></li>
  <li>يرسل SMS عبر Traccar API بـ cURL</li>
</ol>
<p><strong>رسالة الـ SMS:</strong> "شامسونج: كود التحقق هو XXXXX"</p>
<p><strong>Traccar API:</strong> POST https://www.traccar.org/sms/ مع Authorization header</p>
</div>

<h3>verifyOtp(string $phone, string $code, string $guard = 'customer'): array|string</h3>
<div class="section-box">
<p><strong>الغرض:</strong> التحقق من صحة الكود وتسجيل دخول المستخدم.</p>
<table>
  <tr><th>قيمة الإرجاع</th><th>المعنى</th></tr>
  <tr><td>'otp_invalid_or_expired'</td><td>الكود خاطئ أو انتهت صلاحيته</td></tr>
  <tr><td>'user_not_found'</td><td>الهاتف غير مسجل (مستخدم جديد) - يُعيّن phone_verified=true</td></tr>
  <tr><td>'account_deactivated'</td><td>الحساب موقوف (للفني وعامل التوصيل)</td></tr>
  <tr><td>['token' => ..., 'user' => ...]</td><td>نجاح - يُرجع token وبيانات المستخدم</td></tr>
</table>
<p><strong>Guard values:</strong> 'customer' | 'technician' | 'delivery'</p>
</div>

<div class="info-box">
<strong>حسابات الاختبار (Hardcoded Bypass):</strong><br>
• هاتف Apple Review: +12345678900 / كود: 12345<br>
• هاتف التطوير:    +963999999999 / كود: 99999
</div>

<h2>5.2 FcmService - خدمة إشعارات Firebase</h2>
<div class="code">الملف: app/Services/FcmService.php
بيانات الاعتماد: storage/app/firebase-credentials.json
مشروع Firebase: shamsoung-d2d58</div>

<div class="info-box">لا يوجد package خارجي - الكود يبني JWT يدوياً باستخدام PHP OpenSSL ويستدعي FCM HTTP v1 API مباشرة.</div>

<h3>send(string $token, string $title, string $body, array $data = []): void</h3>
<p>إرسال إشعار لجهاز واحد.</p>

<h3>sendMultiple(array $tokens, string $title, string $body, array $data = []): void</h3>
<p>إرسال إشعار لأجهزة متعددة (يُرسل لكل token على حدة).</p>

<h3>آلية العمل الداخلية</h3>
<ol>
  <li><strong>getAccessToken()</strong>: يتحقق من الكاش (<code>fcm_access_token</code>) - إذا موجود يرجعه</li>
  <li><strong>fetchAccessToken()</strong>: يبني JWT ويرسله لـ Google OAuth2 للحصول على access token جديد</li>
  <li><strong>buildJwt()</strong>: يبني JWT بـ RS256 باستخدام المفتاح الخاص من firebase-credentials.json</li>
  <li>يخزن الـ token في كاش لمدة <strong>55 دقيقة</strong></li>
  <li>كل FCM calls هي fire-and-forget (تُسجَّل الأخطاء ولا تُوقف الـ request)</li>
</ol>

<h3>بنية الـ data payload في كل إشعار</h3>
<div class="code">// كل إشعار يحتوي على:
"data": {
  "type": "maintenance_request" | "consultation" | "account_status" | "delivery",
  "id":   "رقم السجل المرتبط"
}</div>

<h2>5.3 ShopService - خدمة البحث عن الصالات</h2>
<div class="code">الملف: app/Services/ShopService.php</div>

<h3>findNearestShops(float $lat, float $lon, int $radiusKm = 50): Collection</h3>
<div class="section-box">
<p>يستخدم <strong>معادلة Haversine</strong> في SQL للبحث عن الصالات ضمن نطاق جغرافي.</p>
<div class="code">SELECT *, (
  6371 * acos(
    cos(radians(?)) * cos(radians(latitude))
    * cos(radians(longitude) - radians(?))
    + sin(radians(?)) * sin(radians(latitude))
  )
) AS distance
FROM shops
WHERE is_active = 1
HAVING distance &lt;= 50
ORDER BY distance ASC</div>
<p>النتيجة: قائمة صالات مرتبة من الأقرب للأبعد مع حقل <code>distance</code> بالكيلومتر.</p>
</div>

<div class="page-break"></div>

<!-- ===== 6. المتحكمات ===== -->
<h1>6. المتحكمات (Controllers)</h1>

<h2>6.1 CustomerAuthController</h2>
<div class="code">الملف: app/Http/Controllers/Api/V1/CustomerAuthController.php</div>
<table>
  <tr><th>الدالة</th><th>الـ Route</th><th>الوصف</th></tr>
  <tr><td>sendOtp()</td><td>POST /customer/send-otp</td><td>يستدعي OtpService::sendOtp - يرجع 429 إذا rate limit</td></tr>
  <tr><td>verifyOtp()</td><td>POST /customer/verify-otp</td><td>يرجع token للموجود أو is_new_user=true للجديد</td></tr>
  <tr><td>register()</td><td>POST /customer/register</td><td>يتحقق من phone_verified ثم ينشئ العميل</td></tr>
  <tr><td>logout()</td><td>POST /customer/logout</td><td>يحذف كل tokens العميل</td></tr>
  <tr><td>profile()</td><td>GET /customer/profile</td><td>بيانات العميل الحالي</td></tr>
  <tr><td>deleteAccount()</td><td>POST /customer/delete-account</td><td>يحذف الـ tokens والحساب</td></tr>
  <tr><td>updateFcmToken()</td><td>POST /customer/fcm-token</td><td>يحدّث رمز Firebase</td></tr>
</table>

<h2>6.2 MaintenanceRequestController (العميل)</h2>
<div class="code">الملف: app/Http/Controllers/Api/V1/MaintenanceRequestController.php</div>
<table>
  <tr><th>الدالة</th><th>الـ Route</th><th>الوصف</th></tr>
  <tr><td>store()</td><td>POST /maintenance-requests</td><td>ينشئ الطلب، يُرسل FCM لفنيي الصالة + DB notification للأدمن</td></tr>
  <tr><td>index()</td><td>GET /maintenance-requests</td><td>طلبات العميل (paginated 15)</td></tr>
  <tr><td>show()</td><td>GET /maintenance-requests/{id}</td><td>تفاصيل طلب محدد مع القطع</td></tr>
  <tr><td>parts()</td><td>GET /maintenance-requests/{id}/parts</td><td>القطع المقترحة من التشخيص</td></tr>
  <tr><td>cancel()</td><td>POST /maintenance-requests/{id}/cancel</td><td>إلغاء (فقط إذا status=pending) + FCM للفنيين</td></tr>
  <tr><td>approve()</td><td>POST /maintenance-requests/{id}/approve</td><td>موافقة + تحقق من القطع الإلزامية + خصم المخزون + FCM</td></tr>
  <tr><td>reject()</td><td>POST /maintenance-requests/{id}/reject</td><td>رفض + سبب الرفض + FCM للفنيين</td></tr>
</table>

<h3>تفاصيل دالة approve()</h3>
<div class="section-box">
<ol>
  <li>يتحقق أن كل القطع الإلزامية (is_required=true) موجودة في selected_parts[]</li>
  <li>يحسب estimated_cost = مجموع (price × quantity) للقطع المختارة</li>
  <li>ينقص stock_quantity من جدول spare_parts لكل قطعة مختارة</li>
  <li>يُغير status → "approved" و customer_status → "approved"</li>
  <li>يرسل FCM لفنيي الصالة</li>
  <li>كل هذا داخل DB::transaction()</li>
</ol>
</div>

<h2>6.3 CartController (العميل)</h2>
<table>
  <tr><th>الدالة</th><th>Route</th><th>الوصف</th></tr>
  <tr><td>index()</td><td>GET /cart</td><td>محتويات السلة مع بيانات المنتج والصالة</td></tr>
  <tr><td>store()</td><td>POST /cart</td><td>إضافة منتج - يتحقق من المخزون - يجمع الكمية إذا موجود</td></tr>
  <tr><td>destroy()</td><td>DELETE /cart/{id}</td><td>حذف عنصر من السلة</td></tr>
</table>

<h2>6.4 OrderController (العميل)</h2>
<table>
  <tr><th>الدالة</th><th>Route</th><th>الوصف</th></tr>
  <tr><td>checkout()</td><td>POST /checkout</td><td>تحويل السلة لطلبيات + إنشاء توصيلات + خصم المخزون</td></tr>
  <tr><td>index()</td><td>GET /orders</td><td>سجل طلبيات العميل</td></tr>
</table>

<h3>تفاصيل دالة checkout()</h3>
<div class="section-box">
<ol>
  <li>يجمع عناصر السلة حسب shop_id (كل صالة = طلبية منفصلة)</li>
  <li>لكل صالة: ينشئ Order + OrderItems + ينقص stock_quantity + ينشئ Delivery من نوع accessory_delivery</li>
  <li>يمسح السلة كاملة</li>
  <li>يرسل FCM للعميل: "تم إنشاء طلب التوصيل"</li>
  <li>كل هذا داخل DB::transaction() - إذا فشل أي خطوة يُتراجع كل شيء</li>
</ol>
</div>

<h2>6.5 Customer/ConsultationController</h2>
<table>
  <tr><th>الدالة</th><th>Route</th><th>الوصف</th></tr>
  <tr><td>store()</td><td>POST /consultations</td><td>إنشاء استشارة AI أو فني</td></tr>
  <tr><td>index()</td><td>GET /consultations</td><td>استشارات العميل</td></tr>
</table>

<h3>منطق store() - نوعان من الاستشارة:</h3>
<div class="section-box">
<p><strong>نوع AI (consultation_type = 'ai'):</strong></p>
<ol>
  <li>يجرب النماذج: gemini-2.5-flash-lite ثم gemini-flash-latest</li>
  <li>يدور على مفاتيح GEMINI_API_KEYS (مفصولة بفاصلة) إذا فشل مفتاح</li>
  <li>يحفظ الرد في حقل reply و status → ai_answered</li>
</ol>
<p><strong>نوع فني (consultation_type = 'technician'):</strong></p>
<ol>
  <li>يُرسل FCM لكل الفنيين النشطين الذين لديهم fcm_token</li>
  <li>يُنشئ DB notification للأدمن</li>
  <li>status → pending</li>
</ol>
</div>

<h2>6.6 UnifiedAuthController</h2>
<div class="code">POST /api/v1/auth/login  { "email": "...", "password": "..." }</div>
<ol>
  <li>يبحث في جدول admins أولاً - إذا وجد وكلمة المرور صحيحة → role: admin</li>
  <li>يبحث في جدول technicians - إذا وجد وكلمة المرور صحيحة وهو نشط → role: technician</li>
  <li>إذا لم يجد: 401 Invalid credentials</li>
</ol>

<h2>6.7 Admin/TechnicianController</h2>
<table>
  <tr><th>الدالة</th><th>Route</th><th>الوصف</th></tr>
  <tr><td>index()</td><td>GET /admin/technicians</td><td>قائمة الفنيين مع بيانات الصالة (paginated 15)</td></tr>
  <tr><td>store()</td><td>POST /admin/technicians</td><td>إنشاء فني - كلمة مرور عشوائية أو مخصصة - يرجع temporary_password</td></tr>
  <tr><td>update()</td><td>POST /admin/technicians/{id}</td><td>تعديل بيانات + FCM إذا تغيرت is_active</td></tr>
  <tr><td>destroy()</td><td>DELETE أو POST /admin/technicians/{id}/delete</td><td>حذف الفني</td></tr>
</table>

<h2>6.8 Admin/SparePartController</h2>
<table>
  <tr><th>الدالة</th><th>Route</th><th>الوصف</th></tr>
  <tr><td>index()</td><td>GET /admin/spare-parts</td><td>قائمة قطع الغيار مع بيانات الصالة</td></tr>
  <tr><td>store()</td><td>POST /admin/spare-parts</td><td>إضافة قطعة جديدة</td></tr>
  <tr><td>update()</td><td>PUT /admin/spare-parts/{id}</td><td>تعديل قطعة</td></tr>
  <tr><td>destroy()</td><td>DELETE /admin/spare-parts/{id}</td><td>حذف قطعة</td></tr>
  <tr><td>indexStockRequests()</td><td>GET /admin/stock-requests</td><td>طلبات التزويد بالمخزون</td></tr>
  <tr><td>requestStock()</td><td>POST /admin/stock-requests</td><td>طلب تزويد جديد + DB notification للأدمن</td></tr>
  <tr><td>approveStockRequest()</td><td>POST /admin/stock-requests/{id}/approve</td><td>موافقة + زيادة stock_quantity</td></tr>
</table>

<h2>6.9 Admin/AdminDeliveryController</h2>
<table>
  <tr><th>الدالة</th><th>Route</th><th>الوصف</th></tr>
  <tr><td>index()</td><td>GET /admin/deliveries</td><td>كل التوصيلات مع فلاتر</td></tr>
  <tr><td>store()</td><td>POST /admin/deliveries</td><td>إنشاء توصيلة + FCM للعميل + DB notification للأدمن</td></tr>
  <tr><td>update()</td><td>PATCH /admin/deliveries/{id}</td><td>تعديل التوصيلة (التحقق من shop_id لعامل التوصيل)</td></tr>
</table>

<h2>6.10 Admin/AdminDashboardController</h2>
<div class="code">GET /api/v1/admin/dashboard/stats</div>
<p>يُرجع:</p>
<ul>
  <li>إجمالي طلبات الصيانة (كل الحالات) + نسبة التغيير الأسبوعي</li>
  <li>الطلبات المنجزة + نسبة التغيير الأسبوعي</li>
  <li>الإيرادات الإجمالية (صيانة + ملحقات) + نسبة التغيير الأسبوعي</li>
  <li>عدد العملاء + الفنيين + الصالات النشطة</li>
</ul>

<h2>6.11 Technician/MaintenanceRequestController</h2>
<table>
  <tr><th>الدالة</th><th>Route</th><th>الوصف</th></tr>
  <tr><td>index()</td><td>GET /technician/maintenance-requests</td><td>طلبات صالة الفني فقط</td></tr>
  <tr><td>show()</td><td>GET /technician/maintenance-requests/{id}</td><td>تفاصيل مع القطع (مقيّد بـ shop_id الفني)</td></tr>
  <tr><td>updateStatus()</td><td>POST /technician/maintenance-requests/{id}/status</td><td>يقبل: under_inspection أو completed فقط + FCM للعميل</td></tr>
  <tr><td>diagnose()</td><td>POST /technician/maintenance-requests/{id}/diagnose</td><td>يُرسل التشخيص + يُغير الحالة + FCM للعميل</td></tr>
</table>

<h3>تفاصيل دالة diagnose()</h3>
<div class="section-box">
<p>Body المطلوب: <code>{ "estimated_days": 3, "parts": [{"spare_part_id": 1, "quantity": 2, "is_required": true}] }</code></p>
<ol>
  <li>يحذف القطع السابقة للطلب (إن وجدت)</li>
  <li>لكل قطعة: يجلب بيانات SparePart وينسخ name وprice إلى MaintenanceRequestPart</li>
  <li>يُغير status → waiting_customer_approval و customer_status → pending_approval</li>
  <li>يرسل FCM للعميل: "Diagnosis Ready"</li>
  <li>كل هذا في DB::transaction()</li>
</ol>
</div>

<h2>6.12 Delivery/DeliveryController</h2>
<table>
  <tr><th>الدالة</th><th>Route</th><th>الوصف</th></tr>
  <tr><td>index()</td><td>GET /delivery/requests</td><td>التوصيلات pending بدون عامل مُعيّن</td></tr>
  <tr><td>accept()</td><td>POST /delivery/requests/{id}/accept</td><td>قبول التوصيلة + status → accepted + FCM للعميل</td></tr>
  <tr><td>reject()</td><td>POST /delivery/requests/{id}/reject</td><td>رفض + تحرير التوصيلة</td></tr>
  <tr><td>show()</td><td>GET /delivery/requests/{id}</td><td>تفاصيل توصيلة</td></tr>
  <tr><td>updateStatus()</td><td>POST /delivery/requests/{id}/status</td><td>on_the_way أو arrived + FCM للعميل</td></tr>
  <tr><td>confirm()</td><td>POST /delivery/requests/{id}/confirm</td><td>تأكيد التسليم (كود أو صورة) + FCM للعميل والأدمن</td></tr>
  <tr><td>collectCash()</td><td>POST /delivery/requests/{id}/collect-cash</td><td>تسجيل تحصيل الكاش + FCM للأدمن</td></tr>
  <tr><td>history()</td><td>GET /delivery/history</td><td>توصيلات مكتملة لعامل التوصيل</td></tr>
  <tr><td>earnings()</td><td>GET /delivery/earnings</td><td>أرباح اليوم</td></tr>
</table>

<div class="page-break"></div>

<!-- ===== 7. جدول الـ Routes ===== -->
<h1>7. جدول كل الـ Routes</h1>

<h2>7.1 المصادقة العامة</h2>
<table>
  <tr><th>Method</th><th>URL</th><th>Auth</th><th>الوصف</th></tr>
  <tr><td><span class="method-post">POST</span></td><td>/api/v1/auth/login</td><td>-</td><td>دخول Admin / Technician بـ email+password</td></tr>
</table>

<h2>7.2 routes العميل</h2>
<table>
  <tr><th>Method</th><th>URL</th><th>Auth</th><th>الوصف</th></tr>
  <tr><td><span class="method-post">POST</span></td><td>/api/v1/customer/send-otp</td><td>-</td><td>إرسال OTP</td></tr>
  <tr><td><span class="method-post">POST</span></td><td>/api/v1/customer/verify-otp</td><td>-</td><td>التحقق من OTP</td></tr>
  <tr><td><span class="method-post">POST</span></td><td>/api/v1/customer/register</td><td>-</td><td>تسجيل جديد</td></tr>
  <tr><td><span class="method-post">POST</span></td><td>/api/v1/customer/logout</td><td>✓</td><td>تسجيل خروج</td></tr>
  <tr><td><span class="method-get">GET</span></td><td>/api/v1/customer/profile</td><td>✓</td><td>الملف الشخصي</td></tr>
  <tr><td><span class="method-post">POST</span></td><td>/api/v1/customer/delete-account</td><td>✓</td><td>حذف الحساب</td></tr>
  <tr><td><span class="method-post">POST</span></td><td>/api/v1/customer/fcm-token</td><td>✓</td><td>تحديث رمز FCM</td></tr>
  <tr><td><span class="method-get">GET</span></td><td>/api/v1/customer/deliveries</td><td>✓</td><td>توصيلاتي</td></tr>
  <tr><td><span class="method-get">GET</span></td><td>/api/v1/customer/deliveries/{id}</td><td>✓</td><td>تفاصيل توصيلة</td></tr>
  <tr><td><span class="method-post">POST</span></td><td>/api/v1/shops/nearest</td><td>✓</td><td>أقرب صالة (lat,lon)</td></tr>
  <tr><td><span class="method-post">POST</span></td><td>/api/v1/maintenance-requests</td><td>✓</td><td>إنشاء طلب صيانة</td></tr>
  <tr><td><span class="method-get">GET</span></td><td>/api/v1/maintenance-requests</td><td>✓</td><td>طلباتي</td></tr>
  <tr><td><span class="method-get">GET</span></td><td>/api/v1/maintenance-requests/{id}</td><td>✓</td><td>تفاصيل طلب</td></tr>
  <tr><td><span class="method-get">GET</span></td><td>/api/v1/maintenance-requests/{id}/parts</td><td>✓</td><td>قطع التشخيص</td></tr>
  <tr><td><span class="method-post">POST</span></td><td>/api/v1/maintenance-requests/{id}/cancel</td><td>✓</td><td>إلغاء الطلب</td></tr>
  <tr><td><span class="method-post">POST</span></td><td>/api/v1/maintenance-requests/{id}/approve</td><td>✓</td><td>موافقة على التشخيص</td></tr>
  <tr><td><span class="method-post">POST</span></td><td>/api/v1/maintenance-requests/{id}/reject</td><td>✓</td><td>رفض التشخيص</td></tr>
  <tr><td><span class="method-get">GET</span></td><td>/api/v1/accessories</td><td>✓</td><td>الملحقات (فلتر shop_id اختياري)</td></tr>
  <tr><td><span class="method-get">GET</span></td><td>/api/v1/accessories/{id}</td><td>✓</td><td>تفاصيل ملحق</td></tr>
  <tr><td><span class="method-get">GET</span></td><td>/api/v1/cart</td><td>✓</td><td>السلة</td></tr>
  <tr><td><span class="method-post">POST</span></td><td>/api/v1/cart</td><td>✓</td><td>إضافة للسلة</td></tr>
  <tr><td><span class="method-delete">DELETE</span></td><td>/api/v1/cart/{id}</td><td>✓</td><td>حذف من السلة</td></tr>
  <tr><td><span class="method-post">POST</span></td><td>/api/v1/checkout</td><td>✓</td><td>إتمام الشراء</td></tr>
  <tr><td><span class="method-get">GET</span></td><td>/api/v1/orders</td><td>✓</td><td>طلبياتي</td></tr>
  <tr><td><span class="method-post">POST</span></td><td>/api/v1/consultations</td><td>✓</td><td>إنشاء استشارة</td></tr>
  <tr><td><span class="method-get">GET</span></td><td>/api/v1/consultations</td><td>✓</td><td>استشاراتي</td></tr>
</table>

<h2>7.3 routes الفني</h2>
<table>
  <tr><th>Method</th><th>URL</th><th>Auth</th><th>الوصف</th></tr>
  <tr><td><span class="method-post">POST</span></td><td>/api/v1/technician/send-otp</td><td>-</td><td>إرسال OTP</td></tr>
  <tr><td><span class="method-post">POST</span></td><td>/api/v1/technician/verify-otp</td><td>-</td><td>التحقق من OTP</td></tr>
  <tr><td><span class="method-post">POST</span></td><td>/api/v1/technician/logout</td><td>✓</td><td>تسجيل خروج</td></tr>
  <tr><td><span class="method-get">GET</span></td><td>/api/v1/technician/profile</td><td>✓</td><td>الملف الشخصي مع الصالة</td></tr>
  <tr><td><span class="method-post">POST</span></td><td>/api/v1/technician/fcm-token</td><td>✓</td><td>تحديث FCM</td></tr>
  <tr><td><span class="method-get">GET</span></td><td>/api/v1/technician/maintenance-requests</td><td>✓</td><td>طلبات صالته</td></tr>
  <tr><td><span class="method-get">GET</span></td><td>/api/v1/technician/maintenance-requests/{id}</td><td>✓</td><td>تفاصيل طلب</td></tr>
  <tr><td><span class="method-post">POST</span></td><td>/api/v1/technician/maintenance-requests/{id}/status</td><td>✓</td><td>تغيير الحالة</td></tr>
  <tr><td><span class="method-post">POST</span></td><td>/api/v1/technician/maintenance-requests/{id}/diagnose</td><td>✓</td><td>إرسال التشخيص</td></tr>
  <tr><td><span class="method-get">GET</span></td><td>/api/v1/technician/spare-parts</td><td>✓</td><td>قطع غيار صالته</td></tr>
  <tr><td><span class="method-get">GET</span></td><td>/api/v1/technician/consultations</td><td>✓</td><td>استشارات الفني</td></tr>
  <tr><td><span class="method-post">POST</span></td><td>/api/v1/technician/consultations/{id}/reply</td><td>✓</td><td>الرد على استشارة</td></tr>
  <tr><td><span class="method-get">GET</span></td><td>/api/v1/technician/notifications</td><td>✓</td><td>الإشعارات (آخر 20)</td></tr>
  <tr><td><span class="method-post">POST</span></td><td>/api/v1/technician/notifications/mark-all-read</td><td>✓</td><td>تعليم الكل مقروء</td></tr>
  <tr><td><span class="method-post">POST</span></td><td>/api/v1/technician/notifications/{id}/read</td><td>✓</td><td>تعليم مقروء</td></tr>
</table>

<h2>7.4 routes عامل التوصيل</h2>
<table>
  <tr><th>Method</th><th>URL</th><th>Auth</th><th>الوصف</th></tr>
  <tr><td><span class="method-post">POST</span></td><td>/api/v1/delivery/send-otp</td><td>-</td><td>إرسال OTP</td></tr>
  <tr><td><span class="method-post">POST</span></td><td>/api/v1/delivery/verify-otp</td><td>-</td><td>التحقق من OTP</td></tr>
  <tr><td><span class="method-post">POST</span></td><td>/api/v1/delivery/logout</td><td>✓</td><td>تسجيل خروج</td></tr>
  <tr><td><span class="method-get">GET</span></td><td>/api/v1/delivery/profile</td><td>✓</td><td>الملف الشخصي</td></tr>
  <tr><td><span class="method-post">POST</span></td><td>/api/v1/delivery/fcm-token</td><td>✓</td><td>تحديث FCM</td></tr>
  <tr><td><span class="method-get">GET</span></td><td>/api/v1/delivery/requests</td><td>✓</td><td>التوصيلات المتاحة</td></tr>
  <tr><td><span class="method-get">GET</span></td><td>/api/v1/delivery/requests/{id}</td><td>✓</td><td>تفاصيل توصيلة</td></tr>
  <tr><td><span class="method-post">POST</span></td><td>/api/v1/delivery/requests/{id}/accept</td><td>✓</td><td>قبول التوصيلة</td></tr>
  <tr><td><span class="method-post">POST</span></td><td>/api/v1/delivery/requests/{id}/reject</td><td>✓</td><td>رفض التوصيلة</td></tr>
  <tr><td><span class="method-post">POST</span></td><td>/api/v1/delivery/requests/{id}/status</td><td>✓</td><td>تحديث الحالة</td></tr>
  <tr><td><span class="method-post">POST</span></td><td>/api/v1/delivery/requests/{id}/confirm</td><td>✓</td><td>تأكيد التسليم</td></tr>
  <tr><td><span class="method-post">POST</span></td><td>/api/v1/delivery/requests/{id}/collect-cash</td><td>✓</td><td>تسجيل تحصيل الكاش</td></tr>
  <tr><td><span class="method-get">GET</span></td><td>/api/v1/delivery/history</td><td>✓</td><td>سجل التوصيلات</td></tr>
  <tr><td><span class="method-get">GET</span></td><td>/api/v1/delivery/earnings</td><td>✓</td><td>أرباح اليوم</td></tr>
</table>

<h2>7.5 routes الأدمن</h2>
<table>
  <tr><th>Method</th><th>URL</th><th>الوصف</th></tr>
  <tr><td><span class="method-post">POST</span></td><td>/api/v1/admin/login</td><td>دخول (deprecated - استخدم /auth/login)</td></tr>
  <tr><td><span class="method-get">GET</span></td><td>/api/v1/admin/profile</td><td>الملف الشخصي</td></tr>
  <tr><td><span class="method-post">POST</span></td><td>/api/v1/admin/profile</td><td>تعديل الملف الشخصي</td></tr>
  <tr><td><span class="method-post">POST</span></td><td>/api/v1/admin/change-password</td><td>تغيير كلمة المرور</td></tr>
  <tr><td><span class="method-post">POST</span></td><td>/api/v1/admin/logout</td><td>تسجيل خروج</td></tr>
  <tr><td><span class="method-get">GET</span></td><td>/api/v1/admin/dashboard/stats</td><td>إحصائيات لوحة التحكم</td></tr>
  <tr><td><span class="method-get">GET</span></td><td>/api/v1/admin/shops</td><td>كل الصالات</td></tr>
  <tr><td><span class="method-post">POST</span></td><td>/api/v1/admin/shops</td><td>إضافة صالة + صورة</td></tr>
  <tr><td><span class="method-put">PUT</span></td><td>/api/v1/admin/shops/{id}</td><td>تعديل صالة</td></tr>
  <tr><td><span class="method-delete">DELETE</span></td><td>/api/v1/admin/shops/{id}</td><td>حذف صالة</td></tr>
  <tr><td><span class="method-post">POST</span></td><td>/api/v1/admin/shops/{id}/delete</td><td>حذف صالة (بديل POST)</td></tr>
  <tr><td><span class="method-get">GET</span></td><td>/api/v1/admin/technicians</td><td>كل الفنيين</td></tr>
  <tr><td><span class="method-post">POST</span></td><td>/api/v1/admin/technicians</td><td>إضافة فني</td></tr>
  <tr><td><span class="method-post">POST</span></td><td>/api/v1/admin/technicians/{id}</td><td>تعديل فني</td></tr>
  <tr><td><span class="method-delete">DELETE</span></td><td>/api/v1/admin/technicians/{id}</td><td>حذف فني</td></tr>
  <tr><td><span class="method-post">POST</span></td><td>/api/v1/admin/technicians/{id}/delete</td><td>حذف فني (بديل POST)</td></tr>
  <tr><td><span class="method-get">GET</span></td><td>/api/v1/admin/spare-parts</td><td>قطع الغيار</td></tr>
  <tr><td><span class="method-post">POST</span></td><td>/api/v1/admin/spare-parts</td><td>إضافة قطعة</td></tr>
  <tr><td><span class="method-put">PUT</span></td><td>/api/v1/admin/spare-parts/{id}</td><td>تعديل قطعة</td></tr>
  <tr><td><span class="method-delete">DELETE</span></td><td>/api/v1/admin/spare-parts/{id}</td><td>حذف قطعة</td></tr>
  <tr><td><span class="method-post">POST</span></td><td>/api/v1/admin/spare-parts/{id}/delete</td><td>حذف قطعة (بديل POST)</td></tr>
  <tr><td><span class="method-get">GET</span></td><td>/api/v1/admin/stock-requests</td><td>طلبات التزويد</td></tr>
  <tr><td><span class="method-post">POST</span></td><td>/api/v1/admin/stock-requests</td><td>إنشاء طلب تزويد</td></tr>
  <tr><td><span class="method-post">POST</span></td><td>/api/v1/admin/stock-requests/{id}/approve</td><td>موافقة على طلب تزويد</td></tr>
  <tr><td><span class="method-get">GET</span></td><td>/api/v1/admin/accessories</td><td>الملحقات</td></tr>
  <tr><td><span class="method-post">POST</span></td><td>/api/v1/admin/accessories</td><td>إضافة ملحق + صورة</td></tr>
  <tr><td><span class="method-put">PUT</span></td><td>/api/v1/admin/accessories/{id}</td><td>تعديل ملحق</td></tr>
  <tr><td><span class="method-delete">DELETE</span></td><td>/api/v1/admin/accessories/{id}</td><td>حذف ملحق</td></tr>
  <tr><td><span class="method-post">POST</span></td><td>/api/v1/admin/accessories/{id}/delete</td><td>حذف ملحق (بديل POST)</td></tr>
  <tr><td><span class="method-get">GET</span></td><td>/api/v1/admin/delivery-workers</td><td>عمال التوصيل</td></tr>
  <tr><td><span class="method-post">POST</span></td><td>/api/v1/admin/delivery-workers</td><td>إضافة عامل</td></tr>
  <tr><td><span class="method-put">PUT</span></td><td>/api/v1/admin/delivery-workers/{id}</td><td>تعديل عامل</td></tr>
  <tr><td><span class="method-delete">DELETE</span></td><td>/api/v1/admin/delivery-workers/{id}</td><td>حذف عامل</td></tr>
  <tr><td><span class="method-post">POST</span></td><td>/api/v1/admin/delivery-workers/{id}/delete</td><td>حذف عامل (بديل POST)</td></tr>
  <tr><td><span class="method-get">GET</span></td><td>/api/v1/admin/notifications</td><td>إشعارات الأدمن</td></tr>
  <tr><td><span class="method-post">POST</span></td><td>/api/v1/admin/notifications/mark-all-read</td><td>تعليم الكل مقروء</td></tr>
  <tr><td><span class="method-post">POST</span></td><td>/api/v1/admin/notifications/{id}/read</td><td>تعليم مقروء</td></tr>
  <tr><td><span class="method-get">GET</span></td><td>/api/v1/admin/maintenance-requests</td><td>كل طلبات الصيانة</td></tr>
  <tr><td><span class="method-get">GET</span></td><td>/api/v1/admin/orders</td><td>كل الطلبيات</td></tr>
  <tr><td><span class="method-get">GET</span></td><td>/api/v1/admin/deliveries</td><td>كل التوصيلات</td></tr>
  <tr><td><span class="method-post">POST</span></td><td>/api/v1/admin/deliveries</td><td>إنشاء توصيلة</td></tr>
  <tr><td><span class="method-put">PUT</span></td><td>/api/v1/admin/deliveries/{id}</td><td>تعديل توصيلة</td></tr>
</table>

<div class="page-break"></div>

<!-- ===== 8. Form Requests ===== -->
<h1>8. Form Requests - قواعد التحقق</h1>

<table>
  <tr><th>الكلاس</th><th>الحقل</th><th>القواعد</th></tr>
  <tr><td rowspan="2">SendOtpRequest</td><td>phone</td><td>required, regex: /^\+963[0-9]{9}$/</td></tr>
  <tr><td>-</td><td>الهاتف يجب أن يبدأ بـ +963 ويليه 9 أرقام</td></tr>
  <tr><td rowspan="2">VerifyOtpRequest</td><td>phone</td><td>required, regex: /^\+963[0-9]{9}$/</td></tr>
  <tr><td>code</td><td>required, string, size:5</td></tr>
  <tr><td rowspan="3">StoreMaintenanceRequest</td><td>shop_id</td><td>required, exists:shops,id</td></tr>
  <tr><td>device_model</td><td>required, string, max:255</td></tr>
  <tr><td>problem_description</td><td>required, string</td></tr>
  <tr><td rowspan="2">ApproveMaintenanceRequest</td><td>selected_parts</td><td>required, array, exists:maintenance_request_parts,id</td></tr>
  <tr><td>payment_method</td><td>required, in:cash_on_delivery,pay_after_service</td></tr>
  <tr><td>RejectMaintenanceRequest</td><td>rejection_reason</td><td>nullable, string</td></tr>
  <tr><td rowspan="3">StoreConsultationRequest</td><td>consultation_type</td><td>required, in:technician,ai</td></tr>
  <tr><td>message</td><td>required_without:image, nullable</td></tr>
  <tr><td>image</td><td>required_without:message, nullable, image, max:5120</td></tr>
  <tr><td rowspan="3">DiagnoseRequest (Technician)</td><td>estimated_days</td><td>required, integer, min:1</td></tr>
  <tr><td>parts[].spare_part_id</td><td>required, integer, exists:spare_parts,id</td></tr>
  <tr><td>parts[].quantity / is_required</td><td>required, integer min:1 / required, boolean</td></tr>
  <tr><td>UpdateStatusRequest (Technician)</td><td>status</td><td>required, in:under_inspection,completed</td></tr>
  <tr><td>ReplyConsultationRequest</td><td>reply</td><td>required, string, min:5</td></tr>
  <tr><td rowspan="5">StoreTechnicianRequest</td><td>first_name / last_name</td><td>required, string, max:255</td></tr>
  <tr><td>phone</td><td>required, regex:+963..., unique:technicians</td></tr>
  <tr><td>email</td><td>nullable, email, unique:technicians</td></tr>
  <tr><td>shop_id</td><td>required, exists:shops,id</td></tr>
  <tr><td>specialization / experience</td><td>required, string, max:255</td></tr>
  <tr><td rowspan="4">StoreShopRequest</td><td>name / address</td><td>required, string</td></tr>
  <tr><td>latitude / longitude</td><td>required, numeric</td></tr>
  <tr><td>phone</td><td>required, string</td></tr>
  <tr><td>image</td><td>nullable, image, max:5120</td></tr>
  <tr><td rowspan="3">StoreDeliveryRequest (Admin)</td><td>type</td><td>required, in:device_pickup,device_dropoff,accessory_delivery</td></tr>
  <tr><td>customer_id / shop_id</td><td>required, exists</td></tr>
  <tr><td>maintenance_request_id / order_id</td><td>nullable, exists</td></tr>
  <tr><td>CheckoutRequest</td><td>payment_method</td><td>required, in:cash_on_delivery,pay_after_service</td></tr>
  <tr><td>ConfirmDeliveryRequest</td><td>confirmation_code / image</td><td>أحدهما مطلوب (required_without)</td></tr>
  <tr><td>CollectCashRequest</td><td>cash_amount</td><td>required, numeric, min:0</td></tr>
</table>

<div class="page-break"></div>

<!-- ===== 9. آلة الحالات ===== -->
<h1>9. آلة حالات طلب الصيانة</h1>

<div class="info-box">
كل طلب صيانة يمر بسلسلة من الحالات المحددة. لا يمكن تخطي أي خطوة.
</div>

<h2>مخطط الحالات</h2>
<table>
  <tr><th>الحالة</th><th>من يغيرها</th><th>الـ Endpoint</th><th>الشرط</th></tr>
  <tr><td><span class="badge badge-yellow">pending</span></td><td>تلقائي</td><td>POST /maintenance-requests</td><td>عند إنشاء الطلب</td></tr>
  <tr><td><span class="badge badge-blue">under_inspection</span></td><td>الفني</td><td>POST /{id}/status</td><td>status = "under_inspection"</td></tr>
  <tr><td><span class="badge badge-yellow">waiting_customer_approval</span></td><td>الفني (تلقائي)</td><td>POST /{id}/diagnose</td><td>بعد إرسال التشخيص</td></tr>
  <tr><td><span class="badge badge-blue">approved</span></td><td>العميل</td><td>POST /{id}/approve</td><td>customer_status → approved</td></tr>
  <tr><td><span class="badge badge-green">completed</span></td><td>الفني</td><td>POST /{id}/status</td><td>status = "completed"</td></tr>
  <tr><td><span class="badge badge-red">cancelled</span></td><td>العميل</td><td>POST /{id}/cancel أو /reject</td><td>cancel: فقط من pending / reject: من waiting</td></tr>
</table>

<h2>حالة العميل (customer_status)</h2>
<table>
  <tr><th>القيمة</th><th>المعنى</th></tr>
  <tr><td>null</td><td>لم يصل للتشخيص بعد</td></tr>
  <tr><td>pending_approval</td><td>ينتظر قرار العميل (بعد التشخيص)</td></tr>
  <tr><td>approved</td><td>وافق العميل</td></tr>
  <tr><td>rejected</td><td>رفض العميل</td></tr>
</table>

<h2>تفاصيل التشخيص (diagnose)</h2>
<div class="section-box">
<p>الفني يرسل: <code>{ estimated_days: N, parts: [{spare_part_id, quantity, is_required}] }</code></p>
<p>النظام ينسخ name وprice تلقائياً من جدول spare_parts إلى maintenance_request_parts.</p>
<p>العميل يرى القطع ويختار منها → القطع الإلزامية يجب اختيارها كلها → عند الموافقة ينقص المخزون.</p>
</div>

<h2>آلة حالات التوصيل</h2>
<table>
  <tr><th>الحالة</th><th>من يغيرها</th><th>الـ Endpoint</th></tr>
  <tr><td>pending</td><td>تلقائي</td><td>عند إنشاء التوصيلة</td></tr>
  <tr><td>accepted</td><td>عامل التوصيل</td><td>POST /delivery/requests/{id}/accept</td></tr>
  <tr><td>on_the_way</td><td>عامل التوصيل</td><td>POST /delivery/requests/{id}/status</td></tr>
  <tr><td>arrived</td><td>عامل التوصيل</td><td>POST /delivery/requests/{id}/status</td></tr>
  <tr><td>delivered</td><td>عامل التوصيل (تلقائي)</td><td>POST /delivery/requests/{id}/confirm</td></tr>
  <tr><td>rejected</td><td>عامل التوصيل</td><td>POST /delivery/requests/{id}/reject</td></tr>
</table>

<div class="page-break"></div>

<!-- ===== 10. نظام الإشعارات ===== -->
<h1>10. نظام الإشعارات FCM</h1>

<h2>تسجيل رمز FCM</h2>
<div class="code">POST /api/v1/customer/fcm-token    { "fcm_token": "..." }
POST /api/v1/technician/fcm-token  { "fcm_token": "..." }
POST /api/v1/delivery/fcm-token    { "fcm_token": "..." }
POST /api/v1/admin/update-fcm-token { "fcm_token": "..." }</div>

<h2>مصفوفة الإشعارات الكاملة</h2>
<table>
  <tr><th>الحدث</th><th>من يفعّله</th><th>المستقبِل</th><th>العنوان</th></tr>
  <tr><td>إنشاء طلب صيانة</td><td>العميل</td><td>فنيو الصالة + DB للأدمن</td><td>New Maintenance Request</td></tr>
  <tr><td>إلغاء الطلب</td><td>العميل</td><td>فنيو الصالة</td><td>Request Cancelled</td></tr>
  <tr><td>موافقة على التشخيص</td><td>العميل</td><td>فنيو الصالة</td><td>Diagnosis Approved</td></tr>
  <tr><td>رفض التشخيص</td><td>العميل</td><td>فنيو الصالة</td><td>Diagnosis Rejected</td></tr>
  <tr><td>إنشاء استشارة فني</td><td>العميل</td><td>كل الفنيين النشطين</td><td>New Consultation Request</td></tr>
  <tr><td>تغيير الحالة → under_inspection</td><td>الفني</td><td>العميل</td><td>Request Under Inspection</td></tr>
  <tr><td>إرسال التشخيص</td><td>الفني</td><td>العميل</td><td>Diagnosis Ready</td></tr>
  <tr><td>تغيير الحالة → completed</td><td>الفني</td><td>العميل</td><td>Repair Completed</td></tr>
  <tr><td>الرد على استشارة</td><td>الفني</td><td>العميل</td><td>Consultation Answered</td></tr>
  <tr><td>تفعيل الفني</td><td>الأدمن</td><td>الفني</td><td>Account Activated</td></tr>
  <tr><td>تعطيل الفني</td><td>الأدمن</td><td>الفني</td><td>Account Deactivated</td></tr>
  <tr><td>تفعيل عامل التوصيل</td><td>الأدمن</td><td>عامل التوصيل</td><td>Account Activated</td></tr>
  <tr><td>تعطيل عامل التوصيل</td><td>الأدمن</td><td>عامل التوصيل</td><td>Account Deactivated</td></tr>
  <tr><td>إنشاء توصيلة</td><td>الأدمن</td><td>العميل</td><td>تم إنشاء طلب التوصيل</td></tr>
  <tr><td>قبول التوصيلة</td><td>عامل التوصيل</td><td>العميل</td><td>عامل التوصيل في الطريق إليك</td></tr>
  <tr><td>حالة → on_the_way</td><td>عامل التوصيل</td><td>العميل</td><td>عامل التوصيل في الطريق</td></tr>
  <tr><td>حالة → arrived</td><td>عامل التوصيل</td><td>العميل</td><td>عامل التوصيل وصل</td></tr>
  <tr><td>تأكيد التسليم</td><td>عامل التوصيل</td><td>العميل + الأدمن</td><td>تم التوصيل بنجاح</td></tr>
  <tr><td>تحصيل الكاش</td><td>عامل التوصيل</td><td>الأدمن</td><td>Cash Collected</td></tr>
  <tr><td>إنشاء طلب تزويد مخزون</td><td>-</td><td>الأدمن (DB فقط)</td><td>Stock Request</td></tr>
  <tr><td>إتمام checkout</td><td>العميل</td><td>العميل</td><td>تم إنشاء طلب التوصيل</td></tr>
</table>

<h2>بنية الـ data في كل إشعار</h2>
<div class="code">// إشعار صيانة:
{ "type": "maintenance_request", "id": "123" }

// إشعار استشارة:
{ "type": "consultation", "id": "45" }

// إشعار حساب:
{ "type": "account_status", "is_active": "1" }

// إشعار توصيل:
{ "type": "delivery", "id": "67" }</div>

<div class="page-break"></div>

<!-- ===== 11. Migrations ===== -->
<h1>11. جداول قاعدة البيانات (Migrations)</h1>

<table>
  <tr><th>التاريخ</th><th>الجدول / العملية</th><th>أبرز الأعمدة</th></tr>
  <tr><td>2026-05-14</td><td>إنشاء customers</td><td>id, first_name, last_name, email unique, password nullable, phone unique, birthdate, fcm_token</td></tr>
  <tr><td>2026-05-14</td><td>إنشاء technicians</td><td>id, shop_id FK nullable, first_name, last_name, email unique nullable, password nullable, phone unique, specialization, experience, is_active</td></tr>
  <tr><td>2026-05-14</td><td>إنشاء shops</td><td>id, name, address, phone, latitude, longitude, is_active, image_path, rating</td></tr>
  <tr><td>2026-05-14</td><td>إنشاء maintenance_requests</td><td>tracking_number unique, customer_id, shop_id, device_model, problem_description, status, estimated_cost, estimated_days</td></tr>
  <tr><td>2026-05-14</td><td>إنشاء accessories</td><td>shop_id, name, description, price, stock_quantity, image_url, is_active</td></tr>
  <tr><td>2026-05-14</td><td>إنشاء cart_items</td><td>customer_id FK, accessory_id FK, quantity</td></tr>
  <tr><td>2026-05-14</td><td>إنشاء orders</td><td>order_number unique, customer_id, shop_id, total_amount, payment_method ENUM, status</td></tr>
  <tr><td>2026-05-14</td><td>إنشاء order_items</td><td>order_id, accessory_id, quantity, unit_price</td></tr>
  <tr><td>2026-05-15</td><td>إنشاء spare_parts</td><td>shop_id, name, price, stock_quantity</td></tr>
  <tr><td>2026-05-15</td><td>إنشاء maintenance_request_parts</td><td>maintenance_request_id, spare_part_id nullable, name, price, quantity, is_required, is_selected</td></tr>
  <tr><td>2026-05-16</td><td>إنشاء consultations</td><td>customer_id, technician_id nullable, consultation_type ENUM, message, image_path, reply, status</td></tr>
  <tr><td>2026-05-16</td><td>إنشاء admins</td><td>id, name, email unique, password, fcm_token</td></tr>
  <tr><td>2026-05-16</td><td>إنشاء stock_requests</td><td>shop_id, spare_part_id, quantity, status ENUM(pending/approved/rejected)</td></tr>
  <tr><td>2026-06-08</td><td>إنشاء otps</td><td>phone, code(5), is_used, phone_verified, expires_at</td></tr>
  <tr><td>2026-06-08</td><td>تعديل maintenance_requests</td><td>إضافة: customer_status, rejection_reason, payment_method</td></tr>
  <tr><td>2026-06-08</td><td>تعديل orders</td><td>payment_method → ENUM(cash_on_delivery, pay_after_service)</td></tr>
  <tr><td>2026-06-08</td><td>إضافة fcm_token</td><td>إضافة fcm_token nullable لـ customers وtechnicians</td></tr>
  <tr><td>2026-06-10</td><td>إنشاء delivery_workers</td><td>shop_id, first_name, last_name, email, phone, password nullable, specialization, experience, is_active, fcm_token</td></tr>
  <tr><td>2026-06-10</td><td>إنشاء deliveries</td><td>type ENUM, delivery_worker_id nullable, customer_id, shop_id, maintenance_request_id nullable, order_id nullable, status ENUM, payment_method, confirmation_code, confirmed_at, cash_collected, cash_amount</td></tr>
  <tr><td>2026-06-10</td><td>إضافة fcm_token للـ admins</td><td>fcm_token nullable</td></tr>
  <tr><td>2026-06-11</td><td>حذف unit_price</td><td>DROP COLUMN unit_price من maintenance_request_parts</td></tr>
  <tr><td>2026-06-13</td><td>توسيع حالات deliveries</td><td>إضافة: picked_up, in_transit, failed, rejected</td></tr>
  <tr><td>2026-06-13</td><td>إنشاء notifications</td><td>admin_id nullable, technician_id nullable, type, title, body, data JSON, is_read</td></tr>
  <tr><td>2026-06-17</td><td>تعديل technicians</td><td>email → nullable</td></tr>
</table>

<div class="warn-box">
<strong>مهم:</strong> جميع التعديلات على الأعمدة تستخدم <code>DB::statement('ALTER TABLE ... MODIFY COLUMN ...')</code> وليس <code>\$table->change()</code>، لأن MySQL لا يدعم الأخيرة بشكل موثوق في Laravel 12.
</div>

<div class="page-break"></div>

<!-- ===== 12. اتفاقية الاستجابات ===== -->
<h1>12. اتفاقية الاستجابات</h1>

<h2>الشكل العام لكل الاستجابات</h2>
<div class="code">// نجاح
{
  "message": "نص وصفي",
  "data": { ... }  // أو [] للقوائم
}

// خطأ
{
  "message": "وصف الخطأ"
}

// خطأ التحقق (422)
{
  "message": "The given data was invalid.",
  "errors": {
    "phone": ["يجب أن يبدأ الهاتف بـ +963"]
  }
}</div>

<h2>رموز HTTP المستخدمة</h2>
<table>
  <tr><th>الرمز</th><th>المعنى</th><th>مثال</th></tr>
  <tr><td>200</td><td>ناجح</td><td>جلب بيانات، تسجيل دخول</td></tr>
  <tr><td>201</td><td>أُنشئ بنجاح</td><td>إنشاء طلب، تسجيل مستخدم</td></tr>
  <tr><td>400</td><td>طلب خاطئ</td><td>السلة فارغة، مخزون غير كافٍ</td></tr>
  <tr><td>401</td><td>غير مصادق</td><td>Token مفقود أو منتهي</td></tr>
  <tr><td>403</td><td>محظور</td><td>حساب موقوف، OTP غير مُتحقَّق</td></tr>
  <tr><td>404</td><td>غير موجود</td><td>هاتف غير مسجل، سجل غير موجود</td></tr>
  <tr><td>422</td><td>خطأ تحقق</td><td>حقل مفقود أو تنسيق خاطئ</td></tr>
  <tr><td>429</td><td>طلبات كثيرة</td><td>تجاوز حد الـ OTP (3 في 60 ثانية)</td></tr>
</table>

<h2>Pagination</h2>
<p>كل endpoint يُرجع قائمة paginated يُرجع هذا الشكل:</p>
<div class="code">{
  "message": "...",
  "data": {
    "current_page": 1,
    "data": [...],
    "last_page": 5,
    "per_page": 15,
    "total": 73,
    "next_page_url": "...",
    "prev_page_url": null
  }
}</div>
<p>للتنقل بين الصفحات: <code>GET /endpoint?page=2</code></p>

<div class="page-break"></div>

<!-- ===== 13. حسابات الاختبار ===== -->
<h1>13. حسابات الاختبار والبيئة</h1>

<h2>حسابات الاختبار</h2>
<table>
  <tr><th>الغرض</th><th>الهاتف / البريد</th><th>كلمة المرور / OTP</th><th>ملاحظة</th></tr>
  <tr><td>Apple Review</td><td>+12345678900</td><td>12345</td><td>OTP ثابت في OtpService</td></tr>
  <tr><td>تطوير / اختبار</td><td>+963999999999</td><td>99999</td><td>OTP ثابت في OtpService</td></tr>
  <tr><td>Admin</td><td>admin@shamsung.com</td><td>password123</td><td>من AdminSeeder</td></tr>
</table>

<h2>routes التشغيل (web.php)</h2>
<div class="warn-box">هذه الـ routes للنشر فقط وتعمل بمفتاح سري. يُفضَّل حذفها بعد الاستخدام.</div>
<table>
  <tr><th>الرابط</th><th>الوصف</th></tr>
  <tr><td>/deploy/cache-clear/shamsung_deploy_2026</td><td>مسح الكاش</td></tr>
  <tr><td>/deploy/migrate/shamsung_deploy_2026</td><td>تشغيل الـ migrations</td></tr>
  <tr><td>/deploy/composer/shamsung_deploy_2026</td><td>تثبيت الـ packages</td></tr>
  <tr><td>/deploy/seed-technician/shamsung_deploy_2026</td><td>إضافة فني تجريبي</td></tr>
  <tr><td>/deploy/reset-tech-password/{key}/{email}/{password}</td><td>إعادة تعيين كلمة مرور فني</td></tr>
</table>

<h2>متغيرات البيئة المهمة (.env)</h2>
<table>
  <tr><th>المتغير</th><th>الوصف</th></tr>
  <tr><td>APP_KEY</td><td>مفتاح التشفير (php artisan key:generate)</td></tr>
  <tr><td>DB_CONNECTION / DB_HOST / DB_DATABASE</td><td>إعدادات MySQL</td></tr>
  <tr><td>GEMINI_API_KEYS</td><td>مفاتيح Gemini مفصولة بفاصلة</td></tr>
  <tr><td>SANCTUM_STATEFUL_DOMAINS</td><td>النطاقات المسموح بها</td></tr>
  <tr><td>FILESYSTEM_DISK</td><td>local (الصور تُحفظ في storage)</td></tr>
</table>

<h2>الأوامر المهمة</h2>
<div class="code">php artisan serve              # تشغيل السيرفر
php artisan migrate            # تشغيل migrations
php artisan db:seed --class=AdminSeeder  # إنشاء حساب Admin
php artisan storage:link       # ربط مجلد الصور
./vendor/bin/pint              # تنسيق الكود
composer test                  # تشغيل Tests</div>

</body>
</html>
HTML;
    }
}
