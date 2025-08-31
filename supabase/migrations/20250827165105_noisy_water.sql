-- Base de données pour le système de gestion des inscriptions EP MATENDO
-- Créer la base de données
CREATE DATABASE IF NOT EXISTS ep_matendo_inscriptions 
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE ep_matendo_inscriptions;

-- Table des inscriptions
CREATE TABLE IF NOT EXISTS inscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    date_naissance DATE NOT NULL,
    classe VARCHAR(50) NOT NULL,
    parent_nom VARCHAR(100) NOT NULL,
    parent_tel VARCHAR(20) NOT NULL,
    document VARCHAR(255) DEFAULT '',
    statut ENUM('reçu', 'incomplet', 'validé', 'rejeté') DEFAULT 'reçu',
    date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    paiement_statut ENUM('non payé', 'payé') DEFAULT 'non payé',
    
    INDEX idx_statut (statut),
    INDEX idx_paiement (paiement_statut),
    INDEX idx_date (date_inscription),
    INDEX idx_classe (classe)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des paiements (pour garder une trace)
CREATE TABLE IF NOT EXISTS paiements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    inscription_id INT NOT NULL,
    montant DECIMAL(10,2) NOT NULL,
    mode ENUM('simulation') DEFAULT 'simulation',
    transaction_id VARCHAR(50) UNIQUE NOT NULL,
    date_paiement TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (inscription_id) REFERENCES inscriptions(id) ON DELETE CASCADE,
    INDEX idx_transaction (transaction_id),
    INDEX idx_date_paiement (date_paiement)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Données de test (optionnel)
INSERT INTO inscriptions (nom, prenom, date_naissance, classe, parent_nom, parent_tel, statut, paiement_statut) VALUES
('Mukadi', 'Jean', '2010-05-15', '5ème Primaire', 'Marie Mukadi', '+243 812 345 678', 'validé', 'payé'),
('Kabongo', 'Grace', '2012-08-22', '3ème Primaire', 'Paul Kabongo', '+243 823 456 789', 'reçu', 'non payé'),
('Mwanza', 'David', '2008-11-03', '1ère Secondaire', 'Sarah Mwanza', '+243 834 567 890', 'incomplet', 'non payé'),
('Tshimanga', 'Esther', '2011-02-18', '4ème Primaire', 'Joseph Tshimanga', '+243 845 678 901', 'validé', 'payé'),
('Kasongo', 'Pierre', '2009-07-30', '6ème Primaire', 'Beatrice Kasongo', '+243 856 789 012', 'reçu', 'non payé');

-- Données de paiement pour les inscriptions payées
INSERT INTO paiements (inscription_id, montant, mode, transaction_id) VALUES
(1, 50000.00, 'simulation', 'SIM_20250101120000_test001'),
(4, 50000.00, 'simulation', 'SIM_20250101130000_test002');