-- MySQL dump 10.13  Distrib 8.0.44, for macos10.15 (x86_64)
--
-- Host: localhost    Database: mazi_coffee
-- ------------------------------------------------------
-- Server version	8.0.44

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
-- Table structure for table `admin_notes`
--

DROP TABLE IF EXISTS `admin_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admin_notes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_notes`
--

LOCK TABLES `admin_notes` WRITE;
/*!40000 ALTER TABLE `admin_notes` DISABLE KEYS */;
INSERT INTO `admin_notes` VALUES (1,'Salut\r\n','2026-01-08 14:44:02');
/*!40000 ALTER TABLE `admin_notes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `global_settings`
--

DROP TABLE IF EXISTS `global_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `global_settings` (
  `key_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`key_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `global_settings`
--

LOCK TABLES `global_settings` WRITE;
/*!40000 ALTER TABLE `global_settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `global_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL,
  `price_at_time` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=65 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_items`
--

LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
INSERT INTO `order_items` VALUES (1,1,1,1,13.00),(2,1,2,1,15.00),(3,2,15,4,7.00),(4,2,7,3,5.00),(5,3,2,1,15.00),(6,3,10,3,15.00),(7,3,7,2,5.00),(8,4,11,2,7.00),(9,4,11,1,7.00),(10,5,5,1,14.00),(11,6,2,2,15.00),(12,7,1,2,13.00),(13,7,6,2,6700.00),(14,7,13,2,7.00),(15,8,9,5,15.00),(16,9,1,1,13.00),(17,10,4,5,12.00),(18,10,1,2,13.00),(19,11,11,3,7.00),(20,11,11,1,7.00),(21,11,7,4,5.00),(22,12,11,1,7.00),(23,12,15,4,7.00),(24,13,12,4,7.00),(25,13,6,5,6700.00),(26,14,15,1,7.00),(27,15,14,1,7.00),(28,16,7,2,5.00),(29,16,9,4,15.00),(30,17,6,2,6700.00),(31,18,12,5,7.00),(32,18,6,2,6700.00),(33,18,10,2,15.00),(34,19,14,1,7.00),(35,19,11,5,7.00),(36,19,9,5,15.00),(37,20,7,3,5.00),(38,20,7,5,5.00),(39,21,10,4,15.00),(40,21,14,5,7.00),(41,21,13,3,7.00),(42,22,7,4,5.00),(43,22,9,5,15.00),(44,22,4,1,12.00),(45,23,2,4,15.00),(46,23,3,3,10.00),(47,24,13,2,7.00),(48,25,14,2,7.00),(49,26,11,1,7.00),(50,26,14,5,7.00),(51,27,5,5,14.00),(52,28,9,2,15.00),(53,28,12,4,7.00),(54,28,5,2,14.00),(55,29,7,5,5.00),(56,30,9,5,15.00),(57,30,2,2,15.00),(58,30,1,3,13.00),(59,31,12,1,7.00),(60,32,7,1,5.00),(61,33,7,1,5.00),(62,34,7,1,5.00),(63,35,3,1,10.00),(64,36,1,1,13.00);
/*!40000 ALTER TABLE `order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `pickup_time` datetime NOT NULL,
  `status` enum('pending','preparing','ready','completed','cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `total_price` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `table_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `table_id` (`table_id`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES (1,1,'2026-01-09 10:32:00','completed',28.00,'2026-01-08 08:35:15',1),(2,2,'2026-01-08 13:30:11','completed',43.00,'2026-01-08 11:30:11',NULL),(3,2,'2026-01-08 13:30:11','completed',70.00,'2026-01-08 11:30:11',NULL),(4,2,'2026-01-08 13:30:11','completed',21.00,'2026-01-08 11:30:11',NULL),(5,2,'2026-01-08 13:30:11','completed',14.00,'2026-01-08 11:30:11',NULL),(6,2,'2026-01-08 13:30:11','completed',30.00,'2026-01-08 11:30:11',NULL),(7,2,'2026-01-07 13:30:11','completed',13440.00,'2026-01-07 11:30:11',NULL),(8,2,'2026-01-07 13:30:11','completed',75.00,'2026-01-07 11:30:11',NULL),(9,2,'2026-01-07 13:30:11','completed',13.00,'2026-01-07 11:30:11',NULL),(10,2,'2026-01-06 13:30:11','completed',86.00,'2026-01-06 11:30:11',NULL),(11,2,'2026-01-06 13:30:11','completed',48.00,'2026-01-06 11:30:11',NULL),(12,2,'2026-01-06 13:30:11','completed',35.00,'2026-01-06 11:30:11',NULL),(13,2,'2026-01-06 13:30:11','completed',33528.00,'2026-01-06 11:30:11',NULL),(14,2,'2026-01-06 13:30:11','completed',7.00,'2026-01-06 11:30:11',NULL),(15,2,'2026-01-05 13:30:11','completed',7.00,'2026-01-05 11:30:11',NULL),(16,2,'2026-01-05 13:30:11','completed',70.00,'2026-01-05 11:30:11',NULL),(17,2,'2026-01-05 13:30:11','completed',13400.00,'2026-01-05 11:30:11',NULL),(18,2,'2026-01-04 13:30:11','completed',13465.00,'2026-01-04 11:30:11',NULL),(19,2,'2026-01-04 13:30:11','completed',117.00,'2026-01-04 11:30:11',NULL),(20,2,'2026-01-04 13:30:11','completed',40.00,'2026-01-04 11:30:11',NULL),(21,2,'2026-01-04 13:30:11','completed',116.00,'2026-01-04 11:30:11',NULL),(22,2,'2026-01-04 13:30:11','completed',107.00,'2026-01-04 11:30:11',NULL),(23,2,'2026-01-03 13:30:11','completed',90.00,'2026-01-03 11:30:11',NULL),(24,2,'2026-01-03 13:30:11','completed',14.00,'2026-01-03 11:30:11',NULL),(25,2,'2026-01-03 13:30:11','completed',14.00,'2026-01-03 11:30:11',NULL),(26,2,'2026-01-03 13:30:11','completed',42.00,'2026-01-03 11:30:11',NULL),(27,2,'2026-01-03 13:30:11','completed',70.00,'2026-01-03 11:30:11',NULL),(28,2,'2026-01-02 13:30:11','completed',86.00,'2026-01-02 11:30:11',NULL),(29,2,'2026-01-02 13:30:11','completed',25.00,'2026-01-02 11:30:11',NULL),(30,2,'2026-01-02 13:30:11','completed',144.00,'2026-01-02 11:30:11',NULL),(31,1,'2026-01-09 10:56:00','completed',7.00,'2026-01-08 16:57:21',NULL),(32,1,'2026-01-09 10:29:00','completed',5.00,'2026-01-08 17:13:36',NULL),(33,1,'2026-01-08 19:16:37','completed',5.00,'2026-01-08 17:16:37',1),(34,4,'2026-01-08 19:27:04','completed',5.00,'2026-01-08 17:27:05',1),(35,4,'2026-01-08 19:28:11','completed',10.00,'2026-01-08 17:28:11',1),(36,4,'2026-01-08 19:33:19','completed',13.00,'2026-01-08 17:33:19',1);
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Table structure for table `reservations`
--

DROP TABLE IF EXISTS `reservations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reservations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `reservation_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `table_id` int NOT NULL,
  `reservation_time` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('active','deleted') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'active',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `table_id` (`table_id`),
  CONSTRAINT `reservations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reservations_ibfk_2` FOREIGN KEY (`table_id`) REFERENCES `tables` (`ID`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reservations`
--

LOCK TABLES `reservations` WRITE;
/*!40000 ALTER TABLE `reservations` DISABLE KEYS */;
INSERT INTO `reservations` VALUES (1,1,NULL,1,'2026-01-03 14:34:00','2026-01-03 12:33:36','active'),(2,1,'Raul Zarnescu',1,'2026-01-14 15:22:00','2026-01-04 09:22:37','active');
/*!40000 ALTER TABLE `reservations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `schedule`
--

DROP TABLE IF EXISTS `schedule`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `schedule` (
  `day_of_week` int NOT NULL,
  `day_name` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `open_time` time DEFAULT NULL,
  `close_time` time DEFAULT NULL,
  `is_closed` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`day_of_week`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `schedule`
--

LOCK TABLES `schedule` WRITE;
/*!40000 ALTER TABLE `schedule` DISABLE KEYS */;
INSERT INTO `schedule` VALUES (0,'Sunday','08:00:00','17:00:00',1),(1,'Monday','08:00:00','17:00:00',0),(2,'Tuesday','08:00:00','17:00:00',0),(3,'Wednesday','08:00:00','17:00:00',0),(4,'Thursday','08:00:00','17:00:00',0),(5,'Friday','08:00:00','17:00:00',0),(6,'Saturday','08:00:00','17:00:00',0);
/*!40000 ALTER TABLE `schedule` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `slider_images`
--

DROP TABLE IF EXISTS `slider_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `slider_images` (
  `id` int NOT NULL AUTO_INCREMENT,
  `image_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subtitle` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `button_text` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'View Menu',
  `button_link` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '?page=menu',
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
  `shape` enum('circle','square','rectangle') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'circle',
  `width` float DEFAULT '10',
  `height` float DEFAULT '10',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tables`
--

LOCK TABLES `tables` WRITE;
/*!40000 ALTER TABLE `tables` DISABLE KEYS */;
INSERT INTO `tables` VALUES (1,'Libera',27.7638,10.0671,'circle',9.375,12.3296),(2,'Ocupata',43.7186,56.2081,'circle',5.62382,7.4979),(3,'Libera',56.6583,56.2081,'circle',5.62382,7.4979),(4,'Ocupata',69.598,56.2081,'circle',5.62382,7.4979),(5,'Libera',22.2365,40.8233,'square',6.75,7),(6,'Libera',45.9799,76.8456,'rectangle',5.25,14.1667),(7,'Libera',67.2111,77.1812,'rectangle',5.37453,13.6666);
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
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` enum('user','admin') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'user',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `PuncteFidelitate` int DEFAULT NULL,
  `PPicture` varchar(2048) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'assets/public/default.png',
  `oauth_provider` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `oauth_uid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_blacklisted` tinyint(1) DEFAULT '0',
  `blacklist_reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'zarnescuraul@gmail.com','Raul','$2y$12$j09C55tliX7bS9ZCNMLFJuerVS.0DZ0pG/pgqVzLaFcFtYosnA8Iq','admin','2025-11-24 10:19:31',34,'assets/uploads/profile_pictures/PP_userid_1.jpg','google','105941628733277196959',0,NULL),(2,'davidrares56@yahoo.com','raress_tc','$2y$12$ud4pLmRn0Vx6Vm0NpNqXY.nnTbNMkNqKmvsru14zHnrYoXpm/.JHi','admin','2025-12-08 17:24:04',10000,'assets/public/default.png',NULL,NULL,0,NULL),(3,'erwin.georgescu@student.unitbv.ro','erwin','$2y$12$K6Bet6UcpmUmXdC9I4FlYOhtazj9eQf.UeIxyKgoiQ7FuQxmSTmDW','admin','2025-12-27 15:34:58',0,'assets/public/default.png',NULL,NULL,0,NULL),(4,'guest@mazicoffee.com','Guest','$2y$10$ya7vmoIY9nQQDqeBygttKOiTfRSHLBNvxc02utkPBrEUmLzQZIEtK','user','2026-01-08 17:27:05',NULL,'assets/public/default.png',NULL,NULL,0,NULL);
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

-- Dump completed on 2026-01-09  4:30:29
