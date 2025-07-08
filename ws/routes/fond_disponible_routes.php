<?php
require_once __DIR__ . '/../controllers/FondDisponibleController.php';

$EFFondDisponibleController = new FondDisponibleController();

Flight::route('GET /fond-disponible', [$EFFondDisponibleController, 'getFondDisponible']);
Flight::route('GET /fond-disponible/@annee/@mois', [$EFFondDisponibleController, 'getFondDisponibleByDate']);
Flight::route('GET /fond-disponible/cumule', [$EFFondDisponibleController, 'getFondDisponibleCumule']);
Flight::route('GET /fond-disponible/depots', [$EFFondDisponibleController, 'getDepotsByMois']);
Flight::route('GET /fond-disponible/prets', [$EFFondDisponibleController, 'getPretsByMois']);
Flight::route('GET /fond-disponible/remboursements', [$EFFondDisponibleController, 'getRemboursementsByMois']);