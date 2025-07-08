<?php
require_once __DIR__ . '/../controllers/ObjetController.php';
require_once __DIR__ . '/../controllers/InteretController.php';

$EFInteretsController = new InteretController('EF_remboursement', 'id_remboursement', [
    'montant' => 0.0,
    'date_remboursement' => '',
    'interet' => 0,
    'capital' => 0.0,
    'isPaid' => '',
    'id_pret' => 0
]);

Flight::route('GET /interets', [$EFInteretsController, 'getInterets']);
// Flight::route('GET /interets/date', [$EFInteretsController, 'getInteretsByDate']);
Flight::route('GET /interets/@date_debut/@date_fin', [$EFInteretsController, 'getInteretsByDate']);
Flight::route('POST /interets/calculer-remboursement/@id_pret', [$EFInteretsController, 'calculerRemboursement']);
Flight::route('GET /interets/@id', [$EFInteretsController, 'getById']);
Flight::route('POST /interets', [$EFInteretsController, 'create']);
Flight::route('PUT /interets/@id', [$EFInteretsController, 'update']);
Flight::route('DELETE /interets/@id', [$EFInteretsController, 'delete']);
