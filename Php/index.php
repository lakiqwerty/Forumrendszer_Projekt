<?php
global $topics;
include_once 'Database.php';
include_once 'Topic.php';

$database = new Database();
$db = $database->getConnection();

$topic = new Topic($db);

$stmt = $topic->getAllTopics();

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
    <title>Fórum Témák</title>
    <link rel="stylesheet" href="index-style.css">
</head>
<body>
<header>
    <div class="container">
        <h1>Fórum Témák</h1>
        <nav>
            <a href="login.php">Bejelentkezés</a> | <a href="register.php">Regisztráció</a>
        </nav>
    </div>
</header>

<main>
    <div class="container">
        <h2>Elérhető Témák</h2>
        <ul>
            <?php if (count($topics) > 0): ?>
                <ul>
                    <?php foreach ($topics as $topic): ?>
                        <li class="topic-item">
                            <div class="topic-info">
                                <strong><?php echo htmlspecialchars($topic['name']); ?></strong>

                            </div>


                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>Még nincsenek topikok.</p>
            <?php endif; ?>
        </ul>
    </div>
</main>

</body>
</html>

