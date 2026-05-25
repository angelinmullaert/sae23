<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'instructeur') {
    header('Location: index.php');
    exit();
}
require 'connexion.php';

$id_vol = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sql = "UPDATE Vols SET date_vol = ?, duree_minutes = ?, compte_rendu = ?, id_pilote = ?, id_instructeur = ?, id_planeur = ? WHERE id_vol = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_POST['date_vol'], $_POST['duree_minutes'], $_POST['compte_rendu'], $_POST['id_pilote'], $_POST['id_instructeur'], $_POST['id_planeur'], $id_vol]);
    header('Location: tableau_de_bord.php');
    exit();
}

$vol = $pdo->prepare("SELECT * FROM Vols WHERE id_vol = ?");
$vol->execute([$id_vol]);
$v = $vol->fetch();

$pilotes = $pdo->query("SELECT id_utilisateur, nom, prenom FROM Utilisateurs WHERE role = 'pilote'")->fetchAll();
$instructeurs = $pdo->query("SELECT id_utilisateur, nom, prenom FROM Utilisateurs WHERE role = 'instructeur'")->fetchAll();
$planeurs = $pdo->query("SELECT id_planeur, immatriculation, modele FROM Planeurs")->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Modifier Vol</title>
    <meta charset="utf-8">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <a href="tableau_de_bord.php">⬅ Annuler</a>
    <h1>Modifier le vol</h1>
    <form method="post">
        <input type="date" name="date_vol" value="<?= $v['date_vol'] ?>">
        <input type="number" name="duree_minutes" value="<?= $v['duree_minutes'] ?>">
        <select name="id_pilote">
            <?php foreach($pilotes as $p): ?>
                <option value="<?= $p['id_utilisateur'] ?>" <?= $p['id_utilisateur'] == $v['id_pilote'] ? 'selected' : '' ?>><?= $p['nom'] ?></option>
            <?php endforeach; ?>
        </select>
        <select name="id_instructeur">
            <?php foreach($instructeurs as $i): ?>
                <option value="<?= $i['id_utilisateur'] ?>" <?= $i['id_utilisateur'] == $v['id_instructeur'] ? 'selected' : '' ?>><?= $i['nom'] ?></option>
            <?php endforeach; ?>
        </select>
        <select name="id_planeur">
            <?php foreach($planeurs as $pl): ?>
                <option value="<?= $pl['id_planeur'] ?>" <?= $pl['id_planeur'] == $v['id_planeur'] ? 'selected' : '' ?>><?= $pl['immatriculation'] ?></option>
            <?php endforeach; ?>
        </select>
        <textarea name="compte_rendu"><?= $v['compte_rendu'] ?></textarea>
        <button type="submit">Modifier</button>
    </form>
</body>
</html>