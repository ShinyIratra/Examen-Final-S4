<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/Objet.php';

class SimulationRemboursement extends Objet {
    
    public static function getBySimulationId($simulationId) {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT * FROM ef_simulation_remboursement
            WHERE id_simulation = ?
            ORDER BY date_remboursement ASC
        ");
        $stmt->execute([$simulationId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public static function createRemboursement($data) {
        $db = getDB();
        $stmt = $db->prepare("
            INSERT INTO ef_simulation_remboursement 
            (montant, date_remboursement, interet, capital, id_simulation)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['montant'],
            $data['date_remboursement'],
            $data['interet'],
            $data['capital'],
            $data['id_simulation']
        ]);
        
        return $db->lastInsertId();
    }
    
    public static function deleteBySimulationId($simulationId) {
        $db = getDB();
        $stmt = $db->prepare("DELETE FROM ef_simulation_remboursement WHERE id_simulation = ?");
        $stmt->execute([$simulationId]);
    }
}