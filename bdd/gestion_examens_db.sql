/* =========================================================
   BASE DE DONNÉES : gestion_examens_db
   Version unifiée – Vue globale Doyen
   ========================================================= */

DROP DATABASE IF EXISTS gestion_examens_db;
CREATE DATABASE gestion_examens_db
CHARACTER SET utf8mb4
COLLATE utf8mb4_general_ci;

USE gestion_examens_db;

SET FOREIGN_KEY_CHECKS = 0;

/* =========================
   DEPARTEMENTS
   ========================= */
CREATE TABLE departements (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(100) NOT NULL UNIQUE
);

INSERT INTO departements (id, nom) VALUES
(1,'Informatique'),
(2,'Mathématiques'),
(3,'Biologie'),
(4,'Droit et Sciences Politiques'),
(5,'Économie et Gestion'),
(6,'Médecine'),
(7,'Langues');

/* =========================
   FORMATIONS
   ========================= */
CREATE TABLE formations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(100) NOT NULL,
  dept_id INT,
  nb_modules INT,
  effectif INT DEFAULT 0,
  FOREIGN KEY (dept_id) REFERENCES departements(id)
);

INSERT INTO formations VALUES
(1,'Licence Informatique G1',1,6,200),
(2,'Master Cybersécurité',1,8,0),
(3,'Licence Mathématiques',2,NULL,0),
(5,'Licence Droit Public',4,7,0),
(6,'Master Finance de Marché',5,8,0),
(7,'Doctorat Médecine Générale',6,12,0);

/* =========================
   MODULES
   ========================= */
CREATE TABLE modules (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(100) NOT NULL,
  credits INT,
  formation_id INT,
  FOREIGN KEY (formation_id) REFERENCES formations(id)
);

INSERT INTO modules VALUES
(1,'Algorithmique Avancée',6,1),
(2,'Bases de Données SQL',4,1),
(6,'Anatomie Humaine',8,7),
(7,'Droit des Affaires',4,5),
(8,'Analyse Mathématique',6,3);

/* =========================
   UTILISATEURS
   ========================= */
CREATE TABLE utilisateurs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  role ENUM('etudiant','professeur','admin','doyen','chef_dep') NOT NULL,
  nom VARCHAR(50),
  prenom VARCHAR(50),
  dept_id INT,
  formation_id INT,
  promo VARCHAR(20),
  FOREIGN KEY (dept_id) REFERENCES departements(id),
  FOREIGN KEY (formation_id) REFERENCES formations(id)
);

INSERT INTO utilisateurs VALUES
(1,'P001','123','professeur','Durand','Jean',1,NULL,NULL),
(2,'E202401','123','etudiant','Dupont','Marie',NULL,1,NULL),
(5,'admin','123','admin','Système','Admin',NULL,NULL,NULL),
(6,'doyen','123','doyen','Benali','Professeur',NULL,NULL,NULL),
(7,'P003','1234','professeur','Rousseau','Jean-Jacques',4,NULL,NULL),
(8,'P004','keynes99','professeur','Keynes','John',5,NULL,NULL);

/* =========================
   LIEUX D’EXAMEN
   ========================= */
CREATE TABLE lieu_examen (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(50) NOT NULL,
  capacite INT NOT NULL,
  type ENUM('Amphi','Salle','Labo'),
  batiment VARCHAR(50)
);

INSERT INTO lieu_examen VALUES
(1,'Amphi A',150,'Amphi','Principal'),
(2,'Salle 101',30,'Salle','Bâtiment B'),
(3,'Labo Info 1',20,'Labo','Technologie'),
(4,'Amphi Euler',200,'Amphi','Sciences');

/* =========================
   EXAMENS (CENTRALE)
   ========================= */
CREATE TABLE examens (
  id INT AUTO_INCREMENT PRIMARY KEY,
  module_id INT,
  prof_id INT,
  salle_id INT,
  date_examen DATE NOT NULL,
  heure_debut TIME NOT NULL,
  duree_minutes INT DEFAULT 90,
  statut ENUM('EN_ATTENTE','VALIDE','REJETE') DEFAULT 'EN_ATTENTE',
  conflit TINYINT DEFAULT 0,
  FOREIGN KEY (module_id) REFERENCES modules(id),
  FOREIGN KEY (prof_id) REFERENCES utilisateurs(id),
  FOREIGN KEY (salle_id) REFERENCES lieu_examen(id)
);

/* =========================
   INSCRIPTIONS
   ========================= */
CREATE TABLE inscriptions (
  etudiant_id INT,
  module_id INT,
  note DECIMAL(4,2),
  PRIMARY KEY (etudiant_id, module_id),
  FOREIGN KEY (etudiant_id) REFERENCES utilisateurs(id),
  FOREIGN KEY (module_id) REFERENCES modules(id)
);

/* =========================
   CONFLITS (HISTORIQUE)
   ========================= */
CREATE TABLE conflicts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  type VARCHAR(50) NOT NULL,
  module VARCHAR(100),
  formation VARCHAR(100),
  reason TEXT,
  severity VARCHAR(20),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

/* =========================
   KPI – VUE DOYEN
   ========================= */
CREATE TABLE kpi_departements (
  id INT AUTO_INCREMENT PRIMARY KEY,
  dept_id INT,
  total_examens INT DEFAULT 0,
  examens_valides INT DEFAULT 0,
  conflits INT DEFAULT 0,
  taux_occupation DECIMAL(5,2),
  date_calc TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (dept_id) REFERENCES departements(id)
);

/* =========================
   LOGS (AUDIT)
   ========================= */
CREATE TABLE logs_actions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  action VARCHAR(100),
  cible VARCHAR(100),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES utilisateurs(id)
);

SET FOREIGN_KEY_CHECKS = 1;
