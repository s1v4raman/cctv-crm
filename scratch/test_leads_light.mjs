import fs from 'fs';

const res = await fetch('http://127.0.0.1:9222/json');
const tabs = await res.json();
const tab = tabs.find(t => t.url && t.url.includes('8001'));

const ws = new WebSocket(tab.webSocketDebuggerUrl);

ws.onopen = () => {
    // Switch to light mode
    ws.send(JSON.stringify({
        id: 1,
        method: 'Runtime.evaluate',
        params: {
            expression: `window.applyCrmMode ? window.applyCrmMode('day', true) : document.documentElement.classList.remove('dark')`
        }
    }));
};

ws.onmessage = async (evt) => {
    const msg = JSON.parse(evt.data);
    if (msg.id === 1) {
        setTimeout(() => {
            ws.send(JSON.stringify({ id: 2, method: 'Page.captureScreenshot', params: { format: 'png' } }));
        }, 500);
    } else if (msg.id === 2) {
        const base64 = msg.result?.data;
        if (base64) {
            fs.writeFileSync('scratch/leads_light_mode.png', Buffer.from(base64, 'base64'));
            console.log('Saved scratch/leads_light_mode.png');
        }
        // Switch back to dark mode for default consistency
        ws.send(JSON.stringify({
            id: 3,
            method: 'Runtime.evaluate',
            params: {
                expression: `window.applyCrmMode ? window.applyCrmMode('night', true) : document.documentElement.classList.add('dark')`
            }
        }));
    } else if (msg.id === 3) {
        ws.close();
        process.exit(0);
    }
};
