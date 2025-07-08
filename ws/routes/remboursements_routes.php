<?php
require_once __DIR__ . '/../controllers/RemboursementController.php';

$RemboursementController = new RemboursementController();

Flight::route('GET /remboursements', [$RemboursementController, 'getAll']);
Flight::route('PUT /remboursements/@id', [$RemboursementController, 'update']);