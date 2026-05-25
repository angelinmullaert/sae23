<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'instructeur') {
    header('Location: index.php');
    exit();
}
require 'connexion.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "UPDATE Vols SET est_supprime = 1 WHERE id_vol = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
}

header('Location: tableau_de_bord.php');
exit();
?>