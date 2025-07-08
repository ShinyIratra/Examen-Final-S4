<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/Objet.php';

class Client extends Objet {

    public static function getClientsWithUser() {
        $db = getDB();
        $sql = "SELECT c.*, u.nom, u.identifiant FROM EF_client c JOIN EF_utilisateur u ON c.id_utilisateur = u.id_utilisateur";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getRemboursementsByPret($id_pret) {
        $db = getDB();
        $stmt = $db->prepare("SELECT r.*, p.montant as pret_montant 
            FROM EF_remboursement r
            JOIN EF_pret p ON r.id_pret = p.id_pret
            WHERE r.id_pret = ?
            ORDER BY r.date_remboursement");
        $stmt->execute([$id_pret]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}