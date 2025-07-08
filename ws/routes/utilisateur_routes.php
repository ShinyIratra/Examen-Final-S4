<?php
require_once __DIR__ . '/../controllers/UtilisateurController.php';

$UtilisateurController = new UtilisateurController();

Flight::route('GET /utilisateurs', [$UtilisateurController, 'getAll']);
Flight::route('GET /utilisateurs/@id', [$UtilisateurController, 'getById']);
Flight::route('POST /utilisateurs', [$UtilisateurController, 'create']);
Flight::route('PUT /utilisateurs/@id', [$UtilisateurController, 'update']);
Flight::route('DELETE /utilisateurs/@id', [$UtilisateurController, 'delete']);