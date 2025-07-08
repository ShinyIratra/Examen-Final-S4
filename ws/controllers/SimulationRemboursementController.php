<?php
require_once __DIR__ . '/../models/Objet.php';
require_once __DIR__ . '/../controllers/ObjetController.php';
require_once __DIR__ . '/../models/SimulationRemboursement.php';
require_once __DIR__ . '/../helpers/Utils.php';

class SimulationRemboursementController extends ObjetController {
    
    public function __construct() {
        parent::__construct(
            'ef_simulation_remboursement', 
            'id_simulation_remboursement',
            [
                'montant' => 'decimal',
                'date_remboursement' => 'date',
                'interet' => 'decimal',
                'capital' => 'decimal',
                'id_simulation' => 'int'
            ]
        );
    }
    
    public function getBySimulationId($simulationId) {
        $remboursements = SimulationRemboursement::getBySimulationId($simulationId);
        Flight::json($remboursements);
    }
}