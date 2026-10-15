import fs from 'fs';

const res = await fetch('http://127.0.0.1:9222/json');
const tabs = await res.json();
const tab = tabs.find(t => t.url && t.url.includes('8001'));
console.log('Tab:', tab?.title, tab?.url);

const ws = new WebSocket(tab.webSocketDebuggerUrl);
ws.onopen = () => {
    const expr = `(() => {
        const form = document.querySelector('form[action*="leads"]');
        const ts = Array.from(document.querySelectorAll('.ts-wrapper')).map(el => ({
            classes: el.className,
            innerHTML: el.innerHTML.substring(0, 300)
        }));
        const filterControls = Array.from(form ? form.querySelectorAll('input, select, button, div') : []).map(el => ({
            tag: el.tagName,
            id: el.id,
            name: el.name,
            classes: el.className,
            styles: el.getAttribute('style'),
            computedWidth: el.offsetWidth,
            computedHeight: el.offsetHeight,
            html: el.outerHTML.substring(0, 200)
        }));
        return {
            formExists: !!form,
            formHtml: form ? form.outerHTML : null,
            filterControls,
            ts
        };
    })()`;
    ws.send(JSON.stringify({ id: 1, method: 'Runtime.evaluate', params: { expression: expr, returnByValue: true } }));
};

ws.onmessage = (evt) => {
    const msg = JSON.parse(evt.data);
    if (msg.id === 1) {
        console.log('Result:', JSON.stringify(msg.result?.result?.value, null, 2));
        ws.send(JSON.stringify({ id: 2, method: 'Page.captureScreenshot', params: { format: 'png' } }));
    } else if (msg.id === 2) {
        const base64 = msg.result?.data;
        if (base64) {
            fs.writeFileSync('scratch/leads_cdp_shot.png', Buffer.from(base64, 'base64'));
            console.log('Saved scratch/leads_cdp_shot.png');
        }
        ws.close();
    }
};
