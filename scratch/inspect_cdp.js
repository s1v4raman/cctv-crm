const http = require('http');

http.get('http://127.0.0.1:9222/json', (res) => {
    let data = '';
    res.on('data', chunk => data += chunk);
    res.on('end', () => {
        const tabs = JSON.parse(data);
        const dashTab = tabs.find(t => t.url && t.url.includes('dashboard')) || tabs.find(t => t.id === '95923EEA2C04F538EDDFADDC03437A47');
        if (!dashTab) {
            console.log('No dashboard tab found in tabs:', tabs.map(t => t.url));
            return;
        }

        console.log('Connecting to', dashTab.title, dashTab.url);
        const ws = new WebSocket(dashTab.webSocketDebuggerUrl);

        ws.onopen = () => {
            // Enable Runtime
            ws.send(jsonMsg(1, 'Runtime.enable'));

            // Query state
            const expr = `({
                className: document.documentElement.className,
                dataTheme: document.documentElement.getAttribute('data-theme'),
                bodyBg: getComputedStyle(document.body).backgroundColor,
                statCardBg: document.querySelector('.stat-card') ? getComputedStyle(document.querySelector('.stat-card')).backgroundColor : null,
                statCardBorder: document.querySelector('.stat-card') ? getComputedStyle(document.querySelector('.stat-card')).borderColor : null,
                statCardColor: document.querySelector('.stat-card') ? getComputedStyle(document.querySelector('.stat-card')).color : null,
                totalLeadsColor: document.querySelector('#stat-total-leads') ? getComputedStyle(document.querySelector('#stat-total-leads')).color : null,
                dealsThBg: document.querySelector('th') ? getComputedStyle(document.querySelector('th')).backgroundColor : null,
                pageBgVar: getComputedStyle(document.documentElement).getPropertyValue('--page-bg'),
                cardBgVar: getComputedStyle(document.documentElement).getPropertyValue('--card-bg'),
                textMainVar: getComputedStyle(document.documentElement).getPropertyValue('--text-main'),
                btnExists: !!document.getElementById('header-theme-toggle-btn')
            })`;
            ws.send(jsonMsg(2, 'Runtime.evaluate', { expression: expr, returnByValue: true }));
        };

        ws.onmessage = (evt) => {
            const msg = JSON.parse(evt.data);
            if (msg.id === 2) {
                console.log('--- Initial Dashboard State ---');
                console.log(JSON.stringify(msg.result?.result?.value, null, 2));

                // Now click the toggle
                console.log('\n--- Clicking #header-theme-toggle-btn ---');
                const clickExpr = `(function() {
                    const btn = document.getElementById('header-theme-toggle-btn');
                    if (btn) btn.click();
                    return {
                        classNameAfterClick: document.documentElement.className,
                        dataThemeAfterClick: document.documentElement.getAttribute('data-theme'),
                        bodyBgAfterClick: getComputedStyle(document.body).backgroundColor,
                        statCardBgAfterClick: document.querySelector('.stat-card') ? getComputedStyle(document.querySelector('.stat-card')).backgroundColor : null,
                        totalLeadsColorAfterClick: document.querySelector('#stat-total-leads') ? getComputedStyle(document.querySelector('#stat-total-leads')).color : null
                    };
                })()`;
                ws.send(jsonMsg(3, 'Runtime.evaluate', { expression: clickExpr, returnByValue: true }));
            } else if (msg.id === 3) {
                console.log('--- After Click Result ---');
                console.log(JSON.stringify(msg.result?.result?.value, null, 2));
                ws.close();
            }
        };

        ws.onerror = (err) => console.error('WS Error:', err);
    });
});

function jsonMsg(id, method, params = {}) {
    return JSON.stringify({ id, method, params });
}
