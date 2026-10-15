import urllib.request, json, asyncio, websockets

async def inspect():
    resp = urllib.request.urlopen('http://127.0.0.1:9222/json')
    tabs = json.loads(resp.read().decode('utf-8'))
    ws_url = None
    for t in tabs:
        if t.get('id') == '95923EEA2C04F538EDDFADDC03437A47':
            ws_url = t.get('webSocketDebuggerUrl')
            break
    
    if not ws_url:
        print("Tab not found")
        return

    async with websockets.connect(ws_url) as ws:
        # Enable Log and Runtime
        await ws.send(json.dumps({"id": 1, "method": "Runtime.enable"}))
        await ws.recv()

        # Evaluate current state
        eval_script = """
        ({
            className: document.documentElement.className,
            dataTheme: document.documentElement.getAttribute('data-theme'),
            bodyBg: getComputedStyle(document.body).backgroundColor,
            pageBgVar: getComputedStyle(document.documentElement).getPropertyValue('--page-bg'),
            cardBgVar: getComputedStyle(document.documentElement).getPropertyValue('--card-bg'),
            hasButton: !!document.getElementById('header-theme-toggle-btn'),
            buttonHtml: document.getElementById('header-theme-toggle-btn') ? document.getElementById('header-theme-toggle-btn').outerHTML : null,
            hasApplyCrmMode: typeof window.applyCrmMode === 'function',
            hasCrmToggleTheme: typeof window.crmToggleTheme === 'function'
        })
        """
        await ws.send(json.dumps({
            "id": 2,
            "method": "Runtime.evaluate",
            "params": {
                "expression": eval_script,
                "returnByValue": True
            }
        }))
        res = json.loads(await ws.recv())
        print("Current Page State:")
        print(json.dumps(res.get('result', {}).get('result', {}).get('value', {}), indent=2))

        # Now simulate clicking the toggle or calling applyCrmMode('light')
        click_script = """
        (function() {
            const btn = document.getElementById('header-theme-toggle-btn');
            if (btn) {
                btn.click();
            }
            return {
                newClass: document.documentElement.className,
                newDataTheme: document.documentElement.getAttribute('data-theme'),
                newBodyBg: getComputedStyle(document.body).backgroundColor
            };
        })()
        """
        await ws.send(json.dumps({
            "id": 3,
            "method": "Runtime.evaluate",
            "params": {
                "expression": click_script,
                "returnByValue": True
            }
        }))
        res2 = json.loads(await ws.recv())
        print("\nAfter clicking btn.click():")
        print(json.dumps(res2.get('result', {}).get('result', {}).get('value', {}), indent=2))

asyncio.run(inspect())
