<?php

class Entreprise {
    private $id;
    private $nom;
    private $secteur;
    private $ville;
    private $description;

    // Définir la connexion PDO
    public static function getPDO() {
        try {
            $pdo = new PDO('mysql:host=localhost;dbname=liste_entreprises', 'root', '');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        } catch (PDOException $e) {
            die("Erreur de connexion à la base de données : " . $e->getMessage());
        }
    }

    // Récupérer toutes les entreprises
    public static function getAll() {
        $pdo = self::getPDO();
        $stmt = $pdo->query("SELECT * FROM entreprises");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);  // Retourner toutes les entreprises sous forme de tableau
    }
}