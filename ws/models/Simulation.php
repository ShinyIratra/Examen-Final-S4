<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/Objet.php';

class Simulation extends Objet {

    public static function getSimulationById($id) {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT s.*, c.*, u.nom as nom_client, u.identifiant, t.taux 
            FROM ef_simulation s
            JOIN ef_client c ON s.id_client = c.id_client
            JOIN ef_utilisateur u ON c.id_utilisateur = u.id_utilisateur
            JOIN ef_type_pret t ON s.id_type_pret = t.id_type_pret
            WHERE s.id_simulation = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function getSimulationsByClientId($clientId) {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT s.*, t.nom as type_pret_nom, t.taux
            FROM ef_simulation s
            JOIN ef_type_pret t ON s.id_type_pret = t.id_type_pret
            WHERE s.id_client = ?
            ORDER BY s.date_pret DESC
        ");
        $stmt->execute([$clientId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public static function getSimulationsByUserId($userId) {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT s.*, t.nom as type_pret_nom, t.taux
            FROM ef_simulation s
            JOIN ef_client c ON s.id_client = c.id_client
            JOIN ef_utilisateur u ON c.id_utilisateur = u.id_utilisateur
            JOIN ef_type_pret t ON s.id_type_pret = t.id_type_pret
            WHERE u.id_utilisateur = ?
            ORDER BY s.date_pret DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public static function createSimulation($data) {
        $db = getDB();
        $stmt = $db->prepare("
            INSERT INTO ef_simulation 
            (montant, date_pret, date_retour, assurance, delai, id_type_pret, id_client)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['montant'],
            $data['date_pret'],
            $data['date_retour'],
            $data['assurance'] ?? 0,
            $data['delai'] ?? 0,
            $data['id_type_pret'],
            $data['id_client']
        ]);
        
        return $db->lastInsertId();
    }
    
    public static function calculateRemboursements($simulationId, $simulationData) {
        $db = getDB();
        
        // Clean up existing remboursements if any
        $cleanStmt = $db->prepare("DELETE FROM ef_simulation_remboursement WHERE id_simulation = ?");
        $cleanStmt->execute([$simulationId]);
        
        // Get loan type details
        $typePretStmt = $db->prepare("SELECT taux, duree_mois FROM ef_type_pret WHERE id_type_pret = ?");
        $typePretStmt->execute([$simulationData['id_type_pret']]);
        $typePret = $typePretStmt->fetch(PDO::FETCH_ASSOC);
        
        $montantTotal = $simulationData['montant'];
        $taux = $typePret['taux'] / 100; // Convert percentage to decimal
        $duree = $typePret['duree_mois'];
        
        // Calculate monthly payment amount (PMT formula)
        $tauxMensuel = $taux / 12;
        $paiementMensuel = $montantTotal * $tauxMensuel * pow(1 + $tauxMensuel, $duree) / (pow(1 + $tauxMensuel, $duree) - 1);
        
        $dateDebut = new DateTime($simulationData['date_pret']);
        $solde = $montantTotal;
        
        $insertStmt = $db->prepare("
            INSERT INTO ef_simulation_remboursement 
            (montant, date_remboursement, interet, capital, id_simulation)
            VALUES (?, ?, ?, ?, ?)
        ");
        
        for ($i = 1; $i <= $duree; $i++) {
            $dateDebut->add(new DateInterval('P1M'));
            $interet = $solde * $tauxMensuel;
            $capital = $paiementMensuel - $interet;
            $solde -= $capital;
            
            $insertStmt->execute([
                $paiementMensuel,
                $dateDebut->format('Y-m-d'),
                $interet,
                $capital,
                $simulationId
            ]);
        }
        
        return true;
    }
    
    public static function getSimulationRemboursements($simulationId) {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT * FROM ef_simulation_remboursement
            WHERE id_simulation = ?
            ORDER BY date_remboursement ASC
        ");
        $stmt->execute([$simulationId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
        
    public static function convertToPret($simulationId) {
        $db = getDB();
        $simulation = self::getSimulationById($simulationId);
        
        if (!$simulation) {
            return false;
        }
        
        // Insert into pret table
        $stmt = $db->prepare("
            INSERT INTO ef_pret 
            (montant, date_pret, date_retour, assurance, delai, id_client, id_type_pret)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $simulation['montant'],
            $simulation['date_pret'],
            $simulation['date_retour'],
            $simulation['assurance'],
            $simulation['delai'],
            $simulation['id_client'],
            $simulation['id_type_pret']
        ]);
        
        return $db->lastInsertId();
    }

}