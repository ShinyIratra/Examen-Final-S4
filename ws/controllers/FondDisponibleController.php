<?php
require_once __DIR__ . '/../models/FondDisponible.php';
require_once __DIR__ . '/../helpers/Utils.php';

class FondDisponibleController {
    
    /**
     * Récupère tous les fonds disponibles par mois
     */
    public function getFondDisponible() {
        $result = FondDisponible::getFondDisponibleByMois();
        Flight::json($result);
    }
    
    /**
     * Récupère les fonds disponibles pour un mois spécifique
     */
    public function getFondDisponibleByDate($annee, $mois) {
        $result = FondDisponible::getFondDisponibleByMois($annee, $mois);
        Flight::json($result);
    }
    
    /**
     * Récupère les fonds disponibles cumulés
     */
    public function getFondDisponibleCumule() {
        $result = FondDisponible::getFondDisponibleCumule();
        Flight::json($result);
    }
    
    /**
     * Récupère les dépôts par mois
     */
    public function getDepotsByMois() {
        $result = FondDisponible::getDepotsByMois();
        Flight::json($result);
    }
    
    /**
     * Récupère les prêts par mois
     */
    public function getPretsByMois() {
        $result = FondDisponible::getPretsByMois();
        Flight::json($result);
    }
    
    /**
     * Récupère les remboursements par mois
     */
    public function getRemboursementsByMois() {
        $result = FondDisponible::getRemboursementsByMois();
        Flight::json($result);
    }
}