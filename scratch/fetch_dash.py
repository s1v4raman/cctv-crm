import urllib.request, http.cookiejar, re

cj = http.cookiejar.CookieJar()
opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(cj))

# First get login page to grab csrf token
resp = opener.open('http://127.0.0.1:8001/login')
content = resp.read().decode('utf-8')
token_m = re.search(r'name="_token"\s+value="([^"]+)"', content)
if not token_m:
    token_m = re.search(r'csrf-token"\s+content="([^"]+)"', content)
csrf = token_m.group(1) if token_m else ''

# Login
login_data = f'_token={csrf}&email=admin@cctv.com&password=password'.encode('utf-8')
req = urllib.request.Request('http://127.0.0.1:8001/login', data=login_data)
resp = opener.open(req)

# Get dashboard
resp = opener.open('http://127.0.0.1:8001/dashboard')
dash_html = resp.read().decode('utf-8')

with open('scratch/rendered_dashboard.html', 'w', encoding='utf-8') as f:
    f.write(dash_html)

scripts = re.findall(r'<script(?:\s+[^>]*)?>(.*?)</script>', dash_html, re.DOTALL)
print('Total scripts in rendered dashboard:', len(scripts))
for i, s in enumerate(scripts):
    if '&amp;' in s:
        print(f'Script {i} contains &amp; in rendered HTML!')
        for l in s.splitlines():
            if '&amp;' in l:
                print('  ', l.strip()[:100])
