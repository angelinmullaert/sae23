<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'instructeur') {
    header('Location: index.php');
    exit();
}
require 'connexion.php';
$role = $_SESSION['role'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sql = "INSERT INTO Planeurs (immatriculation, modele) VALUES (?, ?)";
    $stmt = $pdo->prepare($sql);
    try {
        $stmt->execute([$_POST['immatriculation'], $_POST['modele']]);
        $message = "Appareil ajouté !";
    } catch (Exception $e) {
        $erreur = "Erreur : immatriculation déjà existante.";
    }
}
$planeurs = $pdo->query("SELECT * FROM Planeurs ORDER BY modele")->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Appareils</title>
    <meta charset="utf-8">
    <link rel="stylesheet" href="style.css">
    <script>
        if (localStorage.getItem('theme') === 'dark') {
            document.documentElement.classList.add('dark-mode');
        }
    </script>
</head>
<body>
    <h1>Gestion des Appareils</h1>
    <nav>
        <button id="theme-toggle">🌓 Thème</button>
        <a href="mon_profil.php">👤 Mon Profil</a>
        <a href="ajouter_vol.php">➕ Saisir un vol</a>
        <a href="gerer_adherents.php">👥 Membres</a>
        <a href="gerer_planeurs.php">✈️ Appareils</a>
        <a href="tableau_de_bord.php">🏠 Accueil</a>
        <a href="deconnexion.php">🚪 Déconnexion</a>
    </nav>
    <?php if(isset($erreur)) echo "<p style='color:red;'>$erreur</p>"; ?>
    <?php if(isset($message)) echo "<p style='color:green;'>$message</p>"; ?>
    <form method="post">
        <input type="text" name="immatriculation" placeholder="F-XXXX" required>
        <input type="text" name="modele" placeholder="Modèle" required>
        <button type="submit">Ajouter</button>
    </form>
    <table>
        <tr><th>Immatriculation</th><th>Modèle</th></tr>
        <?php foreach($planeurs as $p): ?>
            <tr><td><?= htmlspecialchars($p['immatriculation']) ?></td><td><?= htmlspecialchars($p['modele']) ?></td></tr>
        <?php endforeach; ?>
    </table>
    <script src="theme.js"></script>
</body>
</html>