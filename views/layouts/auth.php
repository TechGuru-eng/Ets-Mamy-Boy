<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>ETS MAMY BOY - Login</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,650;9..144,750&family=Manrope:wght@400;500;600;700;800&family=Roboto+Mono:wght@600;700&display=swap" rel="stylesheet">
    <!-- Favicon & PWA App Icons -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="icon" type="image/png" sizes="192x192" href="/assets/images/icon-192.png">
    <link rel="apple-touch-icon" href="/assets/images/apple-touch-icon.png">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#102033">
    <!-- Custom Premium CSS -->
    <link href="/assets/css/style.css" rel="stylesheet">
    <style>
        body {
            background: #eef3f8;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            padding: 2.25rem;
            width: 100%;
            max-width: 420px;
            background: #ffffff;
            margin: 1rem;
        }
        .login-logo {
            width: 64px;
            height: 64px;
            background: #102033;
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            color: white;
            font-size: 2rem;
            box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.2);
        }
        .brand-text {
            font-family: var(--font-display);
            font-weight: 750;
            letter-spacing: 0;
            color: var(--text-main);
        }
    </style>
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body>

    <?php echo $content; ?>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
