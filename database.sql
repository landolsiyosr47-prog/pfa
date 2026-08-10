-- ═══════════════════════════════════════════════════════════
--  DATABASE.SQL — Schéma de base de données RéEmploi BTP
--  MySQL 5.7+ / MariaDB 10.3+
-- ═══════════════════════════════════════════════════════════

CREATE DATABASE IF NOT EXISTS reemploi_btp
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE reemploi_btp;

-- ── UTILISATEURS ──────────────────────────────────────────
CREATE TABLE IF NOT EXISTS utilisateurs (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom                 VARCHAR(100) NOT NULL,
    prenom              VARCHAR(100) NOT NULL DEFAULT '',
    email               VARCHAR(180) NOT NULL UNIQUE,
    mot_de_passe        VARCHAR(255) NOT NULL,
    role                ENUM('entreprise','artisan','admin') NOT NULL DEFAULT 'artisan',
    telephone           VARCHAR(30) DEFAULT NULL,
    ville               VARCHAR(100) DEFAULT NULL,
    statut              ENUM('actif','suspendu','en_attente') NOT NULL DEFAULT 'actif',
    derniere_connexion  DATETIME DEFAULT NULL,
    created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_role   (role),
    INDEX idx_statut (statut)
) ENGINE=InnoDB;

-- ── MATÉRIAUX ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS materiaux (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id  INT UNSIGNED NOT NULL,
    nom             VARCHAR(200) NOT NULL,
    categorie       ENUM('menuiserie','carrelage','bois','sanitaire','electricite','metal','isolation','autre') NOT NULL,
    quantite        DECIMAL(10,2) NOT NULL,
    unite           VARCHAR(30) NOT NULL DEFAULT 'unité(s)',
    etat            ENUM('Neuf (surplus)','Très bon état','Bon état','Correct') NOT NULL DEFAULT 'Bon état',
    lieu            VARCHAR(200) NOT NULL,
    description     TEXT DEFAULT NULL,
    telephone       VARCHAR(30) DEFAULT NULL,
    statut          ENUM('disponible','reserve','epuise','inactif') NOT NULL DEFAULT 'disponible',
    co2_economise   DECIMAL(8,3) DEFAULT 0,
    waste_kg        DECIMAL(8,2) DEFAULT 0,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    INDEX idx_categorie (categorie),
    INDEX idx_statut    (statut),
    INDEX idx_lieu      (lieu(50)),
    FULLTEXT idx_search (nom, description, lieu)
) ENGINE=InnoDB;

-- ── RÉSERVATIONS ──────────────────────────────────────────
CREATE TABLE IF NOT EXISTS reservations (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    materiau_id     INT UNSIGNED NOT NULL,
    utilisateur_id  INT UNSIGNED NOT NULL,
    quantite        DECIMAL(10,2) DEFAULT 1,
    message         TEXT DEFAULT NULL,
    statut          ENUM('en_attente','confirmee','annulee','recuperee') NOT NULL DEFAULT 'en_attente',
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (materiau_id)    REFERENCES materiaux(id)    ON DELETE CASCADE,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    INDEX idx_materiau    (materiau_id),
    INDEX idx_utilisateur (utilisateur_id),
    INDEX idx_statut      (statut)
) ENGINE=InnoDB;

-- ── SEED DATA ─────────────────────────────────────────────
-- Mot de passe: Admin@2025 (bcrypt)
INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role, statut) VALUES
('Admin', 'Super', 'admin@reemploi-btp.tn',
 '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: password
 'admin', 'actif'),
('BTP Construct Pro', '', 'entreprise@demo.tn',
 '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 'entreprise', 'actif'),
('Karim', 'Ben Salah', 'artisan@demo.tn',
 '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 'artisan', 'actif');

-- Matériaux de démonstration
INSERT INTO materiaux (utilisateur_id, nom, categorie, quantite, unite, etat, lieu, description, statut, co2_economise, waste_kg) VALUES
(2, 'Portes intérieures en chêne massif', 'menuiserie', 12, 'unité(s)', 'Neuf (surplus)', 'Tunis, Charguia', 'Portes 204x83cm avec serrures et poignées. Surplus de chantier résidentiel.', 'disponible', 0.96, 21.6),
(2, 'Carrelage grès cérame 60×60', 'carrelage', 85, 'm²', 'Neuf (surplus)', 'Ariana, Ettadhamen', 'Grès cérame rectifié 60x60, coloris gris anthracite. Lot homogène.', 'disponible', 2.125, 42.5),
(2, 'Parquet chêne massif flottant', 'bois', 40, 'm²', 'Très bon état', 'La Marsa', 'Lames 190x14mm, finition huilée naturelle.', 'disponible', 0.8, 56.0),
(2, 'Lavabos suspendus céramique', 'sanitaire', 6, 'unité(s)', 'Neuf (surplus)', 'Manouba', 'Lavabos 60cm blancs avec vidage. Modèle standard.', 'disponible', 0.3, 7.8),
(2, 'Câblage électrique NYY 4×6mm²', 'electricite', 200, 'ml', 'Neuf (surplus)', 'Ben Arous', 'Câble rigide 4x6mm², longueurs variables, en couronne.', 'disponible', 0.6, 12.0),
(2, 'Profilés acier galvanisé', 'metal', 30, 'ml', 'Bon état', 'Tunis, Cité Olympique', 'Profils UPN 100, longueur 6m, bon état général.', 'disponible', 1.95, 90.0),
(2, 'Laine de roche 10cm', 'isolation', 60, 'm²', 'Neuf (surplus)', 'Sfax', 'Panneaux 100x60cm, épaisseur 10cm, R=2.6 m².K/W.', 'disponible', 0.48, 15.0),
(2, 'Fenêtres PVC double vitrage 120×120', 'menuiserie', 8, 'unité(s)', 'Très bon état', 'Sousse', 'Fenêtres PVC blanc 2 vantaux avec vitrage 4/16/4.', 'disponible', 1.44, 14.4);
