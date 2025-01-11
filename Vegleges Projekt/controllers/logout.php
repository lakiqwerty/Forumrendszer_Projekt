<?php
session_start(); // Indítjuk a session-t

// Session törlése
session_unset();  // Törli az összes session változót
session_destroy(); // Megsemmisíti a session-t

// Átirányítás a bejelentkezési oldalra
header('Location: login.php');
exit;
?>
