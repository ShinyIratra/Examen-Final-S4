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

}