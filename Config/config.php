<?php
// config/config.php

try {
    $pdo = new PDO("mysql:host=localhost;dbname=liste_entreprises;port=3308;charset=utf8", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>