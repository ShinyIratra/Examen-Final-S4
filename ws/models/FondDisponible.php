<?php
require_once __DIR__ . '/../db.php';

class FondDisponible {
    
    /**
     * Récupère le montant total des dépôts par mois
     */
    public static function getDepotsByMois($annee = null, $mois = null) {
        $db = getDB();
        
        $whereClause = "";
        $params = [];
        
        if ($annee && $mois) {
            $whereClause = "WHERE YEAR(date_depot) = ? AND MONTH(date_depot) = ?";
            $params = [$annee, $mois];
        }
        
        $stmt = $db->prepare("
            SELECT 
                YEAR(date_depot) AS annee,
                MONTH(date_depot) AS mois,
                SUM(montant) AS total_depots
            FROM EF_depot 
            {$whereClause}
            GROUP BY YEAR(date_depot), MONTH(date_depot)
            ORDER BY annee, mois
        ");
        
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Récupère le montant total des prêts accordés par mois
     */
    public static function getPretsByMois($annee = null, $mois = null) {
        $db = getDB();
        
        $whereClause = "";
        $params = [];
        
        if ($annee && $mois) {
            $whereClause = "WHERE YEAR(p.date_pret) = ? AND MONTH(p.date_pret) = ?";
            $params = [$annee, $mois];
        }
        
        $stmt = $db->prepare("
            SELECT 
                YEAR(p.date_pret) AS annee,
                MONTH(p.date_pret) AS mois,
                SUM(p.montant) AS total_prets
            FROM EF_pret p
            JOIN EF_pret_valide pv ON p.id_pret = pv.id_pret
            {$whereClause}
            GROUP BY YEAR(p.date_pret), MONTH(p.date_pret)
            ORDER BY annee, mois
        ");
        
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Récupère le montant total des remboursements par mois
     */
    public static function getRemboursementsByMois($annee = null, $mois = null) {
        $db = getDB();
        
        $whereClause = "WHERE r.isPaid = 1";
        $params = [];
        
        if ($annee && $mois) {
            $whereClause .= " AND YEAR(r.date_remboursement) = ? AND MONTH(r.date_remboursement) = ?";
            $params = [$annee, $mois];
        }
        
        $stmt = $db->prepare("
            SELECT 
                YEAR(r.date_remboursement) AS annee,
                MONTH(r.date_remboursement) AS mois,
                SUM(r.montant) AS total_remboursements
            FROM EF_remboursement r
            JOIN EF_pret p ON r.id_pret = p.id_pret
            JOIN EF_pret_valide pv ON p.id_pret = pv.id_pret
            {$whereClause}
            GROUP BY YEAR(r.date_remboursement), MONTH(r.date_remboursement)
            ORDER BY annee, mois
        ");
        
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Calcule le montant total disponible par mois
     */
    public static function getFondDisponibleByMois($annee = null, $mois = null) {
        $depots = self::getDepotsByMois($annee, $mois);
        $prets = self::getPretsByMois($annee, $mois);
        $remboursements = self::getRemboursementsByMois($annee, $mois);
        
        $result = [];
        $allDates = [];
        
        // Collecter toutes les dates uniques
        foreach ($depots as $depot) {
            $key = $depot['annee'] . '-' . str_pad($depot['mois'], 2, '0', STR_PAD_LEFT);
            $allDates[$key] = ['annee' => $depot['annee'], 'mois' => $depot['mois']];
        }
        
        foreach ($prets as $pret) {
            $key = $pret['annee'] . '-' . str_pad($pret['mois'], 2, '0', STR_PAD_LEFT);
            $allDates[$key] = ['annee' => $pret['annee'], 'mois' => $pret['mois']];
        }
        
        foreach ($remboursements as $remboursement) {
            $key = $remboursement['annee'] . '-' . str_pad($remboursement['mois'], 2, '0', STR_PAD_LEFT);
            $allDates[$key] = ['annee' => $remboursement['annee'], 'mois' => $remboursement['mois']];
        }
        
        // Calculer le montant disponible pour chaque mois
        foreach ($allDates as $key => $date) {
            $totalDepots = 0;
            $totalPrets = 0;
            $totalRemboursements = 0;
            
            // Trouver les dépôts pour ce mois
            foreach ($depots as $depot) {
                if ($depot['annee'] == $date['annee'] && $depot['mois'] == $date['mois']) {
                    $totalDepots = $depot['total_depots'];
                    break;
                }
            }
            
            // Trouver les prêts pour ce mois
            foreach ($prets as $pret) {
                if ($pret['annee'] == $date['annee'] && $pret['mois'] == $date['mois']) {
                    $totalPrets = $pret['total_prets'];
                    break;
                }
            }
            
            // Trouver les remboursements pour ce mois
            foreach ($remboursements as $remboursement) {
                if ($remboursement['annee'] == $date['annee'] && $remboursement['mois'] == $date['mois']) {
                    $totalRemboursements = $remboursement['total_remboursements'];
                    break;
                }
            }
            
            // Calculer le montant disponible (dépôts + remboursements - prêts accordés)
            $montantDisponible = $totalDepots + $totalRemboursements - $totalPrets;
            
            $result[] = [
                'annee' => $date['annee'],
                'mois' => $date['mois'],
                'total_depots' => $totalDepots,
                'total_prets' => $totalPrets,
                'total_remboursements' => $totalRemboursements,
                'montant_disponible' => $montantDisponible
            ];
        }
        
        // Trier par année et mois
        usort($result, function($a, $b) {
            if ($a['annee'] != $b['annee']) {
                return $a['annee'] - $b['annee'];
            }
            return $a['mois'] - $b['mois'];
        });
        
        return $result;
    }
    
    /**
     * Calcule le montant total disponible cumulé
     */
    public static function getFondDisponibleCumule() {
        $fondsByMois = self::getFondDisponibleByMois();
        $montantCumule = 0;
        
        foreach ($fondsByMois as &$fond) {
            $montantCumule += $fond['montant_disponible'];
            $fond['montant_cumule'] = $montantCumule;
        }
        
        return $fondsByMois;
    }
}