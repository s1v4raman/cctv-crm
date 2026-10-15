import re, subprocess, tempfile

def check_file(path):
    with open(path, 'r', encoding='utf-8') as f:
        text = f.read()
    scripts = re.findall(r'<script(?:\s+[^>]*)?>(.*?)</script>', text, re.DOTALL)
    for i, s in enumerate(scripts):
        # strip blade directives
        cleaned = re.sub(r'\{\{.*?\}\}', '"test"', s)
        cleaned = re.sub(r'\{!!.*?!!\}', '"test"', cleaned)
        cleaned = re.sub(r'@[a-zA-Z]+(?:\(.*?\))?', '', cleaned)
        with tempfile.NamedTemporaryFile('w', suffix='.js', delete=False, encoding='utf-8') as tmp:
            tmp.write(cleaned)
            tmp_path = tmp.name
        res = subprocess.run(['node', '-c', tmp_path], capture_output=True, text=True)
        if res.returncode != 0:
            print(f'ERROR in {path} script {i}:')
            print(res.stderr)

check_file('resources/views/components/crm-theme-init.blade.php')
check_file('resources/views/layouts/app.blade.php')
check_file('resources/views/dashboard.blade.php')
