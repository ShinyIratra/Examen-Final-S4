<?php
require_once __DIR__ . '/../controllers/ObjetController.php';
require_once __DIR__ . '/../controllers/ClientController.php';

$EFClientsController = new ClientController('EF_client', 'id_client', [
    'id_utilisateur' => 0
]);

Flight::route('GET /clients', [$EFClientsController, 'getAll']);
Flight::route('GET /clients/details', [$EFClientsController, 'getClientsWithUser']);
Flight::route('GET /clients/@id', [$EFClientsController, 'getById']);
Flight::route('POST /clients', [$EFClientsController, 'create']);
Flight::route('PUT /clients/@id', [$EFClientsController, 'update']);
Flight::route('DELETE /clients/@id', [$EFClientsController, 'delete']);

Flight::route('GET /clients/remboursements/@id_pret', [$EFClientsController, 'getRemboursementsByPret']);