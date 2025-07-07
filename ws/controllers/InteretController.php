<?php
require_once __DIR__ . '/../models/Objet.php';
require_once __DIR__ . '/../helpers/Utils.php';
require_once __DIR__ . '/../models/Interet.php';

class InteretController extends ObjetController {
    
    public function getInteretsByDate($date_debut, $date_fin) {
        $result = Interet::getInteretsByDate($date_debut, $date_fin);
        Flight::json($result);
    }

    public function getInterets() {
        $result = Interet::getInterets();
        Flight::json($result);
    }

}
