<?php
// controller/traitement_admin.php

// ==================== FONCTIONS STATISTIQUES ====================

function getTotalUsers($cnx) {
    $req = $cnx->prepare("SELECT COUNT(*) as total FROM users");
    $req->execute();
    $result = $req->fetch();
    return $result['total'];
}

function getTotalClients($cnx) {
    $req = $cnx->prepare("SELECT COUNT(*) as total FROM users WHERE role = 'CLIENT'");
    $req->execute();
    $result = $req->fetch();
    return $result['total'];
}

function getTotalProducts($cnx) {
    $req = $cnx->prepare("SELECT COUNT(*) as total FROM products");
    $req->execute();
    $result = $req->fetch();
    return $result['total'];
}

function getLowStock($cnx) {
    $req = $cnx->prepare("SELECT COUNT(*) as total FROM products WHERE stock < 10 AND stock > 0");
    $req->execute();
    $result = $req->fetch();
    return $result['total'];
}

function getTotalOrders($cnx) {
    $req = $cnx->prepare("SELECT COUNT(*) as total FROM orders");
    $req->execute();
    $result = $req->fetch();
    return $result['total'];
}

function getPendingOrders($cnx) {
    $req = $cnx->prepare("SELECT COUNT(*) as total FROM orders WHERE status = 'PENDING'");
    $req->execute();
    $result = $req->fetch();
    return $result['total'];
}

function getConfirmedOrders($cnx) {
    $req = $cnx->prepare("SELECT COUNT(*) as total FROM orders WHERE status = 'CONFIRMED'");
    $req->execute();
    $result = $req->fetch();
    return $result['total'];
}

function getShippedOrders($cnx) {
    $req = $cnx->prepare("SELECT COUNT(*) as total FROM orders WHERE status = 'SHIPPED'");
    $req->execute();
    $result = $req->fetch();
    return $result['total'];
}

function getDeliveredOrders($cnx) {
    $req = $cnx->prepare("SELECT COUNT(*) as total FROM orders WHERE status = 'DELIVERED'");
    $req->execute();
    $result = $req->fetch();
    return $result['total'];
}

function getTotalRevenue($cnx) {
    $req = $cnx->prepare("SELECT SUM(total) as total FROM orders WHERE status = 'DELIVERED'");
    $req->execute();
    $result = $req->fetch();
    return $result['total'] ?? 0;
}

function getTotalLivreurs($cnx) {
    $req = $cnx->prepare("SELECT COUNT(*) as total FROM livreurs");
    $req->execute();
    $result = $req->fetch();
    return $result['total'];
}

function getAvailableLivreurs($cnx) {
    $req = $cnx->prepare("SELECT COUNT(*) as total FROM livreurs WHERE status = 'AVAILABLE'");
    $req->execute();
    $result = $req->fetch();
    return $result['total'];
}

function getTotalUsines($cnx) {
    $req = $cnx->prepare("SELECT COUNT(*) as total FROM usine");
    $req->execute();
    $result = $req->fetch();
    return $result['total'];
}

function getOpenReclamations($cnx) {
    $req = $cnx->prepare("SELECT COUNT(*) as total FROM reclamation WHERE statut = 'OUVERT'");
    $req->execute();
    $result = $req->fetch();
    return $result['total'];
}

function getPaidPayments($cnx) {
    $req = $cnx->prepare("SELECT SUM(amount) as total FROM payments WHERE status = 'PAID'");
    $req->execute();
    $result = $req->fetch();
    return $result['total'] ?? 0;
}

// ==================== FONCTIONS POUR LES GRAPHIQUES ====================

function getMonthlySales($cnx) {
    $sales = [];
    for ($i = 1; $i <= 12; $i++) {
        $req = $cnx->prepare("SELECT COALESCE(SUM(total), 0) as total FROM orders WHERE status = 'DELIVERED' AND MONTH(created_at) = :month AND YEAR(created_at) = YEAR(CURDATE())");
        $req->execute([':month' => $i]);
        $result = $req->fetch();
        $sales[] = $result['total'];
    }
    return $sales;
}

function getTopProducts($cnx) {
    $req = $cnx->prepare("
        SELECT p.name, COUNT(*) as total_sold 
        FROM products p
        JOIN ligne_commande lc ON p.id_product = lc.id_produit
        GROUP BY p.id_product
        ORDER BY total_sold DESC
        LIMIT 5
    ");
    $req->execute();
    return $req->fetchAll();
}

// ==================== FONCTIONS POUR LES TABLEAUX ====================

function getLatestOrders($cnx) {
    $req = $cnx->prepare("
        SELECT o.*, u.name as user_name 
        FROM orders o
        JOIN users u ON o.id_user = u.id_user
        ORDER BY o.id_order DESC 
        LIMIT 6
    ");
    $req->execute();
    return $req->fetchAll();
}

function getAllUsersList($cnx) {
    $req = $cnx->prepare("SELECT * FROM users ORDER BY id_user DESC LIMIT 10");
    $req->execute();
    return $req->fetchAll();
}

function getAllProductsList($cnx) {
    $req = $cnx->prepare("SELECT * FROM products LIMIT 10");
    $req->execute();
    return $req->fetchAll();
}

function getAllLivreursList($cnx) {
    $req = $cnx->prepare("
        SELECT l.*, u.name as user_name, u.email 
        FROM livreurs l
        JOIN users u ON l.id_user = u.id_user
        LIMIT 10
    ");
    $req->execute();
    return $req->fetchAll();
}

function getAllUsinesList($cnx) {
    $req = $cnx->prepare("SELECT * FROM usine LIMIT 10");
    $req->execute();
    return $req->fetchAll();
}

function getRecentClaimsList($cnx) {
    $req = $cnx->prepare("
        SELECT r.*, u.name as user_name
        FROM reclamation r
        JOIN users u ON r.id_client = u.id_user
        ORDER BY r.date_creation DESC
        LIMIT 5
    ");
    $req->execute();
    return $req->fetchAll();
}

function getActiveDeliveriesList($cnx) {
    $req = $cnx->prepare("
        SELECT l.*, u.name as user_name
        FROM livraison l
        JOIN commande c ON l.id_commande = c.id
        JOIN users u ON c.id_client = u.id_user
        WHERE l.statut NOT IN ('LIVREE', 'ANNULEE')
        ORDER BY l.date_estimee ASC
        LIMIT 5
    ");
    $req->execute();
    return $req->fetchAll();
}
?>