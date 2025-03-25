<?php
require_once __DIR__ . '/../Model/Entreprise.php';

class EntrepriseController {

    public function afficherEntreprises() {
        $entreprises = Entreprise::getAll();
        
        include __DIR__ . '/../templates/entreprises.twig';
    }
}