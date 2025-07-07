<?php
require_once __DIR__ . '/../models/Objet.php';
require_once __DIR__ . '/../controllers/ObjetController.php';
require_once __DIR__ . '/../models/PretValide.php';
require_once __DIR__ . '/../helpers/Utils.php';

class PretController extends ObjetController {

    public function validePret($id) {
        $result = PretValide::insertPret($id);
        Flight::json(['success' => true, 'id' => $result]);
    }

    public function getPretInvalideById()
    {
        $result = PretValide::getPretInvalideById();
        Flight::json($result);
    }
}