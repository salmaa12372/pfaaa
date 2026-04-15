<?php
// admin/index.php
session_start();
require __DIR__ . '/../config/database.php';

// Statistiques - adaptées à votre structure
$stats = [];

// Vérifier les colonnes existantes et adapter les requêtes
try {
    // Utilisateurs
    $stats['total_users'] = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    
    // Produits - sans created_at
    $stats['total_products'] = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    $stats['low_stock'] = $pdo->query("SELECT COUNT(*) FROM products WHERE stock < 10")->fetchColumn();
    
    // Commandes
    $stats['total_orders'] = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    $stats['pending_orders'] = $pdo->query("SELECT COUNT(*) FROM orders WHERE status='pending'")->fetchColumn();
    $stats['delivered_orders'] = $pdo->query("SELECT COUNT(*) FROM orders WHERE status='delivered'")->fetchColumn();
    
    // Revenus
    $stats['total_revenue'] = $pdo->query("SELECT SUM(total) FROM orders WHERE status='delivered'")->fetchColumn();
    
} catch(PDOException $e) {
    // Gérer l'erreur silencieusement ou logger
    $stats['total_users'] = 0;
    $stats['total_products'] = 0;
    $stats['total_orders'] = 0;
    $stats['total_revenue'] = 0;
}

// Données pour les graphiques
$campaignData = [
    'Facebook Campaign' => 78,
    'Twitter Campaign' => 62,
    'Conventional Media' => 45,
    'Billboards' => 38
];

$deviceData = [
    'iOS' => 123000,
    'Android' => 53000,
    'Blackberry' => 23000,
    'Symbian' => 3000,
    'Others' => 1000
];

