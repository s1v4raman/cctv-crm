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
            await send('Page.navigate', { url: 'http://127.0.0.1:8001/dashboard' });
            setTimeout(async () => {
                const evalRes = await send('Runtime.evaluate', {
                    expression: `(() => {
                        return JSON.stringify({
                            themeMode: document.documentElement.getAttribute('data-theme'),
                            classList: document.documentElement.className,
                            btnHtml: document.getElementById('header-theme-toggle-btn')?.outerHTML,
                            btnStyles: window.getComputedStyle(document.getElementById('header-theme-toggle-btn') || document.body).display,
                            hasToggleFn: typeof window.crmToggleTheme,
                            hasApplyFn: typeof window.applyCrmMode,
                            cookies: document.cookie,
                            localTheme: localStorage.getItem('theme'),
                            localCrmMode: localStorage.getItem('crm_mode')
                        });
                    })()`
                });
                console.log('Eval:', evalRes.result.value);
                
                const shot = await send('Page.captureScreenshot', { format: 'png' });
                fs.writeFileSync('scratch/current_dash.png', Buffer.from(shot.data, 'base64'));
                console.log('Saved scratch/current_dash.png');
                ws.close();
            }, 2000);
        });
    });
});
