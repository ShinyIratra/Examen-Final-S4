<?php
require_once __DIR__ . '/../controllers/ObjetController.php';

$EFDepotController = new ObjetController('EF_depot', 'id_depot', [
    'montant' => 0.0,
    'date_depot' => '',
    'id_utilisateur' => 0
]);

Flight::route('GET /depots', [$EFDepotController, 'getAll']);
Flight::route('GET /depots/@id', [$EFDepotController, 'getById']);
Flight::route('POST /depots', [$EFDepotController, 'create']);
Flight::route('PUT /depots/@id', [$EFDepotController, 'update']);
Flight::route('DELETE /depots/@id', [$EFDepotController, 'delete']);