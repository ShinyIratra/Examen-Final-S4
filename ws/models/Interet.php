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
            GROUP BY annee, mois
            ORDER BY annee, mois");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}