<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'instructeur') {
    header('Location: index.php');
    exit();
}
require 'connexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    
    if (!preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $email)) {
        $erreur = "L'adresse email est invalide.";
    } else {
        $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $sql = "INSERT INTO Utilisateurs (nom, prenom, email, mot_de_passe, role) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        try {
            $stmt->execute([$_POST['nom'], $_POST['prenom'], $email, $pass, $_POST['role']]);
            $message = "Adhérent ajouté avec succès !";
        } catch (Exception $e) {
            $erreur = "Cette adresse email est déjà utilisée.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Ajouter Adhérent</title>
    <meta charset="utf-8">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <a href="tableau_de_bord.php">⬅ Retour</a>
    <h1>Inscrire un adhérent</h1>
    
    <?php if(isset($erreur)) echo "<p style='color:red;'>$erreur</p>"; ?>
    <?php if(isset($message)) echo "<p style='color:green;'>$message</p>"; ?>

    <form method="post">
        <input type="text" name="nom" placeholder="Nom" required>
        <input type="text" name="prenom" placeholder="Prénom" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Mot de passe" required>
        <select name="role">
            <option value="pilote">Pilote</option>
            <option value="instructeur">Instructeur</option>
        </select>
        <button type="submit">Créer le compte</button>
    </form>
</body>
</html>