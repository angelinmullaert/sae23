<?php
require 'connexion.php';

$email = 'admin@flightdesk.fr';
$mdp = 'admin123';
$mdp_crypte = password_hash($mdp, PASSWORD_DEFAULT);

$sql = "INSERT INTO Utilisateurs (nom, prenom, email, mot_de_passe, role) VALUES ('Ventoso-Mullaert', 'Angelin', ?, ?, 'instructeur')";
$stmt = $pdo->prepare($sql);
$stmt->execute([$email, $mdp_crypte]);

echo "Compte administrateur créé avec succes. Vous pouvez vous connecter !";
?>