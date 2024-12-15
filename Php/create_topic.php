<?php
session_start();
include_once 'Database.php';
include_once 'User.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$database = new Database();
$db = $database->getConnection();

$user = new User($db);
$userId = $_SESSION['user_id'];
$userData = $user->getUserById($userId);

if (!$userData) {
    header('Location: logout.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $topicName = trim($_POST['topic_name']);
    $topicDescription = trim($_POST['topic_description']);

    if (!empty($topicName) && !empty($topicDescription)) {
        $checkQuery = "SELECT id FROM categories WHERE name = :name";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->bindParam(':name', $topicName);
        $checkStmt->execute();

        if ($checkStmt->rowCount() > 0) {
            $error = "Ez a topiknév már létezik. Kérjük, válasszon másikat.";
        } else {
            $query = "INSERT INTO categories (name, description, user_id) VALUES (:name, :description, :user_id)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':name', $topicName);
            $stmt->bindParam(':description', $topicDescription);
            $stmt->bindParam(':user_id', $userId);

            if ($stmt->execute()) {
                header('Location: dashboard.php');
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
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="create-topic-style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body>
<header>
    <a href="dashboard.php" class="home-link">Főoldal</a>
    <div class="nav-links">
        <a href="logout.php"><i class="fa-solid fa-right-to-bracket"></i></a>
        <a href="profile.php"><i class="fa-solid fa-user"></i></a>
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
