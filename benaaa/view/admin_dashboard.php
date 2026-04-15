<?php
// view/admin_dashboard.php

include("../config/database.php");
include("../controller/traitement_admin.php");

// Récupération des données
$total_users = getTotalUsers($cnx);
$total_clients = getTotalClients($cnx);
$total_products = getTotalProducts($cnx);
$low_stock = getLowStock($cnx);
$total_orders = getTotalOrders($cnx);
$pending_orders = getPendingOrders($cnx);
$confirmed_orders = getConfirmedOrders($cnx);
$shipped_orders = getShippedOrders($cnx);
$delivered_orders = getDeliveredOrders($cnx);
$total_revenue = getTotalRevenue($cnx);
$total_livreurs = getTotalLivreurs($cnx);
$available_livreurs = getAvailableLivreurs($cnx);
$total_usines = getTotalUsines($cnx);
$open_reclamations = getOpenReclamations($cnx);
$paid_payments = getPaidPayments($cnx);

$monthlySales = getMonthlySales($cnx);
$topProducts = getTopProducts($cnx);
$latestOrders = getLatestOrders($cnx);
$users = getAllUsersList($cnx);
$products = getAllProductsList($cnx);
$livreurs = getAllLivreursList($cnx);
$usines = getAllUsinesList($cnx);
$recentClaims = getRecentClaimsList($cnx);
$activeDeliveries = getActiveDeliveriesList($cnx);

$theme = $_COOKIE['theme'] ?? 'light';
$currentPage = $_GET['page'] ?? 'dashboard';

$pages = ['dashboard', 'users', 'products', 'orders', 'delivery', 'factories', 'claims', 'livraisons', 'statistics', 'settings'];
if (!in_array($currentPage, $pages)) {
    $currentPage = 'dashboard';
}

if (isset($_GET['theme'])) {
    $newTheme = $_GET['theme'] === 'dark' ? 'dark' : 'light';
    setcookie('theme', $newTheme, time() + 365 * 24 * 3600, '/');
    header("Location: admin_dashboard.php?page=" . $currentPage);
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Benna Admin - Dashboard</title>
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

        h1, h2, h3, h4, h5, h6, p, span, div, a, button, input, select, textarea, th, td, li, label {
            font-family: var(--font-playfair);
            font-weight: 700;
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
        }

        .menu-item {
            padding: 10px 25px;
            margin: 3px 0;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 12px;
            border-left: 3px solid transparent;
        }

        .menu-item a {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 12px;
            width: 100%;
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
        }

        .welcome-text h2 i {
            color: var(--gold);
            margin-right: 8px;
        }

        .welcome-text p {
            color: var(--muted);
            font-size: 0.85rem;
            margin-top: 5px;
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
            text-decoration: none;
            color: var(--fg);
            display: inline-block;
        }

        .theme-toggle:hover {
            background: var(--gold);
            color: white;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
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
        }

        .user-role {
            font-size: 0.7rem;
            color: var(--gold);
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
            color: var(--fg);
            margin-bottom: 5px;
        }

        .stat-change {
            font-size: 0.7rem;
            display: flex;
            align-items: center;
            gap: 5px;
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
        }

        .card-header .subtitle {
            font-size: 0.7rem;
            color: var(--muted);
            margin-top: 4px;
        }

        .card-header a {
            color: var(--gold);
            text-decoration: none;
            font-size: 0.8rem;
            transition: color 0.3s;
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
        }

        .device-value {
            font-weight: 700;
            color: var(--gold);
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
        }

        .settings-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 0;
            cursor: pointer;
            transition: all 0.3s;
            border-radius: 8px;
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
        }

        .data-table td {
            padding: 12px 8px;
            font-size: 0.85rem;
            border-bottom: 1px solid var(--border);
        }

        .badge {
            padding: 4px 12px;
            border-radius: 30px;
            font-size: 0.7rem;
            font-weight: 500;
        }

        .badge-success { background: var(--green); color: white; }
        .badge-warning { background: var(--gold); color: white; }
        .badge-danger { background: var(--red); color: white; }
        .badge-info { background: var(--terracotta); color: white; }
        .badge-secondary { background: #95a5a6; color: white; }

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

        .btn-action {
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.7rem;
        }
        .btn-edit { background: var(--gold); color: white; }
        .btn-delete { background: var(--red); color: white; }
        .btn-view { background: var(--green); color: white; }

        /* Responsive */
        @media (max-width: 1200px) {
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .three-columns { grid-template-columns: 1fr; }
        }

        @media (max-width: 992px) {
            .two-columns { grid-template-columns: 1fr; }
        }

        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .main-content { margin-left: 0; }
            .stats-grid { grid-template-columns: 1fr; }
        }

        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: var(--cream); }
        ::-webkit-scrollbar-thumb { background: var(--green); border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--gold); }
    </style>