// Récupération des commandes récentes - adapter selon votre structure
try {
    // Essayer avec created_at d'abord
    $latestOrders = $pdo->query("
        SELECT o.*, u.name 
        FROM orders o
        JOIN users u ON o.id_user = u.id_user
        ORDER BY o.id_order DESC 
        LIMIT 6
    ")->fetchAll();
} catch(PDOException $e) {
    // Fallback sans date
    $latestOrders = $pdo->query("
        SELECT o.*, u.name 
        FROM orders o
        JOIN users u ON o.id_user = u.id_user
        LIMIT 6
    ")->fetchAll();
}

// Récupération des utilisateurs - sans created_at
try {
    $users = $pdo->query("SELECT * FROM users LIMIT 10")->fetchAll();
} catch(PDOException $e) {
    $users = [];
}

// Récupération des produits - sans created_at
try {
    $products = $pdo->query("SELECT * FROM products LIMIT 10")->fetchAll();
} catch(PDOException $e) {
    $products = [];
}

// Vérifier si la table delivery_men existe
try {
    $deliveryMen = $pdo->query("SELECT * FROM delivery_men LIMIT 5")->fetchAll();
} catch(PDOException $e) {
    $deliveryMen = [];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Natur'Admin - Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;0,800;0,900;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --bg: hsl(90, 100%, 89%);
            --fg: hsl(25, 18%, 14%);
            --card: hsl(90, 80%, 97%);
            --cream: hsl(90, 60%, 95%);
            --cream-dark: hsl(90, 50%, 90%);
            --green: hsl(142, 34%, 28%);
            --green-light: hsl(142, 35%, 40%);
            --green-dark: hsl(142, 38%, 18%);
            --gold: hsl(40, 60%, 52%);
            --gold-light: hsl(40, 52%, 70%);
            --terracotta: hsl(14, 55%, 55%);
            --border: hsl(40, 15%, 85%);
            --muted: hsl(30, 15%, 45%);
            --red: hsl(0, 75%, 45%);
            --shadow: 0 12px 50px rgba(0, 0, 0, 0.08);
            --font-playfair: "Playfair Display", Georgia, serif;
        }

        body.dark {
            --bg: hsl(0, 0%, 4%);
            --fg: hsl(40, 33%, 96%);
            --card: hsl(146, 26%, 7%);
            --cream: hsl(150, 20%, 12%);
            --cream-dark: hsl(0, 0%, 6%);
            --border: hsl(150, 10%, 22%);
            --muted: hsl(40, 10%, 65%);
            --shadow: 0 12px 50px rgba(0, 0, 0, 0.5);
        }

        body {
            font-family: var(--font-playfair);
            background: var(--bg);
            color: var(--fg);
            overflow-x: hidden;
            transition: background 0.3s, color 0.3s;
        }

        /* Tout le texte utilise Playfair Display */
        h1, h2, h3, h4, h5, h6, p, span, div, a, button, input, select, textarea, th, td, li, label {
            font-family: var(--font-playfair);
            font-weight:700;
        }

        /* Sidebar */
        .sidebar {
            width: 260px;
            background: linear-gradient(135deg, var(--green-dark) 0%, var(--green) 100%);
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            color: white;
            transition: all 0.3s;
            z-index: 1000;
            box-shadow: var(--shadow);
        }

        .sidebar-header {
            padding: 30px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.15);
        }

        .sidebar-header h3 {
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: 2px;
            font-family: var(--font-playfair);
        }

        .sidebar-header h3 span {
            font-weight: 400;
            color: var(--gold-light);
        }

        .sidebar-header p {
            font-size: 0.7rem;
            margin-top: 8px;
            opacity: 0.8;
            letter-spacing: 3px;
            font-family: var(--font-playfair);
        }

        .sidebar-menu {
            padding: 20px 0;
        }

        .menu-category {
            padding: 15px 25px 5px;
            font-size: 0.65rem;
            text-transform: uppercase;
            letter-spacing: 3px;
            font-weight: 600;
            opacity: 0.6;
            font-family: var(--font-playfair);
        }

        .menu-item {
            padding: 10px 25px;
            margin: 3px 0;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 12px;
            border-left: 3px solid transparent;
            font-family: var(--font-playfair);
        }

        .menu-item:hover {
            background: rgba(255,255,255,0.1);
            border-left-color: var(--gold);
        }

        .menu-item.active {
            background: rgba(255,255,255,0.12);
            border-left-color: var(--gold);
        }

        .menu-item i {
            width: 22px;
            font-size: 1rem;
        }

        /* Main Content */
        .main-content {
            margin-left: 260px;
            padding: 20px 25px;
        }

        /* Top Bar */
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding: 15px 20px;
            background: var(--card);
            border-radius: 16px;
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
        }

        .welcome-text h2 {
            font-size: 1.4rem;
            font-weight: 600;
            color: var(--green);
            font-family: var(--font-playfair);
        }

        .welcome-text h2 i {
            color: var(--gold);
            margin-right: 8px;
        }

        .welcome-text p {
            color: var(--muted);
            font-size: 0.85rem;
            margin-top: 5px;
            font-family: var(--font-playfair);
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .theme-toggle {
            cursor: pointer;
            padding: 8px 12px;
            background: var(--cream);
            border-radius: 30px;
            transition: all 0.3s;
            font-family: var(--font-playfair);
        }

        .theme-toggle:hover {
            background: var(--gold);
            color: white;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
        }

        .user-avatar {
            width: 42px;
            height: 42px;
            background: linear-gradient(135deg, var(--green), var(--gold));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        .user-details {
            text-align: right;
        }

        .user-name {
            font-weight: 700;
            color: var(--fg);
            font-family: var(--font-playfair);
        }

        .user-role {
            font-size: 0.7rem;
            color: var(--gold);
            font-family: var(--font-playfair);
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: var(--card);
            padding: 20px;
            border-radius: 20px;
            border: 1px solid var(--border);
            transition: all 0.3s;
            cursor: pointer;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow);
            border-color: var(--gold);
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .stat-title {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: var(--muted);
            font-family: var(--font-playfair);
        }

        .stat-icon {
            width: 45px;
            height: 45px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--cream), var(--bg));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: var(--green);
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            font-family: var(--font-playfair);
            color: var(--fg);
            margin-bottom: 5px;
        }

        .stat-change {
            font-size: 0.7rem;
            display: flex;
            align-items: center;
            gap: 5px;
            font-family: var(--font-playfair);
        }

        .stat-change.positive { color: var(--green); }
        .stat-change.negative { color: var(--red); }

        /* Cards */
        .card {
            background: var(--card);
            border-radius: 20px;
            padding: 20px;
            border: 1px solid var(--border);
            transition: all 0.3s;
        }

        .card:hover {
            box-shadow: var(--shadow);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border);
        }

        .card-header h3 {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--green);
            font-family: var(--font-playfair);
        }

        .card-header .subtitle {
            font-size: 0.7rem;
            color: var(--muted);
            margin-top: 4px;
            font-family: var(--font-playfair);
        }

        .card-header a {
            color: var(--gold);
            text-decoration: none;
            font-size: 0.8rem;
            transition: color 0.3s;
            font-family: var(--font-playfair);
        }

        .card-header a:hover {
            color: var(--terracotta);
        }

        /* Two/Three Columns */
        .two-columns {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            margin-bottom: 25px;
        }

        .three-columns {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;
            margin-bottom: 25px;
        }

        /* Campaign Items */
        .campaign-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid var(--border);
        }

        .campaign-item:last-child {
            border-bottom: none;
        }

        .campaign-name {
            font-weight: 500;
            min-width: 140px;
            font-family: var(--font-playfair);
        }

        .progress-bar {
            flex: 1;
            margin: 0 15px;
            height: 6px;
            background: var(--cream-dark);
            border-radius: 3px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--green), var(--gold));
            border-radius: 3px;
        }

        .campaign-percentage {
            font-weight: 700;
            color: var(--gold);
            min-width: 45px;
            text-align: right;
            font-family: var(--font-playfair);
        }

        /* Device Items */
        .device-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid var(--border);
        }

        .device-item:last-child {
            border-bottom: none;
        }

        .device-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .device-icon {
            width: 35px;
            height: 35px;
            background: var(--cream);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--green);
        }

        .device-name {
            font-weight: 500;
            font-family: var(--font-playfair);
        }

        .device-value {
            font-weight: 700;
            color: var(--gold);
            font-family: var(--font-playfair);
        }

        /* Settings */
        .settings-list {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .settings-category {
            margin-bottom: 15px;
        }

        .settings-category-title {
            font-size: 0.65rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: var(--muted);
            margin-bottom: 10px;
            font-family: var(--font-playfair);
        }

        .settings-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 0;
            cursor: pointer;
            transition: all 0.3s;
            border-radius: 8px;
            font-family: var(--font-playfair);
        }

        .settings-item:hover {
            background: var(--cream);
            padding-left: 10px;
        }

        .settings-item i {
            width: 25px;
            color: var(--gold);
        }

        /* Tables */
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th {
            text-align: left;
            padding: 12px 8px;
            font-size: 0.7rem;
            font-weight: 700;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 2px;
            border-bottom: 1px solid var(--border);
            font-family: var(--font-playfair);
        }

        .data-table td {
            padding: 12px 8px;
            font-size: 0.85rem;
            border-bottom: 1px solid var(--border);
            font-family: var(--font-playfair);
        }

        .badge {
            padding: 4px 12px;
            border-radius: 30px;
            font-size: 0.7rem;
            font-weight: 500;
            font-family: var(--font-playfair);
        }

        .badge-success { background: var(--green); color: white; }
        .badge-warning { background: var(--gold); color: white; }
        .badge-danger { background: var(--red); color: white; }
        .badge-info { background: var(--terracotta); color: white; }

        /* Chart Container */
        .chart-container {
            position: relative;
            height: 250px;
        }

        /* Sections */
        .page {
            display: none;
        }

        .page.active {
            display: block;
        }

        /* Buttons */
        button, .btn-like {
            font-family: var(--font-playfair);
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .three-columns {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 992px) {
            .two-columns {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.open {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
            }
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--cream);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--green);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--gold);
        }
    </style>
