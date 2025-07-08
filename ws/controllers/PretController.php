<?php
require_once __DIR__ . '/../models/Objet.php';
require_once __DIR__ . '/../controllers/ObjetController.php';
require_once __DIR__ . '/../models/PretValide.php';
require_once __DIR__ . '/../models/Pret.php';
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

    public static function getPretValideByUser($id_user)
    {
        $result = PretValide::getPretValideByUser($id_user);
        Flight::json($result);
    }

    public static function pretPdf($id)
    {
        $result = Pret::getPretById($id);
        if ($result) {
            $pdf = Pret::generatePdf($result);
            Flight::json(['success' => true, 'pdf' => $pdf]);
        } else {
            Flight::json(['success' => false, 'message' => 'Pret not found']);
        }
    }
}