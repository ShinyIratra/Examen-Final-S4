<?php
require_once __DIR__ . '/../models/Objet.php';
require_once __DIR__ . '/../helpers/Utils.php';
require_once __DIR__ . '/../models/Interet.php';

class ObjetController {
    private $nom_table = '';
    private $nom_id = '';
    private $champs = [];

    public function __construct($nom_table, $nom_id, $champs) {
        $this->nom_table = $nom_table;
        $this->nom_id = $nom_id;
        $this->champs = $champs;
    }

    public function getAll() {
        $result = Objet::getAll($this->nom_table);
        Flight::json($result);
    }

    public function getById($id) {
        $result = Objet::getById($id, $this->nom_table, $this->nom_id);
        Flight::json($result);
    }

    public function create() {
        $data = Flight::request()->data;
        $id = Objet::insert($data, $this->nom_table, $this->champs);

        if ($this->nom_table == 'ef_pret') {
            $data_array = (array) $data; // Convertir en array pour la méthode
            Interet::calculerRemboursement($id, $data_array);
        }
        Flight::json(['message' => 'Ajouté', 'id' => $id]);
    }

    public function update($id) {
        $data = Flight::request()->data;
        Objet::update($id, $data, $this->nom_table, $this->champs, $this->nom_id);
        Flight::json(['message' => 'Modifié']);
    }

    public function delete($id) {
        Objet::delete($id, $this->nom_table, $this->nom_id);
        Flight::json(['message' => 'Supprimé']);
    }
}
