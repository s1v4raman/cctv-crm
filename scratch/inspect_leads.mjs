import fs from 'fs';

const res = await fetch('http://127.0.0.1:9222/json');
const tabs = await res.json();
const tab = tabs.find(t => t.url && (t.url.includes('8001') || t.url.includes('analytics') || t.url.includes('leads')));
console.log('Using Tab:', tab?.title, tab?.url);

const ws = new WebSocket(tab.webSocketDebuggerUrl);

await new Promise((resolve) => {
    ws.onopen = resolve;
});

function call(method, params = {}) {
    return new Promise((resolve) => {
        const id = Math.floor(Math.random() * 1000000);
        const handler = (evt) => {
            const data = JSON.parse(evt.data);
            if (data.id === id) {
                ws.removeEventListener('message', handler);
                resolve(data.result);
            }
        };
        ws.addEventListener('message', handler);
        ws.send(JSON.stringify({ id, method, params }));
    });
}

console.log('Navigating to http://127.0.0.1:8001/leads...');
await call('Page.navigate', { url: 'http://127.0.0.1:8001/leads' });
await new Promise(r => setTimeout(r, 2000));

const dom = await call('Runtime.evaluate', {
    expression: `(() => {
        const form = document.querySelector('form[action*="leads"]');
        const searchInput = document.querySelector('#lead-search');
        const statusSelect = document.querySelector('#lead-status-filter');
        const tsWrappers = Array.from(document.querySelectorAll('.ts-wrapper')).map(w => ({
            id: w.id,
            className: w.className,
            html: w.outerHTML.substring(0, 300)
        }));
        return {
            formHtml: form ? form.outerHTML : null,
            searchInputHtml: searchInput ? searchInput.outerHTML : null,
            statusSelectHtml: statusSelect ? statusSelect.outerHTML : null,
            statusSelectParent: statusSelect ? statusSelect.parentElement.outerHTML.substring(0, 300) : null,
            tsWrappersCount: tsWrappers.length,
            tsWrappers
        };
    })()`,
    returnByValue: true
});

console.log('DOM Evaluation:', JSON.stringify(dom.result?.value, null, 2));

const shot = await call('Page.captureScreenshot', { format: 'png' });
fs.writeFileSync('scratch/leads_screenshot_latest.png', Buffer.from(shot.data, 'base64'));
console.log('Saved screenshot to scratch/leads_screenshot_latest.png');

ws.close();
process.exit(0);
