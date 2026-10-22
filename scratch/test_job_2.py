import urllib.request
import urllib.parse
import http.cookiejar
import re

cj = http.cookiejar.CookieJar()
opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(cj))

# 1. GET /login
resp = opener.open("https://securevision-crm.onrender.com/login")
html = resp.read().decode('utf-8')
match = re.search(r'name="_token"\s+value="([^"]+)"', html)
token = match.group(1) if match else None

# 2. POST /login
data = urllib.parse.urlencode({
    '_token': token,
    'email': 'test@example.com',
    'password': 'password'
}).encode('utf-8')
req = urllib.request.Request("https://securevision-crm.onrender.com/login", data=data)
resp2 = opener.open(req)
print("Logged in, status:", resp2.getcode())

# 3. GET /jobs/2
try:
    resp3 = opener.open("https://securevision-crm.onrender.com/jobs/2")
    print("GET /jobs/2 status:", resp3.getcode())
    print("Content preview:", resp3.read().decode('utf-8')[:300])
except urllib.error.HTTPError as e:
    print("GET /jobs/2 error code:", e.code)
    body = e.read().decode('utf-8', errors='ignore')
    print("Error body:")
    print(body[:2000])
except Exception as e:
    print("Error:", e)