</head>
<body class="<?= $theme === 'dark' ? 'dark' : '' ?>">

<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <h3>BENNA<span>ADMIN</span></h3>
        <p>Organic Dashboard</p>
    </div>
    <div class="sidebar-menu">
        <div class="menu-category">GENERAL</div>
        <div class="menu-item <?= $currentPage == 'dashboard' ? 'active' : '' ?>">
            <a href="admin_dashboard.php?page=dashboard"><i class="fas fa-leaf"></i><span>Dashboard</span></a>
        </div>
        <div class="menu-item <?= $currentPage == 'users' ? 'active' : '' ?>">
            <a href="admin_dashboard.php?page=users"><i class="fas fa-users"></i><span>Utilisateurs</span></a>
        </div>
        <div class="menu-item <?= $currentPage == 'products' ? 'active' : '' ?>">
            <a href="admin_dashboard.php?page=products"><i class="fas fa-apple-alt"></i><span>Produits</span></a>
        </div>
        <div class="menu-item <?= $currentPage == 'orders' ? 'active' : '' ?>">
            <a href="admin_dashboard.php?page=orders"><i class="fas fa-shopping-bag"></i><span>Commandes</span></a>
        </div>
        
        <div class="menu-category">LOGISTIQUE</div>
        <div class="menu-item <?= $currentPage == 'delivery' ? 'active' : '' ?>">
            <a href="admin_dashboard.php?page=delivery"><i class="fas fa-truck"></i><span>Livreurs</span></a>
        </div>
        <div class="menu-item <?= $currentPage == 'factories' ? 'active' : '' ?>">
            <a href="admin_dashboard.php?page=factories"><i class="fas fa-factory"></i><span>Usines</span></a>
        </div>
        <div class="menu-item <?= $currentPage == 'livraisons' ? 'active' : '' ?>">
            <a href="admin_dashboard.php?page=livraisons"><i class="fas fa-boxes"></i><span>Livraisons</span></a>
        </div>
        
        <div class="menu-category">SERVICE</div>
        <div class="menu-item <?= $currentPage == 'claims' ? 'active' : '' ?>">
            <a href="admin_dashboard.php?page=claims"><i class="fas fa-comment"></i><span>Réclamations</span></a>
        </div>
        <div class="menu-item <?= $currentPage == 'statistics' ? 'active' : '' ?>">
            <a href="admin_dashboard.php?page=statistics"><i class="fas fa-chart-line"></i><span>Statistiques</span></a>
        </div>
        
        <div class="menu-category">SYSTEME</div>
        <div class="menu-item <?= $currentPage == 'settings' ? 'active' : '' ?>">
            <a href="admin_dashboard.php?page=settings"><i class="fas fa-sliders-h"></i><span>Paramètres</span></a>
        </div>
    </div>
</div>

