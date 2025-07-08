<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/Objet.php';

class PretValide extends Objet {

    public static function insertPret($id) {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO ef_pret_valide (id_pret) VALUES (?)");
        $stmt->execute([$id]);
        return $db->lastInsertId();
    }

    public static function getPretInvalideById() {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM ef_pret WHERE id_pret not in (SELECT id_pret FROM ef_pret_valide) Order by date_pret DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getPretValideByUser($id_user)
    {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM ef_pret WHERE id_pret in (SELECT id_pret FROM ef_pret_valide) and id_client = ? Order by date_pret DESC");
        $stmt->execute([$id_user]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}