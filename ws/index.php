<?php
require 'vendor/autoload.php';
require 'db.php';
require 'RequeteBase.php';

// Middleware pour CORS sur toutes les réponses
Flight::before('start', function() {
    if (isset($_SERVER['HTTP_ORIGIN'])) {
        header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400');
    }
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
            header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        }
        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
            header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
        }
        exit(0);
    }
});

// Utilise les fonctions génériques pour CRUD étudiant
getAll('etudiants', 'etudiant');
getById('etudiants', 'etudiant', 'id');
insert('etudiants', 'etudiant', ['nom' => '', 'prenom' => '', 'email' => '', 'age' => 0]);
update('etudiants', 'etudiant', ['nom' => '', 'prenom' => '', 'email' => '', 'age' => 0], 'id');
delete('etudiants', 'etudiant', 'id');

Flight::start();