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

// Ha nem találjuk a topikot, irányítjuk a főoldalra
if (!$topic) {
    header('Location: index.php');
    exit;
}

// Ha a felhasználó a saját topikját nézi, akkor hozzáférhet a szerkesztéshez
$isOwner = ($topic['user_id'] == $userId);

// Kommentek lekérdezése
$commentsQuery = "SELECT c.id, c.content, c.created_at, c.user_id, c.parent_id, u.username 
                  FROM comments c 
                  JOIN users u ON c.user_id = u.id 
                  WHERE c.topic_id = :topic_id 
                  ORDER BY c.created_at ASC";
$commentsStmt = $db->prepare($commentsQuery);
$commentsStmt->bindParam(':topic_id', $topicId);
$commentsStmt->execute();
$comments = $commentsStmt->fetchAll(PDO::FETCH_ASSOC);

// Komment törlése
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['comment_id'])) {
    $commentId = $_GET['comment_id'];
    // Csak a saját kommentet lehet törölni
    $deleteQuery = "DELETE FROM comments WHERE id = :comment_id AND user_id = :user_id";
    $deleteStmt = $db->prepare($deleteQuery);
    $deleteStmt->bindParam(':comment_id', $commentId);
    $deleteStmt->bindParam(':user_id', $userId);

    if ($deleteStmt->execute()) {
        header("Location: topic_.php?id=$topicId");
        exit;
    } else {
        $error = "Hiba történt a komment törlésekor.";
    }
}


// displayComments függvény a kommentek rekurzív megjelenítésére
function displayComments($comments, $userId, $topicId, $parentId = null, $level = 0, $activeReplyId = null) {
    foreach ($comments as $comment) {
        if ($comment['parent_id'] == $parentId) {
            $commentClass = $parentId ? 'reply-box' : 'comment-box';

            echo '<div class="' . $commentClass . '">';
            echo '<strong>' . htmlspecialchars($comment['username']) . '</strong>';
            echo ' (' . htmlspecialchars($comment['created_at']) . '): ';
            echo '<p>' . nl2br(htmlspecialchars($comment['content'])) . '</p>';

            if ($comment['user_id'] == $userId) {
                echo '<div class="edit-delete-links">';
                echo '<a href="editComment.php?comment_id=' . $comment['id'] . '&id=' . $topicId . '" class="edit-link">Szerkesztés</a>';
                echo '<a href="topic_.php?id=' . $topicId . '&action=delete&comment_id=' . $comment['id'] . '" class="delete-link" onclick="return confirm(\'Biztosan törölni szeretnéd a hozzászólást?\');">Törlés</a>';
                echo '</div>';
            }

            echo '<form method="POST" action="" style="display: inline;">';
            echo '<button type="submit" name="reply_to_comment" value="' . $comment['id'] . '">Válasz</button>';
            echo '</form>';

            if ($activeReplyId == $comment['id']) {
                echo '<div class="reply-form">';
                echo '<form method="POST" action="">';
                echo '<textarea name="reply_comment" placeholder="Írj egy választ..." required></textarea>';
                echo '<input type="hidden" name="parent_id" value="' . $comment['id'] . '">';
                echo '<button type="submit" name="new_comment">Válasz beküldése</button>';
                echo '</form>';
                echo '</div>';
            }

            displayComments($comments, $userId, $topicId, $comment['id'], $level + 1, $activeReplyId);

            echo '</div>';
        }
    }
}

// Hozzászólás küldése
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_comment'])) {
    $comment = trim($_POST['comment']);
    $parentId = $_POST['parent_id'] ?? null;

    if (!empty($comment)) {
        // Új hozzászólás beszúrása az adatbázisba
        $insertQuery = "INSERT INTO comments (topic_id, user_id, content, parent_id) VALUES (:topic_id, :user_id, :content, :parent_id)";
        $insertStmt = $db->prepare($insertQuery);
        $insertStmt->bindParam(':topic_id', $topicId);
        $insertStmt->bindParam(':user_id', $userId);
        $insertStmt->bindParam(':content', $comment);
        $insertStmt->bindParam(':parent_id', $parentId, PDO::PARAM_INT);

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
    <link rel="stylesheet" href="../public/css/topic-style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body>
<header>
    <div class="header-container">
        <a href="dashboard.php" class="home-link">Főoldal</a>
        <div class="nav-links">
            <a href="controllers/logout.php"><i class="fa-solid fa-right-to-bracket"></i></a>
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

    <?php if ($isOwner): ?>
        <!-- Ha a felhasználó a topik tulajdonosa, megjelenik a szerkesztés gomb -->
        <a href="editTopic.php?id=<?php echo $topicId; ?>" class="edit-link">Szerkesztés</a>
    <?php endif; ?>

    <h2>Hozzászólások</h2>
    <?php displayComments($comments, $userId, $topicId); ?>

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
