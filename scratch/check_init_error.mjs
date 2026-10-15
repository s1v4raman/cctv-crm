const res = await fetch('http://127.0.0.1:9222/json');
const tabs = await res.json();
const dashTab = tabs.find(t => t.url && t.url.includes('dashboard')) || tabs.find(t => t.id === '95923EEA2C04F538EDDFADDC03437A47');

const ws = new WebSocket(dashTab.webSocketDebuggerUrl);
ws.onopen = () => {
    // Enable Log & Runtime
    ws.send(JSON.stringify({ id: 1, method: 'Log.enable' }));
    ws.send(JSON.stringify({ id: 2, method: 'Runtime.enable' }));

    // Ask for any exceptions or evaluate crm-theme-init
    const checkScript = `(() => {
        const initScript = Array.from(document.querySelectorAll('script')).find(s => s.textContent.includes('crmLogoCache'));
        return {
            hasInitScript: !!initScript,
            scriptLength: initScript ? initScript.textContent.length : 0,
            hasApplyCrmMode: typeof window.applyCrmMode,
            windowKeys: Object.keys(window).filter(k => k.startsWith('crm'))
        };
    })()`;
    ws.send(JSON.stringify({ id: 3, method: 'Runtime.evaluate', params: { expression: checkScript, returnByValue: true } }));
};

ws.onmessage = (evt) => {
    const msg = JSON.parse(evt.data);
    if (msg.id === 3) {
        console.log('Result:', JSON.stringify(msg.result?.result?.value, null, 2));
    }
    if (msg.method === 'Log.entryAdded' || msg.method === 'Runtime.exceptionThrown') {
        console.log('Console event:', JSON.stringify(msg, null, 2));
    }
};

setTimeout(() => ws.close(), 3000);