</head>
<body>

<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <h3>NATUR'<span>ADMIN</span></h3>
        <p>Organic Dashboard</p>
    </div>
    <div class="sidebar-menu">
        <div class="menu-category">GENERAL</div>
        <div class="menu-item active" onclick="showPage('dashboard')">
            <i class="fas fa-leaf"></i>
            <span>Dashboard</span>
        </div>
        <div class="menu-item" onclick="showPage('users')">
            <i class="fas fa-users"></i>
            <span>Utilisateurs</span>
        </div>
        <div class="menu-item" onclick="showPage('products')">
            <i class="fas fa-apple-alt"></i>
            <span>Produits</span>
        </div>
        <div class="menu-item" onclick="showPage('orders')">
            <i class="fas fa-shopping-bag"></i>
            <span>Commandes</span>
        </div>
        
        <div class="menu-category">NETWORK</div>
        <div class="menu-item" onclick="showPage('analytics')">
            <i class="fas fa-chart-line"></i>
            <span>Analytics</span>
        </div>
        <div class="menu-item" onclick="showPage('campaigns')">
            <i class="fas fa-bullhorn"></i>
            <span>Campaigns</span>
        </div>
        
        <div class="menu-category">SETTINGS</div>
        <div class="menu-item" onclick="showPage('settings')">
            <i class="fas fa-sliders-h"></i>
            <span>Quick Settings</span>
        </div>
        <div class="menu-item" onclick="logout()">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </div>
    </div>
