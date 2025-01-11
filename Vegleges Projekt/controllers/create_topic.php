<?php

use models\Database;
use models\User;

session_start();
include_once '../models/Database.php';
include_once '../models/User.php';

// Ellenőrizd, hogy a felhasználó be van-e jelentkezve
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Ha nincs bejelentkezve, irányítsd a login oldalra
    exit;
}

// Adatbázis kapcsolat
$database = new Database();
$db = $database->getConnection();

// Betöltjük a felhasználó adatait
$user = new User($db);
$userId = $_SESSION['user_id'];
$userData = $user->getUserById($userId);

if (!$userData) {
    // Ha valami hiba történt a felhasználó adatainak betöltésekor
    header('Location: logout.php');
    exit;
}

// Topik létrehozása
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $topicName = trim($_POST['topic_name']);
    $topicDescription = trim($_POST['topic_description']);

    if (!empty($topicName) && !empty($topicDescription)) {
        // Ellenőrizzük, hogy létezik-e már a topik neve
        $checkQuery = "SELECT id FROM categories WHERE name = :name";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->bindParam(':name', $topicName);
        $checkStmt->execute();

        if ($checkStmt->rowCount() > 0) {
            $error = "Ez a topiknév már létezik. Kérjük, válasszon másikat.";
        } else {
            // Topik hozzáadása
            $query = "INSERT INTO categories (name, description, user_id) VALUES (:name, :description, :user_id)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':name', $topicName);
            $stmt->bindParam(':description', $topicDescription);
            $stmt->bindParam(':user_id', $userId);

            if ($stmt->execute()) {
                header('Location: ../views/dashboard.php'); // Sikeres hozzáadás után vissza a főoldalra
                exit;
            } else {
                $error = "Hiba történt a topik létrehozása során.";
            }
        }
    } else {
        $error = "A topik neve és leírása nem lehet üres.";
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Új Topik Létrehozása</title>
    <link rel="stylesheet" href="../public/css/style.css">
    <link rel="stylesheet" href="../public/css/create-topic-style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body>
<header>
    <a href="../dashboard.php" class="home-link">Főoldal</a>
    <div class="nav-links">
        <a href="logout.php"><i class="fa-solid fa-right-to-bracket"></i></a>
        <a href="../profile.php"><i class="fa-solid fa-user"></i></a>
    </div>
</header>



<main class="create-topic-main">
    <form method="POST" action="" class="create-topic-form">
        <label for="topic_name">Topik Neve:</label>
        <input type="text" id="topic_name" name="topic_name" required>

        <label for="topic_description">Topik Leírása:</label>
        <textarea id="topic_description" name="topic_description" required></textarea>

        <button type="submit">Létrehozás</button>
    </form>

    <?php if (isset($error)): ?>
        <p class="error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
</main>
</body>
</html>
