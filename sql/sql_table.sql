drop database db_s2_ETU003332;
CREATE DATABASE db_s2_ETU003332 CHARACTER SET utf8mb4;
USE db_s2_ETU003332;

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
   montant DECIMAL(15,2)  NOT NULL,
   id_utilisateur INT NOT NULL,
   PRIMARY KEY(id_depot),
   FOREIGN KEY(id_utilisateur) REFERENCES EF_utilisateur(id_utilisateur)
);

CREATE TABLE EF_fond(
   id_fond INT AUTO_INCREMENT,
   id_depot INT NOT NULL,
   PRIMARY KEY(id_fond),
   FOREIGN KEY(id_depot) REFERENCES EF_depot(id_depot)
);

CREATE TABLE EF_admin(
   id_admin INT AUTO_INCREMENT,
   id_utilisateur INT NOT NULL,
   PRIMARY KEY(id_admin),
   FOREIGN KEY(id_utilisateur) REFERENCES EF_utilisateur(id_utilisateur)
);

CREATE TABLE EF_pret(
   id_pret INT AUTO_INCREMENT,
   montant DECIMAL(15,2)   NOT NULL,
   id_client INT NOT NULL,
   id_type_pret INT NOT NULL,
   PRIMARY KEY(id_pret),
   FOREIGN KEY(id_client) REFERENCES EF_client(id_client),
   FOREIGN KEY(id_type_pret) REFERENCES EF_type_pret(id_type_pret)
);
