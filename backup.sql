-- MySQL dump 10.13  Distrib 8.0.30, for Win64 (x86_64)
--
-- Host: localhost    Database: my_events
-- ------------------------------------------------------
-- Server version	8.0.30

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
-- Table structure for table `category`
--

DROP TABLE IF EXISTS `category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `category` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `category`
--

LOCK TABLES `category` WRITE;
/*!40000 ALTER TABLE `category` DISABLE KEYS */;
INSERT INTO `category` VALUES (1,'Concert','Événements musicaux avec des artistes en live'),(2,'Festival','Rassemblements culturels et artistiques sur plusieurs jours'),(3,'Conférence','Rencontres professionnelles ou éducatives sur un thème donné'),(4,'Sport','Compétitions et activités sportives organisées'),(5,'Soirée','Fêtes et rassemblements festifs nocturnes'),(6,'Exposition','Présentations artistiques ou culturelles dans un lieu dédié'),(7,'Atelier','Sessions de formation ou de création interactives'),(8,'Meetup','Rencontres informelles entre passionnés d’un sujet'),(9,'Projection','Diffusions de films, documentaires ou courts-métrages'),(10,'Gaming','Événements autour du jeu vidéo, LAN parties et tournois');
/*!40000 ALTER TABLE `category` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `doctrine_migration_versions`
--

DROP TABLE IF EXISTS `doctrine_migration_versions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `doctrine_migration_versions` (
  `version` varchar(191) COLLATE utf8mb3_unicode_ci NOT NULL,
  `executed_at` datetime DEFAULT NULL,
  `execution_time` int DEFAULT NULL,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `doctrine_migration_versions`
--

LOCK TABLES `doctrine_migration_versions` WRITE;
/*!40000 ALTER TABLE `doctrine_migration_versions` DISABLE KEYS */;
INSERT INTO `doctrine_migration_versions` VALUES ('DoctrineMigrations\\Version20250403173532','2025-04-03 17:35:38',405),('DoctrineMigrations\\Version20250403173533','2025-05-27 15:40:49',47),('DoctrineMigrations\\Version20250407135629','2025-04-07 13:56:39',49),('DoctrineMigrations\\Version20250407135630','2025-05-04 16:27:11',74),('DoctrineMigrations\\Version20250527142922','2025-05-27 14:29:34',57),('DoctrineMigrations\\Version20250527162231','2025-05-27 16:22:55',9);
/*!40000 ALTER TABLE `doctrine_migration_versions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `event`
--

DROP TABLE IF EXISTS `event`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `event` (
  `id` int NOT NULL AUTO_INCREMENT,
  `organizer_id` int NOT NULL,
  `category_id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `photo` longtext COLLATE utf8mb4_unicode_ci,
  `max_participants` int NOT NULL,
  `is_paid` tinyint(1) NOT NULL,
  `created_at` datetime NOT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `current_state` varchar(55) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_3BAE0AA7876C4DDA` (`organizer_id`),
  KEY `IDX_3BAE0AA712469DE2` (`category_id`),
  CONSTRAINT `FK_3BAE0AA712469DE2` FOREIGN KEY (`category_id`) REFERENCES `category` (`id`),
  CONSTRAINT `FK_3BAE0AA7876C4DDA` FOREIGN KEY (`organizer_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `event`
--

LOCK TABLES `event` WRITE;
/*!40000 ALTER TABLE `event` DISABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `messenger_messages`
--

DROP TABLE IF EXISTS `messenger_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `messenger_messages` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `body` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `headers` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue_name` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `available_at` datetime NOT NULL,
  `delivered_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_75EA56E0FB7336F0` (`queue_name`),
  KEY `IDX_75EA56E0E3BD61CE` (`available_at`),
  KEY `IDX_75EA56E016BA31DB` (`delivered_at`)
) ENGINE=InnoDB AUTO_INCREMENT=102 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `messenger_messages`
--

LOCK TABLES `messenger_messages` WRITE;
/*!40000 ALTER TABLE `messenger_messages` DISABLE KEYS */;
/*!40000 ALTER TABLE `messenger_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `oauth_connection`
--

DROP TABLE IF EXISTS `oauth_connection`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `oauth_connection` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `provider` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `provider_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_BB31DCDDA76ED395` (`user_id`),
  CONSTRAINT `FK_BB31DCDDA76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `oauth_connection`
--

LOCK TABLES `oauth_connection` WRITE;
/*!40000 ALTER TABLE `oauth_connection` DISABLE KEYS */;
INSERT INTO `oauth_connection` VALUES (1,1,'google','102160246750737953745','robin.sa28@gmail.com','2025-04-03 17:36:04'),(2,2,'google','113581035052792767226','rosanvila28@gmail.com','2025-04-03 17:37:34'),(3,1,'facebook','10236232844631489','robin.sa28@gmail.com','2025-05-04 16:15:22');
/*!40000 ALTER TABLE `oauth_connection` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `participation`
--

DROP TABLE IF EXISTS `participation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `participation` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `event_id` int NOT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_AB55E24FA76ED395` (`user_id`),
  KEY `IDX_AB55E24F71F7E88B` (`event_id`),
  CONSTRAINT `FK_AB55E24F71F7E88B` FOREIGN KEY (`event_id`) REFERENCES `event` (`id`),
  CONSTRAINT `FK_AB55E24FA76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `participation`
--

LOCK TABLES `participation` WRITE;
/*!40000 ALTER TABLE `participation` DISABLE KEYS */;
INSERT INTO `participation` VALUES (20,2,13,'confirmed','2025-05-26 15:03:53'),(22,1,13,'confirmed','2025-05-26 15:21:59'),(23,1,39,'confirmed','2025-06-02 15:04:55'),(24,1,53,'confirmed','2025-06-02 15:06:20'),(25,1,38,'confirmed','2025-06-02 15:09:22'),(26,1,45,'confirmed','2025-06-02 15:13:42');
/*!40000 ALTER TABLE `participation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payment`
--

DROP TABLE IF EXISTS `payment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payment` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `event_id` int NOT NULL,
  `stripe_session_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `currency` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `stripe_payment_intent_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_6D28840DA76ED395` (`user_id`),
  KEY `IDX_6D28840D71F7E88B` (`event_id`),
  CONSTRAINT `FK_6D28840D71F7E88B` FOREIGN KEY (`event_id`) REFERENCES `event` (`id`),
  CONSTRAINT `FK_6D28840DA76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payment`
--

LOCK TABLES `payment` WRITE;
/*!40000 ALTER TABLE `payment` DISABLE KEYS */;
INSERT INTO `payment` VALUES (18,2,13,'cs_test_a1kRCVTgChEgR6PG8Sgn8kuALSDStBLkrPqUOCN4KWC8jvJ7qNy40EU1hm','1.00','eur','completed','2025-05-26 15:03:53','pi_3RT2Z6EAw9iEpEqK1rsnArcG'),(20,1,13,'cs_test_a1LVYJEVpcFRHFtX9WNEnDnOSYjXJc31YZQ63qA243wwPknPY0PukMZbRx','1.00','eur','completed','2025-05-26 15:21:59','pi_3RT2qcEAw9iEpEqK0K8WECNF'),(21,1,53,'cs_test_a1aA73sbyT8IEUhhCtMQuGwg03Lo8y9uAB7rpFvmLcdSYf6gDWL31KWSgE','6.00','eur','completed','2025-06-02 15:06:20','pi_3RVZwKEAw9iEpEqK0bJLolE2'),(22,1,38,'cs_test_a1I6JHJw19PEWc3tHL4PxkxuFtYctNkG4Smz6plOEEUsSlqjI9nVggunNe','64.47','eur','completed','2025-06-02 15:09:22','pi_3RVZzFEAw9iEpEqK1BY1vZxt'),(23,1,45,'cs_test_a1zE4VEc3Wq33mDnAzyGQCSy6pvClmVf7ejsj3ne7FDcfPqA6IfoaIkS8f','35.38','eur','completed','2025-06-02 15:13:42','pi_3RVa3SEAw9iEpEqK1znYIB2f');
/*!40000 ALTER TABLE `payment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reset_password_request`
--

DROP TABLE IF EXISTS `reset_password_request`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reset_password_request` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `selector` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `hashed_token` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `requested_at` datetime NOT NULL,
  `expires_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_7CE748AA76ED395` (`user_id`),
  CONSTRAINT `FK_7CE748AA76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reset_password_request`
--

LOCK TABLES `reset_password_request` WRITE;
/*!40000 ALTER TABLE `reset_password_request` DISABLE KEYS */;
INSERT INTO `reset_password_request` VALUES (6,24,'M4nlzIzcxFwIgASqYL65JpNcOVarzvUFuvLskrVe','oxRkhxo22kHKWBDLXnmOYB91gkFjos5C0wwxqbRRzZI=','2025-05-27 16:27:23','2025-05-28 16:27:23'),(7,24,'2YX3w4Nsk8RUZQL6bE24','5RE/JZc0C3morxRIpotvqg/5mcf5GLZFyQHaaHnILqc=','2025-05-27 16:29:12','2025-05-28 16:29:12'),(8,24,'TuuJ8ocmTVRCyJhWTQ6H','z2K+HYFw3AQ0gSsa2xDOzYZRdqmHlex/+ba76lsYvVY=','2025-05-27 16:32:20','2025-05-28 16:32:20'),(9,24,'SIvhB4RiQ4CleaWcjVIc','B4w5D6cG82cJ6DTCAfqLnYOjhxtHurLXhlL1oE9+e7w=','2025-05-27 16:41:33','2025-05-28 16:41:33'),(10,24,'Yq613qDcLY2escxbcSas','rL4Ir47GuJ9uWJfxtWV4bo418iP96MWRmeBJJwxro+0=','2025-05-27 16:44:20','2025-05-28 16:44:20'),(11,24,'uGcZy7jjKYjJcHI82rc3','TV+7XPxfSgjWaBOP69HV9nvpupG1yoRAra2ca74/5Ek=','2025-05-27 16:52:35','2025-05-28 16:52:35'),(12,24,'ma6QJajQgH5GOxKoVw9O','y7nlMy/jpG6iushXa5Qu1PiiUFiK6XoDZtgpiZ4Cm6A=','2025-05-27 17:01:11','2025-05-28 17:01:11'),(13,24,'PB0TauZb7uTtIIyo1xkM','fr3z301cstrBJpytOgYLDI5gf2OfNtWGMaXi7kB0R5o=','2025-05-27 17:02:59','2025-05-28 17:02:59');
/*!40000 ALTER TABLE `reset_password_request` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `roles` json NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `firstname` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lastname` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_oauth` tinyint(1) NOT NULL,
  `auth_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_auth_code_expires_at` datetime DEFAULT NULL,
  `is_verified` tinyint(1) NOT NULL,
  `stripe_account_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_8D93D649E7927C74` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES (1,'robin.sa28@gmail.com','[\"ROLE_USER\"]','$2y$13$g9FG5CY8qNSwJSCgsYG85O4QJMJC3XOcYDxZ5tHgDR3Anyplt9IMq','Robin','Sanchez-Avila',1,NULL,NULL,0,NULL),(2,'rosanvila28@gmail.com','[\"ROLE_USER\"]','$2y$13$g1EMaqXtL4P5LVpslLaF1u/CFAyNcI6X2mjr1UCL63ehQ2SYbFTPG','Robin','Sanchez-Avila',1,NULL,NULL,0,NULL),(4,'sanoc69749@beznoi.com','[\"ROLE_USER\"]','$2y$13$9USmHIH1ZZRwTsJFRr1bGOwHCaV/jn8yJGJgwpZMYLf6yOVJyCvrO','John','Doe',0,'128291','2025-05-27 14:46:31',1,NULL),(24,'cebahoj511@nomrista.com','[\"ROLE_USER\"]','$2y$13$Mm1fy83YvorIJBJer8qgr.grs1JKaiNnrUqRjiW7aQFD5exE904fe','John','Doe',0,'789744','2025-06-02 15:04:23',1,NULL);
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-06-03 17:17:24
