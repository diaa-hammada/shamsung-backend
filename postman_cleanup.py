"""
Full cleanup/audit of Shamsung API v1 Postman collection.
Run with: python postman_cleanup.py
"""
import json, copy, io, sys
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8')

PATH = r'C:\xampp\htdocs\shamsung\Shamsung API v1.postman_collection.json'

with open(PATH, 'r', encoding='utf-8') as f:
    col = json.load(f)

log = []


# ─────────────────────────────────────────────────────────────────────────────
# Helpers
# ─────────────────────────────────────────────────────────────────────────────

def get_folder(root_items, *names):
    """Walk nested folders by name."""
    items = root_items
    for name in names:
        folder = next((i for i in items if i.get('name') == name and 'item' in i), None)
        if folder is None:
            raise KeyError(f"Folder not found: {name!r}")
        items = folder['item']
    return folder  # returns the last folder object


def make_fcm_request(method, url_suffix, role_label):
    """Build a POST /fcm-token request object."""
    url_raw = '{{local_url}}/' + url_suffix
    path = url_suffix.split('/')
    return {
        'name': f'Update FCM Token',
        'request': {
            'method': 'POST',
            'header': [
                {'key': 'Authorization', 'value': 'Bearer {{auth_token}}', 'type': 'text'},
                {'key': 'Accept', 'value': 'application/json', 'type': 'text'},
            ],
            'body': {
                'mode': 'raw',
                'raw': '{\r\n    "fcm_token": "fGH3kL9...device_token"\r\n}',
                'options': {'raw': {'language': 'json'}},
            },
            'url': {
                'raw': url_raw,
                'host': ['{{local_url}}'],
                'path': path,
            },
            'description': (
                f'Register or refresh the FCM device token for the authenticated {role_label}. '
                'Call this once after login and whenever the device token is refreshed by FCM. '
                'Required for push notification delivery.'
            ),
        },
        'response': [
            {
                'name': '✅ 200 OK',
                'originalRequest': {
                    'method': 'POST',
                    'header': [
                        {'key': 'Authorization', 'value': 'Bearer {{auth_token}}', 'type': 'text'},
                        {'key': 'Accept', 'value': 'application/json', 'type': 'text'},
                    ],
                    'body': {
                        'mode': 'raw',
                        'raw': '{\r\n    "fcm_token": "fGH3kL9...device_token"\r\n}',
                        'options': {'raw': {'language': 'json'}},
                    },
                    'url': {'raw': url_raw, 'host': ['{{local_url}}'], 'path': path},
                },
                'status': 'OK',
                'code': 200,
                '_postman_previewlanguage': 'json',
                'header': [{'key': 'Content-Type', 'value': 'application/json'}],
                'cookie': [],
                'body': '{\n    "message": "FCM token updated successfully"\n}',
            },
            {
                'name': '❌ 422 Missing Token',
                'originalRequest': {
                    'method': 'POST',
                    'header': [
                        {'key': 'Authorization', 'value': 'Bearer {{auth_token}}', 'type': 'text'},
                        {'key': 'Accept', 'value': 'application/json', 'type': 'text'},
                    ],
                    'body': {
                        'mode': 'raw',
                        'raw': '{}',
                        'options': {'raw': {'language': 'json'}},
                    },
                    'url': {'raw': url_raw, 'host': ['{{local_url}}'], 'path': path},
                },
                'status': 'Unprocessable Content',
                'code': 422,
                '_postman_previewlanguage': 'json',
                'header': [{'key': 'Content-Type', 'value': 'application/json'}],
                'cookie': [],
                'body': '{\n    "message": "The fcm token field is required.",\n    "errors": {\n        "fcm_token": ["The fcm token field is required."]\n    }\n}',
            },
        ],
    }


# ─────────────────────────────────────────────────────────────────────────────
# 1. Grab the Unified Login request (deep copy — we'll insert it in 2 places)
# ─────────────────────────────────────────────────────────────────────────────

top_auth_folder = get_folder(col['item'], 'Authentication')
unified_login = copy.deepcopy(top_auth_folder['item'][0])
assert unified_login['name'] == 'Unified Login'
log.append('Extracted: Unified Login (will be duplicated into Admin Web & Technician Web)')


# ─────────────────────────────────────────────────────────────────────────────
# 2. DELETE: Technician Web > Authentication — remove OTP items
# ─────────────────────────────────────────────────────────────────────────────

