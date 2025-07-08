<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/Objet.php';

class Utilisateur extends Objet {
    
    public static function getAll($nom_table = 'v_utilisateurs_with_roles') {
        return parent::getAll($nom_table);
    }
    
    public static function getById($id, $nom_table = 'v_utilisateurs_with_roles', $nom_id = 'id_utilisateur') {
        return parent::getById($id, $nom_table, $nom_id);
    }
    
    // Les autres méthodes restent identiques...
}