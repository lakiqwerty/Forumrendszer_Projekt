<?php

$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'forum';

try {
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "
    CREATE SCHEMA IF NOT EXISTS `forum` DEFAULT CHARACTER SET utf8mb4;
    USE `forum`;

    CREATE TABLE IF NOT EXISTS `categories` (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `name` VARCHAR(100) NOT NULL,
      `description` TEXT NOT NULL,
      `user_id` INT(11) NOT NULL,
      PRIMARY KEY (`id`),
      UNIQUE INDEX `name` (`name` ASC)
    ) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4;

    CREATE TABLE IF NOT EXISTS `roles` (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `name` VARCHAR(50) NOT NULL,
      PRIMARY KEY (`id`),
      UNIQUE INDEX `name` (`name` ASC)
    ) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;

    CREATE TABLE IF NOT EXISTS `users` (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `username` VARCHAR(50) NOT NULL,
      `password` VARCHAR(255) NOT NULL,
      `email` VARCHAR(100) NOT NULL,
      `role_id` INT(11) NULL DEFAULT 2,
      PRIMARY KEY (`id`),
      UNIQUE INDEX `username` (`username` ASC),
      UNIQUE INDEX `email` (`email` ASC),
      INDEX `role_id` (`role_id` ASC),
      CONSTRAINT `users_ibfk_1`
        FOREIGN KEY (`role_id`)
        REFERENCES `roles` (`id`)
        ON DELETE SET NULL
    ) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4;

    CREATE TABLE IF NOT EXISTS `comments` (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `topic_id` INT(11) NOT NULL,
      `user_id` INT(11) NOT NULL,
      `content` TEXT NOT NULL,
      `created_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP(),
      `parent_id` INT(11) NULL DEFAULT NULL,
      PRIMARY KEY (`id`),
      INDEX `topic_id` (`topic_id` ASC),
      INDEX `user_id` (`user_id` ASC),
      CONSTRAINT `comments_ibfk_1`
        FOREIGN KEY (`topic_id`)
        REFERENCES `categories` (`id`)
        ON DELETE CASCADE,
      CONSTRAINT `comments_ibfk_2`
        FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`)
        ON DELETE CASCADE
    ) ENGINE=InnoDB AUTO_INCREMENT=132 DEFAULT CHARSET=utf8mb4;

    CREATE TABLE IF NOT EXISTS `favorites` (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `user_id` INT(11) NOT NULL,
      `topic_id` INT(11) NOT NULL,
      `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
      PRIMARY KEY (`id`),
      INDEX `user_id` (`user_id` ASC),
      INDEX `topic_id` (`topic_id` ASC),
      CONSTRAINT `favorites_ibfk_1`
        FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`)
        ON DELETE CASCADE,
      CONSTRAINT `favorites_ibfk_2`
        FOREIGN KEY (`topic_id`)
        REFERENCES `categories` (`id`)
        ON DELETE CASCADE
    ) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4;

    CREATE TABLE IF NOT EXISTS `topics` (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `user_id` INT(11) NOT NULL,
      `category_id` INT(11) NULL DEFAULT NULL,
      `title` VARCHAR(255) NOT NULL,
      `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
      PRIMARY KEY (`id`),
      INDEX `user_id` (`user_id` ASC),
      INDEX `category_id` (`category_id` ASC),
      CONSTRAINT `topics_ibfk_1`
        FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`)
        ON DELETE CASCADE,
      CONSTRAINT `topics_ibfk_2`
        FOREIGN KEY (`category_id`)
        REFERENCES `categories` (`id`)
        ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    CREATE TABLE IF NOT EXISTS `posts` (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `topic_id` INT(11) NOT NULL,
      `user_id` INT(11) NOT NULL,
      `content` TEXT NOT NULL,
      `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
      `status` ENUM('approved', 'deleted') NULL DEFAULT 'approved',
      PRIMARY KEY (`id`),
      INDEX `topic_id` (`topic_id` ASC),
      INDEX `user_id` (`user_id` ASC),
      CONSTRAINT `posts_ibfk_1`
        FOREIGN KEY (`topic_id`)
        REFERENCES `topics` (`id`)
        ON DELETE CASCADE,
      CONSTRAINT `posts_ibfk_2`
        FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`)
        ON DELETE CASCADE
    ) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;
    ";

    $pdo->exec($sql);

    $insertRoleSql = "INSERT INTO `roles` (`id`, `name`) VALUES (2, 'user') ON DUPLICATE KEY UPDATE name='user';";
    $pdo->exec($insertRoleSql);

    echo "Database, tables, and initial data created successfully.";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

?>
