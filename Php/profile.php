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

$topicsQuery = "SELECT id, name, description FROM categories WHERE user_id = :user_id";
$topicsStmt = $db->prepare($topicsQuery);
$topicsStmt->bindParam(':user_id', $userId);
$topicsStmt->execute();
$topics = $topicsStmt->fetchAll(PDO::FETCH_ASSOC);

$commentsQuery = "SELECT c.id, c.content, c.created_at, t.name AS topic_name 
                  FROM comments c 
                  JOIN categories t ON c.topic_id = t.id 
                  WHERE c.user_id = :user_id 
                  ORDER BY c.created_at DESC";
$commentsStmt = $db->prepare($commentsQuery);
$commentsStmt->bindParam(':user_id', $userId);
$commentsStmt->execute();
$comments = $commentsStmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="profile-style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body>
<header>
    <div class="header-container">
        <a href="dashboard.php" class="home-link">Főoldal</a>
        <h1>Profil</h1>
        <div class="nav-links">
            <a href="logout.php"><i class="fa-solid fa-right-to-bracket"></i></a>
            <a href="profile.php"><i class="fa-solid fa-user"></i></a>
        </div>
    </div>
</header>

<main class="profile-main">
    <h1><?php echo htmlspecialchars($userData['username']); ?> profilja</h1>

    <section class="user-topics">
        <h2>Létrehozott topikok</h2>
        <?php if (count($topics) > 0): ?>
            <ul class="topic-list">
                <?php foreach ($topics as $topic): ?>
                    <li class="topic-item">
                        <a href="topic_.php?id=<?php echo $topic['id']; ?>" class="topic-link">
                            <?php echo htmlspecialchars($topic['name']); ?>
                        </a>
                        <p class="topic-description">Leírás: <?php echo nl2br(htmlspecialchars($topic['description'])); ?></p>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>Még nem hoztál létre topikot.</p>
        <?php endif; ?>
    </section>

    <section class="user-comments">
        <h2>Általad írt hozzászólások</h2>
        <?php if (count($comments) > 0): ?>
            <ul class="comment-list">
                <?php foreach ($comments as $comment): ?>
                    <li class="comment-item">
                        <strong>Topik neve: <?php echo htmlspecialchars($comment['topic_name']); ?></strong><br>
                        <strong>Komment:</strong>
                        <p><?php echo nl2br(htmlspecialchars($comment['content'])); ?></p>
                        <small>Írva: <?php echo htmlspecialchars($comment['created_at']); ?></small>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>Még nem írtál hozzászólásokat.</p>
        <?php endif; ?>
    </section>
</main>
</body>
</html>
