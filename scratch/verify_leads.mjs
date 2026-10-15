import fs from 'fs';

const res = await fetch('http://127.0.0.1:9222/json');
const tabs = await res.json();
const tab = tabs.find(t => t.url && t.url.includes('8001'));
console.log('Tab:', tab?.title, tab?.url);

const ws = new WebSocket(tab.webSocketDebuggerUrl);

ws.onopen = () => {
    ws.send(JSON.stringify({ id: 1, method: 'Page.navigate', params: { url: 'http://127.0.0.1:8001/leads' } }));
};

ws.onmessage = async (evt) => {
    const msg = JSON.parse(evt.data);
    if (msg.id === 1) {
        setTimeout(() => {
            const expr = `(() => {
                const el = document.querySelector('#lead-search');
                if (!el) return null;
                const cs = window.getComputedStyle(el);
                return {
                    backgroundImage: cs.backgroundImage,
                    backgroundRepeat: cs.backgroundRepeat,
                    color: cs.color,
                    padding: cs.padding,
                    placeholder: el.placeholder
                };
            })()`;
            ws.send(JSON.stringify({ id: 2, method: 'Runtime.evaluate', params: { expression: expr, returnByValue: true } }));
        }, 1500);
    } else if (msg.id === 2) {
        console.log('Computed styles on #lead-search:', JSON.stringify(msg.result?.result?.value, null, 2));
        ws.send(JSON.stringify({ id: 3, method: 'Page.captureScreenshot', params: { format: 'png' } }));
    } else if (msg.id === 3) {
        const base64 = msg.result?.data;
        if (base64) {
            fs.writeFileSync('scratch/leads_after_fix.png', Buffer.from(base64, 'base64'));
            console.log('Saved scratch/leads_after_fix.png');
        }
        ws.close();
        process.exit(0);
    }
};
