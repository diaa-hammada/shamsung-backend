import json

with open('Shamsung API v1.postman_collection.json', 'r', encoding='utf-8') as f:
    collection = json.load(f)

def find_folder(items, name):
    for item in items:
        if item.get('name') == name:
            return item
    return None

customer_send_otp = {
    "name": "Customer Send OTP",
    "request": {
        "method": "POST",
        "header": [
            {"key": "Content-Type", "value": "application/json", "type": "text"},
            {"key": "Accept", "value": "application/json", "type": "text"}
        ],
        "body": {
            "mode": "raw",
            "raw": "{\r\n    \"phone\": \"+963933111222\"\r\n}",
            "options": {"raw": {"language": "json"}}
        },
        "url": {
            "raw": "{{base_url}}/customer/send-otp",
            "host": ["{{base_url}}"],
            "path": ["customer", "send-otp"]
        }
    },
    "response": []
}

customer_verify_otp = {
    "name": "Customer Verify OTP",
    "request": {
        "method": "POST",
        "header": [
            {"key": "Content-Type", "value": "application/json", "type": "text"},
            {"key": "Accept", "value": "application/json", "type": "text"}
        ],
        "body": {
            "mode": "raw",
            "raw": "{\r\n    \"phone\": \"+963933111222\",\r\n    \"code\": \"12345\"\r\n}",
            "options": {"raw": {"language": "json"}}
        },
        "url": {
            "raw": "{{base_url}}/customer/verify-otp",
            "host": ["{{base_url}}"],
            "path": ["customer", "verify-otp"]
        }
    },
    "response": []
}

tech_send_otp = {
    "name": "Technician Send OTP",
    "request": {
        "method": "POST",
        "header": [
            {"key": "Content-Type", "value": "application/json", "type": "text"},
            {"key": "Accept", "value": "application/json", "type": "text"}
        ],
        "body": {
            "mode": "raw",
            "raw": "{\r\n    \"phone\": \"+963999999999\"\r\n}",
            "options": {"raw": {"language": "json"}}
        },
        "url": {
            "raw": "{{base_url}}/technician/send-otp",
            "host": ["{{base_url}}"],
            "path": ["technician", "send-otp"]
        }
    },
    "response": []
}

tech_verify_otp = {
    "name": "Technician Verify OTP",
    "request": {
        "method": "POST",
        "header": [
            {"key": "Content-Type", "value": "application/json", "type": "text"},
            {"key": "Accept", "value": "application/json", "type": "text"}
        ],
        "body": {
            "mode": "raw",
            "raw": "{\r\n    \"phone\": \"+963999999999\",\r\n    \"code\": \"99999\"\r\n}",
            "options": {"raw": {"language": "json"}}
        },
        "url": {
            "raw": "{{base_url}}/technician/verify-otp",
            "host": ["{{base_url}}"],
            "path": ["technician", "verify-otp"]
        }
    },
    "response": []
}

tech_logout = {
    "name": "Technician Logout",
    "request": {
        "method": "POST",
        "header": [
            {"key": "Accept", "value": "application/json", "type": "text"}
        ],
        "url": {
            "raw": "{{base_url}}/technician/logout",
            "host": ["{{base_url}}"],
            "path": ["technician", "logout"]
        }
    },
    "response": []
}

# === CUSTOMER APP ===
customer_app = find_folder(collection['item'], 'Customer App')
customer_auth = find_folder(customer_app['item'], 'Authentication')

register_item = next(i for i in customer_auth['item'] if i['name'] == 'Customer Register')
logout_item   = next(i for i in customer_auth['item'] if i['name'] == 'Customer Logout')
delete_item   = next(i for i in customer_auth['item'] if i['name'] == 'Delete Account')
profile_item  = next(i for i in customer_auth['item'] if i['name'] == 'Get Customer Profile')

# Update phone in Register main body (0933111222 -> +963933111222)
register_item['request']['body']['raw'] = register_item['request']['body']['raw'].replace('0933111222', '+963933111222')

# Set new order: Send OTP -> Verify OTP -> Register -> Logout -> Profile -> Delete Account
customer_auth['item'] = [customer_send_otp, customer_verify_otp, register_item, logout_item, profile_item, delete_item]

# === TECHNICIAN WEB ===
tech_web  = find_folder(collection['item'], 'Technician Web')
tech_auth = find_folder(tech_web['item'], 'Authentication')

profile_tech = next(i for i in tech_auth['item'] if i['name'] == 'Technician Profile')

# Set new order: Send OTP -> Verify OTP -> Logout -> Profile
tech_auth['item'] = [tech_send_otp, tech_verify_otp, tech_logout, profile_tech]

with open('Shamsung API v1.postman_collection.json', 'w', encoding='utf-8') as f:
    json.dump(collection, f, ensure_ascii=False, indent=2)

print("Done!")
