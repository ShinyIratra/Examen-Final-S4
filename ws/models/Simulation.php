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
        $stmt = $db->prepare("SELECT taux, duree_mois FROM EF_type_pret WHERE id_type_pret = ?");
        $stmt->execute([$simulationData['id_type_pret']]);
        $tp = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$tp) {
            error_log("ERREUR: Type de prêt non trouvé (id_type_pret={$simulationData['id_type_pret']})");
            return false;
        }
        
        // Paramètres de calcul
        $capital = (float)$simulationData['montant'];
        $taux_mensuel = ((float)$tp['taux'] / 100) / 12;  // ex. 10%/12 = 0,00833333
        $assurance_mensuelle = ((float)$simulationData['assurance'] / 100) / 12 * $capital; // montant mensuel d'assurance
        
        // Calculer la durée en mois à partir des dates
        $date_retour = new DateTime($simulationData['date_retour']);
        $date_pret = new DateTime($simulationData['date_pret']);
        $interval = $date_pret->diff($date_retour);
        $duree_mois = ($interval->y * 12) + $interval->m;
        if ($interval->d > 0 || $date_pret->format('d') == $date_retour->format('d')) {
            $duree_mois++;
        }
        
        $delai = isset($simulationData['delai']) ? (int)$simulationData['delai'] : 0;
        
        // Allonger la durée totale du prêt du délai
        $duree_mois = $duree_mois + $delai;
        
        // Calcul de l'annuité constante (hors délai)
        $nb_mois_remb = $duree_mois - $delai;
        if ($nb_mois_remb <= 0) $nb_mois_remb = 1; // sécurité
        $annuite = $capital*(($taux_mensuel)/(1-pow(1+$taux_mensuel,-$nb_mois_remb)));
        
        // Préparation de la boucle d'insertion
        $date_pret_obj = new DateTime($simulationData['date_pret']);
        $cap_restant = $capital;
        $sql_insert = "
            INSERT INTO ef_simulation_remboursement
                (montant, date_remboursement, interet, capital, id_simulation)
            VALUES (?, ?, ?, ?, ?)
        ";
        $stmt_insert = $db->prepare($sql_insert);
        
        // Génération des lignes de tableau
        for ($m = 1; $m <= $duree_mois; $m++) {
            $date_rm = (clone $date_pret_obj)->add(new DateInterval("P" . ($m-1) . "M"));
            
            if ($m <= $delai) {
                // Pendant le délai : on ne rembourse que l'intérêt
                $interet = $cap_restant * $taux_mensuel;
                $capital_rembourse = 0;
                $montant_total = $interet + $assurance_mensuelle;
            } else {
                // Après le délai : annuité classique
                $interet = $cap_restant * $taux_mensuel;
                $capital_rembourse = $annuite - $interet;
                $montant_total = $annuite + $assurance_mensuelle;
            }
            
            $ok = $stmt_insert->execute([
                $montant_total,
                $date_rm->format('Y-m-d'),
                $interet,
                $capital_rembourse,
                $simulationId
            ]);
            
            if (!$ok) {
                error_log("ERREUR INSERTION: " . print_r($stmt_insert->errorInfo(), true));
                return false;
            }
            
            // mise à jour du capital restant
            $cap_restant = round($cap_restant - $capital_rembourse, 8);
            if ($cap_restant <= 0) {
                break;
            }
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