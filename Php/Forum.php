<?php

require_once 'Database.php';
require_once 'User.php';
require_once 'Topic.php';
require_once 'Post.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user = User::getById($_SESSION['user_id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_topic'])) {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);

    if ($title && $content) {
        $topic = new Topic();
        $topic->setTitle($title);
        $topic->setUserId($user->getId());
        $topicId = $topic->save();

        if ($topicId) {
            $post = new Post();
            $post->setTopicId($topicId);
            $post->setUserId($user->getId());
            $post->setContent($content);
            $post->save();

            header('Location: forum.php');
            exit();
        }
    }
}

$topics = Topic::getAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forum</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<h1>Welcome to the Forum, <?php echo htmlspecialchars($user->getUsername()); ?>!</h1>

<h2>Create a New Topic</h2>
<form method="POST" action="forum.php">
    <label for="title">Title:</label><br>
    <input type="text" id="title" name="title" required><br><br>

    <label for="content">Content:</label><br>
    <textarea id="content" name="content" rows="5" required></textarea><br><br>

    <button type="submit" name="new_topic">Create Topic</button>
</form>

<hr>

<h2>Topics</h2>
<?php if (!empty($topics)): ?>
    <ul>
        <?php foreach ($topics as $topic): ?>
            <li>
                <a href="topic.php?id=<?php echo $topic->getId(); ?>">
                    <?php echo htmlspecialchars($topic->getTitle()); ?>
                </a>
                <br>
                <small>by <?php echo htmlspecialchars(User::getById($topic->getUserId())->getUsername()); ?></small>
            </li>
        <?php endforeach; ?>
    </ul>
<?php else: ?>
    <p>No topics available. Be the first to create one!</p>
<?php endif; ?>
</body>
</html>