</div>

<div class="main-content">
    <div class="top-bar">
        <div class="welcome-text">
            <h2><i class="fas fa-seedling"></i> Benna </h2>
            <p>Welcome to your organic admin dashboard</p>
        </div>
        <div class="user-profile">
            <div class="theme-toggle" onclick="toggleTheme()">
                <i class="fas fa-moon"></i>
            </div>
            <div class="user-info">
                <div class="user-details">
                    <div class="user-name">John Doe</div>
                    <div class="user-role">Administrator</div>
                </div>
                <div class="user-avatar">
                    <i class="fas fa-user"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Dashboard Page -->
    <div id="dashboardPage" class="page active">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-title">Total Users</span>
                    <div class="stat-icon"><i class="fas fa-users"></i></div>
                </div>
                <div class="stat-value"><?= number_format($stats['total_users']) ?></div>
                <div class="stat-change positive"><i class="fas fa-arrow-up"></i> +12.5%</div>
            </div>
            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-title">Total Products</span>
                    <div class="stat-icon"><i class="fas fa-apple-alt"></i></div>
                </div>
                <div class="stat-value"><?= number_format($stats['total_products']) ?></div>
                <div class="stat-change positive"><i class="fas fa-arrow-up"></i> +5.2%</div>
            </div>
            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-title">Total Orders</span>
                    <div class="stat-icon"><i class="fas fa-shopping-bag"></i></div>
                </div>
                <div class="stat-value"><?= number_format($stats['total_orders']) ?></div>
                <div class="stat-change positive"><i class="fas fa-arrow-up"></i> +18.3%</div>
            </div>
            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-title">Revenue</span>
                    <div class="stat-icon"><i class="fas fa-euro-sign"></i></div>
                </div>
                <div class="stat-value">€<?= number_format($stats['total_revenue'] ?? 0, 0) ?></div>
                <div class="stat-change positive"><i class="fas fa-arrow-up"></i> +23.7%</div>
            </div>
        </div>

        <div class="two-columns">
            <div class="card">
                <div class="card-header">
                    <div>
                        <h3>Network Activities</h3>
                        <div class="subtitle">Weekly performance overview</div>
                    </div>
                    <a href="#">Details <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="chart-container">
                    <canvas id="networkChart"></canvas>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3>Top Campaign Performance</h3>
                    <a href="#">View All</a>
                </div>
                <?php foreach($campaignData as $name => $value): ?>
                <div class="campaign-item">
                    <span class="campaign-name"><?= $name ?></span>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?= $value ?>%"></div>
                    </div>
                    <span class="campaign-percentage"><?= $value ?>%</span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="three-columns">
            <div class="card">
                <div class="card-header">
                    <h3>Device Usage</h3>
                    <a href="#">Top 5</a>
                </div>
                <?php foreach($deviceData as $device => $count): ?>
                <div class="device-item">
                    <div class="device-info">
                        <div class="device-icon">
                            <i class="fas fa-<?= $device == 'iOS' ? 'apple' : ($device == 'Android' ? 'android' : 'mobile-alt') ?>"></i>
                        </div>
                        <span class="device-name"><?= $device ?></span>
                    </div>
                    <span class="device-value"><?= number_format($count) ?></span>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3>Quick Settings</h3>
                    <a href="#">Settings</a>
                </div>
                <div class="settings-list">
                    <div class="settings-category">
                        <div class="settings-category-title">Subscription</div>
                        <div class="settings-item"><i class="fas fa-sync-alt"></i><span>Auto Renewal</span></div>
                        <div class="settings-item"><i class="fas fa-trophy"></i><span>Achievements</span></div>
                        <div class="settings-item"><i class="fas fa-sign-out-alt"></i><span>Logout</span></div>
                    </div>
                    <div class="settings-category">
                        <div class="settings-category-title">Achievements</div>
                        <div class="settings-item"><i class="fas fa-medal"></i><span>First Order</span></div>
                        <div class="settings-item"><i class="fas fa-star"></i><span>5 Star Rating</span></div>
                        <div class="settings-item"><i class="fas fa-gem"></i><span>Premium Member</span></div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3>Latest Orders</h3>
                    <a href="#">View All</a>
                </div>
                <table class="data-table">
                    <thead>
                        <tr><th>ID</th><th>Customer</th><th>Amount</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($latestOrders)): ?>
                            <?php foreach($latestOrders as $order): ?>
                            <tr>
                                <td>#<?= $order['id_order'] ?></td>
                                <td><?= htmlspecialchars(substr($order['name'], 0, 12)) ?></td>
                                <td>€<?= number_format($order['total'], 0) ?></td>
                                <td><span class="badge badge-<?= $order['status'] == 'delivered' ? 'success' : ($order['status'] == 'pending' ? 'warning' : 'info') ?>"><?= $order['status'] ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4" style="text-align:center">Aucune commande trouvée</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Users Page -->
    <div id="usersPage" class="page">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-users"></i> Gestion des utilisateurs</h3>
                <a href="#"><i class="fas fa-plus"></i> Ajouter</a>
            </div>
            <table class="data-table">
                <thead>
                    <tr><th>ID</th><th>Nom</th><th>Email</th><th>Rôle</th></tr>
                </thead>
                <tbody>
                    <?php if(!empty($users)): ?>
                        <?php foreach($users as $user): ?>
                        <tr>
                            <td>#<?= $user['id_user'] ?></td>
                            <td><?= htmlspecialchars($user['name']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= $user['role'] ?? 'Client' ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4" style="text-align:center">Aucun utilisateur trouvé</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Products Page -->
    <div id="productsPage" class="page">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-apple-alt"></i> Gestion des produits</h3>
                <a href="#"><i class="fas fa-plus"></i> Ajouter</a>
            </div>
            <table class="data-table">
                <thead>
                    <tr><th>ID</th><th>Nom</th><th>Prix</th><th>Stock</th><th>Catégorie</th></tr>
                </thead>
                <tbody>
                    <?php if(!empty($products)): ?>
                        <?php foreach($products as $product): ?>
                        <tr>
                            <td>#<?= $product['id_product'] ?></td>
                            <td><?= htmlspecialchars($product['name']) ?></td>
                            <td>€<?= number_format($product['price'], 2) ?></td>
                            <td><?= $product['stock'] ?? 'N/A' ?></td>
                            <td><?= $product['category'] ?? 'Bio' ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" style="text-align:center">Aucun produit trouvé</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Orders Page -->
    <div id="ordersPage" class="page">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-shopping-bag"></i> Toutes les commandes</h3>
                <a href="#">Exporter</a>
            </div>
            <table class="data-table">
                <thead>
                    <tr><th>ID</th><th>Client</th><th>Total</th><th>Status</th></tr>
                </thead>
                <tbody>
                    <?php if(!empty($latestOrders)): ?>
                        <?php foreach($latestOrders as $order): ?>
                        <tr>
                            <td>#<?= $order['id_order'] ?></td>
                            <td><?= htmlspecialchars($order['name']) ?></td>
                            <td>€<?= number_format($order['total'], 2) ?></td>
                            <td><span class="badge badge-<?= $order['status'] == 'delivered' ? 'success' : 'warning' ?>"><?= $order['status'] ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4" style="text-align:center">Aucune commande trouvée</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Analytics Page -->
    <div id="analyticsPage" class="page">
        <div class="card">
            <div class="card-header">
                <h3>Analytics Dashboard</h3>
                <a href="#">Refresh</a>
            </div>
            <div class="chart-container" style="height: 400px;">
                <canvas id="analyticsChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Campaigns Page -->
    <div id="campaignsPage" class="page">
        <div class="card">
            <div class="card-header">
                <h3>Campaign Management</h3>
                <a href="#">New Campaign</a>
            </div>
            <?php foreach($campaignData as $name => $value): ?>
            <div class="campaign-item">
                <span class="campaign-name"><?= $name ?></span>
                <div class="progress-bar"><div class="progress-fill" style="width: <?= $value ?>%"></div></div>
                <span class="campaign-percentage"><?= $value ?>%</span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Settings Page -->
    <div id="settingsPage" class="page">
        <div class="card">
            <div class="card-header">
                <h3>System Settings</h3>
                <a href="#">Save</a>
            </div>
            <div class="settings-list">
                <div class="settings-category">
                    <div class="settings-category-title">General Settings</div>
                    <div class="settings-item"><i class="fas fa-globe"></i><span>Site Language</span></div>
                    <div class="settings-item"><i class="fas fa-bell"></i><span>Notifications</span></div>
                    <div class="settings-item"><i class="fas fa-lock"></i><span>Security</span></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Network Chart
const ctx = document.getElementById('networkChart')?.getContext('2d');
if (ctx) {
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Jan 01', 'Jan 02', 'Jan 03', 'Jan 04', 'Jan 05', 'Jan 06'],
            datasets: [{
                label: 'Activity',
                data: [45, 62, 38, 72, 58, 85],
                borderColor: 'hsl(142, 34%, 28%)',
                backgroundColor: 'rgba(82, 121, 111, 0.05)',
                tension: 0.4,
                fill: true,
                pointBackgroundColor: 'hsl(142, 34%, 28%)',
                pointBorderColor: 'white',
                pointRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, max: 100, grid: { color: 'var(--border)' } }, x: { grid: { display: false } } }
        }
    });
}

