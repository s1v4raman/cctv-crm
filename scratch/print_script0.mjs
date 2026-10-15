const res = await fetch('http://127.0.0.1:9222/json');
const tabs = await res.json();
const dashTab = tabs.find(t => t.url && t.url.includes('dashboard')) || tabs.find(t => t.id === '95923EEA2C04F538EDDFADDC03437A47');

const ws = new WebSocket(dashTab.webSocketDebuggerUrl);
ws.onopen = () => {
    const expr = `(() => {
        const lines = document.querySelectorAll('script')[0].textContent.split('\\n');
        return lines.slice(0, 45).map((l, i) => (i+1) + ': ' + l);
    })()`;
    ws.send(JSON.stringify({ id: 1, method: 'Runtime.evaluate', params: { expression: expr, returnByValue: true } }));
};

ws.onmessage = (evt) => {
    const msg = JSON.parse(evt.data);
    if (msg.id === 1) {
        console.log(msg.result?.result?.value.join('\n'));
        ws.close();
    }
};
