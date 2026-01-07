-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : lun. 29 déc. 2025 à 23:22
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `gestion_examens_db`
--

-- --------------------------------------------------------

CREATE TABLE `conflicts` (
  `id` int(11) NOT NULL,
  `type` varchar(100) NOT NULL,
  `module` varchar(100) DEFAULT NULL,
  `formation` varchar(100) DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `severity` varchar(50) DEFAULT 'warning',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `departements`
--

CREATE TABLE `departements` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `departements`
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
-- Structure de la table `examens`
--

CREATE TABLE `examens` (
  `id` int(11) NOT NULL,
  `module_id` int(11) DEFAULT NULL,
  `prof_id` int(11) DEFAULT NULL,
  `salle_id` int(11) DEFAULT NULL,
  `date_examen` date NOT NULL,
  `heure_debut` time NOT NULL,
  `duree_minutes` int(11) DEFAULT 90
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `examens`
--

INSERT INTO `examens` (`id`, `module_id`, `prof_id`, `salle_id`, `date_examen`, `heure_debut`, `duree_minutes`) VALUES
(1, 1, 1, 1, '2025-01-15', '09:00:00', 120),
(20, 2, 1, 3, '2025-01-15', '14:30:00', 90),
(21, 6, 1, 1, '2025-01-17', '08:00:00', 180),
(22, 7, 8, 2, '2025-01-21', '09:00:00', 120),
(23, 8, 7, 4, '2025-01-22', '10:30:00', 120);

-- --------------------------------------------------------

--
-- Structure de la table `formations`
--

CREATE TABLE `formations` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `dept_id` int(11) DEFAULT NULL,
  `nb_modules` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `formations`
--

INSERT INTO `formations` (`id`, `nom`, `dept_id`, `nb_modules`) VALUES
(1, 'Licence Informatique G1', 1, 6),
(2, 'Master Cybersécurité', 1, 8),
(3, 'Licence Mathématiques', 2, NULL),
(5, 'Licence Droit Public', 4, 7),
(6, 'Master Finance de Marché', 5, 8),
(7, 'Doctorat Médecine Générale', 6, 12);

-- --------------------------------------------------------

--
-- Structure de la table `inscriptions`
--

CREATE TABLE `inscriptions` (
  `etudiant_id` int(11) NOT NULL,
  `module_id` int(11) NOT NULL,
  `note` decimal(4,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `lieu_examen`
--

CREATE TABLE `lieu_examen` (
  `id` int(11) NOT NULL,
  `nom` varchar(50) NOT NULL,
  `capacite` int(11) NOT NULL,
  `type` enum('Amphi','Salle','Labo') DEFAULT NULL,
  `batiment` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `lieu_examen`
--

INSERT INTO `lieu_examen` (`id`, `nom`, `capacite`, `type`, `batiment`) VALUES
(1, 'Amphi A', 150, 'Amphi', 'Principal'),
(2, 'Salle 101', 30, 'Salle', 'Bâtiment B'),
(3, 'Labo Info 1', 20, 'Labo', 'Technologie'),
(4, 'Amphi Euler', 200, 'Amphi', 'Sciences');

-- --------------------------------------------------------

--
-- Structure de la table `modules`
--

CREATE TABLE `modules` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `credits` int(11) DEFAULT NULL,
  `formation_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `modules`
--

INSERT INTO `modules` (`id`, `nom`, `credits`, `formation_id`) VALUES
(1, 'Algorithmique Avancée', 6, 1),
(2, 'Bases de Données SQL', 4, 1),
(6, 'Anatomie Humaine', 8, 7),
(7, 'Droit des Affaires', 4, 5),
(8, 'Analyse Mathématique', 6, 3);

-- --------------------------------------------------------

--
-- Structure de la table `utilisateurs`
--

CREATE TABLE `utilisateurs` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('etudiant','professeur','admin','doyen','chef_dep') NOT NULL,
  `nom` varchar(50) DEFAULT NULL,
  `prenom` varchar(50) DEFAULT NULL,
  `dept_id` int(11) DEFAULT NULL,
  `formation_id` int(11) DEFAULT NULL,
  `promo` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `utilisateurs`
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
-- Index pour les tables déchargées
--

ALTER TABLE `conflicts`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `departements`
--
ALTER TABLE `departements`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nom` (`nom`);

--
-- Index pour la table `examens`
--
ALTER TABLE `examens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `module_id` (`module_id`),
  ADD KEY `prof_id` (`prof_id`),
  ADD KEY `salle_id` (`salle_id`);

--
-- Index pour la table `formations`
--
ALTER TABLE `formations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dept_id` (`dept_id`);

--
-- Index pour la table `inscriptions`
--
ALTER TABLE `inscriptions`
  ADD PRIMARY KEY (`etudiant_id`,`module_id`),
  ADD KEY `module_id` (`module_id`);

--
-- Index pour la table `lieu_examen`
--
ALTER TABLE `lieu_examen`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `modules`
--
ALTER TABLE `modules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `formation_id` (`formation_id`);

--
-- Index pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `dept_id` (`dept_id`),
  ADD KEY `formation_id` (`formation_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

ALTER TABLE `conflicts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `departements`
--
ALTER TABLE `departements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT pour la table `examens`
--
ALTER TABLE `examens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT pour la table `formations`
--
ALTER TABLE `formations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT pour la table `lieu_examen`
--
ALTER TABLE `lieu_examen`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `modules`
--
ALTER TABLE `modules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `examens`
--
ALTER TABLE `examens`
  ADD CONSTRAINT `examens_ibfk_1` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`),
  ADD CONSTRAINT `examens_ibfk_2` FOREIGN KEY (`prof_id`) REFERENCES `utilisateurs` (`id`),
  ADD CONSTRAINT `examens_ibfk_3` FOREIGN KEY (`salle_id`) REFERENCES `lieu_examen` (`id`);

--
-- Contraintes pour la table `formations`
--
ALTER TABLE `formations`
  ADD CONSTRAINT `formations_ibfk_1` FOREIGN KEY (`dept_id`) REFERENCES `departements` (`id`);

--
-- Contraintes pour la table `inscriptions`
--
ALTER TABLE `inscriptions`
  ADD CONSTRAINT `inscriptions_ibfk_1` FOREIGN KEY (`etudiant_id`) REFERENCES `utilisateurs` (`id`),
  ADD CONSTRAINT `inscriptions_ibfk_2` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`);

--
-- Contraintes pour la table `modules`
--
ALTER TABLE `modules`
  ADD CONSTRAINT `modules_ibfk_1` FOREIGN KEY (`formation_id`) REFERENCES `formations` (`id`);

--
-- Contraintes pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  ADD CONSTRAINT `utilisateurs_ibfk_1` FOREIGN KEY (`dept_id`) REFERENCES `departements` (`id`),
  ADD CONSTRAINT `utilisateurs_ibfk_2` FOREIGN KEY (`formation_id`) REFERENCES `formations` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
