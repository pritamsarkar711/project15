{{-- Standalone 503 page: intentionally does NOT extend any site layout so it
     still renders when the layouts/config themselves are the problem.
     No automatic redirect on server errors: if every page errored, an
     auto-redirect would create an infinite loop. Instead: a clean page with
     a prominent way back home. --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>The site is being updated</title>
    <link href="{{ \App\Support\SiteFont::googleUrl() }}" rel="stylesheet">
    <style>
        *,*::before,*::after{box-sizing:border-box}
        body{margin:0;background:#fafafa;color:#1e293b;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;text-align:center}
        .box{max-width:460px;width:100%;background:#fff;border:1px solid #e2e8f0;padding:40px 28px}
        .icon{width:56px;height:56px;background:#fff7ed;color:#ea580c;display:flex;align-items:center;justify-content:center;margin:0 auto 18px}
        .code{font-size:12px;font-weight:700;letter-spacing:.18em;color:#94a3b8;text-transform:uppercase}
        h1{font-size:22px;font-weight:800;margin:10px 0 8px;color:#0f172a}
        p{font-size:14px;line-height:1.65;color:#64748b;margin:0 0 22px}
        .btn{display:inline-block;background:#173A2A;color:#fff;text-decoration:none;font-weight:600;font-size:14px;padding:12px 26px;transition:background .15s}
        .btn:hover{background:#0F2A1E}
        .sub{font-size:12px;color:#94a3b8;margin-top:16px}
        @media (prefers-color-scheme:dark){
            body{background:#121212;color:#e2e8f0}
            .box{background:#1e1e1e;border-color:#2f2f2f}
            h1{color:#f1f5f9}
        }
    </style>
</head>
<body style="font-family:{{ \App\Support\SiteFont::cssStack() }}">
    <div class="box">
        <div class="icon">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
        </div>
        <div class="code">Error 503</div>
        <h1>The site is being updated</h1>
        <p>We are applying a quick update or the server is briefly overloaded. This page will be back in a moment, please try again shortly.</p>
        <a class="btn" href="/">Go to homepage</a>
    </div>
</body>
</html>
