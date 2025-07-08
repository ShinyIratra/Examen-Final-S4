drop database db_s2_ETU003332;
CREATE DATABASE db_s2_ETU003332 CHARACTER SET utf8mb4;
USE db_s2_ETU003332;

--- Fafana kelikelya any ---
CREATE TABLE etudiant (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100),
    prenom VARCHAR(100),
    email VARCHAR(100),
    age INT
);
---

CREATE TABLE EF_utilisateur(
   id_utilisateur INT AUTO_INCREMENT,
   nom VARCHAR(255)  NOT NULL,
   mdp TEXT NOT NULL,
   identifiant TEXT NOT NULL,
   PRIMARY KEY(id_utilisateur),
   UNIQUE(identifiant)
);

CREATE TABLE EF_type_pret(
   id_type_pret INT AUTO_INCREMENT,
   nom VARCHAR(255)  NOT NULL,
   taux DECIMAL(15,2)   NOT NULL,
   duree_mois INT NOT NULL,
   PRIMARY KEY(id_type_pret)
);

CREATE TABLE EF_client(
   id_client INT AUTO_INCREMENT,
   id_utilisateur INT NOT NULL,
   PRIMARY KEY(id_client),
   FOREIGN KEY(id_utilisateur) REFERENCES EF_utilisateur(id_utilisateur)
);

CREATE TABLE EF_depot(
   id_depot INT AUTO_INCREMENT,
   montant DECIMAL(15,2)   NOT NULL,
   date_depot DATETIME NOT NULL,
   id_utilisateur INT NOT NULL,
   PRIMARY KEY(id_depot),
   FOREIGN KEY(id_utilisateur) REFERENCES EF_utilisateur(id_utilisateur)
);

CREATE TABLE EF_admin(
   id_admin INT AUTO_INCREMENT,
   id_utilisateur INT NOT NULL,
   PRIMARY KEY(id_admin),
   FOREIGN KEY(id_utilisateur) REFERENCES EF_utilisateur(id_utilisateur)
);

CREATE TABLE EF_simulation(
   id_simulation INT AUTO_INCREMENT,
   montant DECIMAL(15,2)   NOT NULL,
   date_pret DATETIME NOT NULL,
   date_retour DATETIME NOT NULL,
   assurance DECIMAL(15,2)   DEFAULT 0,
   delai INT DEFAULT 0,
   id_type_pret INT NOT NULL,
   id_client INT NOT NULL,
   PRIMARY KEY(id_simulation),
   FOREIGN KEY(id_type_pret) REFERENCES EF_type_pret(id_type_pret),
   FOREIGN KEY(id_client) REFERENCES EF_client(id_client)
);

CREATE TABLE EF_simulation_remboursement(
   id_simulation_remboursement INT AUTO_INCREMENT,
   montant DECIMAL(15,2)   NOT NULL,
   date_remboursement DATE NOT NULL,
   interet DECIMAL(15,2)   NOT NULL,
   capital DECIMAL(15,2)   NOT NULL,
   id_simulation INT NOT NULL,
   PRIMARY KEY(id_simulation_remboursement),
   FOREIGN KEY(id_simulation) REFERENCES EF_simulation(id_simulation)
);

CREATE TABLE EF_pret(
   id_pret INT AUTO_INCREMENT,
   montant DECIMAL(15,2)   NOT NULL,
   date_pret DATETIME NOT NULL,
   date_retour DATETIME NOT NULL,
   assurance DECIMAL(15,2)   DEFAULT 0,
   delai INT DEFAULT 0,
   id_client INT NOT NULL,
   id_type_pret INT NOT NULL,
   PRIMARY KEY(id_pret),
   FOREIGN KEY(id_client) REFERENCES EF_client(id_client),
   FOREIGN KEY(id_type_pret) REFERENCES EF_type_pret(id_type_pret)
);

CREATE TABLE EF_remboursement(
   id_remboursement INT AUTO_INCREMENT,
   montant DECIMAL(15,2)   NOT NULL,
   date_remboursement DATE NOT NULL,
   interet DECIMAL(15,2)   NOT NULL,
   capital DECIMAL(15,2)   NOT NULL,
   isPaid BOOLEAN NOT NULL DEFAULT 0,
   date_payement DATE,
   id_pret INT NOT NULL,
   PRIMARY KEY(id_remboursement),
   FOREIGN KEY(id_pret) REFERENCES EF_pret(id_pret)
);

CREATE TABLE EF_pret_valide(
   id_pret_valide INT AUTO_INCREMENT,
   id_pret INT NOT NULL,
   PRIMARY KEY(id_pret_valide),
   FOREIGN KEY(id_pret) REFERENCES EF_pret(id_pret)
);

CREATE VIEW v_utilisateurs_with_roles AS
SELECT 
    u.id_utilisateur,
    u.nom,
    u.mdp,
    u.identifiant,
    CASE 
        WHEN a.id_admin IS NOT NULL THEN 'admin'
        WHEN c.id_client IS NOT NULL THEN 'client'
        ELSE 'aucun'
    END AS role
FROM EF_utilisateur u
LEFT JOIN EF_admin a ON u.id_utilisateur = a.id_utilisateur
LEFT JOIN EF_client c ON u.id_utilisateur = c.id_utilisateur;