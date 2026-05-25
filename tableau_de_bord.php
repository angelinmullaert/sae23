<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}
require 'connexion.php';

$role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

$recherche = $_GET['q'] ?? '';
$recherche_sql = '%' . $recherche . '%';

$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limite = 10;
$offset = ($page - 1) * $limite;

$labels = []; 
$valeurs = [];

if ($role === 'instructeur') {
    $sql_stats = "SELECT COUNT(id_vol) as total_vols, SUM(duree_minutes) as total_heures FROM Vols WHERE est_supprime = 0";
    $stmt_stats = $pdo->query($sql_stats);
    $stats = $stmt_stats->fetch();

    $sql_graph = "SELECT pl.immatriculation, SUM(v.duree_minutes) as total FROM Vols v JOIN Planeurs pl ON v.id_planeur = pl.id_planeur WHERE v.est_supprime = 0 GROUP BY pl.id_planeur";
    $data_graph = $pdo->query($sql_graph)->fetchAll();
    foreach($data_graph as $d) { 
        $labels[] = $d['immatriculation']; 
        $valeurs[] = $d['total']; 
    }

    $sql_count = "SELECT COUNT(*) FROM Vols v JOIN Utilisateurs p ON v.id_pilote = p.id_utilisateur JOIN Planeurs pl ON v.id_planeur = pl.id_planeur WHERE v.est_supprime = 0 AND (p.nom LIKE ? OR p.prenom LIKE ? OR pl.immatriculation LIKE ?)";
    $stmt_count = $pdo->prepare($sql_count);
    $stmt_count->execute([$recherche_sql, $recherche_sql, $recherche_sql]);
    $total_lignes = $stmt_count->fetchColumn();

    $sql = "SELECT v.*, p.nom as p_nom, p.prenom as p_prenom, pl.immatriculation, i.nom as i_nom, i.prenom as i_prenom
            FROM Vols v 
            JOIN Utilisateurs p ON v.id_pilote = p.id_utilisateur 
            JOIN Utilisateurs i ON v.id_instructeur = i.id_utilisateur
            JOIN Planeurs pl ON v.id_planeur = pl.id_planeur 
            WHERE v.est_supprime = 0 AND (p.nom LIKE ? OR p.prenom LIKE ? OR pl.immatriculation LIKE ?)
            ORDER BY v.date_vol DESC LIMIT $limite OFFSET $offset";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$recherche_sql, $recherche_sql, $recherche_sql]);
} else {
    $sql_stats = "SELECT COUNT(id_vol) as total_vols, SUM(duree_minutes) as total_heures FROM Vols WHERE id_pilote = ? AND est_supprime = 0";
    $stmt_stats = $pdo->prepare($sql_stats);
    $stmt_stats->execute([$user_id]);
    $stats = $stmt_stats->fetch();

    $sql_graph = "SELECT pl.immatriculation, SUM(v.duree_minutes) as total FROM Vols v JOIN Planeurs pl ON v.id_planeur = pl.id_planeur WHERE v.id_pilote = ? AND v.est_supprime = 0 GROUP BY pl.id_planeur";
    $stmt_graph = $pdo->prepare($sql_graph);
    $stmt_graph->execute([$user_id]);
    $data_graph = $stmt_graph->fetchAll();
    foreach($data_graph as $d) { 
        $labels[] = $d['immatriculation']; 
        $valeurs[] = $d['total']; 
    }

    $sql_count = "SELECT COUNT(*) FROM Vols v JOIN Utilisateurs i ON v.id_instructeur = i.id_utilisateur JOIN Planeurs pl ON v.id_planeur = pl.id_planeur WHERE v.id_pilote = ? AND v.est_supprime = 0 AND (i.nom LIKE ? OR i.prenom LIKE ? OR pl.immatriculation LIKE ?)";
    $stmt_count = $pdo->prepare($sql_count);
    $stmt_count->execute([$user_id, $recherche_sql, $recherche_sql, $recherche_sql]);
    $total_lignes = $stmt_count->fetchColumn();

    $sql = "SELECT v.*, i.nom as i_nom, i.prenom as i_prenom, pl.immatriculation, p.nom as p_nom, p.prenom as p_prenom
            FROM Vols v 
            JOIN Utilisateurs i ON v.id_instructeur = i.id_utilisateur 
            JOIN Utilisateurs p ON v.id_pilote = p.id_utilisateur
            JOIN Planeurs pl ON v.id_planeur = pl.id_planeur 
            WHERE v.id_pilote = ? AND v.est_supprime = 0 AND (i.nom LIKE ? OR i.prenom LIKE ? OR pl.immatriculation LIKE ?)
            ORDER BY v.date_vol DESC LIMIT $limite OFFSET $offset";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id, $recherche_sql, $recherche_sql, $recherche_sql]);
}