<div class="main-content">
    <div class="top-bar">
        <div class="welcome-text">
            <h2><i class="fas fa-seedling"></i> Benna</h2>
            <p>Welcome to your organic admin dashboard</p>
        </div>
        <div class="user-profile">
            <a href="admin_dashboard.php?page=<?= $currentPage ?>&theme=<?= $theme === 'dark' ? 'light' : 'dark' ?>" class="theme-toggle">
                <i class="fas fa-<?= $theme === 'dark' ? 'sun' : 'moon' ?>"></i>
            </a>
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
    <div id="dashboardPage" class="page <?= $currentPage == 'dashboard' ? 'active' : '' ?>">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-header"><span class="stat-title">Utilisateurs</span><div class="stat-icon"><i class="fas fa-users"></i></div></div>
                <div class="stat-value"><?= number_format($total_users) ?></div>
                <div class="stat-change positive"><i class="fas fa-arrow-up"></i> +12.5%</div>
            </div>
            <div class="stat-card">
                <div class="stat-header"><span class="stat-title">Produits</span><div class="stat-icon"><i class="fas fa-apple-alt"></i></div></div>
                <div class="stat-value"><?= number_format($total_products) ?></div>
                <div class="stat-change <?= $low_stock > 0 ? 'negative' : 'positive' ?>">
                    <?php if($low_stock > 0): ?>
                        <i class="fas fa-exclamation-triangle"></i> <?= $low_stock ?> stock faible
                    <?php else: ?>
                        <i class="fas fa-check-circle"></i> Stock OK
                    <?php endif; ?>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-header"><span class="stat-title">Commandes</span><div class="stat-icon"><i class="fas fa-shopping-bag"></i></div></div>
                <div class="stat-value"><?= number_format($total_orders) ?></div>
                <div class="stat-change positive"><i class="fas fa-arrow-up"></i> +18.3%</div>
            </div>
            <div class="stat-card">
                <div class="stat-header"><span class="stat-title">Chiffre d'affaires</span><div class="stat-icon"><i class="fas fa-euro-sign"></i></div></div>
                <div class="stat-value">€<?= number_format($total_revenue ?? 0, 0) ?></div>
                <div class="stat-change positive"><i class="fas fa-arrow-up"></i> +23.7%</div>
            </div>
            <div class="stat-card">
                <div class="stat-header"><span class="stat-title">Livreurs</span><div class="stat-icon"><i class="fas fa-truck"></i></div></div>
                <div class="stat-value"><?= number_format($total_livreurs) ?></div>
                <div class="stat-change positive"><i class="fas fa-user-check"></i> <?= $available_livreurs ?> disponibles</div>
            </div>
            <div class="stat-card">
                <div class="stat-header"><span class="stat-title">Réclamations</span><div class="stat-icon"><i class="fas fa-comment"></i></div></div>
                <div class="stat-value"><?= number_format($open_reclamations) ?></div>
                <div class="stat-change negative"><i class="fas fa-clock"></i> En attente</div>
            </div>
            <div class="stat-card">
                <div class="stat-header"><span class="stat-title">Usines</span><div class="stat-icon"><i class="fas fa-factory"></i></div></div>
                <div class="stat-value"><?= number_format($total_usines) ?></div>
                <div class="stat-change positive">Partenaires</div>
            </div>
            <div class="stat-card">
                <div class="stat-header"><span class="stat-title">Paiements</span><div class="stat-icon"><i class="fas fa-credit-card"></i></div></div>
                <div class="stat-value">€<?= number_format($paid_payments ?? 0, 0) ?></div>
                <div class="stat-change positive">Validés</div>
            </div>
        </div>

        <div class="two-columns">
            <div class="card">
                <div class="card-header">
                    <div>
                        <h3>Ventes mensuelles</h3>
                        <div class="subtitle">Performance <?= date('Y') ?></div>
                    </div>
                    <a href="#">Détails <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="chart-container">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3>Top produits</h3>
                    <a href="#">Voir tout</a>
                </div>
                <?php if(!empty($topProducts)): ?>
                    <?php foreach($topProducts as $index => $product): ?>
                    <div class="campaign-item">
                        <span class="campaign-name"><?= htmlspecialchars($product['name']) ?></span>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?= min(100, ($product['total_sold'] / max($topProducts[0]['total_sold'] ?? 1, 1)) * 100) ?>%"></div>
                        </div>
                        <span class="campaign-percentage"><?= $product['total_sold'] ?> vendus</span>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="campaign-item">
                        <span class="campaign-name">Aucune donnée disponible</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="three-columns">
            <div class="card">
                <div class="card-header">
                    <h3>Statut commandes</h3>
                    <a href="#">Voir tout</a>
                </div>
                <div class="campaign-item">
                    <span class="campaign-name">En attente</span>
                    <div class="progress-bar"><div class="progress-fill" style="width: <?= $total_orders > 0 ? ($pending_orders / $total_orders * 100) : 0 ?>%"></div></div>
                    <span class="campaign-percentage"><?= $pending_orders ?></span>
                </div>
                <div class="campaign-item">
                    <span class="campaign-name">Confirmées</span>
                    <div class="progress-bar"><div class="progress-fill" style="width: <?= $total_orders > 0 ? ($confirmed_orders / $total_orders * 100) : 0 ?>%"></div></div>
                    <span class="campaign-percentage"><?= $confirmed_orders ?></span>
                </div>
                <div class="campaign-item">
                    <span class="campaign-name">Expédiées</span>
                    <div class="progress-bar"><div class="progress-fill" style="width: <?= $total_orders > 0 ? ($shipped_orders / $total_orders * 100) : 0 ?>%"></div></div>
                    <span class="campaign-percentage"><?= $shipped_orders ?></span>
                </div>
                <div class="campaign-item">
                    <span class="campaign-name">Livrées</span>
                    <div class="progress-bar"><div class="progress-fill" style="width: <?= $total_orders > 0 ? ($delivered_orders / $total_orders * 100) : 0 ?>%"></div></div>
                    <span class="campaign-percentage"><?= $delivered_orders ?></span>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3>Réclamations récentes</h3>
                    <a href="#">Voir tout</a>
                </div>
                <?php if(!empty($recentClaims)): ?>
                    <?php foreach($recentClaims as $claim): ?>
                    <div class="device-item">
                        <div class="device-info">
                            <div class="device-icon">
                                <i class="fas fa-<?= $claim['statut'] == 'OUVERT' ? 'exclamation-circle' : ($claim['statut'] == 'EN_COURS' ? 'spinner fa-pulse' : 'check-circle') ?>"></i>
                            </div>
                            <span class="device-name"><?= htmlspecialchars(substr($claim['user_name'], 0, 15)) ?></span>
                        </div>
                        <span class="badge badge-<?= $claim['statut'] == 'OUVERT' ? 'danger' : ($claim['statut'] == 'EN_COURS' ? 'warning' : 'success') ?>"><?= $claim['statut'] ?></span>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="device-item">
                        <div class="device-info">
                            <span class="device-name">Aucune réclamation</span>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3>Dernières commandes</h3>
                    <a href="#">Voir tout</a>
                </div>
                <table class="data-table">
                    <thead>
                        <tr><th>ID</th><th>Client</th><th>Total</th><th>Statut</th></tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($latestOrders)): ?>
                            <?php foreach($latestOrders as $order): ?>
                            <tr>
                                <td>#<?= $order['id_order'] ?></td>
                                <td><?= htmlspecialchars(substr($order['user_name'], 0, 12)) ?></td>
                                <td>€<?= number_format($order['total'], 0) ?></td>
                                <td><span class="badge badge-<?= $order['status'] == 'DELIVERED' ? 'success' : ($order['status'] == 'PENDING' ? 'warning' : 'info') ?>"><?= $order['status'] ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4" style="text-align:center">Aucune commande</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Users Page -->
    <div id="usersPage" class="page <?= $currentPage == 'users' ? 'active' : '' ?>">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-users"></i> Gestion des utilisateurs</h3>
                <a href="#">Ajouter</a>
            </div>
            <table class="data-table">
                <thead>
                    <tr><th>ID</th><th>Nom</th><th>Email</th><th>Rôle</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php foreach($users as $user): ?>
                    <tr>
                        <td>#<?= $user['id_user'] ?></td>
                        <td><?= htmlspecialchars($user['name']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><span class="badge badge-<?= $user['role'] == 'ADMIN' ? 'success' : 'secondary' ?>"><?= $user['role'] ?? 'CLIENT' ?></span></td>
                        <td>
                            <button class="btn-action btn-edit">Modifier</button>
                            <button class="btn-action btn-delete">Supprimer</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Products Page -->
    <div id="productsPage" class="page <?= $currentPage == 'products' ? 'active' : '' ?>">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-apple-alt"></i> Gestion des produits</h3>
                <a href="#">Ajouter</a>
            </div>
            <table class="data-table">
                <thead>
                    <tr><th>ID</th><th>Nom</th><th>Prix</th><th>Stock</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php foreach($products as $product): ?>
                    <tr>
                        <td>#<?= $product['id_product'] ?></td>
                        <td><?= htmlspecialchars($product['name']) ?></td>
                        <td>€<?= number_format($product['price'], 2) ?></td>
                        <td><?= $product['stock'] > 0 ? $product['stock'] : '<span class="badge badge-danger">Rupture</span>' ?></td>
                        <td>
                            <button class="btn-action btn-edit">Modifier</button>
                            <button class="btn-action btn-delete">Supprimer</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Orders Page -->
    <div id="ordersPage" class="page <?= $currentPage == 'orders' ? 'active' : '' ?>">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-shopping-bag"></i> Toutes les commandes</h3>
                <a href="#">Exporter</a>
            </div>
            <table class="data-table">
                <thead>
                    <tr><th>ID</th><th>Client</th><th>Total</th><th>Statut</th><th>Date</th></tr>
                </thead>
                <tbody>
                    <?php foreach($latestOrders as $order): ?>
                    <tr>
                        <td>#<?= $order['id_order'] ?></td>
                        <td><?= htmlspecialchars($order['user_name']) ?></td>
                        <td>€<?= number_format($order['total'], 2) ?></td>
                        <td><span class="badge badge-<?= $order['status'] == 'DELIVERED' ? 'success' : ($order['status'] == 'PENDING' ? 'warning' : 'info') ?>"><?= $order['status'] ?></span></td>
                        <td><?= date('d/m/Y', strtotime($order['created_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Delivery Page (Livreurs) -->
    <div id="deliveryPage" class="page <?= $currentPage == 'delivery' ? 'active' : '' ?>">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-truck"></i> Gestion des livreurs</h3>
                <a href="#">Ajouter</a>
            </div>
            <table class="data-table">
                <thead>
                    <tr><th>ID</th><th>Nom</th><th>Email</th><th>Téléphone</th><th>Véhicule</th><th>Statut</th></tr>
                </thead>
                <tbody>
                    <?php foreach($livreurs as $livreur): ?>
                    <tr>
                        <td>#<?= $livreur['id_livreur'] ?></td>
                        <td><?= htmlspecialchars($livreur['user_name']) ?></td>
                        <td><?= htmlspecialchars($livreur['email']) ?></td>
                        <td><?= $livreur['phone'] ?? '-' ?></td>
                        <td><?= $livreur['vehicle'] ?? '-' ?></td>
                        <td><span class="badge badge-<?= $livreur['status'] == 'AVAILABLE' ? 'success' : 'danger' ?>"><?= $livreur['status'] ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Factories Page (Usines) -->
    <div id="factoriesPage" class="page <?= $currentPage == 'factories' ? 'active' : '' ?>">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-factory"></i> Gestion des usines</h3>
                <a href="#">Ajouter</a>
            </div>
            <table class="data-table">
                <thead>
                    <tr><th>ID</th><th>Nom</th><th>Localisation</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php foreach($usines as $usine): ?>
                    <tr>
                        <td>#<?= $usine['id_usine'] ?></td>
                        <td><?= htmlspecialchars($usine['name']) ?></td>
                        <td><?= htmlspecialchars($usine['location']) ?></td>
                        <td><button class="btn-action btn-edit">Modifier</button></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Livraisons Page -->
    <div id="livraisonsPage" class="page <?= $currentPage == 'livraisons' ? 'active' : '' ?>">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-boxes"></i> Livraisons en cours</h3>
                <a href="#">Voir tout</a>
            </div>
            <table class="data-table">
                <thead>
                    <tr><th>ID</th><th>Client</th><th>Statut</th><th>Date estimée</th><th>Code suivi</th></tr>
                </thead>
                <tbody>
                    <?php foreach($activeDeliveries as $delivery): ?>
                    <tr>
                        <td>#<?= $delivery['id'] ?></td>
                        <td><?= htmlspecialchars($delivery['user_name']) ?></td>
                        <td><span class="badge badge-<?= $delivery['statut'] == 'EN_COURS' ? 'info' : 'warning' ?>"><?= $delivery['statut'] ?></span></td>
                        <td><?= date('d/m/Y', strtotime($delivery['date_estimee'])) ?></td>
                        <td><?= $delivery['code_suivi'] ?? '-' ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Claims Page -->
    <div id="claimsPage" class="page <?= $currentPage == 'claims' ? 'active' : '' ?>">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-comment"></i> Gestion des réclamations</h3>
                <a href="#">Exporter</a>
            </div>
            <table class="data-table">
                <thead>
                    <tr><th>ID</th><th>Client</th><th>Sujet</th><th>Statut</th><th>Date</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php foreach($recentClaims as $claim): ?>
                    <tr>
                        <td>#<?= $claim['id'] ?></td>
                        <td><?= htmlspecialchars($claim['user_name']) ?></td>
                        <td><?= htmlspecialchars(substr($claim['sujet'], 0, 30)) ?>...</td>
                        <td><span class="badge badge-<?= $claim['statut'] == 'OUVERT' ? 'danger' : ($claim['statut'] == 'EN_COURS' ? 'warning' : 'success') ?>"><?= $claim['statut'] ?></span></td>
                        <td><?= date('d/m/Y', strtotime($claim['date_creation'])) ?></td>
                        <td><button class="btn-action btn-view">Traiter</button></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Statistics Page -->
    <div id="statisticsPage" class="page <?= $currentPage == 'statistics' ? 'active' : '' ?>">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-header"><span class="stat-title">Taux conversion</span><div class="stat-icon"><i class="fas fa-chart-line"></i></div></div>
                <div class="stat-value"><?= $total_orders > 0 ? round($delivered_orders / $total_orders * 100, 1) : 0 ?>%</div>
            </div>
            <div class="stat-card">
                <div class="stat-header"><span class="stat-title">Panier moyen</span><div class="stat-icon"><i class="fas fa-shopping-cart"></i></div></div>
                <div class="stat-value">€<?= $total_orders > 0 ? number_format($total_revenue / $total_orders, 0) : 0 ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-header"><span class="stat-title">Clients actifs</span><div class="stat-icon"><i class="fas fa-user-check"></i></div></div>
                <div class="stat-value"><?= number_format($total_clients) ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-header"><span class="stat-title">Taux réclamation</span><div class="stat-icon"><i class="fas fa-percent"></i></div></div>
                <div class="stat-value"><?= $total_orders > 0 ? round($open_reclamations / $total_orders * 100, 1) : 0 ?>%</div>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><h3>Performance détaillée</h3><a href="#">Exporter</a></div>
            <div class="chart-container" style="height: 400px;">
                <canvas id="detailedChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Settings Page -->
    <div id="settingsPage" class="page <?= $currentPage == 'settings' ? 'active' : '' ?>">
        <div class="card">
            <div class="card-header"><h3>Paramètres système</h3><a href="#">Enregistrer</a></div>
            <div class="settings-list">
                <div class="settings-category">
                    <div class="settings-category-title">Général</div>
                    <div class="settings-item"><i class="fas fa-globe"></i><span>Langue du site</span></div>
                    <div class="settings-item"><i class="fas fa-bell"></i><span>Notifications</span></div>
                    <div class="settings-item"><i class="fas fa-lock"></i><span>Sécurité</span></div>
                </div>
                <div class="settings-category">
                    <div class="settings-category-title">E-commerce</div>
                    <div class="settings-item"><i class="fas fa-truck"></i><span>Frais de livraison</span></div>
                    <div class="settings-item"><i class="fas fa-percent"></i><span>TVA</span></div>
                    <div class="settings-item"><i class="fas fa-credit-card"></i><span>Méthodes de paiement</span></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Sales Chart
const salesCtx = document.getElementById('salesChart')?.getContext('2d');
if (salesCtx) {
    new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'],
            datasets: [{
                label: 'Ventes (€)',
                data: <?= json_encode($monthlySales) ?>,
                borderColor: 'hsl(142, 34%, 28%)',
                backgroundColor: 'rgba(82, 121, 111, 0.05)',
                tension: 0.4,
                fill: true,
                pointBackgroundColor: 'hsl(142, 34%, 28%)',
                pointBorderColor: 'white',
                pointRadius: 4
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, grid: { color: 'var(--border)' } }, x: { grid: { display: false } } } }
    });
}

// Detailed Chart
const detailedCtx = document.getElementById('detailedChart')?.getContext('2d');
if (detailedCtx) {
    new Chart(detailedCtx, {
        type: 'bar',
        data: {
            labels: ['Commandes', 'Livrées', 'Clients', 'Produits', 'Réclamations'],
            datasets: [{
                label: 'Statistiques',
                data: [<?= $total_orders ?>, <?= $delivered_orders ?>, <?= $total_clients ?>, <?= $total_products ?>, <?= $open_reclamations ?>],
                backgroundColor: ['hsl(142, 34%, 28%)', 'hsl(40, 60%, 52%)', 'hsl(14, 55%, 55%)', 'hsl(142, 35%, 40%)', 'hsl(0, 75%, 45%)'],
                borderRadius: 8
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'top' } } }
    });
}

// Mobile sidebar toggle
function toggleSidebar() { document.getElementById('sidebar').classList.toggle('open'); }
if (window.innerWidth <= 768) {
    const topBar = document.querySelector('.top-bar');
    const menuBtn = document.createElement('button');
    menuBtn.innerHTML = '<i class="fas fa-bars"></i>';
    menuBtn.style.cssText = 'background: none; border: none; font-size: 1.5rem; cursor: pointer; margin-right: 15px; color: var(--green);';
    menuBtn.onclick = toggleSidebar;
    document.querySelector('.welcome-text').prepend(menuBtn);
}
</script>
</body>
</html>