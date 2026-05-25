<?php
session_start();
require 'connexion.php';

if (isset($_POST['email']) && isset($_POST['password'])) {
    $query = $pdo->prepare("SELECT * FROM Utilisateurs WHERE email = ?");
    $query->execute([$_POST['email']]);
    $user = $query->fetch();

    if ($user && password_verify($_POST['password'], $user['mot_de_passe'])) {
        $_SESSION['user_id'] = $user['id_utilisateur'];
        $_SESSION['role'] = $user['role'];
        header('Location: tableau_de_bord.php');
    } else {
        header('Location: index.php?erreur=1');
    }
}
?>