<?php
require_once __DIR__ . '/../controllers/ObjetController.php';

$TypePretsController = new ObjetController('EF_type_pret', 'id_type_pret', [
    'nom' => '',
    'taux' => 0.0,
    'duree_mois' => 0,
]);

Flight::route('GET /type-prets', [$TypePretsController, 'getAll']);
Flight::route('GET /type-prets/@id', [$TypePretsController, 'getById']);
Flight::route('POST /type-prets', [$TypePretsController, 'create']);
Flight::route('PUT /type-prets/@id', [$TypePretsController, 'update']);
Flight::route('DELETE /type-prets/@id', [$TypePretsController, 'delete']);