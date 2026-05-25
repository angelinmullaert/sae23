<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'instructeur') {
    header('Location: index.php');
    exit();
}
require 'connexion.php';

if (isset($_GET['id']) && $_GET['id'] != $_SESSION['user_id']) {
    $id = $_GET['id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM Utilisateurs WHERE id_utilisateur = ?");
        $stmt->execute([$id]);
    } catch (Exception $e) {
        die("Impossible de supprimer ce membre car il est lie a des rapports de vol.");
    }
}

header('Location: gerer_adherents.php');
exit();
?>