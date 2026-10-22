import urllib.request
import urllib.parse
import http.cookiejar
import re

users_to_test = [
    ('guest', None, None),
    ('technician', 'bob@example.com', 'password'),
    ('staff', 'alex@example.com', 'password'),
    ('reviewer', 'reviewer@cctvcrm.com', 'reviewer123'),
]

for role, email, password in users_to_test:
    cj = http.cookiejar.CookieJar()
    opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(cj))
    print(f"\n--- Testing as {role} ({email}) ---")

    if email:
        try:
            resp = opener.open("https://securevision-crm.onrender.com/login")
            html = resp.read().decode('utf-8')
            match = re.search(r'name="_token"\s+value="([^"]+)"', html)
            token = match.group(1) if match else None

            data = urllib.parse.urlencode({
                '_token': token,
                'email': email,
                'password': password
            }).encode('utf-8')
            req = urllib.request.Request("https://securevision-crm.onrender.com/login", data=data)
            resp2 = opener.open(req)
            print("Login response:", resp2.getcode(), resp2.geturl())
        except Exception as e:
            print("Login failed:", e)

    try:
        req_job = urllib.request.Request("https://securevision-crm.onrender.com/jobs/2")
        resp_job = opener.open(req_job)
        print("GET /jobs/2 result:", resp_job.getcode(), resp_job.geturl())
    except urllib.error.HTTPError as e:
        print("GET /jobs/2 HTTP error:", e.code)
        body = e.read().decode('utf-8', errors='ignore')
        # check if laravel error page
        print("Body snippet:", body[:500])
    except Exception as e:
        print("GET /jobs/2 error:", e)
