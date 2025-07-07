-- Utilisateurs
INSERT INTO EF_utilisateur (nom, mdp, identifiant) VALUES
('Iratra', '123', 'admin'),
('Tafita', '123', 'user1'),
('User2', '123', 'user2');

-- Types de prêt
INSERT INTO EF_type_pret (nom, taux) VALUES
('Immobilier', 2.50),
('Consommation', 4.20),
('Auto', 3.10);

-- Clients (liés aux utilisateurs)
INSERT INTO EF_client (id_utilisateur) VALUES
(1),
(2);

-- Admins (liés aux utilisateurs)
INSERT INTO EF_admin (id_utilisateur) VALUES
(3);

----------------------------
-- DONNEE LIE TRANSACTION --
----------------------------
-- Dépôts (liés aux utilisateurs)
INSERT INTO EF_depot (montant, id_utilisateur) VALUES
(1000.00, 1),
(2500.50, 2),
(500.00, 3);

-- Fonds (liés aux dépôts)
INSERT INTO EF_fond (id_depot) VALUES
(1),
(2);

-- Prêts (liés aux clients et types de prêt)
INSERT INTO EF_pret (montant, id_client, id_type_pret) VALUES
(20000.00, 1, 1),
(5000.00, 2, 2),
(12000.00, 1, 3);