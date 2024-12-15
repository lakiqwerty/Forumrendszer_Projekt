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

if (!isset($_GET['comment_id']) || !isset($_GET['id'])) {
    header('Location: topic_.php?id=' . $_GET['id'] ?? '');
    exit;
}

$commentId = intval($_GET['comment_id']);
$topicId = intval($_GET['id']);

$query = "SELECT id, content, user_id FROM comments WHERE id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $commentId);
$stmt->execute();
$comment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$comment || $comment['user_id'] != $userId) {
    header('Location: topic_.php?id=' . $topicId);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_comment'])) {
    $newContent = trim($_POST['comment_content']);

    if (!empty($newContent)) {
        $updateCommentQuery = "UPDATE comments SET content = :content WHERE id = :id AND user_id = :user_id";
        $updateCommentStmt = $db->prepare($updateCommentQuery);
        $updateCommentStmt->bindParam(':content', $newContent);
        $updateCommentStmt->bindParam(':id', $commentId);
        $updateCommentStmt->bindParam(':user_id', $userId);

        if ($updateCommentStmt->execute()) {
            header("Location: topic_.php?id=" . $topicId);
            exit;
        } else {
            $error = "Hiba történt a hozzászólás frissítésekor.";
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
    <title>Hozzászólás szerkesztése</title>
    <link rel="stylesheet" href="editcomment-style.css">
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

<main>
    <h1>Hozzászólás szerkesztése</h1>

    <?php if (isset($error)): ?>
        <p class="error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form method="POST" action="">
        <label for="comment_content">Hozzászólás:</label>
        <textarea id="comment_content" name="comment_content" required><?php echo htmlspecialchars($comment['content']); ?></textarea>
        <button type="submit" name="edit_comment">Mentés</button>
    </form>
</main>
</body>
</html>
