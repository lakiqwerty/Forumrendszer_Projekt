<?php

use models\Database;
use models\User;

session_start();
include_once '../models/Database.php';
include_once '../models/User.php';

// Ha nincs bejelentkezve a felhasználó, irányítjuk a login oldalra
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$database = new Database();
$db = $database->getConnection();

$user = new User($db);
$userId = $_SESSION['user_id'];
$userData = $user->getUserById($userId);

// Ha nem találjuk a felhasználót, kijelentkeztetjük
if (!$userData) {
    header('Location: logout.php');
    exit;
}

// Ha nincs ID a GET paraméterek között, irányítjuk a főoldalra
if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$topicId = intval($_GET['id']);
$query = "SELECT name, description, user_id FROM categories WHERE id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $topicId);
$stmt->execute();
$topic = $stmt->fetch(PDO::FETCH_ASSOC);

// Ha nem találjuk a topikot vagy nem a felhasználó hozta létre, visszairányítjuk a főoldalra
if (!$topic || $topic['user_id'] != $userId) {
    header('Location: index.php');
    exit;
}

// Topik frissítése
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_topic'])) {
    $newName = trim($_POST['topic_name']);
    $newDescription = trim($_POST['topic_description']);

    // Ha mindkét mező ki van töltve, végrehajtjuk a frissítést
    if (!empty($newName) && !empty($newDescription)) {
        $updateTopicQuery = "UPDATE categories SET name = :name, description = :description WHERE id = :id";
        $updateTopicStmt = $db->prepare($updateTopicQuery);
        $updateTopicStmt->bindParam(':name', $newName);
        $updateTopicStmt->bindParam(':description', $newDescription);
        $updateTopicStmt->bindParam(':id', $topicId);

        if ($updateTopicStmt->execute()) {
            header("Location: topic_.php?id=$topicId");
            exit;
        } else {
            $error = "Hiba történt a topik frissítésekor.";
        }
    } else {
        $error = "Minden mezőt ki kell tölteni.";
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Topik szerkesztése</title>
    <link rel="stylesheet" href="../public/css/editTopic-style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body>
<header>
    <a href="dashboard.php" class="home-link">Főoldal</a>
    <div class="nav-links">
        <a href="../controllers/logout.php"><i class="fa-solid fa-right-to-bracket"></i></a>
        <a href="profile.php"><i class="fa-solid fa-user"></i></a>
    </div>
</header>

<main>
    <h1>Topik szerkesztése</h1>

    <?php if (isset($error)): ?>
        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form method="POST" action="">
        <label for="topic_name">Topik neve:</label>
        <input type="text" id="topic_name" name="topic_name" value="<?php echo htmlspecialchars($topic['name']); ?>" required>

        <label for="topic_description">Topik leírása:</label>
        <textarea id="topic_description" name="topic_description" required><?php echo htmlspecialchars($topic['description']); ?></textarea>

        <button type="submit" name="edit_topic">Mentés</button>
    </form>
</main>
</body>
</html>
