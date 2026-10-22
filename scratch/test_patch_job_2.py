import urllib.request
import urllib.parse
import http.cookiejar
import re

cj = http.cookiejar.CookieJar()
opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(cj))

# 1. Login as Admin
resp = opener.open("https://securevision-crm.onrender.com/login")
html = resp.read().decode('utf-8')
match = re.search(r'name="_token"\s+value="([^"]+)"', html)
token = match.group(1)

data = urllib.parse.urlencode({
    '_token': token,
    'email': 'test@example.com',
    'password': 'password'
}).encode('utf-8')
opener.open(urllib.request.Request("https://securevision-crm.onrender.com/login", data=data))
print("Admin logged in successfully.")

# 2. Get /jobs/2 to extract token
resp_job = opener.open("https://securevision-crm.onrender.com/jobs/2")
html_job = resp_job.read().decode('utf-8')
match_job = re.search(r'name="_token"\s+value="([^"]+)"', html_job)
token_job = match_job.group(1)

# 3. Submit PATCH to /jobs/2 with status 'assigned' and technician 3
patch_data = urllib.parse.urlencode({
    '_token': token_job,
    '_method': 'PATCH',
    'scheduled_date': '2026-10-25',
    'assigned_technician_id': '3', # Bob
    'status': 'assigned',
    'installation_notes': 'Testing field deployment'
}).encode('utf-8')

req_patch = urllib.request.Request("https://securevision-crm.onrender.com/jobs/2", data=patch_data)
try:
    resp_patch = opener.open(req_patch)
    print("PATCH /jobs/2 status:", resp_patch.getcode(), resp_patch.geturl())
except urllib.error.HTTPError as e:
    print("PATCH /jobs/2 HTTP error:", e.code)
    body = e.read().decode('utf-8', errors='ignore')
    print("Error body excerpt:", body[:1000])
except Exception as e:
    print("Error:", e)
