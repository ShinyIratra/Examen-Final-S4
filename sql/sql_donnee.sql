-- Utilisateurs
INSERT INTO EF_utilisateur (nom, mdp, identifiant) VALUES
('Iratra', '123', 'admin'),
('Tafita', '123', 'user1'),
('User2', '123', 'user2');

-- Types de prêt
INSERT INTO EF_type_pret (nom, taux, duree_mois) VALUES ('Etudiant', 10, 24 );

-- Clients (liés aux utilisateurs)
INSERT INTO EF_client (id_utilisateur) VALUES
(2),
(3);

-- Admins (liés aux utilisateurs)
INSERT INTO EF_admin (id_utilisateur) VALUES
(1);

INSERT INTO EF_remboursement (montant, date_remboursement, interet, capital, isPaid, date_payement, id_pret) 
                VALUES (10000, '2024-01-15', 0, 10000, false, NULL, 1);
----------------------------
-- DONNEE LIE TRANSACTION --
----------------------------
-- Dépôts (liés aux utilisateurs)
INSERT INTO EF_depot (montant, date_depot, id_utilisateur) VALUES
(1000.00, '2024-01-01 10:00:00', 1),
(2500.50, '2024-02-01 11:00:00', 2),
(500.00, '2024-03-01 12:00:00', 3);

-- Fonds (liés aux dépôts)
INSERT INTO EF_fond (id_depot) VALUES
(1),
(2);

INSERT INTO EF_pret_valide (id_pret) VALUES
(1);
-- Prêts (liés aux clients et types de prêt)
INSERT INTO EF_pret (montant, date_pret, date_retour, id_client, id_type_pret) VALUES
(20000.00, '2024-01-10', '2026-01-10', 1, 1),
(5000.00, '2024-02-15', '2025-02-15', 2, 2),
(12000.00, '2024-03-20', '2028-03-20', 1, 3);

SELECT YEAR(date_remboursement) AS annee, MONTH(date_remboursement) AS mois, SUM(interet) AS total_interets FROM EF_remboursement r JOIN EF_pret p ON r.id_pret = p.id_pret WHERE r.date_remboursement IS NOT NULL AND r.id_pret IN (SELECT id_pret FROM ef_pret_valide) GROUP BY annee, mois ORDER BY annee, mois