<?php
session_start();
include_once 'Database.php';
include_once 'User.php';

$database = new Database();
$db = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $email = $_POST['email'];

    $user = new User($db);
    $result = $user->register($username, $password, $email);

    if ($result) {
        header('Location: login.php');
        exit;
    } else {
        $error_message = "A regisztráció nem sikerült. Próbáld újra!";
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Regisztráció</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="register-style.css">
</head>
<body>
<header>
    <h1>Regisztráció</h1>
</header>

<main class="register-main">
    <p class="login-link">Már van fiókod? <a href="login.php">Bejelentkezés itt</a>.</p>

    <form method="POST" class="register-form">
        <label for="username">Felhasználónév</label>
        <input type="text" id="username" name="username" required class="register-input">

        <label for="email">Email cím</label>
        <input type="email" id="email" name="email" required class="register-input">

        <label for="password">Jelszó</label>
        <input type="password" id="password" name="password" required class="register-input">

        <button type="submit" class="register-button">Regisztráció</button>
    </form>
</main>
</body>
</html>
