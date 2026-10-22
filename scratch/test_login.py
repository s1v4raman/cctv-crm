import urllib.request
import urllib.parse
import http.cookiejar
import re

cj = http.cookiejar.CookieJar()
opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(cj))

# 1. GET /login
try:
    resp = opener.open("https://securevision-crm.onrender.com/login")
    html = resp.read().decode('utf-8')
    match = re.search(r'name="_token"\s+value="([^"]+)"', html)
    token = match.group(1) if match else None
    print("Fetched CSRF Token:", token)
except Exception as e:
    print("GET error:", e)
    token = None

# 2. POST /login
if token:
    data = urllib.parse.urlencode({
        '_token': token,
        'email': 'test@example.com',
        'password': 'password'
    }).encode('utf-8')
    req = urllib.request.Request("https://securevision-crm.onrender.com/login", data=data)
    try:
        resp2 = opener.open(req)
        print("POST status:", resp2.getcode())
        print("Redirected to:", resp2.geturl())
    except urllib.error.HTTPError as e:
        print("POST HTTP error:", e.code)
        body = e.read().decode('utf-8', errors='ignore')
        print("Error body excerpt:", body[:500])
    except Exception as e:
        print("POST error:", e)
