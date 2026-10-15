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
            
            // 1. Test Light Mode
            await send('Page.navigate', { url: 'http://127.0.0.1:8001/dashboard' });
            await new Promise(r => setTimeout(r, 1500));
            await send('Runtime.evaluate', {
                expression: `window.applyCrmMode('day', true)`
            });
            await new Promise(r => setTimeout(r, 1000));
            
            const lightStats = await send('Runtime.evaluate', {
                expression: `(() => {
                    const bodyCs = window.getComputedStyle(document.body);
                    const card = document.querySelector('.stat-card');
                    const cardCs = card ? window.getComputedStyle(card) : null;
                    const th = document.querySelector('th');
                    const thCs = th ? window.getComputedStyle(th) : null;
                    const td = document.querySelector('td');
                    const tdCs = td ? window.getComputedStyle(td) : null;
                    const rootCs = window.getComputedStyle(document.documentElement);
                    const toggleBtn = document.getElementById('header-theme-toggle-btn');
                    return JSON.stringify({
                        theme: document.documentElement.getAttribute('data-theme'),
                        pageBg: bodyCs.backgroundColor,
                        cardBg: cardCs?.backgroundColor,
                        cardBorder: cardCs?.borderColor,
                        thBg: thCs?.backgroundColor,
                        thColor: thCs?.color,
                        tdColor: tdCs?.color,
                        tdBorderR: tdCs?.borderRightColor,
                        tdBorderB: tdCs?.borderBottomColor,
                        btnDisplay: toggleBtn ? window.getComputedStyle(toggleBtn).display : null,
                        btnHtml: toggleBtn ? toggleBtn.innerHTML : null
                    });
                })()`
            });
            console.log('LIGHT MODE REPORT:', lightStats.result.value);
            const shotLight = await send('Page.captureScreenshot', { format: 'png' });
            fs.writeFileSync('scratch/test_light_mode.png', Buffer.from(shotLight.data, 'base64'));
            console.log('Saved scratch/test_light_mode.png');

            // 2. Test Dark Mode
            await send('Runtime.evaluate', {
                expression: `window.applyCrmMode('night', true)`
            });
            await new Promise(r => setTimeout(r, 1000));
            
            const darkStats = await send('Runtime.evaluate', {
                expression: `(() => {
                    const bodyCs = window.getComputedStyle(document.body);
                    const card = document.querySelector('.stat-card');
                    const cardCs = card ? window.getComputedStyle(card) : null;
                    const th = document.querySelector('th');
                    const thCs = th ? window.getComputedStyle(th) : null;
                    const td = document.querySelector('td');
                    const tdCs = td ? window.getComputedStyle(td) : null;
                    const toggleBtn = document.getElementById('header-theme-toggle-btn');
                    return JSON.stringify({
                        theme: document.documentElement.getAttribute('data-theme'),
                        pageBg: bodyCs.backgroundColor,
                        cardBg: cardCs?.backgroundColor,
                        cardBorder: cardCs?.borderColor,
                        thBg: thCs?.backgroundColor,
                        thColor: thCs?.color,
                        tdColor: tdCs?.color,
                        tdBorderR: tdCs?.borderRightColor,
                        tdBorderB: tdCs?.borderBottomColor,
                        btnDisplay: toggleBtn ? window.getComputedStyle(toggleBtn).display : null,
                        btnHtml: toggleBtn ? toggleBtn.innerHTML : null
                    });
                })()`
            });
            console.log('DARK MODE REPORT:', darkStats.result.value);
            const shotDark = await send('Page.captureScreenshot', { format: 'png' });
            fs.writeFileSync('scratch/test_dark_mode.png', Buffer.from(shotDark.data, 'base64'));
            console.log('Saved scratch/test_dark_mode.png');

            ws.close();
        });
    });
});
