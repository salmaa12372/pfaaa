<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/traitement.php';
$cnx = getConnection();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    updateUser($cnx, $_POST['id'], ['nom' => $_POST['nom'], 'email' => $_POST['email']]);
    header('Location: ../view/user_list.php'); exit();
}
if (isset($_GET['id'])) { $id = $_GET['id']; $user = getUserById($cnx, $id); }
