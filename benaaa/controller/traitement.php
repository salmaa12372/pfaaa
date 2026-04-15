<?php
require_once __DIR__ . '/../model/User.php';

function AddUser($cnx, $data) {
    $nom = $data['user_name']; 
    $email = $data['email'];
    $password = password_hash($data['password'], PASSWORD_DEFAULT);
    $role = $data['role']; // 'CLIENT', 'NUTRITIONIST' ou 'LIVREUR'
    
    // Vérifier si l'email existe déjà
    $checkStmt = $cnx->prepare("SELECT * FROM users WHERE email = :email");
    $checkStmt->bindParam(':email', $email);
    $checkStmt->execute();
    if ($checkStmt->fetch()) {
        return "Email already exists!";
    }
    
    $req = "INSERT INTO users (name, email, password, role) VALUES (:nom, :email, :password, :role)";
    $stmt = $cnx->prepare($req);
    $stmt->bindParam(':nom', $nom);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password', $password);
    $stmt->bindParam(':role', $role);
    
    $result = $stmt->execute();
    
    // Si l'utilisateur est un livreur, ajouter une entrée dans la table livreurs
    if ($result && $role == 'LIVREUR') {
        $id_user = $cnx->lastInsertId();
        $insertLivreur = $cnx->prepare("INSERT INTO livreurs (id_user, status) VALUES (:id_user, 'AVAILABLE')");
        $insertLivreur->bindParam(':id_user', $id_user);
        $insertLivreur->execute();
    }
    
    return $result ? true : "Error adding user.";
}

function ConnectUser($cnx, $data) {
    $email = $data['email'];
    $password = $data['password'];
    
    $stmt = $cnx->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password'])) {
        // Retourner les infos nécessaires
        return [
            'id_user' => $user['id_user'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role']
        ];
    }
    return false;
}

function getAllUsers($cnx) {
    $req = "SELECT * FROM users WHERE role != 'ADMIN'";
    $res = $cnx->query($req);
    $rows = $res->fetchAll();
    $users = [];
    foreach ($rows as $row) {
        $user = new User($row['name'], $row['email'], $row['password']);
        $user->id = $row['id_user'];
        $user->role = $row['role'];
        $users[] = $user;
    }
    return $users;
}

function searchUsers($cnx, $name) {
    $req = "SELECT * FROM users WHERE name LIKE '%$name%' AND role != 'ADMIN'";
    $res = $cnx->query($req);
    $rows = $res->fetchAll();
    $users = [];
    foreach ($rows as $row) {
        $user = new User($row['name'], $row['email'], $row['password']);
        $user->id = $row['id_user'];
        $user->role = $row['role'];
        $users[] = $user;
    }
    return $users;
} 

function getUserById($cnx, $id) {
    $stmt = $cnx->prepare("SELECT * FROM users WHERE id_user = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $row = $stmt->fetch();
    if ($row) {
        $user = new User($row['name'], $row['email'], $row['password']);
        $user->id = $row['id_user'];
        $user->role = $row['role'];
        return $user;
    }
    return null;
}

function updateUser($cnx, $id, $data) {
    $nom = $data['nom'];
    $email = $data['email'];
    $stmt = $cnx->prepare("UPDATE users SET name=:nom, email=:email WHERE id_user=:id");
    $stmt->bindParam(':nom', $nom);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':id', $id);
    return $stmt->execute();
}

function deleteUser($cnx, $id) {
    // Vérifier si l'utilisateur est un livreur
    $checkRole = $cnx->prepare("SELECT role FROM users WHERE id_user = :id");
    $checkRole->bindParam(':id', $id);
    $checkRole->execute();
    $user = $checkRole->fetch();
    
    if ($user && $user['role'] == 'LIVREUR') {
        // Supprimer d'abord l'entrée dans livreurs
        $deleteLivreur = $cnx->prepare("DELETE FROM livreurs WHERE id_user = :id");
        $deleteLivreur->bindParam(':id', $id);
        $deleteLivreur->execute();
    }
    
    $stmt = $cnx->prepare("DELETE FROM users WHERE id_user=:id");
    $stmt->bindParam(':id', $id);
    return $stmt->execute();
}

// Fonction pour obtenir tous les livreurs
function getAllLivreurs($cnx) {
    $req = "SELECT u.*, l.phone, l.vehicle, l.zone, l.status 
            FROM users u 
            JOIN livreurs l ON u.id_user = l.id_user 
            WHERE u.role = 'LIVREUR'";
    $res = $cnx->query($req);
    return $res->fetchAll(PDO::FETCH_ASSOC);
}

// Fonction pour mettre à jour le statut d'un livreur
function updateLivreurStatus($cnx, $id_user, $status) {
    $stmt = $cnx->prepare("UPDATE livreurs SET status = :status WHERE id_user = :id_user");
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':id_user', $id_user);
    return $stmt->execute();
}
?>