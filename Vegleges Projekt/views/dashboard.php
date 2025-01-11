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

// Kedvencekhez adás
if (isset($_GET['action']) && $_GET['action'] === 'add_favorite' && isset($_GET['topic_id'])) {
    $topicId = intval($_GET['topic_id']);

    // Ellenőrizzük, hogy a kedvenc már hozzá van-e adva
    $checkQuery = "SELECT id FROM favorites WHERE user_id = :user_id AND topic_id = :topic_id";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->bindParam(':user_id', $userId);
    $checkStmt->bindParam(':topic_id', $topicId);
    $checkStmt->execute();

    if ($checkStmt->rowCount() == 0) {
        // Hozzáadjuk a kedvencekhez
        $insertFavoriteQuery = "INSERT INTO favorites (user_id, topic_id) VALUES (:user_id, :topic_id)";
        $insertFavoriteStmt = $db->prepare($insertFavoriteQuery);
        $insertFavoriteStmt->bindParam(':user_id', $userId);
        $insertFavoriteStmt->bindParam(':topic_id', $topicId);

        if ($insertFavoriteStmt->execute()) {
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Hiba történt a kedvenc hozzáadása során.";
        }
    }
}

// Törlés funkció
if (isset($_GET['action']) && $_GET['action'] === 'delete_topic' && isset($_GET['topic_id'])) {
    $topicId = intval($_GET['topic_id']);

    // Ellenőrizd, hogy a topik a felhasználóhoz tartozik-e
    $checkOwnerQuery = "SELECT id FROM categories WHERE id = :topic_id AND user_id = :user_id";
    $checkOwnerStmt = $db->prepare($checkOwnerQuery);
    $checkOwnerStmt->bindParam(':topic_id', $topicId);
    $checkOwnerStmt->bindParam(':user_id', $userId);
    $checkOwnerStmt->execute();

    if ($checkOwnerStmt->rowCount() > 0) {
        // Töröljük a topikot
        $deleteQuery = "DELETE FROM categories WHERE id = :topic_id";
        $deleteStmt = $db->prepare($deleteQuery);
        $deleteStmt->bindParam(':topic_id', $topicId);

        if ($deleteStmt->execute()) {
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Hiba történt a topik törlése során.";
        }
    } else {
        $error = "Nincs jogosultságod a topik törlésére.";
    }
}

// Keresési feltétel
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Topikok lekérdezése kedvenc státusszal együtt
$topicsQuery = "
    SELECT c.id, c.name, c.description, c.user_id, 
           (SELECT COUNT(*) 
            FROM favorites 
            WHERE favorites.topic_id = c.id AND favorites.user_id = :user_id) AS is_favorite
    FROM categories c
    WHERE c.name LIKE :search OR c.description LIKE :search
    ORDER BY c.id DESC
";
$topicsStmt = $db->prepare($topicsQuery);
$topicsStmt->bindParam(':user_id', $userId);
$searchTerm = '%' . $search . '%';
$topicsStmt->bindParam(':search', $searchTerm);
$topicsStmt->execute();
$topics = $topicsStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="../public/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body>
<header>
    <h1>Üdvözlünk, <?php echo htmlspecialchars($userData['username']); ?>!</h1>
    <form method="GET" action="dashboard.php" class="search-form">
        <input type="text" name="search" placeholder="Keresés topikok között" value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit"><i class="fa fa-search"></i></button>
    </form>
    <div class="nav-links">
        <a href="../controllers/logout.php"><i class="fa-solid fa-right-to-bracket"></i></a>
        <a href="profile.php"><i class="fa-solid fa-user"></i></a>
    </div>
</header>

<main>
    <h2>Topikok</h2>

    <div class="topic-container">
        <?php if (count($topics) > 0): ?>
            <ul>
                <?php foreach ($topics as $topic): ?>
                    <li class="topic-item">
                        <div class="topic-info">
                            <strong><?php echo htmlspecialchars($topic['name']); ?></strong>:
                            <p><?php echo htmlspecialchars($topic['description']); ?></p>
                        </div>
                        <!-- Topik megtekintése -->
                        <a href="topic_.php?id=<?php echo $topic['id']; ?>" class="view-link">Megtekintés</a>

                        <!-- Törlés opció -->
                        <?php if ($topic['user_id'] == $userId): ?>
                            <a href="dashboard.php?action=delete_topic&topic_id=<?php echo $topic['id']; ?>"
                               class="delete-link" onclick="return confirm('Biztosan törölni szeretnéd ezt a topikot?');">
                                <i class="fa-solid fa-trash"></i> Törlés
                            </a>
                        <?php endif; ?>

                        <!-- Kedvenc funkció -->
                        <a href="dashboard.php?action=add_favorite&topic_id=<?php echo $topic['id']; ?>" class="favorite-link">
                            <i class="fa-solid fa-heart" style="color: <?php echo $topic['is_favorite'] ? 'red' : 'gray'; ?>;"></i> Kedvencekhez
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>Nincs találat a keresett kifejezésre.</p>
        <?php endif; ?>
    </div>

    <!-- Új topik létrehozása -->
    <div class="create-topic-button-container">
        <a href="../controllers/create_topic.php" class="create-topic-button">Új topik létrehozása</a>
    </div>

    <!-- Hibák megjelenítése -->
    <?php if (isset($error)): ?>
        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
</main>

</body>
</html>
