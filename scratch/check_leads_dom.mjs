import http from 'http';
import fs from 'fs';

http.get('http://127.0.0.1:9222/json', (res) => {
    let data = '';
    res.on('data', c => data += c);
    res.on('end', async () => {
        const tabs = JSON.parse(data);
        const tab = tabs.find(t => t.id === 'B1D601D8EEF89B05037CF0B5963B5F0A');
        const ws = new WebSocket(tab.webSocketDebuggerUrl);
        let id = 1;
        function send(method, params = {}) {
            return new Promise((resolve, reject) => {
                const reqId = id++;
                const handler = (event) => {
                    const r = JSON.parse(event.data);
                    if (r.id === reqId) {
                        ws.removeEventListener('message', handler);
                        if (r.error) reject(r.error);
                        else resolve(r.result);
                    }
                };
                ws.addEventListener('message', handler);
                ws.send(JSON.stringify({ id: reqId, method, params }));
            });
        }
        ws.addEventListener('open', async () => {
            await send('Page.navigate', { url: 'http://127.0.0.1:8001/leads' });
            await new Promise(r => setTimeout(r, 2000));
            const evalRes = await send('Runtime.evaluate', {
                expression: `(() => {
                    const filterForm = document.querySelector('form[action*="leads"]');
                    const formHtml = filterForm ? filterForm.outerHTML : 'NOT FOUND';
                    const ts = Array.from(document.querySelectorAll('.ts-wrapper')).map(el => ({
                        classes: el.className,
                        html: el.outerHTML
                    }));
                    return JSON.stringify({ formHtml, ts }, null, 2);
                })()`
            });
            console.log(evalRes.result.value);

            // Also capture a screenshot
            const shot = await send('Page.captureScreenshot', { format: 'png' });
            fs.writeFileSync('scratch/leads_filter_screenshot.png', Buffer.from(shot.data, 'base64'));
            console.log('Saved screenshot to scratch/leads_filter_screenshot.png');
            process.exit(0);
        });
    });
});
