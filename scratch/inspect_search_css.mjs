const res = await fetch('http://127.0.0.1:9222/json');
const tabs = await res.json();
const tab = tabs.find(t => t.url && t.url.includes('8001'));

const ws = new WebSocket(tab.webSocketDebuggerUrl);
ws.onopen = () => {
    const expr = `(() => {
        const el = document.querySelector('#lead-search');
        if (!el) return 'Element not found';
        const cs = window.getComputedStyle(el);
        
        // Find which stylesheets and rules affect #lead-search
        const matched = [];
        for (const sheet of document.styleSheets) {
            try {
                for (const rule of sheet.cssRules) {
                    if (rule.selectorText && el.matches(rule.selectorText)) {
                        matched.push({
                            selector: rule.selectorText,
                            cssText: rule.cssText
                        });
                    }
                }
            } catch (e) {}
        }

        return {
            backgroundImage: cs.backgroundImage,
            background: cs.background,
            padding: cs.padding,
            color: cs.color,
            fontSize: cs.fontSize,
            lineHeight: cs.lineHeight,
            matchedRulesCount: matched.length,
            matchedRules: matched
        };
    })()`;
    ws.send(JSON.stringify({ id: 1, method: 'Runtime.evaluate', params: { expression: expr, returnByValue: true } }));
};

ws.onmessage = (evt) => {
    const msg = JSON.parse(evt.data);
    if (msg.id === 1) {
        console.log('Result:', JSON.stringify(msg.result?.result?.value, null, 2));
        ws.close();
    }
};
