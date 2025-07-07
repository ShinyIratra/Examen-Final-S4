<?php
require_once __DIR__ . '/../controllers/ObjetController.php';

$EFDepotController = new ObjetController('EF_depot', 'id_depot', [
    'montant' => 0.0,
    'date_depot' => '',
    'id_utilisateur' => 0
]);

Flight::route('GET /depot', [$EFDepotController, 'getAll']);
Flight::route('GET /depot/@id', [$EFDepotController, 'getById']);
Flight::route('POST /depot', [$EFDepotController, 'create']);
Flight::route('PUT /depot/@id', [$EFDepotController, 'update']);
Flight::route('DELETE /depot/@id', [$EFDepotController, 'delete']);