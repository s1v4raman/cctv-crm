import re

path = 'resources/views/layouts/app.blade.php'
with open(path, 'r', encoding='utf-8') as f:
    content = f.read()

# Replace desktop search
pattern1 = r'placeholder="Search leads, jobs, quotes, equipment\.\.\."\s*style="[^"]*"\s*class="crm-search-input[^"]*"'
replacement1 = 'placeholder="Search leads, jobs, quotes, equipment..."\n                                           style="padding-left: 2.5rem !important; height: 38px !important; border-radius: 6px !important;"\n                                           class="crm-search-input w-full text-xs bg-white dark:bg-[#0b132b] border-[1.5px] border-[#1e293b] dark:border-slate-600 rounded text-slate-800 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none"'

new_content, count1 = re.subn(pattern1, replacement1, content)
print(f'Desktop search matches replaced: {count1}')

# Replace mobile search
pattern2 = r'placeholder="Search\.\.\."\s*style="[^"]*border:[^"]*"\s*class="[^"]*"'
replacement2 = 'placeholder="Search..."\n                                       style="height: 38px !important; border-radius: 6px !important;"\n                                       class="w-full px-3 text-xs bg-white dark:bg-slate-900 border-[1.5px] border-[#1e293b] dark:border-slate-600 rounded text-slate-800 dark:text-white placeholder-slate-400 focus:outline-none"'

new_content, count2 = re.subn(pattern2, replacement2, new_content)
print(f'Mobile search matches replaced: {count2}')

with open(path, 'w', encoding='utf-8') as f:
    f.write(new_content)
print('Updated app.blade.php successfully')
