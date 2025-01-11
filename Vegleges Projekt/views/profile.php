<?php

use models\Database;
use models\User;

session_start();
include_once '../models/Database.php';
include_once '../models/User.php';

// Ellenőrizd, hogy a felhasználó be van-e jelentkezve
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
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
    header('Location: logout.php');
    exit;
}

// Kedvenc törlése, ha van POST kérés
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_favorite'])) {
    $topicId = $_POST['remove_favorite'];

    $deleteQuery = "DELETE FROM favorites WHERE user_id = :user_id AND topic_id = :topic_id";
    $deleteStmt = $db->prepare($deleteQuery);
    $deleteStmt->bindParam(':user_id', $userId);
    $deleteStmt->bindParam(':topic_id', $topicId);
    $deleteStmt->execute();

    header('Location: profile.php'); // Frissítés az aktuális oldalón
    exit;
}

// Létrehozott topikok betöltése
$topicsQuery = "SELECT id, name, description FROM categories WHERE user_id = :user_id";
$topicsStmt = $db->prepare($topicsQuery);
$topicsStmt->bindParam(':user_id', $userId);
$topicsStmt->execute();
$topics = $topicsStmt->fetchAll(PDO::FETCH_ASSOC);

// Kedvenc topikok betöltése
$favoritesQuery = "SELECT c.id, c.name, c.description 
                   FROM favorites f 
                   JOIN categories c ON f.topic_id = c.id 
                   WHERE f.user_id = :user_id";
$favoritesStmt = $db->prepare($favoritesQuery);
$favoritesStmt->bindParam(':user_id', $userId);
$favoritesStmt->execute();
$favorites = $favoritesStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil</title>
    <link rel="stylesheet" href="../public/css/profile-style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body>
<header>
    <div class="header-container">
        <a href="dashboard.php" class="home-link">Főoldal</a>
        <h1>Profil</h1>
        <div class="nav-links">
            <a href="../controllers/logout.php"><i class="fa-solid fa-right-to-bracket"></i></a>
        </div>
    </div>
</header>

<main class="profile-main">
    <h1><?php echo htmlspecialchars($userData['username']); ?> profilja</h1>

    <section class="user-topics">
        <h2>Létrehozott topikok</h2>
        <?php if (count($topics) > 0): ?>
            <ul>
                <?php foreach ($topics as $topic): ?>
                    <li>
                        <a href="topic_.php?id=<?php echo $topic['id']; ?>"><?php echo htmlspecialchars($topic['name']); ?></a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>Még nem hoztál létre topikot.</p>
        <?php endif; ?>
    </section>

    <section class="user-favorites">
        <h2>Kedvenc topikjaid</h2>
        <?php if (count($favorites) > 0): ?>
            <ul>
                <?php foreach ($favorites as $favorite): ?>
                    <li>
                        <a href="topic_.php?id=<?php echo $favorite['id']; ?>"><?php echo htmlspecialchars($favorite['name']); ?></a>
                        <p><?php echo htmlspecialchars($favorite['description']); ?></p>
                        <form method="POST" style="display: inline;">
                            <button type="submit" name="remove_favorite" value="<?php echo $favorite['id']; ?>">Eltávolítás</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>Még nincsenek kedvenc topikjaid.</p>
        <?php endif; ?>
    </section>
</main>

</body>
</html>
