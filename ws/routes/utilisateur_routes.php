<?php
require_once __DIR__ . '/../controllers/ObjetController.php';

$UtilisateurController = new ObjetController('EF_utilisateur', 'id_utilisateur', [
    'nom' => '',
    'mdp' => '',
    'identifiant' => ''
]);

Flight::route('GET /utilisateur', [$UtilisateurController, 'getAll']);
Flight::route('GET /utilisateur/@id', [$UtilisateurController, 'getById']);
Flight::route('POST /utilisateur', [$UtilisateurController, 'create']);
Flight::route('PUT /utilisateur/@id', [$UtilisateurController, 'update']);
Flight::route('DELETE /utilisateur/@id', [$UtilisateurController, 'delete']);