import http from 'http';

http.get('http://127.0.0.1:9222/json', (res) => {
    let data = '';
    res.on('data', c => data += c);
    res.on('end', async () => {
        const tabs = JSON.parse(data);
        const tab = tabs.find(t => t.id === '95923EEA2C04F538EDDFADDC03437A47');
        const ws = new WebSocket(tab.webSocketDebuggerUrl);
        let id = 1;
        function send(method, params = {}) {
            return new Promise((resolve, reject) => {
                const reqId = id++;
                const handler = (event) => {
                    const res = JSON.parse(event.data);
                    if (res.id === reqId) {
                        ws.removeEventListener('message', handler);
                        if (res.error) reject(res.error);
                        else resolve(res.result);
                    }
                };
                ws.addEventListener('message', handler);
                ws.send(JSON.stringify({ id: reqId, method, params }));
            });
        }
        ws.addEventListener('open', async () => {
            const evalRes = await send('Runtime.evaluate', {
                expression: `(() => {
                    const thead = document.querySelector('thead');
                    if (!thead) return 'no thead';
                    const rules = [];
                    for (const sheet of document.styleSheets) {
                        try {
                            for (const rule of sheet.cssRules) {
                                if (rule.selectorText && thead.matches(rule.selectorText)) {
                                    rules.push({ sel: rule.selectorText, bg: rule.style.backgroundColor, raw: rule.cssText });
                                }
                            }
                        } catch(e) {}
                    }
                    return JSON.stringify(rules);
                })()`
            });
            console.log('Matching Rules for thead:', evalRes.result.value);
            ws.close();
        });
    });
});
