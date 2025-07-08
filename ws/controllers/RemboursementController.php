<?php
require_once __DIR__ . '/../models/Objet.php';

class RemboursementController {
    private $nom_table = 'ef_remboursement';
    private $nom_id = 'id_remboursement';
    private $champs = [
        'montant' => 0.0,
        'date_remboursement' => '',
        'interet' => 0.0,
        'capital' => 0.0,
        'isPaid' => 0,
        'date_payement' => '',
        'id_pret' => 0
    ];

    public function getAll() {
        $result = Objet::getAll($this->nom_table);
        Flight::json($result);
    }

    public function update($id) {
        $data = Flight::request()->data;
        Objet::update($id, $data, $this->nom_table, $this->champs, $this->nom_id);
        Flight::json(['message' => 'Modifié']);
    }
}