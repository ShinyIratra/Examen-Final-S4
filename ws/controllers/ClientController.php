<?php
require_once __DIR__ . '/../models/Objet.php';
require_once __DIR__ . '/../controllers/ObjetController.php';
require_once __DIR__ . '/../models/Client.php';
require_once __DIR__ . '/../helpers/Utils.php';

class ClientController extends ObjetController {

    public function getClientsWithUser() {
        $result = Client::getClientsWithUser();
        Flight::json($result);
    }

    public function getRemboursementsByPret($id_pret) {
        $result = Client::getRemboursementsByPret($id_pret);
        Flight::json($result);
    }
}