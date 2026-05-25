<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'instructeur') {
    header('Location: index.php');
    exit();
}
require 'connexion.php';

$id = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sql = "UPDATE Utilisateurs SET nom = ?, prenom = ?, email = ?, role = ? WHERE id_utilisateur = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_POST['nom'], $_POST['prenom'], $_POST['email'], $_POST['role'], $id]);
    header('Location: gerer_adherents.php');
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM Utilisateurs WHERE id_utilisateur = ?");
$stmt->execute([$id]);
$u = $stmt->fetch();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Modifier Adhérent</title>
    <meta charset="utf-8">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <a href="gerer_adherents.php">⬅ Annuler</a>
    <h1>Modifier le membre</h1>
    <form method="post">
        <input type="text" name="nom" value="<?= htmlspecialchars($u['nom']) ?>">
        <input type="text" name="prenom" value="<?= htmlspecialchars($u['prenom']) ?>">
        <input type="email" name="email" value="<?= htmlspecialchars($u['email']) ?>">
        <select name="role">
            <option value="pilote" <?= $u['role'] == 'pilote' ? 'selected' : '' ?>>Pilote</option>
            <option value="instructeur" <?= $u['role'] == 'instructeur' ? 'selected' : '' ?>>Instructeur</option>
        </select>
        <button type="submit">Enregistrer les modifications</button>
    </form>
</body>
</html>