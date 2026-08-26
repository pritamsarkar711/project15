{{-- Standalone 403 page (fallback: 403s normally redirect home, this renders only in edge cases): intentionally does NOT extend any site layout so it
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
    <title>Access denied</title>
    <link href="https://fonts.googleapis.com/css2?family=Work+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        *,*::before,*::after{box-sizing:border-box}
        body{margin:0;font-family:'Inter',system-ui,-apple-system,sans-serif;background:#fafafa;color:#1e293b;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;text-align:center}
        .box{max-width:460px;width:100%;background:#fff;border:1px solid #e2e8f0;padding:40px 28px}
        .icon{width:56px;height:56px;background:#fefce8;color:#ca8a04;display:flex;align-items:center;justify-content:center;margin:0 auto 18px}
        .code{font-size:12px;font-weight:700;letter-spacing:.18em;color:#94a3b8;text-transform:uppercase}
        h1{font-size:22px;font-weight:800;margin:10px 0 8px;color:#0f172a}
        p{font-size:14px;line-height:1.65;color:#64748b;margin:0 0 22px}
        .btn{display:inline-block;background:#0C3B2E;color:#fff;text-decoration:none;font-weight:600;font-size:14px;padding:12px 26px;transition:background .15s}
        .btn:hover{background:#072A20}
        .sub{font-size:12px;color:#94a3b8;margin-top:16px}
        @media (prefers-color-scheme:dark){
            body{background:#121212;color:#e2e8f0}
            .box{background:#1e1e1e;border-color:#2f2f2f}
            h1{color:#f1f5f9}
        }
    </style>
</head>
<body>
    <div class="box">
        <div class="icon">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
        </div>
        <div class="code">Error 403</div>
        <h1>Access denied</h1>
        <p>You do not have permission to view this page.</p>
        <a class="btn" href="/">Go to homepage</a>
        <p class="sub">If the problem keeps happening, open /deploy.php once and hard refresh with Ctrl + Shift + R.</p>
    </div>
</body>
</html>
