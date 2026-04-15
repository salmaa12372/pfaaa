<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/traitement.php';
$cnx = getConnection();
if (isset($_GET['id'])) deleteUser($cnx, $_GET['id']);
header('Location: ../view/user_list.php'); exit();
