const res = await fetch('http://127.0.0.1:9222/json');
const tabs = await res.json();
const dashTab = tabs.find(t => t.url && t.url.includes('dashboard')) || tabs.find(t => t.id === '95923EEA2C04F538EDDFADDC03437A47');

const ws = new WebSocket(dashTab.webSocketDebuggerUrl);
ws.onopen = () => {
    // Reload page
    ws.send(JSON.stringify({ id: 1, method: 'Page.reload' }));
};

ws.onmessage = async (evt) => {
    const msg = JSON.parse(evt.data);
    if (msg.id === 1) {
        // Wait 1.5s for load
        setTimeout(() => {
            const expr = `(() => ({
                hasApplyCrmMode: typeof window.applyCrmMode,
                hasCrmToggleTheme: typeof window.crmToggleTheme,
                className: document.documentElement.className,
                dataTheme: document.documentElement.getAttribute('data-theme')
            }))()`;
            ws.send(JSON.stringify({ id: 2, method: 'Runtime.evaluate', params: { expression: expr, returnByValue: true } }));
        }, 1500);
    } else if (msg.id === 2) {
        console.log('Page after reload:', JSON.stringify(msg.result?.result?.value, null, 2));
        ws.close();
    }
};
