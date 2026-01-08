-- MySQL dump 10.13  Distrib 8.0.44, for Linux (x86_64)
--
-- Host: localhost    Database: gestion_examens_db
-- ------------------------------------------------------
-- Server version	8.0.44-0ubuntu0.24.04.2

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `conflicts`
--

DROP TABLE IF EXISTS `conflicts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `conflicts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `type` varchar(50) NOT NULL,
  `module` varchar(100) DEFAULT NULL,
  `formation` varchar(100) DEFAULT NULL,
  `reason` text,
  `severity` varchar(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=57 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `conflicts`
--

LOCK TABLES `conflicts` WRITE;
/*!40000 ALTER TABLE `conflicts` DISABLE KEYS */;
INSERT INTO `conflicts` VALUES (56,'CONTRAINTE_ÉGALITÉ','Analyse Mathématique','Licence Mathématiques','Non planifié: quota global de surveillances atteint (4)','warning','2026-01-07 21:17:36');
/*!40000 ALTER TABLE `conflicts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `departements`
--

DROP TABLE IF EXISTS `departements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `departements` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nom` (`nom`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `departements`
--

LOCK TABLES `departements` WRITE;
/*!40000 ALTER TABLE `departements` DISABLE KEYS */;
INSERT INTO `departements` VALUES (3,'Biologie'),(4,'Droit et Sciences Politiques'),(5,'Économie et Gestion'),(1,'Informatique'),(7,'langues'),(2,'Mathématiques'),(6,'Médecine');
/*!40000 ALTER TABLE `departements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `examens`
--

DROP TABLE IF EXISTS `examens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `examens` (
  `id` int NOT NULL AUTO_INCREMENT,
  `module_id` int DEFAULT NULL,
  `prof_id` int DEFAULT NULL,
  `salle_id` int DEFAULT NULL,
  `date_examen` date NOT NULL,
  `heure_debut` time NOT NULL,
  `duree_minutes` int DEFAULT '90',
  PRIMARY KEY (`id`),
  KEY `module_id` (`module_id`),
  KEY `prof_id` (`prof_id`),
  KEY `salle_id` (`salle_id`),
  CONSTRAINT `examens_ibfk_1` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`),
  CONSTRAINT `examens_ibfk_2` FOREIGN KEY (`prof_id`) REFERENCES `utilisateurs` (`id`),
  CONSTRAINT `examens_ibfk_3` FOREIGN KEY (`salle_id`) REFERENCES `lieu_examen` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=184 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `examens`
--

LOCK TABLES `examens` WRITE;
/*!40000 ALTER TABLE `examens` DISABLE KEYS */;
INSERT INTO `examens` VALUES (180,1,1,2,'2026-01-08','08:00:00',90),(181,2,11,2,'2026-01-12','08:00:00',90),(182,7,7,3,'2026-01-08','08:00:00',90),(183,6,8,2,'2026-01-08','10:00:00',90);
/*!40000 ALTER TABLE `examens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `formations`
--

DROP TABLE IF EXISTS `formations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `formations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `dept_id` int DEFAULT NULL,
  `nb_modules` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `dept_id` (`dept_id`),
  CONSTRAINT `formations_ibfk_1` FOREIGN KEY (`dept_id`) REFERENCES `departements` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `formations`
--

LOCK TABLES `formations` WRITE;
/*!40000 ALTER TABLE `formations` DISABLE KEYS */;
INSERT INTO `formations` VALUES (1,'Licence Informatique G1',1,6),(2,'Master Cybersécurité',1,8),(3,'Licence Mathématiques',2,NULL),(5,'Licence Droit Public',4,7),(6,'Master Finance de Marché',5,8),(7,'Doctorat Médecine Générale',6,12);
/*!40000 ALTER TABLE `formations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inscriptions`
--

DROP TABLE IF EXISTS `inscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `inscriptions` (
  `etudiant_id` int NOT NULL,
  `module_id` int NOT NULL,
  `note` decimal(4,2) DEFAULT NULL,
  PRIMARY KEY (`etudiant_id`,`module_id`),
  KEY `module_id` (`module_id`),
  CONSTRAINT `inscriptions_ibfk_1` FOREIGN KEY (`etudiant_id`) REFERENCES `utilisateurs` (`id`),
  CONSTRAINT `inscriptions_ibfk_2` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inscriptions`
--

LOCK TABLES `inscriptions` WRITE;
/*!40000 ALTER TABLE `inscriptions` DISABLE KEYS */;
/*!40000 ALTER TABLE `inscriptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lieu_examen`
--

DROP TABLE IF EXISTS `lieu_examen`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lieu_examen` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `capacite` int NOT NULL,
  `type` enum('Amphi','Salle','Labo') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `batiment` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lieu_examen`
--

LOCK TABLES `lieu_examen` WRITE;
/*!40000 ALTER TABLE `lieu_examen` DISABLE KEYS */;
INSERT INTO `lieu_examen` VALUES (2,'Salle 101',20,'Salle','Bâtiment B'),(3,'Labo Info 1',20,'Labo','Technologie');
/*!40000 ALTER TABLE `lieu_examen` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `modules`
--

DROP TABLE IF EXISTS `modules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `modules` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `credits` int DEFAULT NULL,
  `formation_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `formation_id` (`formation_id`),
  CONSTRAINT `modules_ibfk_1` FOREIGN KEY (`formation_id`) REFERENCES `formations` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `modules`
--

LOCK TABLES `modules` WRITE;
/*!40000 ALTER TABLE `modules` DISABLE KEYS */;
INSERT INTO `modules` VALUES (1,'Algorithmique Avancée',6,1),(2,'Bases de Données SQL',4,1),(6,'Anatomie Humaine',8,7),(7,'Droit des Affaires',4,5),(8,'Analyse Mathématique',6,3);
/*!40000 ALTER TABLE `modules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `utilisateurs`
--

DROP TABLE IF EXISTS `utilisateurs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `utilisateurs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `role` enum('etudiant','professeur','admin','doyen','chef_dep') COLLATE utf8mb4_general_ci NOT NULL,
  `nom` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `prenom` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `dept_id` int DEFAULT NULL,
  `formation_id` int DEFAULT NULL,
  `promo` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `dept_id` (`dept_id`),
  KEY `formation_id` (`formation_id`),
  CONSTRAINT `utilisateurs_ibfk_1` FOREIGN KEY (`dept_id`) REFERENCES `departements` (`id`),
  CONSTRAINT `utilisateurs_ibfk_2` FOREIGN KEY (`formation_id`) REFERENCES `formations` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `utilisateurs`
--

LOCK TABLES `utilisateurs` WRITE;
/*!40000 ALTER TABLE `utilisateurs` DISABLE KEYS */;
INSERT INTO `utilisateurs` VALUES (1,'P001','123','professeur','Durand','Jean',1,NULL,NULL),(2,'E202401','123','etudiant','Dupont','Marie',NULL,1,NULL),(5,'admin','123','admin','Système','Admin',NULL,NULL,NULL),(6,'doyen','123','doyen','Benali','Professeur',NULL,NULL,NULL),(7,'P003','1234','professeur','Rousseau','Jean-Jacques',4,NULL,NULL),(8,'P004','keynes99','professeur','Keynes','John',5,NULL,NULL),(9,'E202403','Etudiant1_Secret','etudiant','Zidane','Zinedine',NULL,5,NULL),(10,'E202404','lagPass_2024','etudiant','Lagarde','Christine',NULL,6,NULL),(11,'aa','123','professeur','aa','bb',1,1,NULL);
/*!40000 ALTER TABLE `utilisateurs` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-01-08  0:19:21
