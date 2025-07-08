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

    public static function calculerRemboursement($id_pret, $data_pret)
{
    // 1. Validation minimale des paramètres
    if (is_object($data_pret)) {
        $data_pret = (array)$data_pret;
    }
    foreach (['id_type_pret','montant','date_pret','assurance'] as $key) {
        if (!isset($data_pret[$key])) {
            error_log("ERREUR: paramètre '{$key}' manquant");
            return ["error" => "paramètre '{$key}' manquant"];
        }
    }

    // 2. Récupération du taux et de la durée en mois
    $db   = getDB();
    $stmt = $db->prepare("
        SELECT taux, duree_mois 
        FROM EF_type_pret 
        WHERE id_type_pret = ?
    ");
    $stmt->execute([$data_pret['id_type_pret']]);
    $tp = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$tp) {
        error_log("ERREUR: Type de prêt non trouvé (id_type_pret={$data_pret['id_type_pret']})");
        return ["error" => "Type de prêt non trouvé"];
    }

    // 3. Paramètres de calcul
    $capital          = (float)$data_pret['montant'];
    $taux_mensuel     = ((float)$tp['taux']   / 100) / 12;  // ex. 10%/12 = 0,00833333
    $assur_mensuelle  = ((float)$data_pret['assurance'] / 100) / 12 * $capital; // ex. 2%/12 * capital
    
    $date_retour = new DateTime($data_pret['date_retour']);
    $date_pret = new DateTime($data_pret['date_pret']);
    $interval = $date_pret->diff($date_retour);
    $duree_mois = ($interval->y * 12) + $interval->m;
    if ($interval->d > 0 || $date_pret->format('d') == $date_retour->format('d')) {
        $duree_mois++;
    }

    // 4. Calcul de l’annuité constante
    $annuite = $capital*(($taux_mensuel)/(1-pow(1+$taux_mensuel,-$duree_mois)));

    // 5. Préparation de la boucle d’insertion
    $date_pret       = new DateTime($data_pret['date_pret']);
    $cap_restant     = $capital;
    $sql_insert      = "
        INSERT INTO EF_remboursement
            (montant, date_remboursement, interet, capital, isPaid, date_payement, id_pret)
        VALUES (?,       ?,                 ?,      ?,       0,      NULL,         ?)
    ";
    $stmt_insert = $db->prepare($sql_insert);

    // 6. Génération des lignes de tableau
    for ($m = 1; $m <= $duree_mois; $m++) {
        $interet           = round($cap_restant * $taux_mensuel, 8);
        $capital_rembourse = round($annuite - $interet,   8);
        $montant_total     = round($annuite + $assur_mensuelle, 8);

        // date de paiement = date_pret + (m-1) mois
        $date_rm = (clone $date_pret)->add(new DateInterval("P" . ($m-1) . "M"));

        $ok = $stmt_insert->execute([
            $montant_total,
            $date_rm->format('Y-m-d'),
            $interet,
            $capital_rembourse,
            $id_pret
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

}