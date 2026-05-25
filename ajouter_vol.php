<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'instructeur') {
    header('Location: index.php');
    exit();
}
require 'connexion.php';
$role = $_SESSION['role'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sql = "INSERT INTO Vols (date_vol, duree_minutes, compte_rendu, id_pilote, id_instructeur, id_planeur) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_POST['date_vol'], $_POST['duree_minutes'], $_POST['compte_rendu'], $_POST['id_pilote'], $_POST['id_instructeur'], $_POST['id_planeur']]);
    $stmt_p = $pdo->prepare("SELECT email, prenom FROM Utilisateurs WHERE id_utilisateur = ?");
    $stmt_p->execute([$_POST['id_pilote']]);
    $pilote = $stmt_p->fetch();
    mail($pilote['email'], "Nouveau vol", "Bonjour " . $pilote['prenom'] . ", un nouveau vol a ete enregistre.");
    $message = "Vol enregistré !";
}
$pilotes = $pdo->query("SELECT id_utilisateur, nom, prenom FROM Utilisateurs WHERE role = 'pilote'")->fetchAll();
$instructeurs = $pdo->query("SELECT id_utilisateur, nom, prenom FROM Utilisateurs WHERE role = 'instructeur'")->fetchAll();
$planeurs = $pdo->query("SELECT id_planeur, immatriculation FROM Planeurs")->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Saisir un vol</title>
    <meta charset="utf-8">
    <link rel="stylesheet" href="style.css">
    <script>
        if (localStorage.getItem('theme') === 'dark') {
            document.documentElement.classList.add('dark-mode');
        }
    </script>
</head>
<body>
    <h1>Saisir un vol</h1>
    <nav>
        <button id="theme-toggle">🌓 Thème</button>
        <a href="mon_profil.php">👤 Mon Profil</a>
        <a href="ajouter_vol.php">➕ Saisir un vol</a>
        <a href="gerer_adherents.php">👥 Membres</a>
        <a href="gerer_planeurs.php">✈️ Appareils</a>
        <a href="tableau_de_bord.php">🏠 Accueil</a>
        <a href="deconnexion.php">🚪 Déconnexion</a>
    </nav>
    <?php if(isset($message)) echo "<p style='color:green;'>$message</p>"; ?>
    <form method="post">
        <input type="date" name="date_vol" required>
        <input type="number" name="duree_minutes" placeholder="Minutes" required>
        <select name="id_pilote">
            <?php foreach($pilotes as $p): ?>
                <option value="<?= $p['id_utilisateur'] ?>"><?= htmlspecialchars($p['nom'].' '.$p['prenom']) ?></option>
            <?php endforeach; ?>
        </select>
        <select name="id_instructeur">
            <?php foreach($instructeurs as $i): ?>
                <option value="<?= $i['id_utilisateur'] ?>" <?= $i['id_utilisateur'] == $_SESSION['user_id'] ? 'selected' : '' ?>><?= htmlspecialchars($i['nom'].' '.$i['prenom']) ?></option>
            <?php endforeach; ?>
        </select>
        <select name="id_planeur">
            <?php foreach($planeurs as $pl): ?>
                <option value="<?= $pl['id_planeur'] ?>"><?= htmlspecialchars($pl['immatriculation']) ?></option>
            <?php endforeach; ?>
        </select>
        <textarea name="compte_rendu" placeholder="Rapport..."></textarea>
        <button type="submit">Enregistrer</button>
    </form>
    <script src="theme.js"></script>
</body>
</html>