{{-- Maintenance mode page — HTTP 503. Deliberately SELF-CONTAINED (no layout,
   no partials) so it renders even if everything else is broken while the
   admin updates the site. Site design: Inter, #0C3B2E dark green, emerald
   accents, square corners. Minimal content per design brief: logo + timer. --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ setting('site_name', 'huvanti.com') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="{{ \App\Support\SiteFont::googleUrl() }}" rel="stylesheet">
    <style>
        *{box-sizing:border-box}
        body{margin:0;background:#fafafa;color:#1e293b;min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:24px;text-align:center}
        .logo{margin-bottom:40px}
        .timer{display:flex;gap:10px;align-items:flex-start;justify-content:center}
        .cell{background:#0C3B2E;color:#fff;min-width:78px;padding:14px 6px 10px}
        .cell .num{font-size:30px;font-weight:800;line-height:1;font-variant-numeric:tabular-nums}
        .cell .lab{font-size:10px;font-weight:600;letter-spacing:.14em;text-transform:uppercase;color:#86efac;margin-top:6px}
        .sep{font-size:26px;font-weight:800;color:#0C3B2E;line-height:1.4;user-select:none}
        .soon{margin-top:26px;font-size:13px;font-weight:500;color:#64748b}
        @media (prefers-color-scheme: dark){
            body{background:#121212;color:#e2e8f0}
            .sep{color:#34d399}
            .soon{color:#94a3b8}
        }
        @media (max-width:420px){.cell{min-width:62px;padding:12px 4px 8px}.cell .num{font-size:24px}}
    </style>
</head>
<body style="font-family:{{ \App\Support\SiteFont::cssStack() }}">
    <div class="logo">
        @php
            $logoLight = setting('site_logo_light');
            $logoDark = setting('site_logo_dark');
            $logo = $logoLight ?: ($logoDark ?: null);
            $siteName = setting('site_name', 'huvanti.com');
        @endphp
        @if($logo)
            <img src="{{ asset('storage/'.$logo) }}" alt="{{ $siteName }}" class="h-9 w-auto" loading="eager">
        @else
            <span style="font-size:24px;font-weight:800;letter-spacing:-.02em;color:#0f172a">{{ preg_replace('/\.com$/i','', $siteName) }}<span style="color:#059669;font-weight:500">{{ str_ends_with(strtolower($siteName), '.com') ? '.com' : '' }}</span></span>
        @endif
    </div>

    @if($endsAt)
        <div class="timer" id="timer" data-ends="{{ $endsAt->toIso8601String() }}">
            <div class="cell"><div class="num" id="t-d">00</div><div class="lab">Days</div></div>
            <div class="sep">:</div>
            <div class="cell"><div class="num" id="t-h">00</div><div class="lab">Hours</div></div>
            <div class="sep">:</div>
            <div class="cell"><div class="num" id="t-m">00</div><div class="lab">Min</div></div>
            <div class="sep">:</div>
            <div class="cell"><div class="num" id="t-s">00</div><div class="lab">Sec</div></div>
        </div>
    @endif

    <p class="soon">We'll be back soon</p>

    @if($endsAt)
    <script>
    (function () {
        var ends = new Date(document.getElementById('timer').getAttribute('data-ends')).getTime();
        var els = { d: document.getElementById('t-d'), h: document.getElementById('t-h'), m: document.getElementById('t-m'), s: document.getElementById('t-s') };
        function pad(n) { return String(n).padStart(2, '0'); }
        function tick() {
            var left = Math.max(0, Math.floor((ends - Date.now()) / 1000));
            var d = Math.floor(left / 86400), h = Math.floor((left % 86400) / 3600), m = Math.floor((left % 3600) / 60), s = left % 60;
            els.d.textContent = pad(d); els.h.textContent = pad(h); els.m.textContent = pad(m); els.s.textContent = pad(s);
            if (left <= 0) {
                // The timer finished: try once to bring the visitor to the live
                // site. The sessionStorage flag guarantees no reload loop if
                // maintenance is still on.
                try {
                    if (!sessionStorage.getItem('mm-reload')) {
                        sessionStorage.setItem('mm-reload', '1');
                        setTimeout(function () { location.reload(); }, 2000);
                    }
                } catch (e) { /* private mode — just hold at 00 */ }
            }
        }
        tick();
        setInterval(tick, 1000);
    })();
    </script>
    @endif
</body>
</html>
