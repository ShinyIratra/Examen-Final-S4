<?php
require 'vendor/autoload.php';
require 'db.php';
require 'routes/etudiant_routes.php';
require 'routes/type_prets_routes.php';
require 'routes/ajout_depot_routes.php';
require 'routes/clients_routes.php';
require 'routes/prets_routes.php';
require 'routes/interets_routes.php';
require 'routes/remboursements_routes.php';
require 'routes/simulations_routes.php';
require 'routes/fond_disponible_routes.php';

Flight::start();