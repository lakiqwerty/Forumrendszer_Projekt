<?php
global $topics;

use models\Database;
use models\Topic;

include_once '../models/Database.php';
include_once '../models/Topic.php';

// Létrehozzuk az adatbázis kapcsolatot
$database = new Database();
$db = $database->getConnection();

// Létrehozzuk a Topic osztályt
$topic = new Topic($db);

// Lekérjük az összes témát
$stmt = $topic->getAllTopics();

// Topikok betöltése
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
    <link rel="stylesheet" href="css/index-style.css">
</head>
<body>
<header>
    <div class="container">
        <h1>Fórum Témák</h1>
        <nav>
            <a href="../controllers/login.php">Bejelentkezés</a> | <a href="../controllers/register.php">Regisztráció</a>
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

