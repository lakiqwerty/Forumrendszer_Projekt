<?php
session_start();
include_once 'Database.php';
include_once 'User.php';

$database = new Database();
$db = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $user = new User($db);
    $userId = $user->login($username, $password);

    if ($userId) {
        $_SESSION['user_id'] = $userId;
        header('Location: dashboard.php');
        exit;
    } else {
        $error_message = "Hibás felhasználónév vagy jelszó!";
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bejelentkezés</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="login-style.css">
</head>
<body>
<header>
    <h1>Bejelentkezés</h1>
</header>

<main class="login-main">
    <p class="register-link">Nincs még fiókod? <a href="register.php">Regisztráció itt</a>.</p>

    <form method="POST" class="login-form">
        <label for="username">Felhasználónév</label>
        <input type="text" id="username" name="username" required class="login-input">

        <label for="password">Jelszó</label>
        <input type="password" id="password" name="password" required class="login-input">

        <button type="submit" class="login-button">Bejelentkezés</button>
    </form>
</main>
</body>
</html>
