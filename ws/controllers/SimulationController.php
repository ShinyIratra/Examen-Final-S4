<?php
require_once __DIR__ . '/../models/Objet.php';
require_once __DIR__ . '/../controllers/ObjetController.php';
require_once __DIR__ . '/../models/Simulation.php';
require_once __DIR__ . '/../helpers/Utils.php';

class SimulationController extends ObjetController {
    
    public function __construct() {
        parent::__construct(
            'ef_simulation', 
            'id_simulation',
            [
                'montant' => 'decimal',
                'date_pret' => 'datetime',
                'date_retour' => 'datetime',
                'assurance' => 'decimal',
                'delai' => 'int',
                'id_type_pret' => 'int',
                'id_client' => 'int'
            ]
        );
    }
    
    // Custom methods specific to simulation
    
    public function createSimulation() {
        $data = Flight::request()->data;
        
        // Validate required fields
        $requiredFields = ['montant', 'date_pret', 'date_retour', 'id_type_pret', 'id_client'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                Flight::json(['success' => false, 'message' => "Missing required field: $field"], 400);
                return;
            }
        }
        
        $simulationId = Simulation::createSimulation($data);
        
        if ($simulationId) {
            $simulation = Simulation::getSimulationById($simulationId);
            Simulation::calculateRemboursements($simulationId, $simulation);
            Flight::json(['success' => true, 'id' => $simulationId]);
        } else {
            Flight::json(['success' => false, 'message' => 'Failed to create simulation'], 500);
        }
    }
    
    // These methods are specific to simulation and not covered by parent class
    
    public function getSimulationsByClientId($clientId) {
        $simulations = Simulation::getSimulationsByClientId($clientId);
        Flight::json($simulations);
    }
    
    public function getSimulationsByUserId($userId) {
        $simulations = Simulation::getSimulationsByUserId($userId);
        Flight::json($simulations);
    }
    
    public function getSimulationRemboursements($simulationId) {
        $remboursements = Simulation::getSimulationRemboursements($simulationId);
        Flight::json($remboursements);
    }
    
    public function simulationPdf($id) {
        $simulation = Simulation::getSimulationById($id);
        
        if ($simulation) {
            $pdf = Simulation::generatePdf($simulation);
            Flight::json(['success' => true, 'pdf' => $pdf]);
        } else {
            Flight::json(['success' => false, 'message' => 'Simulation not found'], 404);
        }
    }
    
    public function convertToPret($simulationId) {
        $pretId = Simulation::convertToPret($simulationId);
        
        if ($pretId) {
            Flight::json(['success' => true, 'id_pret' => $pretId]);
        } else {
            Flight::json(['success' => false, 'message' => 'Failed to convert simulation to pret'], 500);
        }
    }
}