<?php
require_once __DIR__ . '/../models/FondDisponible.php';
require_once __DIR__ . '/../helpers/Utils.php';

class FondDisponibleController {

    public function getFondDisponible() {
        $result = FondDisponible::getFondDisponibleByMois();
        Flight::json($result);
    }
   
    public function getFondDisponibleByDate($annee, $mois) {
        $result = FondDisponible::getFondDisponibleByMois($annee, $mois);
        Flight::json($result);
    }

    public function getFondDisponibleCumule() {
        $result = FondDisponible::getFondDisponibleCumule();
        Flight::json($result);
    }
    

    public function getDepotsByMois() {
        $result = FondDisponible::getDepotsByMois();
        Flight::json($result);
    }

    public function getPretsByMois() {
        $result = FondDisponible::getPretsByMois();
        Flight::json($result);
    }
    
    public function getRemboursementsByMois() {
        $result = FondDisponible::getRemboursementsByMois();
        Flight::json($result);
    }
}