tech_auth = get_folder(col['item'], 'Technician Web', 'Authentication')
to_delete_tech = ['Technician Send OTP', 'Technician Verify OTP']
before = len(tech_auth['item'])
tech_auth['item'] = [i for i in tech_auth['item'] if i['name'] not in to_delete_tech]
deleted = before - len(tech_auth['item'])
for name in to_delete_tech:
    log.append(f'DELETED: Technician Web > Authentication > {name}')


# ─────────────────────────────────────────────────────────────────────────────
# 3. DELETE: Admin Web > Authentication — remove "Admin Login (Deprecated)"
# ─────────────────────────────────────────────────────────────────────────────

admin_auth = get_folder(col['item'], 'Admin Web', 'Authentication')
to_delete_admin = ['Admin Login (Deprecated)']
admin_auth['item'] = [i for i in admin_auth['item'] if i['name'] not in to_delete_admin]
log.append('DELETED: Admin Web > Authentication > Admin Login (Deprecated)')


# ─────────────────────────────────────────────────────────────────────────────
# 4. DELETE: Technician Web > Maintenance Requests > "Add Spare Parts & Estimate"
#    (orphan — POST /technician/maintenance-requests/{id}/parts does not exist)
# ─────────────────────────────────────────────────────────────────────────────

tech_mr = get_folder(col['item'], 'Technician Web', 'Maintenance Requests')
before = len(tech_mr['item'])
tech_mr['item'] = [i for i in tech_mr['item'] if i['name'] != 'Add Spare Parts & Estimate']
if len(tech_mr['item']) < before:
    log.append('DELETED: Technician Web > Maintenance Requests > Add Spare Parts & Estimate (orphan — no matching backend route)')


# ─────────────────────────────────────────────────────────────────────────────
# 5. SEARCH & DELETE any remaining "(Deprecated)" items anywhere in collection
# ─────────────────────────────────────────────────────────────────────────────

def purge_deprecated(items, folder_path=''):
    removed = []
    kept = []
    for item in items:
        if 'item' in item:
            item['item'], sub_removed = purge_deprecated(item['item'], folder_path + ' > ' + item['name'])
            removed.extend(sub_removed)
            kept.append(item)
        elif '(Deprecated)' in item.get('name', '') or '(deprecated)' in item.get('name', '').lower():
            removed.append(folder_path + ' > ' + item['name'])
        else:
            kept.append(item)
    return kept, removed

col['item'], extra_deprecated = purge_deprecated(col['item'])
for name in extra_deprecated:
    log.append(f'DELETED (sweep): {name}')


# ─────────────────────────────────────────────────────────────────────────────
# 6. MOVE: "Get Shop Spare Parts" from Technician Web > Maintenance Requests
#          → Technician Web > Inventory
# ─────────────────────────────────────────────────────────────────────────────

tech_mr = get_folder(col['item'], 'Technician Web', 'Maintenance Requests')
tech_inv = get_folder(col['item'], 'Technician Web', 'Inventory')

spare_parts_item = next((i for i in tech_mr['item'] if i['name'] == 'Get Shop Spare Parts'), None)
if spare_parts_item:
    tech_mr['item'] = [i for i in tech_mr['item'] if i['name'] != 'Get Shop Spare Parts']
    tech_inv['item'].append(spare_parts_item)
    log.append('MOVED: Technician Web > Maintenance Requests > Get Shop Spare Parts → Technician Web > Inventory')


# ─────────────────────────────────────────────────────────────────────────────
# 7. FIX URLs: Admin Notifications — {{base_url}} → {{local_url}}, remove /api/v1 doubling
# ─────────────────────────────────────────────────────────────────────────────

def fix_url(url_obj):
    """Normalize {{base_url}} → {{local_url}} and strip double /api/v1 in path."""
    if isinstance(url_obj, str):
        return url_obj.replace('{{base_url}}', '{{local_url}}').replace('/api/v1/', '/', 1)
    if isinstance(url_obj, dict):
        raw = url_obj.get('raw', '')
        raw = raw.replace('{{base_url}}', '{{local_url}}')
        # Remove leading api/v1 from path segments if present
        path = url_obj.get('path', [])
        if path and path[0] == 'api' and len(path) > 1 and path[1] == 'v1':
            path = path[2:]
        raw = raw.replace('{{base_url}}/api/v1/', '{{local_url}}/')
        raw = raw.replace('{{local_url}}/api/v1/', '{{local_url}}/')
        url_obj['raw'] = raw
        url_obj['host'] = ['{{local_url}}']
        url_obj['path'] = path
    return url_obj


