import http from 'http';
import fs from 'fs';

http.get('http://127.0.0.1:9222/json', (res) => {
    let data = '';
    res.on('data', c => data += c);
    res.on('end', async () => {
        const tabs = JSON.parse(data);
        const tab = tabs.find(t => t.id === '95923EEA2C04F538EDDFADDC03437A47');
        if (!tab) { console.error('Tab not found'); return; }
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
            console.log('Connected to CDP');
            await send('Page.enable');
            // Toggle to light mode
            await send('Runtime.evaluate', {
                expression: `window.applyCrmMode('day', true)`
            });
            setTimeout(async () => {
                const evalRes = await send('Runtime.evaluate', {
                    expression: `(() => {
                        const th = document.querySelector('th');
                        const thead = th ? th.closest('thead') : null;
                        const tr = th ? th.closest('tr') : null;
                        return JSON.stringify({
                            themeMode: document.documentElement.getAttribute('data-theme'),
                            classList: document.documentElement.className,
                            thBg: th ? window.getComputedStyle(th).backgroundColor : null,
                            thColor: th ? window.getComputedStyle(th).color : null,
                            theadBg: thead ? window.getComputedStyle(thead).backgroundColor : null,
                            trBg: tr ? window.getComputedStyle(tr).backgroundColor : null,
                            tableHeaderBgVar: window.getComputedStyle(document.documentElement).getPropertyValue('--table-header-bg')
                        });
                    })()`
                });
                console.log('Toggled to light:', evalRes.result.value);
                
                const shot = await send('Page.captureScreenshot', { format: 'png' });
                fs.writeFileSync('scratch/light_dash.png', Buffer.from(shot.data, 'base64'));
                console.log('Saved scratch/light_dash.png');
                ws.close();
            }, 1000);
        });
    });
});
