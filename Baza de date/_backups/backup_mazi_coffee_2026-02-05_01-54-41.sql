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
INSERT INTO `admin_notes` VALUES (1,'\r\nba vedeti ca arde cafeneaua are cineva un AAAAAAAAAA MA ARDE MOR AICI hjvbasuybwrayiuvbewgiBVUIERSWBOL\r\n','2026-01-10 18:38:51');
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
INSERT INTO `global_settings` VALUES ('loyalty_earn_reward','5','2026-02-04 10:05:41'),('loyalty_earn_threshold','25','2026-02-04 10:05:41'),('loyalty_max_spend_points','100','2026-02-04 10:05:41'),('loyalty_spend_unit_points','10','2026-02-04 10:05:41'),('loyalty_spend_unit_value','1','2026-02-04 10:05:41'),('newsletter_email','zarnescuraul@gmail.com','2026-01-10 10:58:30'),('support_email','zarnescuraul@gmail.com','2026-02-03 15:42:59'),('tva_a','19','2026-02-03 14:50:17'),('tva_b','9','2026-02-03 14:50:17'),('tva_c','5','2026-02-03 14:50:17'),('tva_d','0','2026-02-03 14:50:17');
/*!40000 ALTER TABLE `global_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ingredients`
--

DROP TABLE IF EXISTS `ingredients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ingredients` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` enum('solid','liquid') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'solid',
  `stock_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ingredients`
--

LOCK TABLES `ingredients` WRITE;
/*!40000 ALTER TABLE `ingredients` DISABLE KEYS */;
INSERT INTO `ingredients` VALUES (1,'Lapte','assets/uploads/ingredients/ing_698384378b239.jpg','liquid',8000.00,'2026-02-04 17:39:03'),(2,'Cafea','assets/uploads/ingredients/ing_69838c070ce64.png','solid',6000.00,'2026-02-04 18:12:23');
/*!40000 ALTER TABLE `ingredients` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `newsletters_history`
--

DROP TABLE IF EXISTS `newsletters_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `newsletters_history` (
  `id` int NOT NULL AUTO_INCREMENT,
  `subject` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `sent_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `recipients_count` int DEFAULT '0',
  `failures_count` int DEFAULT '0',
  `sent_by` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `newsletters_history`
--

LOCK TABLES `newsletters_history` WRITE;
/*!40000 ALTER TABLE `newsletters_history` DISABLE KEYS */;
INSERT INTO `newsletters_history` VALUES (1,'Test 3','Acesta este al 3-lea test de tip Newsletter. Va rog sa imi spuneti daca l-ati primit','2026-01-10 13:22:04',0,4,1),(2,'Test 4','Acesta este al 4-lea test de tip Newsletter. Va rog sa imi spuneti daca l-ati primit','2026-01-10 13:27:48',4,0,1),(3,'Test 5','Acesta este al 5-lea test de tip Newsletter. Va rog sa imi spuneti daca l-ati primit','2026-01-10 15:36:51',5,0,1),(4,'Test 6','Acesta este al 6-lea test de tip Newsletter. Va rog sa imi spuneti daca l-ati primit','2026-01-10 15:41:12',5,0,1),(5,'Test 7','Acesta este al 7-lea test de tip Newsletter. Va rog sa imi spuneti daca l-ati primit','2026-01-10 15:42:44',5,0,1);
/*!40000 ALTER TABLE `newsletters_history` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=81 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_items`
--

LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
INSERT INTO `order_items` VALUES (1,1,1,1,13.00),(2,1,2,1,15.00),(3,2,15,4,7.00),(4,2,7,3,5.00),(5,3,2,1,15.00),(6,3,10,3,15.00),(7,3,7,2,5.00),(8,4,11,2,7.00),(9,4,11,1,7.00),(10,5,5,1,14.00),(11,6,2,2,15.00),(12,7,1,2,13.00),(13,7,6,2,6700.00),(14,7,13,2,7.00),(15,8,9,5,15.00),(16,9,1,1,13.00),(17,10,4,5,12.00),(18,10,1,2,13.00),(19,11,11,3,7.00),(20,11,11,1,7.00),(21,11,7,4,5.00),(22,12,11,1,7.00),(23,12,15,4,7.00),(24,13,12,4,7.00),(25,13,6,5,6700.00),(26,14,15,1,7.00),(27,15,14,1,7.00),(28,16,7,2,5.00),(29,16,9,4,15.00),(30,17,6,2,6700.00),(31,18,12,5,7.00),(32,18,6,2,6700.00),(33,18,10,2,15.00),(34,19,14,1,7.00),(35,19,11,5,7.00),(36,19,9,5,15.00),(37,20,7,3,5.00),(38,20,7,5,5.00),(39,21,10,4,15.00),(40,21,14,5,7.00),(41,21,13,3,7.00),(42,22,7,4,5.00),(43,22,9,5,15.00),(44,22,4,1,12.00),(45,23,2,4,15.00),(46,23,3,3,10.00),(47,24,13,2,7.00),(48,25,14,2,7.00),(49,26,11,1,7.00),(50,26,14,5,7.00),(51,27,5,5,14.00),(52,28,9,2,15.00),(53,28,12,4,7.00),(54,28,5,2,14.00),(55,29,7,5,5.00),(56,30,9,5,15.00),(57,30,2,2,15.00),(58,30,1,3,13.00),(59,31,12,1,7.00),(60,32,7,1,5.00),(61,33,7,1,5.00),(62,34,7,1,5.00),(63,35,3,1,10.00),(64,36,1,1,13.00),(65,37,2,1,15.00),(66,37,6,1,6700.00),(67,37,12,1,7.00),(68,38,1,1,10.00),(69,39,9,1,6.00),(70,40,6,1,14.00),(71,41,14,1,16.00),(72,42,6,1,14.00),(73,43,5,1,12.00),(74,43,13,1,17.00),(75,44,9,1,6.00),(76,44,11,1,14.00),(77,44,13,1,17.00),(78,45,5,1,12.00),(79,45,11,1,14.00),(80,45,14,1,16.00);
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
  `payment_method` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'card',
  `total_price` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `table_id` int DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `points_earned` int DEFAULT '0',
  `points_redeemed` int DEFAULT '0',
  `discount_amount` decimal(10,2) DEFAULT '0.00',
  `points_spent` int DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `table_id` (`table_id`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES (1,1,'2026-01-09 10:32:00','completed','card',28.00,'2026-01-08 08:35:15',1,NULL,0,0,0.00,0),(2,2,'2026-01-08 13:30:11','completed','card',43.00,'2026-01-08 11:30:11',NULL,NULL,0,0,0.00,0),(3,2,'2026-01-08 13:30:11','completed','card',70.00,'2026-01-08 11:30:11',NULL,NULL,0,0,0.00,0),(4,2,'2026-01-08 13:30:11','completed','card',21.00,'2026-01-08 11:30:11',NULL,NULL,0,0,0.00,0),(5,2,'2026-01-08 13:30:11','completed','card',14.00,'2026-01-08 11:30:11',NULL,NULL,0,0,0.00,0),(6,2,'2026-01-08 13:30:11','completed','card',30.00,'2026-01-08 11:30:11',NULL,NULL,0,0,0.00,0),(7,2,'2026-01-07 13:30:11','completed','card',13440.00,'2026-01-07 11:30:11',NULL,NULL,0,0,0.00,0),(8,2,'2026-01-07 13:30:11','completed','card',75.00,'2026-01-07 11:30:11',NULL,NULL,0,0,0.00,0),(9,2,'2026-01-07 13:30:11','completed','card',13.00,'2026-01-07 11:30:11',NULL,NULL,0,0,0.00,0),(10,2,'2026-01-06 13:30:11','completed','card',86.00,'2026-01-06 11:30:11',NULL,NULL,0,0,0.00,0),(11,2,'2026-01-06 13:30:11','completed','card',48.00,'2026-01-06 11:30:11',NULL,NULL,0,0,0.00,0),(12,2,'2026-01-06 13:30:11','completed','card',35.00,'2026-01-06 11:30:11',NULL,NULL,0,0,0.00,0),(13,2,'2026-01-06 13:30:11','completed','card',33528.00,'2026-01-06 11:30:11',NULL,NULL,0,0,0.00,0),(14,2,'2026-01-06 13:30:11','completed','card',7.00,'2026-01-06 11:30:11',NULL,NULL,0,0,0.00,0),(15,2,'2026-01-05 13:30:11','completed','card',7.00,'2026-01-05 11:30:11',NULL,NULL,0,0,0.00,0),(16,2,'2026-01-05 13:30:11','completed','card',70.00,'2026-01-05 11:30:11',NULL,NULL,0,0,0.00,0),(17,2,'2026-01-05 13:30:11','completed','card',13400.00,'2026-01-05 11:30:11',NULL,NULL,0,0,0.00,0),(18,2,'2026-01-04 13:30:11','completed','card',13465.00,'2026-01-04 11:30:11',NULL,NULL,0,0,0.00,0),(19,2,'2026-01-04 13:30:11','completed','card',117.00,'2026-01-04 11:30:11',NULL,NULL,0,0,0.00,0),(20,2,'2026-01-04 13:30:11','completed','card',40.00,'2026-01-04 11:30:11',NULL,NULL,0,0,0.00,0),(21,2,'2026-01-04 13:30:11','completed','card',116.00,'2026-01-04 11:30:11',NULL,NULL,0,0,0.00,0),(22,2,'2026-01-04 13:30:11','completed','card',107.00,'2026-01-04 11:30:11',NULL,NULL,0,0,0.00,0),(23,2,'2026-01-03 13:30:11','completed','card',90.00,'2026-01-03 11:30:11',NULL,NULL,0,0,0.00,0),(24,2,'2026-01-03 13:30:11','completed','card',14.00,'2026-01-03 11:30:11',NULL,NULL,0,0,0.00,0),(25,2,'2026-01-03 13:30:11','completed','card',14.00,'2026-01-03 11:30:11',NULL,NULL,0,0,0.00,0),(26,2,'2026-01-03 13:30:11','completed','card',42.00,'2026-01-03 11:30:11',NULL,NULL,0,0,0.00,0),(27,2,'2026-01-03 13:30:11','completed','card',70.00,'2026-01-03 11:30:11',NULL,NULL,0,0,0.00,0),(28,2,'2026-01-02 13:30:11','completed','card',86.00,'2026-01-02 11:30:11',NULL,NULL,0,0,0.00,0),(29,2,'2026-01-02 13:30:11','completed','card',25.00,'2026-01-02 11:30:11',NULL,NULL,0,0,0.00,0),(30,2,'2026-01-02 13:30:11','completed','card',144.00,'2026-01-02 11:30:11',NULL,NULL,0,0,0.00,0),(31,1,'2026-01-09 10:56:00','completed','card',7.00,'2026-01-08 16:57:21',NULL,NULL,0,0,0.00,0),(32,1,'2026-01-09 10:29:00','completed','card',5.00,'2026-01-08 17:13:36',NULL,NULL,0,0,0.00,0),(33,1,'2026-01-08 19:16:37','completed','card',5.00,'2026-01-08 17:16:37',1,NULL,0,0,0.00,0),(34,4,'2026-01-08 19:27:04','completed','card',5.00,'2026-01-08 17:27:05',1,NULL,0,0,0.00,0),(35,4,'2026-01-08 19:28:11','completed','card',10.00,'2026-01-08 17:28:11',1,NULL,0,0,0.00,0),(36,4,'2026-01-08 19:33:19','completed','card',13.00,'2026-01-08 17:33:19',1,NULL,0,0,0.00,0),(37,1,'2026-01-10 10:30:00','completed','card',6722.00,'2026-01-09 18:30:55',NULL,'2026-02-04 18:17:35',1340,0,0.00,0),(38,2,'2026-01-23 10:00:00','completed','card',10.00,'2026-01-22 20:31:17',NULL,'2026-02-04 12:15:16',0,0,0.00,0),(39,1,'2026-01-24 10:39:00','pending','card',6.00,'2026-01-22 20:39:20',NULL,NULL,0,0,0.00,0),(40,1,'2026-01-23 11:13:00','pending','card',14.00,'2026-01-22 21:13:17',NULL,NULL,0,0,0.00,0),(41,1,'2026-01-23 11:15:00','pending','card',16.00,'2026-01-22 21:16:46',NULL,NULL,0,0,0.00,0),(42,1,'2026-01-24 14:40:00','pending','card',14.00,'2026-01-22 21:43:36',NULL,NULL,0,0,0.00,0),(43,2,'2026-02-04 09:18:00','completed','card',29.00,'2026-02-04 04:31:23',NULL,'2026-02-04 12:15:21',5,0,0.00,0),(44,2,'2026-02-04 16:06:00','completed','card',32.00,'2026-02-04 10:14:26',NULL,'2026-02-04 12:15:06',5,0,0.00,50),(45,2,'2026-02-04 15:23:00','completed','card',35.00,'2026-02-04 10:24:13',NULL,'2026-02-04 18:20:22',5,0,0.00,70);
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_resets` (
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_resets`
--

LOCK TABLES `password_resets` WRITE;
/*!40000 ALTER TABLE `password_resets` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_resets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_ingredients`
--

DROP TABLE IF EXISTS `product_ingredients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_ingredients` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `ingredient_id` int NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `unit` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `ingredient_id` (`ingredient_id`),
  CONSTRAINT `product_ingredients_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_ingredients_ibfk_2` FOREIGN KEY (`ingredient_id`) REFERENCES `ingredients` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_ingredients`
--

LOCK TABLES `product_ingredients` WRITE;
/*!40000 ALTER TABLE `product_ingredients` DISABLE KEYS */;
INSERT INTO `product_ingredients` VALUES (1,1,2,25.00,'g'),(2,1,1,250.00,'ml');
/*!40000 ALTER TABLE `product_ingredients` ENABLE KEYS */;
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
  `tags` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `ingredients` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `quantity` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `image_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category` enum('coffee','tea','chocolate','refreshment','signature','addon') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'coffee',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `discount` int DEFAULT '0',
  `tva_code` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'A',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (1,'Espresso single','Esența pură a cafelei: un shot intens, bogat și plin de caracter, extras cu măiestrie pentru a trezi toate simțurile.','10.00','cafea, apă','20ml',20.00,'assets/menu/images/espresso.webp','coffee','2026-01-22 15:04:24',0,'A'),(2,'Espresso dublu','Energie la dublu. Aceeași intensitate remarcabilă, într-o doză generoasă care îți oferă impulsul perfect pentru întreaga zi.','cafea, cafeina, tare, dublu, fierbinte','cafea, apă','40ml',12.00,'assets/menu/images/prod_6981e6ec6d71a.webp','coffee','2026-01-22 15:04:24',0,'A'),(3,'Long Black','Eleganță în simplitate. Un dublu espresso turnat delicat peste apă fierbinte, păstrând crema bogată și aromele intense.','cafea, neagra, fierbinte, americano, intens','espresso dublu, apă','80ml',125.00,'assets/menu/images/prod_6981e7e620e8a.webp','coffee','2026-01-22 15:04:24',0,'A'),(4,'V60','O experiență artizanală. Cafea preparată manual, picătură cu picătură, pentru a dezvălui notele florale și fructate subtile ale boabelor de origine.','cafea, filtru, brew manual, neagra, specialitate, fierbinte','cafea, apă','200ml',20.00,'assets/menu/images/prod_6981e873deb8d.webp','coffee','2026-01-22 15:04:24',0,'A'),(5,'Cortado','Echilibrul ideal. Tăria unui espresso întâlnește dulceața laptelui cald, într-o armonie perfectă de gust și textură.','cafea, lapte, espresso, mic, tare, fierbinte','espresso single, cremă de lapte','120ml',12.00,'assets/menu/images/cortado.webp','coffee','2026-01-22 15:04:24',0,'A'),(6,'Cappuccino','Clasicul italian desăvârșit. Espresso catifelat îmbrățișat de lapte cald și o coroană bogată de spumă fină.','cafea, lapte, spuma, cremos, fierbinte','espresso single, cremă de lapte','200ml',14.00,'assets/menu/images/cappuccino.webp','coffee','2026-01-22 15:04:24',0,'A'),(7,'Flat White','Cremozitate absolută. Două shot-uri de espresso învăluite în cremă de lapte micro-texturată, pentru o băutură fină, dar puternică.','cafea, lapte, fin, fierbinte, latte art, catifelat','espresso dublu, cremă de lapte','180ml',15.00,'assets/menu/images/prod_6981e8f815ea7.webp','coffee','2026-01-22 15:04:24',0,'A'),(8,'Latte','Răsfăț cremos. O îmbrățișare caldă de lapte spumat și espresso, perfectă pentru momentele lungi de relaxare.','cafea, lapte, cremos, mare, fierbinte','espresso single, cremă de lapte','300ml',16.00,'assets/menu/images/latte.webp','coffee','2026-01-22 15:04:24',0,'A'),(9,'Babyccino','Bucuria celor mici. Spumă de lapte pufoasă, pudrată cu cacao fină – un deliciu jucăuș fără cofeină.','lapte, spuma, copii, fierbinte, fara cafeina','cremă de lapte','120ml',6.00,'assets/menu/images/babycino.webp','chocolate','2026-01-22 15:04:24',0,'A'),(10,'Hot Cioco','Decadență lichidă. Ciocolată caldă premium, densă și catifelată, care îți încălzește sufletul cu fiecare înghițitură.','ciocolata, fierbinte, dulce, cacao, desert','ciocolată caldă pudră, cremă de lapte','200ml',15.00,'assets/menu/images/hot_chocolate.webp','chocolate','2026-01-22 15:04:24',0,'A'),(11,'Ceai','Infuzia liniștii. O selecție de frunze de ceai premium, alese cu grijă pentru a oferi o pauză de prospețime și calm.','ceai, plante, fierbinte, relaxant, natural','ceai, apă','300ml',14.00,'assets/menu/images/ceai.webp','tea','2026-01-22 15:04:24',0,'A'),(12,'Espresso Tonic','Efervescență și energie. Întâlnirea surprinzătoare dintre espresso intens și apa tonică rece, pentru un cocktail de cafea revitalizant.','cafea, tonic, rece, acidulat, racoritor, vara, gheata','espresso dublu, apă tonică, gheață','180ml',17.00,'assets/menu/images/prod_6981e935443de.webp','signature','2026-01-22 15:04:24',0,'A'),(13,'Cold Brew Tonic','Răcorire sofisticată. Finețea cafelei cold brew combinată cu perlajul apei tonice, o băutură vibrantă și cristalină.','cafea, cold brew, tonic, acidulat, racoritor, gheata, rece','cold brew, apă tonică, gheață','180ml',17.00,'assets/menu/images/cold_brew_tonic.webp','signature','2026-01-22 15:04:24',0,'A'),(14,'Cold Brew Latte','Dulceață naturală. Cafea extrasă la rece și lapte proaspăt, o alternativă fină, lipsită de aciditate, perfectă pentru zilele calde.','cafea, cold brew, lapte, rece, gheata, fin','cold brew, apă, gheață','250ml',16.00,'assets/menu/images/cold_brew_latte.webp','coffee','2026-01-22 15:04:24',0,'A'),(15,'Cold Brew','Răbdarea gustului. Cafea infuzată lent în apă rece timp îndelungat, rezultând un elixir dulceag, energizant și plin de claritate.','cafea, cold brew, neagra, rece, tare, fin, gheata, racoritor','cafea, apă, gheață','180ml',14.00,'assets/menu/images/cold_brew.webp','coffee','2026-01-22 15:04:24',0,'A'),(16,'Ice Cappuccino','Cappuccino-ul tău preferat, servit „on the rocks”. Espresso rece, lapte și spumă, pentru o plăcere răcoroasă.','cafea, lapte, gheata, rece, spuma, vara','espresso single, cremă de lapte, gheață','180ml',14.00,'assets/menu/images/prod_6981e981c5e2e.webp','coffee','2026-01-22 15:04:24',0,'A'),(17,'Ice Latte','Clasicul Latte în variantă estivală. Espresso rece turnat peste lapte și gheață, simplu și revigorant.','cafea, lapte, gheata, rece, vara, cremos, racoritor','espresso single, cremă de lapte, gheață','250ml',16.00,'assets/menu/images/ice_latte.webp','coffee','2026-01-22 15:04:24',0,'A'),(18,'Matcha Latte','Ritualul verde. Pudră fină de ceai verde Matcha japonez, bogată în antioxidanți, combinată cu lapte cremos pentru o energie zen.','ceai, matcha, verde, lapte, fierbinte, sanatos, energizant','ceai matcha pudră, apă, cremă de lapte','300ml',20.00,'assets/menu/images/prod_6981e9d36c66e.webp','tea','2026-01-22 15:04:24',0,'A'),(19,'Ice Matcha Latte','Zen în pahar de gheață. Prospețimea vegetală a ceaiului Matcha întâlnește răcoarea laptelui cu gheață.','ceai, matcha, verde, lapte, gheata, rece, vara','ceai matcha pudră, apă, cremă de lapte, gheață','250ml',20.00,'assets/menu/images/prod_6981ea3a11af9.webp','tea','2026-01-22 15:04:24',0,'A'),(20,'Matcha Tonic','Energie efervescentă. O băutură modernă și vibrantă, unde Matcha întâlnește apa tonică pentru un boost de vitalitate.','ceai, matcha, verde, tonic, acidulat, rece, gheata','ceai matcha pudră, apă, apă tonică','180ml',22.00,'assets/menu/images/prod_6981eb677c4ee.webp','signature','2026-01-22 15:04:24',0,'A'),(21,'Socată / Limonadă cu soc','Gustul copilăriei reinterpretat. O băutură artizanală răcoritoare, cu arome florale de soc și lămâie proaspătă.','soc, limonada, rece, dulce, racoritor, vara, acidulat, natural','sirop de soc, zeamă de lămâie, apă carbogazoasă, gheață','250ml',14.00,'assets/menu/images/prod_6981ebcb46aac.webp','refreshment','2026-01-22 15:04:24',0,'A'),(22,'Limonadă cu zmeură','Explozie fructată. Limonadă clasică îmbogățită cu sirop natural de zmeură, dulce-acrișoară și irezistibilă.','zmeura, limonada, fructe, rece, dulce, racoritor, vara, natural','sirop de zmeură, zeamă de lămâie, apă carbogazoasă','250ml',14.00,'assets/menu/images/prod_6981ec3a70adb.webp','refreshment','2026-01-22 15:04:24',0,'A'),(23,'Extra shot','Boost-ul tău de energie. Adaugă încă o doză de intensitate băuturii tale preferate.','cafea, cafeina, extra, intens','espresso single','20ml',5.00,'assets/menu/images/prod_6981ec85d73e8.webp','addon','2026-01-22 15:04:24',0,'A'),(24,'Lapte vegetal','Alternative delicioase. Optează pentru lapte de ovăz sau mazăre, cremos și prietenos cu natura, pentru cafeaua ta.','vegan, lapte, post, migdale, ovaz, soia, fara lactoza','lapte de ovăz/mazăre','20ml',3.00,'assets/menu/images/prod_6981ecdd5c5cd.webp','addon','2026-01-22 15:04:24',0,'B');
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
  `status` enum('active','deleted','closed') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'active',
  `checked_in` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `table_id` (`table_id`),
  CONSTRAINT `reservations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reservations_ibfk_2` FOREIGN KEY (`table_id`) REFERENCES `tables` (`ID`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reservations`
--

LOCK TABLES `reservations` WRITE;
/*!40000 ALTER TABLE `reservations` DISABLE KEYS */;
INSERT INTO `reservations` VALUES (1,1,NULL,1,'2026-01-03 14:34:00','2026-01-03 12:33:36','closed',0),(2,1,'Raul Zarnescu',1,'2026-01-14 15:22:00','2026-01-04 09:22:37','closed',0),(3,1,'Raul',3,'2026-02-04 16:00:00','2026-02-03 15:03:25','active',1);
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
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `settings` (
  `setting_key` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `setting_value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES ('loyalty_enabled','1'),('loyalty_max_points','100'),('loyalty_max_points_per_order','100'),('loyalty_points_per_threshold','5'),('loyalty_redeem_points','10'),('loyalty_redeem_value','1'),('loyalty_redemption_rate','10'),('loyalty_threshold','25'),('loyalty_threshold_amount','25'),('tva','9'),('tva_a','19'),('tva_b','9'),('tva_c','5'),('tva_d','0');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
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
  `display_order` int DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `slider_images`
--

LOCK TABLES `slider_images` WRITE;
/*!40000 ALTER TABLE `slider_images` DISABLE KEYS */;
INSERT INTO `slider_images` VALUES (1,'assets/img/slider_1.webp','Dimineți Perfecte','Savurează un Cappuccino autentic',NULL,'2025-12-29 10:20:49','Vezi Meniul','?page=menu',1,1),(2,'assets/img/slider_2.webp','Gustări Proaspete','Croissant cu unt, scos din cuptor',NULL,'2025-12-29 10:20:49','Comandă Acum','?page=menu',1,0),(3,'assets/img/slider_3.webp','Aromă Intensă','Cele mai bune boabe de cafea',NULL,'2025-12-29 10:20:49','Rezervă Masă','?page=tables',1,3),(5,'assets/images/slider/slide_69821575bc989.jpg','Oferta Saptamanii','Bine ati venit',NULL,'2026-02-03 15:34:13','View Menu','?page=menu',1,2);
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
INSERT INTO `tables` VALUES (1,'Libera',27.6382,10.0671,'circle',9.375,12.3296),(2,'Ocupata',43.8442,56.2081,'circle',5.62186,7.49266),(3,'Libera',56.7839,56.0403,'circle',5.62186,7.49266),(4,'Ocupata',69.598,56.2081,'circle',5.62382,7.4979),(5,'Libera',22.2362,40.7718,'square',6.74859,6.99979),(6,'Libera',45.9799,76.8456,'rectangle',5.25,14.1667),(7,'Libera',67.2111,77.1812,'rectangle',5.37453,13.6666);
/*!40000 ALTER TABLE `tables` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_tokens`
--

DROP TABLE IF EXISTS `user_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_tokens` (
  `id` int NOT NULL AUTO_INCREMENT,
  `selector` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `validator` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `user_id` int NOT NULL,
  `expires_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_user_tokens_user_id` (`user_id`),
  CONSTRAINT `fk_user_tokens_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_tokens`
--

LOCK TABLES `user_tokens` WRITE;
/*!40000 ALTER TABLE `user_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_tokens` ENABLE KEYS */;
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
  `role` enum('user','admin','employer') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'user',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `PuncteFidelitate` int DEFAULT NULL,
  `PPicture` varchar(2048) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'assets/public/default.png',
  `oauth_provider` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `oauth_uid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_blacklisted` tinyint(1) DEFAULT '0',
  `blacklist_reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'zarnescuraul@gmail.com','Raul','$2y$12$j09C55tliX7bS9ZCNMLFJuerVS.0DZ0pG/pgqVzLaFcFtYosnA8Iq','admin','2025-11-24 10:19:31',1540,'assets/uploads/profile_pictures/PP_userid_1.jpg','google','105941628733277196959',0,NULL),(2,'davidrares56@yahoo.com','raress_tc','$2y$12$ud4pLmRn0Vx6Vm0NpNqXY.nnTbNMkNqKmvsru14zHnrYoXpm/.JHi','admin','2025-12-08 17:24:04',9895,'assets/uploads/profile_pictures/PP_userid_2.gif',NULL,NULL,0,NULL),(3,'erwin.georgescu@student.unitbv.ro','erwin','$2y$12$K6Bet6UcpmUmXdC9I4FlYOhtazj9eQf.UeIxyKgoiQ7FuQxmSTmDW','admin','2025-12-27 15:34:58',0,'assets/public/default.png',NULL,NULL,0,NULL),(4,'guest@mazicoffee.com','Guest','$2y$10$ya7vmoIY9nQQDqeBygttKOiTfRSHLBNvxc02utkPBrEUmLzQZIEtK','user','2026-01-08 17:27:05',NULL,'assets/public/default.png',NULL,NULL,0,NULL),(5,'sisea.ianis@gmail.com','ianis','$2y$12$w1.oeOUzogdPxmvjKgbZqO/gT9Lxnyaj1a7NJW.QE3QmwqXmph6/W','employer','2026-01-10 13:15:52',0,'assets/public/default.png',NULL,NULL,0,NULL);
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

-- Dump completed on 2026-02-04 22:16:38
