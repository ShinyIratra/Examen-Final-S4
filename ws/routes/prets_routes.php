<?php
require_once __DIR__ . '/../controllers/PretController.php';

$PretsController = new PretController('ef_pret', 'id_pret', [
    'montant' => 0.0,
    'date_pret' => '',
    'date_retour' => '',
    'assurance' => 0,
    'delai' => 0,
    'id_client' => 0,
    'id_type_pret' => 0,
]);

Flight::route('GET /prets', [$PretsController, 'getAll']);
Flight::route('GET /prets/valides', [$PretsController, 'getAllPretsValides']);
Flight::route('GET /prets/tri/@ordre', function($ordre) use ($PretsController) {
    $croissant = $ordre === 'asc';
    $PretsController->getAllDescByColonne("date_pret", $croissant);
});
Flight::route('GET /prets/invalides/desc', [$PretsController, 'getPretInvalideById']);
Flight::route('GET /prets/@id', [$PretsController, 'getById']);
Flight::route('GET /prets/user/@id', [$PretsController, 'getPretValideByUser']);
Flight::route('GET /prets/pdf/@id', [$PretsController, 'pretPdf']); // Add this new route
Flight::route('POST /prets', [$PretsController, 'create']);
Flight::route('POST /prets/valide/@id', [$PretsController, 'validePret']);
Flight::route('PUT /prets/@id', [$PretsController, 'update']);
Flight::route('DELETE /prets/@id', [$PretsController, 'delete']);