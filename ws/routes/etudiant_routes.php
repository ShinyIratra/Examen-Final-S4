<?php
require_once __DIR__ . '/../controllers/ObjetController.php';

$EtudiantController = new ObjetController('etudiant', 'id', [
    'nom' => '',
    'prenom' => '',
    'email' => '',
    'age' => 0
]);

Flight::route('GET /etudiants', [$EtudiantController, 'getAll']);
Flight::route('GET /etudiants/@id', [$EtudiantController, 'getById']);
Flight::route('POST /etudiants', [$EtudiantController, 'create']);
Flight::route('PUT /etudiants/@id', [$EtudiantController, 'update']);
Flight::route('DELETE /etudiants/@id', [$EtudiantController, 'delete']);