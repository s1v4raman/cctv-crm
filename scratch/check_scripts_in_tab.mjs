const res = await fetch('http://127.0.0.1:9222/json');
const tabs = await res.json();
const dashTab = tabs.find(t => t.url && t.url.includes('dashboard')) || tabs.find(t => t.id === '95923EEA2C04F538EDDFADDC03437A47');

const ws = new WebSocket(dashTab.webSocketDebuggerUrl);
ws.onopen = () => {
    const expr = `(() => {
        const scripts = Array.from(document.querySelectorAll('script')).map((s, i) => ({
            index: i,
            src: s.src || null,
            textStart: s.textContent.trim().substring(0, 60)
        }));
        return {
            title: document.title,
            url: window.location.href,
            scripts: scripts,
            hasCrmInit: scripts.some(s => s.textStart.includes('crmLogoCache') || s.textStart.includes('Instant Synchronous Early Theme'))
        };
    })()`;
    ws.send(JSON.stringify({ id: 1, method: 'Runtime.evaluate', params: { expression: expr, returnByValue: true } }));
};

ws.onmessage = (evt) => {
    const msg = JSON.parse(evt.data);
    if (msg.id === 1) {
        console.log(JSON.stringify(msg.result?.result?.value, null, 2));
        ws.close();
    }
};
