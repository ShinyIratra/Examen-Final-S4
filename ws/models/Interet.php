<?php
require_once __DIR__ . '/../db.php';

class Interet  {
    public static function getInteretsByDate($date_debut, $date_fin) {
        $db = getDB();
        $stmt = $db->prepare("SELECT
            YEAR(date_remboursement) AS annee,
            MONTH(date_remboursement) AS mois,
            SUM(interet) AS total_interets
            FROM EF_remboursement r
            JOIN EF_pret p ON r.id_pret = p.id_pret
            WHERE r.date_remboursement IS NOT NULL
            AND r.date_remboursement BETWEEN ? AND ?
            AND r.id_pret IN (SELECT id_pret FROM ef_pret_valide)
            GROUP BY annee, mois
            ORDER BY annee, mois");
        $stmt->execute([$date_debut, $date_fin]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getInterets() {
        $db = getDB();
        $stmt = $db->prepare("SELECT
            YEAR(date_remboursement) AS annee,
            MONTH(date_remboursement) AS mois,
            SUM(interet) AS total_interets
            FROM EF_remboursement r
            JOIN EF_pret p ON r.id_pret = p.id_pret
            WHERE r.date_remboursement IS NOT NULL
            AND r.id_pret IN (SELECT id_pret FROM ef_pret_valide)
            GROUP BY annee, mois
            ORDER BY annee, mois");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function calculerRemboursement($id_pret, $data_pret) {
        if (is_object($data_pret)) {
            $data_pret = (array)$data_pret;
        }
        error_log("DEBUG data_pret: " . print_r($data_pret, true));
        if (!isset($data_pret['id_type_pret'])) {
            error_log("ERREUR: id_type_pret absent du body");
            return ["error" => "id_type_pret absent du body", "data_pret" => $data_pret];
        }
        $db = getDB();
        
        // NOUVELLE VÉRIFICATION : Le prêt doit être validé
        $stmt = $db->prepare("SELECT 1 FROM ef_pret_valide WHERE id_pret = ?");
        $stmt->execute([$id_pret]);
        if (!$stmt->fetch()) {
            error_log("ERREUR: Le prêt ID $id_pret n'est pas validé");
            return ["error" => "Le prêt n'est pas validé", "id_pret" => $id_pret];
        }
        
        // Debug: Afficher les données reçues
        error_log("=== DEBUT DEBUG REMBOURSEMENT ===");
        error_log("ID Prêt: " . $id_pret);
        error_log("Data prêt: " . print_r($data_pret, true));
        
        $stmt = $db->prepare("SELECT taux, duree_mois FROM EF_type_pret WHERE id_type_pret = ?");
        $stmt->execute([$data_pret['id_type_pret']]);
        $type_pret = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$type_pret) {
            error_log("ERREUR: Type de prêt non trouvé");
            return false;
        }
        
        error_log("Type prêt trouvé: " . print_r($type_pret, true));

        $capital = $data_pret['montant'];
        $taux = $type_pret['taux'] / 100 / 12;

        // Calcul de la durée réelle en mois entre date_pret et date_retour
        $date_pret = new DateTime($data_pret['date_pret']);
        $date_retour = new DateTime($data_pret['date_retour']);
        $interval = $date_pret->diff($date_retour);
        $duree_mois = ($interval->y * 12) + $interval->m;
        if ($interval->d > 0 ) $duree_mois++; // arrondir au mois supérieur si jours restants

        $assurance = ($data_pret['assurance'] / 100 / 12) * $capital;

        error_log("Paramètres: Capital=$capital, Taux=$taux, Durée=$duree_mois mois, Assurance=$assurance");

        if($taux > 0) {
            $annuite = $capital * ($taux * pow(1 + $taux, $duree_mois)) / (pow(1 + $taux, $duree_mois) - 1);
        } else {
            $annuite = $capital / $duree_mois;
        }

        error_log("Annuité calculée: $annuite");

        $capital_restant = $capital;

        for ($mois = 1; $mois <= $duree_mois; $mois++) {
            error_log("--- MOIS $mois ---");
            
            $interet = $capital_restant * $taux;
            $capital_rembourse = $annuite - $interet;
            $montant_total = $annuite + $assurance;

            error_log("Intérêt: $interet, Capital remboursé: $capital_rembourse, Montant total: $montant_total");

            // Calcul de la date de remboursement pour ce mois
            $date_remboursement = clone $date_pret;
            $date_remboursement->add(new DateInterval("P{$mois}M"));

            error_log("Date remboursement: " . $date_remboursement->format('Y-m-d'));

            $sql = "INSERT INTO EF_remboursement 
                (montant, date_remboursement, interet, capital, isPaid, date_payement, id_pret) 
                VALUES (?, ?, ?, ?, 0, NULL, ?)";
            
            error_log("SQL: $sql");
            error_log("Valeurs: [" . $montant_total . ", " . $date_remboursement->format('Y-m-d') . ", $interet, $capital_rembourse, $id_pret]");
            
            $stmt_insert = $db->prepare($sql);
            $result = $stmt_insert->execute([
                $montant_total,
                $date_remboursement->format('Y-m-d'),
                $interet,
                $capital_rembourse,
                $id_pret
            ]);
            
            if (!$result) {
                error_log("ERREUR INSERTION: " . print_r($stmt_insert->errorInfo(), true));
                return false;
            } else {
                error_log("INSERTION RÉUSSIE pour le mois $mois");
            }

            $capital_restant -= $capital_rembourse;
            if ($capital_restant < 0.01) {
                $capital_restant = 0;
            }
            
            error_log("Capital restant: $capital_restant");
        }
        
        error_log("=== FIN DEBUG REMBOURSEMENT ===");
        return true;
    }

}