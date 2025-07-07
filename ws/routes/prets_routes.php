<?php
require_once __DIR__ . '/../controllers/ObjetController.php';

$PretsController = new ObjetController('ef_pret', 'id_pret', [
    'montant' => 0.0,
    'date_pret' => '',
    'date_retour' => '',
    'id_client' => 0,
    'id_type_pret' => 0,
]);

Flight::route('GET /prets', [$PretsController, 'getAll']);
Flight::route('GET /prets/@id', [$PretsController, 'getById']);
Flight::route('POST /prets', [$PretsController, 'create']);
Flight::route('PUT /prets/@id', [$PretsController, 'update']);
Flight::route('DELETE /prets/@id', [$PretsController, 'delete']);