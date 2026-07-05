import json, io, sys
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8')

with open(r'C:\xampp\htdocs\shamsung\Shamsung API v1.postman_collection.json', 'r', encoding='utf-8') as f:
    col = json.load(f)

tech_folder = next(i for i in col['item'] if i['name'] == 'Technician Web')

# Remove existing Notifications folder if any (idempotent)
tech_folder['item'] = [i for i in tech_folder['item'] if i['name'] != 'Notifications']

base_url_parts = ['{{base_url}}']
base_path = ['api', 'v1', 'technician', 'notifications']

def make_request(method, path_suffix='', body=None):
    path = base_path + ([path_suffix] if path_suffix else [])
    raw = '{{base_url}}/api/v1/technician/notifications' + ('/' + path_suffix if path_suffix else '')
    req = {
        'method': method,
        'header': [{'key': 'Authorization', 'value': 'Bearer {{auth_token}}', 'type': 'text'}],
        'url': {'raw': raw, 'host': base_url_parts, 'path': path},
    }
    if body:
        req['body'] = body
    return req

notifications_folder = {
    'name': 'Notifications',
    'item': [
        {
            'name': 'Get Notifications',
            'request': {
                **make_request('GET'),
                'description': 'Returns the 20 most recent notifications for the authenticated technician plus an unread count.\n\nTriggered by: customer creates a technician-type consultation (type=new_consultation).',
            },
            'response': [{
                'name': 'Success',
                'status': 'OK',
                'code': 200,
                'header': [{'key': 'Content-Type', 'value': 'application/json'}],
                '_postman_previewlanguage': 'json',
                'originalRequest': make_request('GET'),
                'body': json.dumps({
                    'message': 'Notifications retrieved successfully',
                    'data': {
                        'unread_count': 2,
                        'notifications': [
                            {
                                'id': 3,
                                'type': 'new_consultation',
                                'title': 'استشارة جديدة',
                                'body': 'أرسل محمد علي: شاشة هاتفي توقفت عن العمل فجأة',
                                'data': {'consultation_id': 7, 'customer_id': 9},
                                'is_read': False,
                                'created_at': '2026-06-14T10:30:00.000000Z',
                            },
                            {
                                'id': 1,
                                'type': 'new_consultation',
                                'title': 'استشارة جديدة',
                                'body': 'أرسل سارة حسن: البطارية تنتهي بسرعة كبيرة',
                                'data': {'consultation_id': 5, 'customer_id': 3},
                                'is_read': True,
                                'created_at': '2026-06-13T08:15:00.000000Z',
                            },
                        ],
                    },
                }, ensure_ascii=False, indent=2),
            }],
        },
        {
            'name': 'Mark Notification as Read',
            'request': {
                **make_request('POST', '1/read'),
                'description': 'Mark a single notification as read. Returns 404 if the notification does not belong to the authenticated technician.',
            },
            'response': [{
                'name': 'Success',
                'status': 'OK',
                'code': 200,
                'header': [{'key': 'Content-Type', 'value': 'application/json'}],
                '_postman_previewlanguage': 'json',
                'originalRequest': make_request('POST', '1/read'),
                'body': json.dumps({'message': 'Notification marked as read'}, ensure_ascii=False, indent=2),
            }],
        },
        {
            'name': 'Mark All Notifications as Read',
            'request': {
                **make_request('POST', 'mark-all-read'),
                'description': 'Marks all unread notifications for the authenticated technician as read in one shot.',
            },
            'response': [{
                'name': 'Success',
                'status': 'OK',
                'code': 200,
                'header': [{'key': 'Content-Type', 'value': 'application/json'}],
                '_postman_previewlanguage': 'json',
                'originalRequest': make_request('POST', 'mark-all-read'),
                'body': json.dumps({'message': 'All notifications marked as read'}, ensure_ascii=False, indent=2),
            }],
        },
    ],
}

tech_folder['item'].append(notifications_folder)

with open(r'C:\xampp\htdocs\shamsung\Shamsung API v1.postman_collection.json', 'w', encoding='utf-8') as f:
    json.dump(col, f, ensure_ascii=False, indent=2)

print('Done. Technician folders:', [i['name'] for i in tech_folder['item']])
