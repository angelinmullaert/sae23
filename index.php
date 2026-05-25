<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: tableau_de_bord.php');
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Connexion</title>
    <meta charset="utf-8">
    <link rel="stylesheet" href="style.css">
    <script>
        if (localStorage.getItem('theme') === 'dark') {
            document.documentElement.classList.add('dark-mode');
        }
    </script>
</head>
<body>
    <h1>FlightDesk</h1>
    <nav style="justify-content: center;">
        <button id="theme-toggle">🌓 Thème</button>
    </nav>
    <form action="traitement_login.php" method="post">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Mot de passe" required>
        <button type="submit">Connexion</button>
    </form>
    <script src="theme.js"></script>
</body>
</html>