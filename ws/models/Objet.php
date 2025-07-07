<?php
require_once __DIR__ . '/../db.php';

class Objet {
    public static function getAll($nom_table) {
        $db = getDB();
        $stmt = $db->query("SELECT * FROM " . $nom_table);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getById($id, $nom_table, $nom_id) {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM " . $nom_table . " WHERE " . $nom_id . " = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function insert($data, $nom_table, $champs) {
        $db = getDB();
        $values = array_map(function($field) use ($data) { return $data->$field; }, array_keys($champs));
        $stmt = $db->prepare("INSERT INTO " . $nom_table . " (" . implode(", ", array_keys($champs)) . ") VALUES (" . implode(", ", array_fill(0, count($champs), '?')) . ")");
        $stmt->execute($values);
        return $db->lastInsertId();
    }

    public static function update($id, $data, $nom_table, $champs, $nom_id) {
        $db = getDB();
        $set = implode(', ', array_map(function($field) { return "$field = ?"; }, array_keys($champs)));
        $values = array_map(function($field) use ($data) { return $data->$field; }, array_keys($champs));
        $values[] = $id;
        $stmt = $db->prepare("UPDATE " . $nom_table . " SET " . $set . " WHERE " . $nom_id . " = ?");
        $stmt->execute($values);
    }

    public static function delete($id, $nom_table, $nom_id) {
        $db = getDB();
        $stmt = $db->prepare("DELETE FROM " . $nom_table . " WHERE " . $nom_id . " = ?");
        $stmt->execute([$id]);
    }
}
