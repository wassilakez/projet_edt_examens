-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jan 07, 2026 at 11:26 AM
-- Server version: 10.10.2-MariaDB
-- PHP Version: 8.0.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `gestion_examens_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `departements`
--

DROP TABLE IF EXISTS `departements`;
CREATE TABLE IF NOT EXISTS `departements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nom` (`nom`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departements`
--

INSERT INTO `departements` (`id`, `nom`) VALUES
(3, 'Biologie'),
(4, 'Droit et Sciences Politiques'),
(5, 'Économie et Gestion'),
(1, 'Informatique'),
(7, 'langues'),
(2, 'Mathématiques'),
(6, 'Médecine');

-- --------------------------------------------------------

--
-- Table structure for table `examens`
--

DROP TABLE IF EXISTS `examens`;
CREATE TABLE IF NOT EXISTS `examens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `module_id` int(11) DEFAULT NULL,
  `prof_id` int(11) DEFAULT NULL,
  `salle_id` int(11) DEFAULT NULL,
  `date_examen` date NOT NULL,
  `heure_debut` time NOT NULL,
  `duree_minutes` int(11) DEFAULT 90,
  `statut` enum('EN_ATTENTE','VALIDE','REJETE') DEFAULT 'EN_ATTENTE',
  `conflit` tinyint(4) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `module_id` (`module_id`),
  KEY `prof_id` (`prof_id`),
  KEY `salle_id` (`salle_id`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `examens`
--

INSERT INTO `examens` (`id`, `module_id`, `prof_id`, `salle_id`, `date_examen`, `heure_debut`, `duree_minutes`, `statut`, `conflit`) VALUES
(1, 1, 1, 1, '2025-01-15', '09:00:00', 120, 'VALIDE', 0),
(20, 2, 1, 1, '2025-01-15', '09:00:00', 90, 'VALIDE', 0),
(21, 6, 1, 1, '2025-01-17', '08:00:00', 180, 'VALIDE', 0),
(22, 7, 1, 2, '2025-01-21', '09:00:00', 120, 'VALIDE', 0),
(23, 8, 7, 4, '2025-01-22', '10:30:00', 120, 'VALIDE', 0),
(24, 1, 1, 1, '2025-01-25', '09:00:00', 120, 'VALIDE', 0),
(25, 2, 1, 3, '2025-01-25', '14:00:00', 90, 'VALIDE', 0),
(26, 1, 1, 1, '2025-01-26', '09:00:00', 120, 'VALIDE', 0),
(27, 7, 7, 2, '2025-01-27', '10:00:00', 90, 'VALIDE', 0);

-- --------------------------------------------------------

--
-- Table structure for table `formations`
--

DROP TABLE IF EXISTS `formations`;
CREATE TABLE IF NOT EXISTS `formations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `dept_id` int(11) DEFAULT NULL,
  `nb_modules` int(11) DEFAULT NULL,
  `effectif` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `dept_id` (`dept_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `formations`
--

INSERT INTO `formations` (`id`, `nom`, `dept_id`, `nb_modules`, `effectif`) VALUES
(1, 'Licence Informatique G1', 1, 6, 200),
(2, 'Master Cybersécurité', 1, 8, 0),
(3, 'Licence Mathématiques', 2, NULL, 0),
(5, 'Licence Droit Public', 4, 7, 0),
(6, 'Master Finance de Marché', 5, 8, 0),
(7, 'Doctorat Médecine Générale', 6, 12, 0);

-- --------------------------------------------------------

--
-- Table structure for table `inscriptions`
--

DROP TABLE IF EXISTS `inscriptions`;
CREATE TABLE IF NOT EXISTS `inscriptions` (
  `etudiant_id` int(11) NOT NULL,
  `module_id` int(11) NOT NULL,
  `note` decimal(4,2) DEFAULT NULL,
  PRIMARY KEY (`etudiant_id`,`module_id`),
  KEY `module_id` (`module_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inscriptions`
--

INSERT INTO `inscriptions` (`etudiant_id`, `module_id`, `note`) VALUES
(2, 1, NULL),
(2, 2, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `lieu_examen`
--

DROP TABLE IF EXISTS `lieu_examen`;
CREATE TABLE IF NOT EXISTS `lieu_examen` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(50) NOT NULL,
  `capacite` int(11) NOT NULL,
  `type` enum('Amphi','Salle','Labo') DEFAULT NULL,
  `batiment` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lieu_examen`
--

INSERT INTO `lieu_examen` (`id`, `nom`, `capacite`, `type`, `batiment`) VALUES
(1, 'Amphi A', 150, 'Amphi', 'Principal'),
(2, 'Salle 101', 30, 'Salle', 'Bâtiment B'),
(3, 'Labo Info 1', 20, 'Labo', 'Technologie'),
(4, 'Amphi Euler', 200, 'Amphi', 'Sciences');

-- --------------------------------------------------------

--
-- Table structure for table `modules`
--

DROP TABLE IF EXISTS `modules`;
CREATE TABLE IF NOT EXISTS `modules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `credits` int(11) DEFAULT NULL,
  `formation_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `formation_id` (`formation_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `modules`
--

INSERT INTO `modules` (`id`, `nom`, `credits`, `formation_id`) VALUES
(1, 'Algorithmique Avancée', 6, 1),
(2, 'Bases de Données SQL', 4, 1),
(6, 'Anatomie Humaine', 8, 7),
(7, 'Droit des Affaires', 4, 5),
(8, 'Analyse Mathématique', 6, 3);

-- --------------------------------------------------------

--
-- Table structure for table `utilisateurs`
--

DROP TABLE IF EXISTS `utilisateurs`;
CREATE TABLE IF NOT EXISTS `utilisateurs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('etudiant','professeur','admin','doyen','chef_dep') NOT NULL,
  `nom` varchar(50) DEFAULT NULL,
  `prenom` varchar(50) DEFAULT NULL,
  `dept_id` int(11) DEFAULT NULL,
  `formation_id` int(11) DEFAULT NULL,
  `promo` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `dept_id` (`dept_id`),
  KEY `formation_id` (`formation_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `utilisateurs`
--

INSERT INTO `utilisateurs` (`id`, `username`, `password`, `role`, `nom`, `prenom`, `dept_id`, `formation_id`, `promo`) VALUES
(1, 'P001', '123', 'professeur', 'Durand', 'Jean', 1, NULL, NULL),
(2, 'E202401', '123', 'etudiant', 'Dupont', 'Marie', NULL, 1, NULL),
(5, 'admin', '123', 'admin', 'Système', 'Admin', NULL, NULL, NULL),
(6, 'doyen', '123', 'doyen', 'Benali', 'Professeur', NULL, NULL, NULL),
(7, 'P003', '1234', 'professeur', 'Rousseau', 'Jean-Jacques', 4, NULL, NULL),
(8, 'P004', 'keynes99', 'professeur', 'Keynes', 'John', 5, NULL, NULL),
(9, 'E202403', 'Etudiant1_Secret', 'etudiant', 'Zidane', 'Zinedine', NULL, 5, NULL),
(10, 'E202404', 'lagPass_2024', 'etudiant', 'Lagarde', 'Christine', NULL, 6, NULL);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `examens`
--
ALTER TABLE `examens`
  ADD CONSTRAINT `examens_ibfk_1` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`),
  ADD CONSTRAINT `examens_ibfk_2` FOREIGN KEY (`prof_id`) REFERENCES `utilisateurs` (`id`),
  ADD CONSTRAINT `examens_ibfk_3` FOREIGN KEY (`salle_id`) REFERENCES `lieu_examen` (`id`);

--
-- Constraints for table `formations`
--
ALTER TABLE `formations`
  ADD CONSTRAINT `formations_ibfk_1` FOREIGN KEY (`dept_id`) REFERENCES `departements` (`id`);

--
-- Constraints for table `inscriptions`
--
ALTER TABLE `inscriptions`
  ADD CONSTRAINT `inscriptions_ibfk_1` FOREIGN KEY (`etudiant_id`) REFERENCES `utilisateurs` (`id`),
  ADD CONSTRAINT `inscriptions_ibfk_2` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`);

--
-- Constraints for table `modules`
--
ALTER TABLE `modules`
  ADD CONSTRAINT `modules_ibfk_1` FOREIGN KEY (`formation_id`) REFERENCES `formations` (`id`);

--
-- Constraints for table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  ADD CONSTRAINT `utilisateurs_ibfk_1` FOREIGN KEY (`dept_id`) REFERENCES `departements` (`id`),
  ADD CONSTRAINT `utilisateurs_ibfk_2` FOREIGN KEY (`formation_id`) REFERENCES `formations` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