$vols = $stmt->fetchAll();
$heures_format = floor(($stats['total_heures'] ?? 0) / 60) . 'h ' . (($stats['total_heures'] ?? 0) % 60) . 'm';
$total_pages = ceil($total_lignes / $limite);
?>
<!DOCTYPE html>
<html>
<head>
    <title>FlightDesk - Tableau de bord</title>
    <meta charset="utf-8">
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        if (localStorage.getItem('theme') === 'dark') {
            document.documentElement.classList.add('dark-mode');
        }
    </script>
</head>
<body>
    <h1>FlightDesk - Tableau de bord</h1>
    <nav>
        <button id="theme-toggle">🌓 Thème</button>
        <a href="mon_profil.php">👤 Mon Profil</a>
        <?php if ($role === 'instructeur'): ?>
            <a href="ajouter_vol.php">➕ Saisir un vol</a>
            <a href="gerer_adherents.php">👥 Membres</a>
            <a href="gerer_planeurs.php">✈️ Appareils</a>
        <?php endif; ?>
        <a href="deconnexion.php">🚪 Déconnexion</a>
    </nav>

    <div class="stats-container">
        <div class="stats-box">
            <strong>📊 Stats :</strong><br>
            Vols : <?= htmlspecialchars($stats['total_vols'] ?? 0) ?><br>
            Temps : <?= $heures_format ?>
        </div>
        <div style="width:200px;">
            <canvas id="myChart"></canvas>
        </div>
    </div>

    <form method="get" class="search-form">
        <input type="text" name="q" placeholder="Recherche..." value="<?= htmlspecialchars($recherche) ?>">
        <button type="submit">🔍 Filtrer</button>
        <a href="tableau_de_bord.php">Effacer</a>
    </form>

    <table border="1" width="100%">
        <tr>
            <th>Date</th><th>Planeur</th><th>Pilote</th><th>Instructeur</th><th>Durée</th><th>Compte-rendu</th><th>Actions</th>
        </tr>
        <?php foreach ($vols as $vol): ?>
            <tr>
                <td><?= date('d/m/Y', strtotime($vol['date_vol'])) ?></td>
                <td><?= htmlspecialchars($vol['immatriculation']) ?></td>
                <td><?= htmlspecialchars($vol['p_nom'].' '.$vol['p_prenom']) ?></td>
                <td><?= htmlspecialchars($vol['i_nom'].' '.$vol['i_prenom']) ?></td>
                <td><?= htmlspecialchars($vol['duree_minutes']) ?> min</td>
                <td><?= nl2br(htmlspecialchars($vol['compte_rendu'])) ?></td>
                <td>
                    <a href="exporter_pdf.php?id=<?= $vol['id_vol'] ?>">📄 PDF</a>
                    <?php if ($role === 'instructeur'): ?>
                        <a href="modifier_vol.php?id=<?= $vol['id_vol'] ?>">✏️ Modifier</a>
                        <a href="supprimer_vol.php?id=<?= $vol['id_vol'] ?>" onclick="return confirm('Supprimer ?');">🗑️ Supprimer</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

    <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php for($i=1; $i<=$total_pages; $i++): ?>
                <a class="<?= ($i==$page)?'active':'' ?>" href="?q=<?=urlencode($recherche)?>&page=<?=$i?>"><?=$i?></a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>

    <script>
    new Chart(document.getElementById('myChart'), {
        type: 'pie',
        data: {
            labels: <?= json_encode($labels) ?>,
            datasets: [{ data: <?= json_encode($valeurs) ?>, backgroundColor: ['#3498db', '#e74c3c', '#2ecc71', '#f1c40f', '#9b59b6'] }]
        },
        options: { plugins: { legend: { display: false } } }
    });
    </script>
    <script src="theme.js"></script>
</body>
</html>