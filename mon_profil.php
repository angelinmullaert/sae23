<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}
require 'connexion.php';
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = trim($_POST['email']);
    $date_expire = $_POST['certificat_expire'];
    if (!empty($_FILES['certificat']['name'])) {
        $extension = pathinfo($_FILES['certificat']['name'], PATHINFO_EXTENSION);
        $nom_fichier = "certif_" . $user_id . "." . $extension;
        move_uploaded_file($_FILES['certificat']['tmp_name'], "uploads/" . $nom_fichier);
        $stmt = $pdo->prepare("UPDATE Utilisateurs SET certificat_nom = ? WHERE id_utilisateur = ?");
        $stmt->execute([$nom_fichier, $user_id]);
    }
    $sql = "UPDATE Utilisateurs SET nom = ?, prenom = ?, email = ?, certificat_expire = ? WHERE id_utilisateur = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nom, $prenom, $email, $date_expire, $user_id]);
    if (!empty($_POST['nouveau_mdp'])) {
        $pass = password_hash($_POST['nouveau_mdp'], PASSWORD_DEFAULT);
        $stmt_mdp = $pdo->prepare("UPDATE Utilisateurs SET mot_de_passe = ? WHERE id_utilisateur = ?");
        $stmt_mdp->execute([$pass, $user_id]);
    }
    $message = "Profil mis à jour !";
}
$stmt = $pdo->prepare("SELECT * FROM Utilisateurs WHERE id_utilisateur = ?");
$stmt->execute([$user_id]);
$u = $stmt->fetch();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Mon Profil</title>
    <meta charset="utf-8">
    <link rel="stylesheet" href="style.css">
    <script>
        if (localStorage.getItem('theme') === 'dark') {
            document.documentElement.classList.add('dark-mode');
        }
    </script>
</head>
<body>
    <h1>Mon Profil</h1>
    <nav>
        <button id="theme-toggle">🌓 Thème</button>
        <a href="mon_profil.php">👤 Mon Profil</a>
        <?php if ($role === 'instructeur'): ?>
            <a href="ajouter_vol.php">➕ Saisir un vol</a>
            <a href="gerer_adherents.php">👥 Membres</a>
            <a href="gerer_planeurs.php">✈️ Appareils</a>
        <?php endif; ?>
        <a href="tableau_de_bord.php">🏠 Accueil</a>
        <a href="deconnexion.php">🚪 Déconnexion</a>
    </nav>
    <?php if(isset($message)) echo "<p style='color:green;'>$message</p>"; ?>
    <form method="post" enctype="multipart/form-data">
        <label>Nom :</label>
        <input type="text" name="nom" value="<?= htmlspecialchars($u['nom']) ?>" required>
        <label>Prénom :</label>
        <input type="text" name="prenom" value="<?= htmlspecialchars($u['prenom']) ?>" required>
        <label>Email :</label>
        <input type="email" name="email" value="<?= htmlspecialchars($u['email']) ?>" required>
        <label>Certificat Médical :</label>
        <input type="file" name="certificat">
        <label>Expiration :</label>
        <input type="date" name="certificat_expire" value="<?= $u['certificat_expire'] ?>">
        <label>Nouveau mot de passe :</label>
        <input type="password" name="nouveau_mdp">
        <button type="submit">Enregistrer</button>
    </form>
    <script src="theme.js"></script>
</body>
</html>