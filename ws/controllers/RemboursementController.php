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
        $db = getDB();
        $sql = "SELECT * FROM ef_remboursement WHERE id_pret IN (SELECT id_pret FROM ef_pret_valide)";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        Flight::json($result);
    }

    public function update($id) {
        $data = Flight::request()->data;
        Objet::update($id, $data, $this->nom_table, $this->champs, $this->nom_id);
        Flight::json(['message' => 'Modifié']);
    }

    public function create() {
        $data = Flight::request()->data;
        $db = getDB();
        
        // Vérifier que le prêt est validé avant d'autoriser l'insertion
        $stmt = $db->prepare("SELECT 1 FROM ef_pret_valide WHERE id_pret = ?");
        $stmt->execute([$data->id_pret]);
        if (!$stmt->fetch()) {
            Flight::json(['error' => 'Le prêt n\'est pas validé, remboursement impossible.'], 400);
            return;
        }
        
        // Si validé, continuer l'insertion
        Objet::create($data, $this->nom_table, $this->champs);
        Flight::json(['message' => 'Ajouté']);
    }
}