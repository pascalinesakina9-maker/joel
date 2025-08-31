-- Mise à jour de la base de données EP MATENDO
-- Script de migration pour améliorer la structure

USE ep_matendo_inscriptions;

-- Table des classes (nouvelle)
CREATE TABLE IF NOT EXISTS classes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL UNIQUE,
    niveau ENUM('primaire', 'secondaire') NOT NULL,
    frais_scolarite DECIMAL(10,2) DEFAULT 50000.00,
    capacite_max INT DEFAULT 30,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_niveau (niveau),
    INDEX idx_nom (nom)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des utilisateurs (pour l'authentification)
CREATE TABLE IF NOT EXISTS utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
    role ENUM('admin', 'secretaire', 'parent') DEFAULT 'parent',
    telephone VARCHAR(20),
    actif BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Mise à jour de la table inscriptions
ALTER TABLE inscriptions 
ADD COLUMN IF NOT EXISTS profile_image VARCHAR(255) DEFAULT 'default-avatar.png',
ADD COLUMN IF NOT EXISTS classe_id INT,
ADD COLUMN IF NOT EXISTS parent_id INT,
ADD COLUMN IF NOT EXISTS adresse TEXT,
ADD COLUMN IF NOT EXISTS notes TEXT,
ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Ajouter les contraintes de clés étrangères
ALTER TABLE inscriptions 
ADD CONSTRAINT fk_inscriptions_classe 
FOREIGN KEY (classe_id) REFERENCES classes(id) ON DELETE SET NULL,
ADD CONSTRAINT fk_inscriptions_parent 
FOREIGN KEY (parent_id) REFERENCES utilisateurs(id) ON DELETE SET NULL;

-- Mise à jour de la table paiements
ALTER TABLE paiements 
ADD COLUMN IF NOT EXISTS statut ENUM('en_attente', 'confirme', 'echoue') DEFAULT 'confirme',
ADD COLUMN IF NOT EXISTS methode_paiement VARCHAR(50) DEFAULT 'simulation',
ADD COLUMN IF NOT EXISTS notes TEXT;

-- Insérer les classes par défaut
INSERT IGNORE INTO classes (nom, niveau, frais_scolarite) VALUES
('1ère Primaire', 'primaire', 45000.00),
('2ème Primaire', 'primaire', 45000.00),
('3ème Primaire', 'primaire', 47000.00),
('4ème Primaire', 'primaire', 47000.00),
('5ème Primaire', 'primaire', 50000.00),
('6ème Primaire', 'primaire', 50000.00),
('1ère Secondaire', 'secondaire', 55000.00),
('2ème Secondaire', 'secondaire', 55000.00),
('3ème Secondaire', 'secondaire', 60000.00),
('4ème Secondaire', 'secondaire', 60000.00),
('5ème Secondaire', 'secondaire', 65000.00),
('6ème Secondaire', 'secondaire', 65000.00);

-- Créer un utilisateur admin par défaut
INSERT IGNORE INTO utilisateurs (nom, email, mot_de_passe, role) VALUES
('Administrateur', 'admin@epmatendo.cd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Mettre à jour les inscriptions existantes avec les nouvelles relations
UPDATE inscriptions i 
JOIN classes c ON i.classe = c.nom 
SET i.classe_id = c.id 
WHERE i.classe_id IS NULL;

-- Index pour optimiser les performances
CREATE INDEX IF NOT EXISTS idx_inscriptions_profile ON inscriptions(profile_image);
CREATE INDEX IF NOT EXISTS idx_inscriptions_parent ON inscriptions(parent_id);
CREATE INDEX IF NOT EXISTS idx_paiements_statut ON paiements(statut);