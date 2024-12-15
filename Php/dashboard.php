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


if (isset($_GET['action']) && $_GET['action'] === 'delete_topic' && isset($_GET['topic_id'])) {
    $topicId = intval($_GET['topic_id']);

    $deleteQuery = "SELECT user_id FROM categories WHERE id = :topic_id";
    $deleteStmt = $db->prepare($deleteQuery);
    $deleteStmt->bindParam(':topic_id', $topicId);
    $deleteStmt->execute();
    $topic = $deleteStmt->fetch(PDO::FETCH_ASSOC);

    if ($topic && $topic['user_id'] == $userId) {
        $deleteCommentsQuery = "DELETE FROM comments WHERE topic_id = :topic_id";
        $deleteCommentsStmt = $db->prepare($deleteCommentsQuery);
        $deleteCommentsStmt->bindParam(':topic_id', $topicId);
        $deleteCommentsStmt->execute();

        $deleteTopicQuery = "DELETE FROM categories WHERE id = :topic_id";
        $deleteTopicStmt = $db->prepare($deleteTopicQuery);
        $deleteTopicStmt->bindParam(':topic_id', $topicId);

        if ($deleteTopicStmt->execute()) {
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Hiba történt a topik törlésekor.";
        }
    } else {
        $error = "Nincs jogosultságod a topik törléséhez.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_topic_name']) && isset($_POST['new_topic_description'])) {
    $newTopicName = trim($_POST['new_topic_name']);
    $newTopicDescription = trim($_POST['new_topic_description']);

    if (!empty($newTopicName) && !empty($newTopicDescription)) {
        $insertTopicQuery = "INSERT INTO categories (name, description, user_id) VALUES (:name, :description, :user_id)";
        $insertTopicStmt = $db->prepare($insertTopicQuery);
        $insertTopicStmt->bindParam(':name', $newTopicName);
        $insertTopicStmt->bindParam(':description', $newTopicDescription);
        $insertTopicStmt->bindParam(':user_id', $userId);

        if ($insertTopicStmt->execute()) {
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Hiba történt az új topik létrehozása során.";
        }
    } else {
        $error = "Minden mezőt ki kell tölteni az új topik létrehozásához.";
    }
}


$topicsQuery = "SELECT id, name, description, user_id FROM categories ORDER BY id DESC";
$topicsStmt = $db->prepare($topicsQuery);
$topicsStmt->execute();
$topics = $topicsStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body>
<header>
    <h1>Üdvözlünk, <?php echo htmlspecialchars($userData['username']); ?>!</h1>
    <div class="nav-links">
        <a href="logout.php"><i class="fa-solid fa-right-to-bracket"></i></a>
        <a href="profile.php"><i class="fa-solid fa-user"></i> </a>
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
                        <a href="topic_.php?id=<?php echo $topic['id']; ?>" class="view-link">Megtekintés</a>
                        <?php if ($topic['user_id'] == $userId): ?>
                            <a href="dashboard.php?action=delete_topic&topic_id=<?php echo $topic['id']; ?>"
                               class="delete-link" onclick="return confirm('Biztosan törölni szeretnéd ezt a topikot?');"><i class="fa-solid fa-trash"></i></a>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>Még nincsenek topikok.</p>
        <?php endif; ?>
    </div>

    <h3>Új topik létrehozása</h3>
    <a href="create_topic.php" class="create-topic-btn">Topik létrehozása</a>


    <?php if (isset($error)): ?>
        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
</main>

</body>
</html>