// Analytics Chart
const analyticsCtx = document.getElementById('analyticsChart')?.getContext('2d');
if (analyticsCtx) {
    new Chart(analyticsCtx, {
        type: 'bar',
        data: {
            labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
            datasets: [{
                label: 'Sales (€)',
                data: [12500, 18200, 14800, 21500],
                backgroundColor: 'hsl(142, 34%, 28%)',
                borderRadius: 8
            }]
        },
        options: { 
            responsive: true, 
            maintainAspectRatio: false,
            plugins: {
                legend: { labels: { font: { family: 'Playfair Display' } } }
            }
        }
    });
}

// Page navigation
function showPage(pageName) {
    document.querySelectorAll('.page').forEach(page => page.classList.remove('active'));
    document.getElementById(pageName + 'Page').classList.add('active');
    
    document.querySelectorAll('.menu-item').forEach(item => item.classList.remove('active'));
    event.currentTarget.classList.add('active');
    
    if (window.innerWidth <= 768) document.getElementById('sidebar').classList.remove('open');
}

// Theme toggle
function toggleTheme() {
    document.body.classList.toggle('dark');
    localStorage.setItem('theme', document.body.classList.contains('dark') ? 'dark' : 'light');
}

// Load saved theme
if (localStorage.getItem('theme') === 'dark') document.body.classList.add('dark');

function logout() {
    window.location.href = '../logout.php';
}

// Mobile sidebar
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
}
</script>

</body>
</html>