{{--
    Shared page for 403 / 404 / 419 / 429 / 500 / 503.
    Kept independent of the database and session: a 500 may be caused by the database itself.
--}}
@php
    $homeUrl = url('/dashboard');
    $homeLabel = 'Go to dashboard';
    // Unknown URLs are answered before the session starts, so a missing user does not mean a
    // guest: /dashboard sends real guests to the sign-in page anyway.
    if ($signedInCheck ?? true) {
        try {
            $viewer = auth()->user();
            if (!$viewer) {
                // keep the dashboard link
            } elseif ($viewer->isTechnician()) {
                $homeUrl = route('technician.dashboard');
            } elseif ($viewer->isWorker()) {
                [$homeUrl, $homeLabel] = [route('worker.portal'), 'Go to my page'];
            }
        } catch (\Throwable $e) {
            // keep the plain dashboard link
        }
    }
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{{ $title }} | {{ config('app.name', 'Precision IT Systems') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('logo.png') }}">
    <style>
        :root { --bg: #f1f5f9; --card: #ffffff; --border: #cbd5e1; --text: #0f172a; --muted: #64748b; --accent: #2563eb; }
        @media (prefers-color-scheme: dark) {
            :root { --bg: #0f172a; --card: #1e293b; --border: #334155; --text: #f1f5f9; --muted: #94a3b8; --accent: #3b82f6; }
        }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px 16px;
               background: var(--bg); color: var(--text); font: 14px/1.5 system-ui, -apple-system, "Segoe UI", Roboto, sans-serif; }
        .card { width: 100%; max-width: 440px; background: var(--card); border: 1px solid var(--border); border-radius: 10px; padding: 28px 24px; }
        .brand { display: flex; align-items: center; gap: 10px; margin-bottom: 24px; font-weight: 700; font-size: 14px; }
        .brand img { height: 32px; width: auto; max-width: 170px; object-fit: contain; }
        .dark-logo { display: none; }
        @media (prefers-color-scheme: dark) {
            .light-logo { display: none; }
            .dark-logo { display: inline-block; }
        }
        .code { font-size: 13px; font-weight: 600; color: var(--muted); letter-spacing: .02em; }
        h1 { margin: 4px 0 8px; font-size: 22px; line-height: 1.25; }
        p { margin: 0 0 8px; color: var(--muted); }
        .detail { margin-top: 12px; padding: 10px 12px; border-radius: 6px; background: var(--bg); color: var(--text); font-size: 13px; }
        .actions { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 24px; }
        .btn { display: inline-flex; align-items: center; min-height: 38px; padding: 0 16px; border-radius: 6px; font-weight: 600; font-size: 14px;
               text-decoration: none; border: 1px solid var(--accent); cursor: pointer; font-family: inherit; }
        .btn-primary { background: var(--accent); color: #fff; }
        .btn-secondary { background: transparent; color: var(--accent); }
        .btn:focus-visible { outline: 2px solid var(--accent); outline-offset: 2px; }
    </style>
</head>
<body>
    <main class="card">
        <div class="brand">
            <img src="{{ asset('logo.png') }}" class="light-logo" alt="Precision IT Systems">
            <img src="{{ asset('logo-dark.png') }}" class="dark-logo" alt="Precision IT Systems">
        </div>

        <div class="code">Error {{ $code }}</div>
        <h1>{{ $title }}</h1>
        <p>{{ $message }}</p>

        @if (!empty($detail))
            <div class="detail">{{ $detail }}</div>
        @endif

        @if ($showActions ?? true)
            <div class="actions">
                <a href="{{ $homeUrl }}" class="btn btn-primary">{{ $homeLabel }}</a>
                <button type="button" class="btn btn-secondary" onclick="history.length > 1 ? history.back() : location.assign('{{ $homeUrl }}')">Go back</button>
            </div>
        @endif
    </main>
</body>
</html>
