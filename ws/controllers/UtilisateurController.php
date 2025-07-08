<?php
require_once __DIR__ . '/../models/Utilisateur.php';
require_once __DIR__ . '/../controllers/ObjetController.php';

class UtilisateurController extends ObjetController {
    
    public function __construct() {
        parent::__construct('EF_utilisateur', 'id_utilisateur', [
            'nom' => '',
            'identifiant' => '',
            'mdp' => ''
        ]);
    }
    
    // Remplacer getAll par cette méthode personnalisée
    public function getAll() {
        try {
            $db = getDB();
            $stmt = $db->query("SELECT * FROM v_utilisateurs_with_roles");
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            Flight::json($result);
        } catch (Exception $e) {
            Flight::json(['error' => $e->getMessage()], 500);
        }
    }
    
    public function create() {
        try {
            $data = Flight::request()->data;
            
            // Validation simple
            if (empty($data->nom) || empty($data->identifiant) || empty($data->mdp) || empty($data->role)) {
                Flight::json(['error' => 'Tous les champs sont requis'], 400);
                return;
            }
            
            $db = getDB();
            $db->beginTransaction();
            
            try {
                // 1. Créer l'utilisateur
                $stmt = $db->prepare("INSERT INTO EF_utilisateur (nom, identifiant, mdp) VALUES (?, ?, ?)");
                $stmt->execute([
                    $data->nom,
                    $data->identifiant,
                    $data->mdp
                ]);
                
                $userId = $db->lastInsertId();
                
                // 2. Créer le rôle correspondant
                if ($data->role === 'admin') {
                    $stmt = $db->prepare("INSERT INTO EF_admin (id_utilisateur) VALUES (?)");
                    $stmt->execute([$userId]);
                } elseif ($data->role === 'client') {
                    $stmt = $db->prepare("INSERT INTO EF_client (id_utilisateur) VALUES (?)");
                    $stmt->execute([$userId]);
                }
                
                $db->commit();
                Flight::json(['message' => 'Utilisateur créé avec succès', 'id' => $userId]);
                
            } catch (Exception $e) {
                $db->rollBack();
                throw $e;
            }
            
        } catch (Exception $e) {
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                Flight::json(['error' => 'Cet identifiant existe déjà'], 400);
            } else {
                Flight::json(['error' => $e->getMessage()], 500);
            }
        }
    }
    
    public function update($id) {
        try {
            $data = Flight::request()->data;
            $db = getDB();
            $db->beginTransaction();
            
            try {
                // 1. Mettre à jour les données utilisateur
                $userData = [];
                $params = [];
                
                if (!empty($data->nom)) {
                    $userData[] = "nom = ?";
                    $params[] = $data->nom;
                }
                if (!empty($data->identifiant)) {
                    $userData[] = "identifiant = ?";
                    $params[] = $data->identifiant;
                }
                if (!empty($data->mdp)) {
                    $userData[] = "mdp = ?";
                    $params[] = $data->mdp;
                }
                
                if (!empty($userData)) {
                    $params[] = $id;
                    $stmt = $db->prepare("UPDATE EF_utilisateur SET " . implode(', ', $userData) . " WHERE id_utilisateur = ?");
                    $stmt->execute($params);
                }
                
                // 2. Gérer le changement de rôle SEULEMENT si nécessaire
                if (!empty($data->role)) {
                    // Vérifier le rôle actuel
                    $stmt = $db->prepare("
                        SELECT 
                            CASE 
                                WHEN a.id_admin IS NOT NULL THEN 'admin'
                                WHEN c.id_client IS NOT NULL THEN 'client'
                                ELSE 'aucun'
                            END AS role_actuel
                        FROM EF_utilisateur u
                        LEFT JOIN EF_admin a ON u.id_utilisateur = a.id_utilisateur
                        LEFT JOIN EF_client c ON u.id_utilisateur = c.id_utilisateur
                        WHERE u.id_utilisateur = ?
                    ");
                    $stmt->execute([$id]);
                    $roleActuel = $stmt->fetchColumn();
                    
                    // SEULEMENT si le rôle change
                    if ($roleActuel !== $data->role) {
                        // Supprimer l'ancien rôle
                        if ($roleActuel === 'admin') {
                            $stmt = $db->prepare("DELETE FROM EF_admin WHERE id_utilisateur = ?");
                            $stmt->execute([$id]);
                        } elseif ($roleActuel === 'client') {
                            $stmt = $db->prepare("DELETE FROM EF_client WHERE id_utilisateur = ?");
                            $stmt->execute([$id]);
                        }
                        
                        // Ajouter le nouveau rôle
                        if ($data->role === 'admin') {
                            $stmt = $db->prepare("INSERT INTO EF_admin (id_utilisateur) VALUES (?)");
                            $stmt->execute([$id]);
                        } elseif ($data->role === 'client') {
                            $stmt = $db->prepare("INSERT INTO EF_client (id_utilisateur) VALUES (?)");
                            $stmt->execute([$id]);
                        }
                    }
                }
                
                $db->commit();
                Flight::json(['message' => 'Utilisateur modifié avec succès']);
                
            } catch (Exception $e) {
                $db->rollBack();
                throw $e;
            }
            
        } catch (Exception $e) {
            Flight::json(['error' => $e->getMessage()], 500);
        }
    }
    
    public function delete($id) {
        try {
            $db = getDB();
            $db->beginTransaction();
            
            try {
                // Supprimer les rôles associés
                $stmt = $db->prepare("DELETE FROM EF_admin WHERE id_utilisateur = ?");
                $stmt->execute([$id]);
                $stmt = $db->prepare("DELETE FROM EF_client WHERE id_utilisateur = ?");
                $stmt->execute([$id]);
                
                // Supprimer l'utilisateur
                $stmt = $db->prepare("DELETE FROM EF_utilisateur WHERE id_utilisateur = ?");
                $stmt->execute([$id]);
                
                $db->commit();
                Flight::json(['message' => 'Utilisateur supprimé avec succès']);
                
            } catch (Exception $e) {
                $db->rollBack();
                throw $e;
            }
            
        } catch (Exception $e) {
            Flight::json(['error' => $e->getMessage()], 500);
        }
    }
    
    // Autres méthodes existantes...
}