<?php
require 'connexion.php';

$email = 'VOTRE_ID';
$mdp = 'VOTRE_MOT_DE_PASSE';
$mdp_crypte = password_hash($mdp, PASSWORD_DEFAULT);

$sql = "INSERT INTO Utilisateurs (nom, prenom, email, mot_de_passe, role) VALUES ('VOTRE_NOM', 'VOTRE_PRENOM', ?, ?, 'VOTRE_FONCTION')";
$stmt = $pdo->prepare($sql);
$stmt->execute([$email, $mdp_crypte]);

echo "Compte administrateur créé avec succes. Vous pouvez vous connecter !";
?>
