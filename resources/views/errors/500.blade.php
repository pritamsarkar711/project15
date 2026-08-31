<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Error 500</title>
    <link href="{{ \App\Support\SiteFont::googleUrl() }}" rel="stylesheet">
    <style>
        *{box-sizing:border-box}body{margin:0;background:#fafafa;color:#1e293b;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;text-align:center}
        .box{max-width:400px;width:100%;background:#fff;border:1px solid #e2e8f0;border-radius:16px;box-shadow:0 10px 30px -12px rgba(15,23,42,.12);padding:32px 24px}
        h1{font-size:20px;font-weight:700;margin:0 0 8px;color:#0f172a}
        p{font-size:14px;color:#64748b;margin:0 0 20px}
        .btn{display:inline-block;background:#059669;color:#fff;text-decoration:none;font-weight:600;font-size:14px;padding:10px 22px;border-radius:10px}
        @media(prefers-color-scheme:dark){body{background:#121212;color:#e2e8f0}.box{background:#1e1e1e;border-color:#2f2f2f}h1{color:#f1f5f9}}
    </style>
</head>
<body style="font-family:{{ \App\Support\SiteFont::cssStack() }}">
    <div class="box">
        <h1>Error 500</h1>
        <p>Something went wrong. Please try again.</p>
        <a class="btn" href="/">Go to homepage</a>
    </div>
</body>
</html>
