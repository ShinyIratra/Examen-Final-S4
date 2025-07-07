<?php
    function getAll($url, $nom_table)
    {
        Flight::route('GET /' . $url, function() use ($nom_table) {
            $db = getDB();
            $stmt = $db->query("SELECT * FROM " . $nom_table);
            Flight::json($stmt->fetchAll(PDO::FETCH_ASSOC));
        });
    }

    function getById($url, $nom_table, $nom_id)
    {
        Flight::route('GET /' . $url . '/@id', function($id) use ($nom_table, $nom_id) {
            $db = getDB();
            $stmt = $db->prepare("SELECT * FROM " . $nom_table . " WHERE " . $nom_id . " = ?");
            $stmt->execute([$id]);
            Flight::json($stmt->fetch(PDO::FETCH_ASSOC));
        });
    }

    function insert($url, $nom_table, $champs)
    {
        Flight::route('POST /' . $url, function() use ($nom_table, $champs) {
            $data = Flight::request()->data;
            $db = getDB();
            $values = array_map(function($field) use ($data) { return $data->$field; }, array_keys($champs));
            $stmt = $db->prepare("INSERT INTO " . $nom_table . " (" . implode(", ", array_keys($champs)) . ") VALUES (" . implode(", ", array_fill(0, count($champs), '?')) . ")");
            $stmt->execute($values);
            Flight::json(['message' => $nom_table . ' ajouté', 'id' => $db->lastInsertId()]);
        });
    }

    function update($url, $nom_table, $champs, $nom_id)
    {
        Flight::route('PUT /' . $url . '/@id', function($id) use ($nom_table, $champs, $nom_id) {
            $data = Flight::request()->data;
            $db = getDB();
            $set = implode(', ', array_map(function($field) { return "$field = ?"; }, array_keys($champs)));
            $values = array_map(function($field) use ($data) { return $data->$field; }, array_keys($champs));
            $values[] = $id;
            $stmt = $db->prepare("UPDATE " . $nom_table . " SET " . $set . " WHERE " . $nom_id . " = ?");
            $stmt->execute($values);
            Flight::json(['message' => $nom_table . ' modifié']);
        });
    }

    function delete($url, $nom_table, $nom_id)
    {
        Flight::route('DELETE /' . $url . '/@id', function($id) use ($nom_table, $nom_id) {
            $db = getDB();
            $stmt = $db->prepare("DELETE FROM " . $nom_table . " WHERE " . $nom_id . " = ?");
            $stmt->execute([$id]);
            Flight::json(['message' => $nom_table . ' supprimé']);
        });
    }
?>