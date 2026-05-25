<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'instructeur') {
    header('Location: index.php');
    exit();
}
require 'connexion.php';
$adherents = $pdo->query("SELECT * FROM Utilisateurs ORDER BY role, nom")->fetchAll();
$aujourdhui = date('Y-m-d');
$role = $_SESSION['role'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Gérer les Membres</title>
    <meta charset="utf-8">
    <link rel="stylesheet" href="style.css">
    <script>
        if (localStorage.getItem('theme') === 'dark') {
            document.documentElement.classList.add('dark-mode');
        }
    </script>
</head>
<body>
    <h1>Répertoire des Membres</h1>
    
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

    <a href="ajouter_adherent.php" style="display:inline-block; margin-bottom:20px; font-weight:600;">➕ Inscrire un membre</a>
    
    <table border="1" width="100%">
        <tr>
            <th>Rôle</th><th>Nom</th><th>Email</th><th>Certificat</th><th>Actions</th>
        </tr>
        <?php foreach($adherents as $a): ?>
            <tr>
                <td><?= htmlspecialchars(ucfirst($a['role'])) ?></td>
                <td><?= htmlspecialchars($a['nom'].' '.$a['prenom']) ?></td>
                <td><?= htmlspecialchars($a['email']) ?></td>
                <td>
                    <?php if($a['certificat_expire']): ?>
                        <?php $date_exp_fr = date('d/m/Y', strtotime($a['certificat_expire'])); ?>
                        <?= ($a['certificat_expire'] < $aujourdhui) ? "<span style='color:red;'>Périmé le $date_exp_fr</span>" : "Valide jusqu'au $date_exp_fr" ?>
                        <?php if($a['certificat_nom']): ?>
                            <a href="uploads/<?= $a['certificat_nom'] ?>" target="_blank">Voir</a>
                        <?php endif; ?>
                    <?php else: ?>
                        Non renseigné
                    <?php endif; ?>
                </td>
                <td>
                    <a href="modifier_adherent.php?id=<?= $a['id_utilisateur'] ?>">✏️ Modifier</a>
                    <?php if($a['id_utilisateur'] != $_SESSION['user_id']): ?>
                        <a href="supprimer_adherent.php?id=<?= $a['id_utilisateur'] ?>" onclick="return confirm('Supprimer ?');">🗑️ Supprimer</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
    <script src="theme.js"></script>
</body>
</html>