def fix_request_urls(items, folder_path=''):
    for item in items:
        if 'item' in item:
            fix_request_urls(item['item'], folder_path + ' > ' + item['name'])
        else:
            req = item.get('request', {})
            url = req.get('url', {})
            if isinstance(url, dict):
                raw_before = url.get('raw', '')
                fix_url(url)
                if raw_before != url.get('raw', ''):
                    log.append(f'FIXED URL: {folder_path} > {item["name"]}  [{raw_before}] → [{url["raw"]}]')
            # Fix URLs in example responses too
            for resp in item.get('response', []):
                orig = resp.get('originalRequest', {})
                orig_url = orig.get('url', {})
                if isinstance(orig_url, dict):
                    fix_url(orig_url)


fix_request_urls(col['item'])


# ─────────────────────────────────────────────────────────────────────────────
# 8. DUPLICATE Unified Login → Admin Web > Authentication (prepend)
# ─────────────────────────────────────────────────────────────────────────────

admin_auth = get_folder(col['item'], 'Admin Web', 'Authentication')
admin_login_copy = copy.deepcopy(unified_login)
admin_login_copy['name'] = 'Login (Unified)'
admin_auth['item'].insert(0, admin_login_copy)
log.append('ADDED: Admin Web > Authentication > Login (Unified) [copy of Unified Login]')


# ─────────────────────────────────────────────────────────────────────────────
# 9. DUPLICATE Unified Login → Technician Web > Authentication (prepend)
# ─────────────────────────────────────────────────────────────────────────────

tech_auth = get_folder(col['item'], 'Technician Web', 'Authentication')
tech_login_copy = copy.deepcopy(unified_login)
tech_login_copy['name'] = 'Login (Unified)'
tech_auth['item'].insert(0, tech_login_copy)
log.append('ADDED: Technician Web > Authentication > Login (Unified) [copy of Unified Login]')


# ─────────────────────────────────────────────────────────────────────────────
# 10. ADD: POST /customer/fcm-token → Customer App > Authentication
# ─────────────────────────────────────────────────────────────────────────────

customer_auth = get_folder(col['item'], 'Customer App', 'Authentication')
# Check not already present
if not any(i.get('name') == 'Update FCM Token' for i in customer_auth['item']):
    customer_auth['item'].append(make_fcm_request('POST', 'customer/fcm-token', 'customer'))
    log.append('ADDED: Customer App > Authentication > Update FCM Token (POST /customer/fcm-token)')


# ─────────────────────────────────────────────────────────────────────────────
# 11. ADD: POST /technician/fcm-token → Technician Web > Authentication
# ─────────────────────────────────────────────────────────────────────────────

tech_auth = get_folder(col['item'], 'Technician Web', 'Authentication')
if not any(i.get('name') == 'Update FCM Token' for i in tech_auth['item']):
    tech_auth['item'].append(make_fcm_request('POST', 'technician/fcm-token', 'technician'))
    log.append('ADDED: Technician Web > Authentication > Update FCM Token (POST /technician/fcm-token)')


# ─────────────────────────────────────────────────────────────────────────────
# 12. ADD: POST /admin/update-fcm-token → Admin Web > Authentication
# ─────────────────────────────────────────────────────────────────────────────

admin_auth = get_folder(col['item'], 'Admin Web', 'Authentication')
if not any(i.get('name') == 'Update FCM Token' for i in admin_auth['item']):
    admin_auth['item'].append(make_fcm_request('POST', 'admin/update-fcm-token', 'admin'))
    log.append('ADDED: Admin Web > Authentication > Update FCM Token (POST /admin/update-fcm-token)')


# ─────────────────────────────────────────────────────────────────────────────
# 13. KEEP top-level "Authentication" folder (canonical, no change needed)
# ─────────────────────────────────────────────────────────────────────────────
log.append('KEPT: Top-level Authentication > Unified Login (canonical reference, no change)')


# ─────────────────────────────────────────────────────────────────────────────
# Save
# ─────────────────────────────────────────────────────────────────────────────

with open(PATH, 'w', encoding='utf-8') as f:
    json.dump(col, f, ensure_ascii=False, indent=2)

print('=== CHANGES MADE ===')
for entry in log:
    print(' •', entry)

print()
print('=== FINAL STRUCTURE ===')

def print_tree(items, indent=0):
    for item in items:
        marker = '[F]' if 'item' in item else '[R]'
        print(' ' * indent + marker + ' ' + item['name'])
        if 'item' in item:
            print_tree(item['item'], indent + 2)

print_tree(col['item'])
