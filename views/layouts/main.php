<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>ETS MAMY BOY - <?= htmlspecialchars($title ?? 'Dashboard') ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,650;9..144,750&family=Manrope:wght@400;500;600;700;800&family=Roboto+Mono:wght@600;700&display=swap" rel="stylesheet">
    <!-- Favicon & PWA App Icons -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico?v=2">
    <link rel="icon" type="image/png" sizes="192x192" href="/assets/images/icon-192.png?v=2">
    <link rel="apple-touch-icon" href="/assets/images/apple-touch-icon.png?v=2">
    <link rel="manifest" href="/manifest.json?v=2">
    <meta name="theme-color" content="#102033">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Mamy Boy">

    <!-- Custom Premium CSS -->
    <link href="/assets/css/style.css" rel="stylesheet">
</head>
<body>
    
    <header class="navbar navbar-dark sticky-top bg-primary-custom flex-md-nowrap p-0 shadow-sm">
        <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3 fs-5 fw-bold text-white d-flex align-items-center" href="/dashboard">
            <i class="bi bi-box-seam me-2"></i> ETS MAMY BOY
        </a>
        <div class="navbar-nav w-100 d-flex justify-content-end align-items-center flex-row px-3 gap-2">
            <button id="pwaInstallBtn" class="btn btn-sm btn-outline-light d-flex align-items-center gap-1 my-1" style="display:none!important;">
                <i class="bi bi-download"></i> Install App
            </button>
            <div class="nav-item text-nowrap d-none d-md-block">
                <span class="nav-link text-white px-3"><i class="bi bi-person-circle"></i> <?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?></span>
            </div>
            <div class="nav-item text-nowrap d-none d-md-block">
                <a class="nav-link text-white px-3 fw-bold" href="/logout"><i class="bi bi-box-arrow-right"></i> Sign out</a>
            </div>
        </div>
    </header>

    <div class="container-fluid main-content">
        <div class="row">
            <!-- Sidebar -->
            <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-4">
                    <ul class="nav flex-column mb-auto">
                        <li class="nav-item">
                            <a class="nav-link <?= $active_menu === 'dashboard' ? 'active' : '' ?>" href="/dashboard">
                                <i class="bi bi-grid-1x2"></i> Dashboard
                            </a>
                        </li>
                        
                        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-4 mt-4 mb-2 text-muted text-uppercase fs-7 fw-bold">
                            <span>Transactions</span>
                        </h6>
                        <li class="nav-item">
                            <a class="nav-link <?= $active_menu === 'purchases' || $active_menu === 'purchases_create' ? 'active' : '' ?>" href="/purchases">
                                <i class="bi bi-cart3"></i> Purchases
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $active_menu === 'crates' || $active_menu === 'crates_create' ? 'active' : '' ?>" href="/crates">
                                <i class="bi bi-box-arrow-in-down"></i> Empty Crates
                            </a>
                        </li>
                        
                        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-4 mt-4 mb-2 text-muted text-uppercase fs-7 fw-bold">
                            <span>Management</span>
                        </h6>
                        <li class="nav-item">
                            <a class="nav-link <?= $active_menu === 'agents' ? 'active' : '' ?>" href="/agents">
                                <i class="bi bi-people"></i> Agents
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $active_menu === 'ristourne' ? 'active' : '' ?>" href="/ristourne">
                                <i class="bi bi-cash-stack"></i> Ristourne
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $active_menu === 'reports' ? 'active' : '' ?>" href="/reports">
                                <i class="bi bi-file-earmark-bar-graph"></i> Reports
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $active_menu === 'goals' ? 'active' : '' ?>" href="/goals">
                                <i class="bi bi-bullseye"></i> Goals
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $active_menu === 'payment_request' ? 'active' : '' ?>" href="/payment-request">
                                <i class="bi bi-receipt-cutoff"></i> Payment Request
                            </a>
                        </li>
                        
                        <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'ADMIN'): ?>
                        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-4 mt-4 mb-2 text-muted text-uppercase fs-7 fw-bold">
                            <span>Admin</span>
                        </h6>
                        <li class="nav-item">
                            <a class="nav-link <?= $active_menu === 'users' ? 'active' : '' ?>" href="/users">
                                <i class="bi bi-person-gear"></i> Users
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $active_menu === 'settings' ? 'active' : '' ?>" href="/settings">
                                <i class="bi bi-gear"></i> Settings
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </nav>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                
                <?php if (isset($_SESSION['flash_success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i> <?= htmlspecialchars($_SESSION['flash_success']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['flash_success']); ?>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['flash_error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= htmlspecialchars($_SESSION['flash_error']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['flash_error']); ?>
                <?php endif; ?>

                <?php echo $content; ?>
                
            </main>
        </div>
    </div>

    <!-- Mobile Bottom Nav -->
    <nav class="mobile-bottom-nav">
        <div class="mobile-nav-container">
            <a href="/dashboard" class="mobile-nav-item <?= $active_menu === 'dashboard' ? 'active' : '' ?>">
                <i class="bi <?= $active_menu === 'dashboard' ? 'bi-grid-1x2-fill' : 'bi-grid-1x2' ?>"></i>
                Home
            </a>
            <a href="/purchases" class="mobile-nav-item <?= strpos($active_menu, 'purchases') !== false ? 'active' : '' ?>">
                <i class="bi <?= strpos($active_menu, 'purchases') !== false ? 'bi-cart-fill' : 'bi-cart' ?>"></i>
                Buy
            </a>
            <a href="/crates" class="mobile-nav-item <?= strpos($active_menu, 'crates') !== false ? 'active' : '' ?>">
                <i class="bi <?= strpos($active_menu, 'crates') !== false ? 'bi-box-fill' : 'bi-box' ?>"></i>
                Returns
            </a>
            <a href="/goals" class="mobile-nav-item <?= $active_menu === 'goals' ? 'active' : '' ?>">
                <i class="bi <?= $active_menu === 'goals' ? 'bi-bullseye' : 'bi-bullseye' ?>"></i>
                Goals
            </a>
            <a href="/logout" class="mobile-nav-item text-danger">
                <i class="bi bi-box-arrow-right"></i>
                Exit
            </a>
        </div>
    </nav>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- PWA Service Worker Registration -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw.js').then(function(reg) {
                    console.log('ServiceWorker registered with scope: ', reg.scope);
                }, function(err) {
                    console.log('ServiceWorker registration failed: ', err);
                });
            });
        }

        let deferredPrompt;
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            const installBtn = document.getElementById('pwaInstallBtn');
            if (installBtn) {
                installBtn.style.display = 'inline-block';
                installBtn.addEventListener('click', () => {
                    deferredPrompt.prompt();
                    deferredPrompt.userChoice.then((choiceResult) => {
                        if (choiceResult.outcome === 'accepted') {
                            console.log('User accepted the PWA install prompt');
                        }
                        deferredPrompt = null;
                    });
                });
            }
        });

        function triggerPrintReport() {
            try {
                window.focus();
                if (typeof window.print === 'function') {
                    window.print();
                } else {
                    alert('Printing is not directly supported in this browser. Please use your browser menu -> Share/Print to generate a PDF.');
                }
            } catch (err) {
                console.error('Print error:', err);
                alert('Could not launch print dialog. Please try printing from your browser menu.');
            }
        }
    </script>
</body>
</html>
