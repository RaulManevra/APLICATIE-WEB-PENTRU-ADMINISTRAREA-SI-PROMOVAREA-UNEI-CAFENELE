-- MySQL dump 10.13  Distrib 8.0.39, for Win64 (x86_64)
--
-- Host: localhost    Database: mazi_coffee
-- ------------------------------------------------------
-- Server version	8.0.39

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
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `price` decimal(10,2) NOT NULL,
  `image_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category` enum('coffee','other') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'coffee',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (1,'Caramel Macchiato','Delicious Caramel Macchiato',13.00,'assets/menu/images/Caramel Macchiato.jpeg','coffee','2025-12-07 19:52:52'),(2,'Caramel Mocha','Rich Caramel Mocha',15.00,'assets/menu/images/Caramel Mocha.jpg','coffee','2025-12-07 19:52:52'),(3,'Espresso','Strong and pure Espresso',10.00,'assets/menu/images/Espresso.webp','coffee','2025-12-07 19:52:52'),(4,'Iced Americano','Refreshing Iced Americano',12.00,'assets/menu/images/Iced Americano.jpg','coffee','2025-12-07 19:52:52'),(5,'Latte','Smooth and creamy Latte',14.00,'assets/menu/images/Latte.webp','coffee','2025-12-07 19:52:52'),(6,'Dorian Cafelutza','HATZ MAESTRE',6700.00,'assets/menu/images/prod_694d66784164b.jpeg','coffee','2025-12-25 16:29:44'),(7,'Capuccino','Un echilibru perfect între espresso intens, lapte fierbinte catifelat și spumă fină de lapte. O cafea cremoasă, aromată, ideală pentru orice moment al zilei.',5.00,'assets/menu/images/prod_694ffeafc7eda.jpg','coffee','2025-12-27 15:43:43'),(9,'Cortado','Espresso intens, echilibrat cu o cantitate egală de lapte cald, pentru o băutură fină, cremoasă și fără să fie prea dulce.',15.00,'assets/menu/images/prod_69500f45814e3.jpeg','coffee','2025-12-27 15:47:30'),(10,'Mocha','Combinația perfectă de espresso intens, ciocolată fină și lapte catifelat, decorată cu spumă delicată pentru o experiență dulce și aromată.',15.00,'assets/menu/images/prod_69500f8bf17c6.jpeg','coffee','2025-12-27 15:48:27'),(11,'Iced latte','Espresso rece, lapte proaspăt și cuburi de gheață, pentru o băutură răcoritoare, fină și revigorantă.',7.00,'assets/menu/images/prod_6950001b8e7e2.jfif','coffee','2025-12-27 15:49:47'),(12,'Ice-caramell-latte','Espresso rece, lapte proaspăt și gheață, cu un strop de caramel dulce, pentru o băutură cremoasă și răcoritoare.',7.00,'assets/menu/images/prod_69500064bfede.jpg','coffee','2025-12-27 15:51:00'),(13,'Iced-mocha','Espresso rece, ciocolată fină, lapte proaspăt și gheață, pentru o băutură cremoasă și răcoritoare, cu gust bogat de ciocolată.',7.00,'assets/menu/images/prod_695000a881661.jpg','coffee','2025-12-27 15:52:08'),(14,'Ice-spanish-latte','Espresso de specialitate, lapte fin și gheață, îndulcit subtil cu zahăr caramelizat, pentru o experiență rafinată și revigorantă.',7.00,'assets/menu/images/prod_6950010c326ac.jpg','coffee','2025-12-27 15:53:48'),(15,'Cicolată caldă','O îmbrățișare într-o ceașcă! Ciocolată dulce, lapte cremos și spumă pufoasă, perfectă pentru momentele de relaxare.',7.00,'assets/menu/images/prod_6950014e93c55.jpg','coffee','2025-12-27 15:54:54');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `slider_images`
--

DROP TABLE IF EXISTS `slider_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `slider_images` (
  `id` int NOT NULL AUTO_INCREMENT,
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subtitle` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `button_text` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'View Menu',
  `button_link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '?page=menu',
  `is_button_visible` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `slider_images`
--

LOCK TABLES `slider_images` WRITE;
/*!40000 ALTER TABLE `slider_images` DISABLE KEYS */;
INSERT INTO `slider_images` VALUES (1,'assets/img/slider_1.png','Dimineți Perfecte','Savurează un Cappuccino autentic',NULL,'2025-12-29 10:20:49','Vezi Meniul','?page=menu',1),(2,'assets/img/slider_2.png','Gustări Proaspete','Croissant cu unt, scos din cuptor',NULL,'2025-12-29 10:20:49','Comandă Acum','?page=menu',1),(3,'assets/img/slider_3.png','Aromă Intensă','Cele mai bune boabe de cafea',NULL,'2025-12-29 10:20:49','Rezervă Masă','?page=tables',1),(4,'assets/img/Coffee_1.png','Oferta Săptămânii','Bine ai venit!','Săptămâna asta te răsfățăm! La orice Caramel Macchiato cumpărat, primești încă unul din partea casei. Ofertă valabilă doar săptămâna aceasta – vino să te bucuri de gustul perfect al caramelului împreună cu un prieten!','2025-12-29 11:03:46','Vezi Meniul','?page=menu',1);
/*!40000 ALTER TABLE `slider_images` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tables`
--

DROP TABLE IF EXISTS `tables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tables` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `Status` enum('Inactiva','Libera','Ocupata','Rezervata') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `x_pos` float DEFAULT '10',
  `y_pos` float DEFAULT '10',
  `shape` enum('circle','square','rectangle') COLLATE utf8mb4_general_ci DEFAULT 'circle',
  `width` int DEFAULT '60',
  `height` int DEFAULT '60',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tables`
--

LOCK TABLES `tables` WRITE;
/*!40000 ALTER TABLE `tables` DISABLE KEYS */;
INSERT INTO `tables` VALUES (1,'Libera',27.925,30.8062,'circle',70,70),(2,'Ocupata',43.3313,64.2844,'circle',45,45),(3,'Libera',56.1797,64.3562,'circle',45,45),(4,'Libera',68.7891,64.3406,'circle',45,45),(5,'Libera',22.7943,53.0523,'square',54,42),(6,'Libera',45.4864,79.5193,'rectangle',42,85),(7,'Libera',66.1875,79.4989,'rectangle',47,83);
/*!40000 ALTER TABLE `tables` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` enum('user','admin') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'user',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `PuncteFidelitate` int DEFAULT NULL,
  `PPicture` varchar(2048) COLLATE utf8mb4_unicode_ci DEFAULT 'assets/public/default.png',
  `oauth_provider` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `oauth_uid` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'zarnescuraul@gmail.com','Raul','$2y$12$j09C55tliX7bS9ZCNMLFJuerVS.0DZ0pG/pgqVzLaFcFtYosnA8Iq','admin','2025-11-24 10:19:31',34,'assets/uploads/profile_pictures/PP_userid_1.jpg','google','105941628733277196959'),(2,'davidrares56@yahoo.com','raress_tc','$2y$12$ud4pLmRn0Vx6Vm0NpNqXY.nnTbNMkNqKmvsru14zHnrYoXpm/.JHi','admin','2025-12-08 17:24:04',10000,'assets/public/default.png',NULL,NULL),(3,'erwin.georgescu@student.unitbv.ro','erwin','$2y$12$K6Bet6UcpmUmXdC9I4FlYOhtazj9eQf.UeIxyKgoiQ7FuQxmSTmDW','admin','2025-12-27 15:34:58',0,'assets/public/default.png',NULL,NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-01-02 11:25:38
