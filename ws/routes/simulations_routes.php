<?php
require_once __DIR__ . '/../controllers/SimulationController.php';

$SimulationsController = new SimulationController();

// Routes for simulations
Flight::route('GET /simulations', [$SimulationsController, 'getAll']);
Flight::route('GET /simulations/tri/@ordre', function($ordre) use ($SimulationsController) {
    $croissant = $ordre === 'asc';
    $SimulationsController->getAllDescByColonne("date_pret", $croissant);
});
Flight::route('GET /simulations/@id', [$SimulationsController, 'getById']);
Flight::route('GET /simulations/client/@id', [$SimulationsController, 'getSimulationsByClientId']);
Flight::route('GET /simulations/user/@id', [$SimulationsController, 'getSimulationsByUserId']);
Flight::route('GET /simulations/remboursements/@id', [$SimulationsController, 'getSimulationRemboursements']);
Flight::route('GET /simulations/pdf/@id', [$SimulationsController, 'simulationPdf']);
Flight::route('POST /simulations', [$SimulationsController, 'createSimulation']);
Flight::route('POST /simulations/convert-to-pret/@id', [$SimulationsController, 'convertToPret']);
Flight::route('PUT /simulations/@id', [$SimulationsController, 'update']);
Flight::route('DELETE /simulations/@id', [$SimulationsController, 'delete']);