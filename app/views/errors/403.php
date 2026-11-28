<?php
http_response_code(403);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Access Denied | StockSync</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f5f7fb;
            color: #273142;
            font-family: Arial, sans-serif;
        }
        .error-card {
            width: min(90%, 460px);
            padding: 45px 30px;
            text-align: center;
            background: white;
            border-radius: 14px;
            box-shadow: 0 8px 30px rgba(0,0,0,.07);
        }
        .error-code {
            color: #f5c518;
            font-size: 64px;
            font-weight: 800;
            margin: 0;
        }
        h2 { margin-bottom: 10px; }
        p { color: #687386; line-height: 1.6; }
        a {
            display: inline-block;
            margin-top: 18px;
            padding: 12px 22px;
            border-radius: 7px;
            background: #f5c518;
            color: #202938;
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <main class="error-card">
        <p class="error-code">403</p>
        <h2>Access Denied</h2>
        <p>You do not have permission to access this page. Contact your administrator if you believe this is a mistake.</p>
        <a href="<?= htmlspecialchars(defined('APP_URL') ? APP_URL : '/', ENT_QUOTES, 'UTF-8') ?>/dashboard">Return to Dashboard</a>
    </main>
</body>
</html>
