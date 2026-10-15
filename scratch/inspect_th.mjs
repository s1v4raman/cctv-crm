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
                    const th = document.querySelector('th');
                    if (!th) return 'No th found';
                    const cs = window.getComputedStyle(th);
                    const tr = th.closest('tr');
                    const trCs = tr ? window.getComputedStyle(tr) : null;
                    const thead = th.closest('thead');
                    const theadCs = thead ? window.getComputedStyle(thead) : null;
                    return JSON.stringify({
                        theme: document.documentElement.getAttribute('data-theme'),
                        thBg: cs.backgroundColor,
                        thColor: cs.color,
                        trBg: trCs ? trCs.backgroundColor : null,
                        theadBg: theadCs ? theadCs.backgroundColor : null,
                        cardBgVar: window.getComputedStyle(document.documentElement).getPropertyValue('--card-bg'),
                        tableHeaderBgVar: window.getComputedStyle(document.documentElement).getPropertyValue('--table-header-bg')
                    });
                })()`
            });
            console.log('TH Result:', evalRes.result.value);
            ws.close();
        });
    });
});
