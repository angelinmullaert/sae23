<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}
require 'connexion.php';
require 'fpdf.php';

if (!isset($_GET['id'])) {
    header('Location: tableau_de_bord.php');
    exit();
}

$id_vol = $_GET['id'];
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

if ($role === 'instructeur') {
    $sql = "SELECT v.*, p.nom as p_nom, p.prenom as p_prenom, pl.immatriculation, i.nom as i_nom, i.prenom as i_prenom 
            FROM Vols v 
            JOIN Utilisateurs p ON v.id_pilote = p.id_utilisateur 
            JOIN Utilisateurs i ON v.id_instructeur = i.id_utilisateur
            JOIN Planeurs pl ON v.id_planeur = pl.id_planeur 
            WHERE v.id_vol = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_vol]);
} else {
    $sql = "SELECT v.*, i.nom as i_nom, i.prenom as i_prenom, pl.immatriculation, p.nom as p_nom, p.prenom as p_prenom
            FROM Vols v 
            JOIN Utilisateurs i ON v.id_instructeur = i.id_utilisateur 
            JOIN Utilisateurs p ON v.id_pilote = p.id_utilisateur
            JOIN Planeurs pl ON v.id_planeur = pl.id_planeur 
            WHERE v.id_vol = ? AND v.id_pilote = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_vol, $user_id]);
}

$vol = $stmt->fetch();

if (!$vol) {
    header('Location: tableau_de_bord.php');
    exit();
}

$date_fr = date('d/m/Y', strtotime($vol['date_vol']));

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, utf8_decode('Rapport de Vol - FlightDesk'), 0, 1, 'C');
$pdf->Ln(10);

$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, utf8_decode('Date du vol : ' . $date_fr), 0, 1);
$pdf->Cell(0, 10, utf8_decode('Planeur : ' . $vol['immatriculation']), 0, 1);
$pdf->Cell(0, 10, utf8_decode('Pilote : ' . $vol['p_nom'] . ' ' . $vol['p_prenom']), 0, 1);
$pdf->Cell(0, 10, utf8_decode('Instructeur : ' . $vol['i_nom'] . ' ' . $vol['i_prenom']), 0, 1);
$pdf->Cell(0, 10, utf8_decode('Durée : ' . $vol['duree_minutes'] . ' minutes'), 0, 1);

$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, utf8_decode('Compte-rendu pédagogique :'), 0, 1);
$pdf->SetFont('Arial', '', 12);
$pdf->MultiCell(0, 10, utf8_decode($vol['compte_rendu']));

$pdf->Output('D', 'rapport_vol_' . $id_vol . '.pdf');
?>