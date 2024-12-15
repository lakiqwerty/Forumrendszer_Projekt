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

if (!$topic) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_topic']) && $topic['user_id'] == $userId) {
    $newName = trim($_POST['topic_name']);
    $newDescription = trim($_POST['topic_description']);

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

$commentsQuery = "SELECT c.id, c.content, c.created_at, c.user_id, u.username 
                  FROM comments c 
                  JOIN users u ON c.user_id = u.id 
                  WHERE c.topic_id = :topic_id 
                  ORDER BY c.created_at DESC";
$commentsStmt = $db->prepare($commentsQuery);
$commentsStmt->bindParam(':topic_id', $topicId);
$commentsStmt->execute();
$comments = $commentsStmt->fetchAll(PDO::FETCH_ASSOC);

if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['comment_id'])) {
    $commentId = intval($_GET['comment_id']);

    $deleteQuery = "SELECT user_id FROM comments WHERE id = :comment_id";
    $deleteStmt = $db->prepare($deleteQuery);
    $deleteStmt->bindParam(':comment_id', $commentId);
    $deleteStmt->execute();
    $comment = $deleteStmt->fetch(PDO::FETCH_ASSOC);

    if ($comment && $comment['user_id'] == $userId) {
        $deleteQuery = "DELETE FROM comments WHERE id = :comment_id";
        $deleteStmt = $db->prepare($deleteQuery);
        $deleteStmt->bindParam(':comment_id', $commentId);

        if ($deleteStmt->execute()) {
            header("Location: topic_.php?id=$topicId");
            exit;
        } else {
            $error = "Hiba történt a hozzászólás törlésekor.";
        }
    } else {
        $error = "Nincs jogosultságod a hozzászólás törléséhez.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_comment'])) {
    $comment = trim($_POST['comment']);
    if (!empty($comment)) {
        $insertQuery = "INSERT INTO comments (topic_id, user_id, content) VALUES (:topic_id, :user_id, :content)";
        $insertStmt = $db->prepare($insertQuery);
        $insertStmt->bindParam(':topic_id', $topicId);
        $insertStmt->bindParam(':user_id', $userId);
        $insertStmt->bindParam(':content', $comment);
        if ($insertStmt->execute()) {
            header("Location: topic_.php?id=$topicId");
            exit;
        } else {
            $error = "Hiba történt a hozzászólás mentésekor.";
        }
    } else {
        $error = "A hozzászólás nem lehet üres.";
    }
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($topic['name']); ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="topic-style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body>
<header>
    <div class="header-container">
        <a href="dashboard.php" class="home-link">Főoldal</a>
        <div class="nav-links">
            <a href="logout.php"><i class="fa-solid fa-right-to-bracket"></i></a>
            <a href="profile.php"><i class="fa-solid fa-user"></i></a>
        </div>
    </div>
</header>

<main>
    <h1><?php echo htmlspecialchars($topic['name']); ?></h1>

    <div class="topic-description">
        <strong>Leírás:</strong>
        <?php echo nl2br(htmlspecialchars($topic['description'])); ?>
    </div>

    <?php if ($topic['user_id'] == $userId): ?>
        <h3><a href="editTopic.php?id=<?php echo $topicId; ?>">Szerkesztés</a> </h3>
    <?php endif; ?>

    <h2>Hozzászólások</h2>
    <?php if (count($comments) > 0): ?>
        <ul>
            <?php foreach ($comments as $comment): ?>
                <li>
                    <strong><?php echo htmlspecialchars($comment['username']); ?></strong>
                    (<?php echo htmlspecialchars($comment['created_at']); ?>):
                    <?php echo nl2br(htmlspecialchars($comment['content'])); ?>
                    <?php if ($comment['user_id'] == $userId): ?>
                        <div class="edit-delete-links">
                            <a href="editComment.php?comment_id=<?php echo $comment['id']; ?>&id=<?php echo $topicId; ?>" class="edit-link">Szerkesztés</a>
                            <a href="topic_.php?id=<?php echo $topicId; ?>&action=delete&comment_id=<?php echo $comment['id']; ?>"
                               class="delete-link" onclick="return confirm('Biztosan törölni szeretnéd a hozzászólást?');">Törlés</a>
                        </div>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>Még nincsenek hozzászólások.</p>
    <?php endif; ?>

    <h3>Új hozzászólás</h3>
    <form method="POST" action="">
        <textarea name="comment" placeholder="Írj egy hozzászólást..." required></textarea>
        <button type="submit" name="new_comment">Beküldés</button>
    </form>

    <?php if (isset($error)): ?>
        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
</main>

</body>
</html>
