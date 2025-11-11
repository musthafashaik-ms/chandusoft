-- MySQL dump 10.13  Distrib 8.4.3, for Win64 (x86_64)
--
-- Host: localhost    Database: chandusoft
-- ------------------------------------------------------
-- Server version	8.4.3

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
-- Table structure for table `catalog`
--

DROP TABLE IF EXISTS `catalog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `catalog` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `short_desc` text,
  `status` enum('published','archived') DEFAULT 'published',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `catalog`
--

LOCK TABLES `catalog` WRITE;
/*!40000 ALTER TABLE `catalog` DISABLE KEYS */;
INSERT INTO `catalog` VALUES (23,'New','new',125.00,'uploads/2025/10/catalog_1761209062.png','','published','2025-10-23 02:57:04','2025-10-23 08:44:22'),(24,'Test1','test1',150.00,'uploads/2025/10/catalog_1761207206.png','Good Product','published','2025-10-23 08:13:27','2025-10-23 08:13:27'),(25,'Test2','test2',150.00,'uploads/2025/10/catalog_1761211323.jpg','','published','2025-10-23 09:22:03','2025-10-28 08:58:08'),(26,'Test3','test3',60.00,'uploads/2025/10/catalog_1761211376.png','','archived','2025-10-23 09:22:56','2025-10-27 10:27:01'),(27,'Test4','test4',80.00,'uploads/2025/10/catalog_1761211455.jpg','','published','2025-10-23 09:24:15','2025-10-23 09:33:12'),(28,'Testz','testz',120.00,'uploads/2025/10/catalog_1761285759_3a11d8.webp','','published','2025-10-24 06:02:39','2025-10-24 06:02:39'),(29,'Test6','test6',88.00,'uploads/catalog_1761544949.png','','published','2025-10-25 04:04:48','2025-10-29 06:28:02'),(30,'Milk','milk',42.00,'uploads/2025/10/catalog_1761382676_e8727c.webp','Thick Milk','published','2025-10-25 08:57:57','2025-10-29 06:20:10'),(31,'TestA','testa',150.00,'uploads/catalog_1761564948.jpg','Good','published','2025-10-27 11:35:48','2025-10-27 11:35:48'),(32,'Curd','z',20.00,'uploads/catalog_1761718970.png','Best Product','published','2025-10-29 06:22:50','2025-10-29 11:06:12'),(33,'M','m',80.00,'uploads/catalog_1761736159.jpg','M for Butter Milk','published','2025-10-29 11:09:19','2025-11-07 09:33:07'),(34,'jai sai','jai-sai',150.00,'uploads/catalog_1762511831.png','Nothing','published','2025-11-07 10:37:12','2025-11-07 10:37:12');
/*!40000 ALTER TABLE `catalog` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `enquiries`
--

DROP TABLE IF EXISTS `enquiries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `enquiries` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product` varchar(255) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `message` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `submitted_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `enquiries`
--

LOCK TABLES `enquiries` WRITE;
/*!40000 ALTER TABLE `enquiries` DISABLE KEYS */;
INSERT INTO `enquiries` VALUES (1,'test 2','Shaik','DUMMY357@GMAIL.COM','good','2025-10-22 05:41:11','2025-10-22 11:11:11'),(2,'Test','Musthafa Shaik','musthafa.shaik@gmai.com','Best Product','2025-10-22 05:48:14','2025-10-22 11:18:14'),(3,'Test','Musthafa Shaik','musthafa.shaik@gmai.com','Best Product','2025-10-22 05:53:19','2025-10-22 11:23:19'),(4,'New','Jafar','jafar@gmail.com','hi','2025-10-23 03:38:13','2025-10-23 09:08:13'),(5,'Curd','Musthafa Shaik','musthafa.shaik8@gmai.com','hello','2025-10-30 08:53:44','2025-10-30 14:23:44'),(6,'Curd','jaisai','jaisai@gmail.com','jai','2025-10-30 09:18:53','2025-10-30 14:48:53'),(7,'Curd','Musthafa Shaik','DUMMY357@GMAIL.COM','hhy','2025-11-01 10:14:59','2025-11-01 15:44:59'),(8,'Curd','Musthafa Shaik','musthafa.shaik@gmai.com','good','2025-11-03 09:47:43','2025-11-03 15:17:43');
/*!40000 ALTER TABLE `enquiries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `leads`
--

DROP TABLE IF EXISTS `leads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `leads` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `IP` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=102 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `leads`
--

LOCK TABLES `leads` WRITE;
/*!40000 ALTER TABLE `leads` DISABLE KEYS */;
INSERT INTO `leads` VALUES (1,'Musthafa Shaik','musthafa.shaik@gmai.com','Hello','2025-10-04 06:21:17',NULL),(2,'Nagur Basha','nb@gmail.com','Hero','2025-10-04 06:24:13',NULL),(3,'Haseena','h@gmail.com','Queen','2025-10-04 06:25:44',NULL),(4,'Jafar','j@gmail.com','Strong','2025-10-04 06:26:12',NULL),(5,'Mohammad','m@gmail.com','Teacher','2025-10-04 06:27:13',NULL),(6,'Abu Bakr','ab@gmail.com','Favorite','2025-10-04 06:28:18',NULL),(7,'Omar','o@gmail.com','Fear','2025-10-04 06:30:05',NULL),(8,'Ali','a@gmail.com','Faith','2025-10-04 06:30:25',NULL),(9,'Michael Scott','michael@example.com','Can you provide more details about your services?','2025-10-04 07:06:14',NULL),(10,'Pam Beesly','pam@example.com','I am interested in collaboration','2025-10-04 07:07:05',NULL),(11,'Jim Halpert','jim@example.com','Please send me a pricing list.','2025-10-04 07:07:47',NULL),(12,'Dwight Schrute','dwight@example.com','Looking for bulk services.','2025-10-04 07:08:58',NULL),(13,'Angela Martin','angela@example.com','Can we schedule a meeting?','2025-10-04 07:09:49',NULL),(14,'Kevin Malone','kevin@example.com','Need more information about your products.\'','2025-10-04 07:10:50',NULL),(15,'Oscar Martine','oscar@example.com','I have some questions before signing up','2025-10-04 07:11:35',NULL),(16,'Stanley Hudson','stanley@example.com','Please contact me soon.','2025-10-04 07:12:24',NULL),(17,'Phyllis Vance','phyllis@example.com','Interested in a long-term contract.','2025-10-04 07:13:04',NULL),(18,'Meredith Palmer','meredith@example.com','Can I get a quotation?','2025-10-04 07:13:47',NULL),(19,'phani','phani@gmail.com','Hero','2025-10-06 06:30:26',NULL),(20,'chaitanya','chaitanya@gmail.com','hi','2025-10-06 07:21:38',NULL),(21,'Musthafa Shaik','musthafa.shaik@gmai.com','KK','2025-10-06 10:51:18',NULL),(22,'user','user@gmail.com','Editor','2025-10-06 11:12:57',NULL),(32,'Sameer Md','sameermd@gmail.com','Hi welcome!!!','2025-10-07 06:32:59',NULL),(34,'phani','kumar@gmail.com','hii','2025-10-07 06:38:50',NULL),(35,'saleem','saleem12@gmail.com','Saleem Bashaa....','2025-10-07 07:12:21',NULL),(37,'jaisai','sai@gmail.com','sai bhai','2025-10-07 07:42:59',NULL),(43,'Musthafa Shaik','shaik@gmail.com','Created by musthafa','2025-10-07 08:52:46',NULL),(44,'musthafa','musthafa.shaik@chandusoft.com','g','2025-10-07 09:02:18',NULL),(45,'Sameer ','sameer0@gmail.com','zero','2025-10-07 09:06:08',NULL),(46,'saleemm','saleemm@gmail.com','hi','2025-10-07 10:12:49',NULL),(47,'kk','k@gmail.com','k','2025-10-07 10:41:58',NULL),(52,'aa','aa@gmail.com','aa','2025-10-07 10:49:23',NULL),(53,'aa','DUMMY357@GMAIL.COM','rghtr','2025-10-07 10:50:06',NULL),(55,'cc','DUMMY357@GMAIL.COM','fde','2025-10-07 10:52:38',NULL),(57,'musthafa','musthafa.shaik63@gmai.com','Boom','2025-10-07 11:02:18',NULL),(61,'Musthafa Shaik','sample123@gmail.com','Hey','2025-10-07 11:08:39',NULL),(62,' Shaik','s@gmail.com','hello','2025-10-07 11:15:52',NULL),(67,'Shaik','sk@gmail.com','Hello','2025-10-07 11:19:25',NULL),(70,'Sai dd','sai258@gmail.com','\\asdfevgf','2025-10-07 11:31:51',NULL),(71,'fg','DUMMY357@GMAIL.COM','dgfd','2025-10-07 11:33:09',NULL),(73,'asfg','fgdg@gmail.com','hsdghtgh','2025-10-07 11:34:02',NULL),(75,'Shaik Musthafa','musthafa.shaik01@gmail.com','Good Evening Sir','2025-10-07 11:48:25',NULL),(76,'zz','z@gmail.com','z+','2025-10-08 03:54:44',NULL),(79,'Musthafa Shaik','m@gmail.com','ms','2025-10-08 05:33:46',NULL),(81,'Jafar','jafar@gmail.com','Hey Jafar','2025-10-10 09:10:38',NULL),(84,'jaisai','jaisai5@gmail.com','hero','2025-10-10 11:26:50',NULL),(85,'jyrj','yj@gmail.com','joi','2025-10-10 11:34:16',NULL),(96,'Musthafa Shaik','musthafa.shaik@gmai.com','hi','2025-10-28 06:01:43',NULL),(97,'jaisai','jaisai@gmail.com','hero','2025-10-28 06:42:24',NULL),(98,'Musthafa Shaik','musthafa.shaik@gmai.com','Hello','2025-10-29 05:57:51',NULL),(99,'Chaitanya','Chaitanya@gmail.com','Good Girl','2025-10-29 06:47:29',NULL),(100,'Chaitanya','Chaitanya@gmail.com','Good Girl','2025-10-29 06:52:31',NULL),(101,'John Nikhel','john@gmail.com','Good Person','2025-10-29 11:26:03',NULL);
/*!40000 ALTER TABLE `leads` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_items` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int unsigned DEFAULT NULL,
  `product_id` int unsigned DEFAULT NULL,
  `product_name` varchar(255) DEFAULT NULL,
  `quantity` int DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `subtotal` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=138 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_items`
--

LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
INSERT INTO `order_items` VALUES (47,43,31,'TestA',1,150.00,150.00),(48,1,32,'Curd',2,20.00,40.00),(49,2,32,'Curd',2,20.00,40.00),(50,3,32,'Curd',2,20.00,40.00),(51,4,32,'Curd',2,20.00,40.00),(52,5,32,'Curd',2,20.00,40.00),(53,6,33,'M',1,80.00,80.00),(54,7,32,'Curd',1,20.00,20.00),(55,8,32,'Curd',1,20.00,20.00),(56,9,32,'Curd',1,20.00,20.00),(57,10,30,'Milk',1,42.00,42.00),(58,11,30,'Milk',1,42.00,42.00),(59,12,30,'Milk',1,42.00,42.00),(60,13,30,'Milk',1,42.00,42.00),(61,14,30,'Milk',1,42.00,42.00),(62,15,30,'Milk',1,42.00,42.00),(63,16,30,'Milk',1,42.00,42.00),(64,17,30,'Milk',1,42.00,42.00),(65,18,31,'TestA',1,150.00,150.00),(66,19,27,'Test4',1,80.00,80.00),(67,20,31,'TestA',1,150.00,150.00),(68,21,30,'Milk',1,42.00,42.00),(69,22,31,'TestA',2,150.00,300.00),(70,22,32,'Curd',7,20.00,140.00),(71,23,24,'Test1',1,150.00,150.00),(72,24,31,'TestA',31,150.00,4650.00),(73,25,31,'TestA',2,150.00,300.00),(74,26,29,'Test6',1,88.00,88.00),(75,27,33,'M',10,80.00,800.00),(76,28,30,'Milk',5,42.00,210.00),(77,29,30,'Milk',1,42.00,42.00),(78,29,33,'M',1,80.00,80.00),(79,30,31,'TestA',2,150.00,300.00),(80,30,32,'Curd',2,20.00,40.00),(81,31,32,'Curd',2,20.00,40.00),(82,32,30,'Milk',5,42.00,210.00),(83,33,24,'Test1',1,150.00,150.00),(84,34,28,'Testz',1,120.00,120.00),(85,34,32,'Curd',3,20.00,60.00),(86,35,32,'Curd',1,20.00,20.00),(87,36,31,'TestA',3,150.00,450.00),(88,37,33,'M',1,80.00,80.00),(89,38,33,'M',4,80.00,320.00),(90,39,31,'TestA',1,150.00,150.00),(91,40,28,'Testz',1,120.00,120.00),(92,40,32,'Curd',3,20.00,60.00),(93,41,30,'Milk',1,42.00,42.00),(94,42,24,'Test1',4,150.00,600.00),(95,43,30,'Milk',1,42.00,42.00),(96,44,31,'TestA',1,150.00,150.00),(97,45,32,'Curd',1,20.00,20.00),(98,46,27,'Test4',1,80.00,80.00),(99,46,32,'Curd',1,20.00,20.00),(100,47,32,'Curd',1,20.00,20.00),(101,48,30,'Milk',1,42.00,42.00),(102,49,30,'Milk',2,42.00,84.00),(103,50,32,'Curd',1,20.00,20.00),(104,51,30,'Milk',1,42.00,42.00),(105,52,30,'Milk',1,42.00,42.00),(106,53,32,'Curd',1,20.00,20.00),(107,54,32,'Curd',1,20.00,20.00),(108,55,33,'M',1,80.00,80.00),(109,56,32,'Curd',1,20.00,20.00),(110,57,31,'TestA',1,150.00,150.00),(111,58,33,'M',1,80.00,80.00),(112,59,33,'M',1,80.00,80.00),(113,60,30,'Milk',1,42.00,42.00),(114,61,32,'Curd',1,20.00,20.00),(115,62,32,'Curd',1,20.00,20.00),(116,63,32,'Curd',1,20.00,20.00),(117,64,30,'Milk',3,42.00,126.00),(118,64,32,'Curd',2,20.00,40.00),(119,65,31,'TestA',1,150.00,150.00),(120,66,30,'Milk',1,42.00,42.00),(121,67,31,'TestA',1,150.00,150.00),(122,68,32,'Curd',3,20.00,60.00),(123,68,33,'M',1,80.00,80.00),(124,69,31,'TestA',1,150.00,150.00),(125,70,31,'TestA',1,150.00,150.00),(126,71,32,'Curd',1,20.00,20.00),(127,72,24,'Test1',5,150.00,750.00),(128,73,32,'Curd',1,20.00,20.00),(129,74,31,'TestA',1,150.00,150.00),(130,75,33,'M',1,80.00,80.00),(131,76,30,'Milk',1,42.00,42.00),(132,76,32,'Curd',2,20.00,40.00),(133,76,33,'M',1,80.00,80.00),(134,77,32,'Curd',1,20.00,20.00),(135,78,30,'Milk',4,42.00,168.00),(136,79,32,'Curd',1,20.00,20.00),(137,79,34,'jai sai',1,150.00,150.00);
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
  `order_ref` varchar(50) NOT NULL,
  `customer_name` varchar(120) NOT NULL,
  `customer_email` varchar(160) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `payment_gateway` enum('stripe','paypal') NOT NULL DEFAULT 'stripe',
  `payment_status` enum('pending','paid','failed','refunded','cancelled') NOT NULL DEFAULT 'pending',
  `txn_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `address` varchar(255) NOT NULL,
  `city` varchar(100) NOT NULL,
  `postal_code` varchar(20) NOT NULL,
  `metadata` json DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_ref` (`order_ref`)
) ENGINE=InnoDB AUTO_INCREMENT=80 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES (1,'B2F62E0DA9C3','Sai dd','DUMMY357@GMAIL.COM',40.00,'stripe','pending',NULL,'2025-10-31 11:35:39','2025-10-31 11:35:39','124 Main ST, Montreal, Province: Quebec, Postal Code: H3Z2Y7, Phone Number:','New York','10116',NULL),(2,'19E1FEFE285C','Sai dd','DUMMY357@GMAIL.COM',40.00,'stripe','pending',NULL,'2025-10-31 11:35:43','2025-10-31 11:35:43','124 Main ST, Montreal, Province: Quebec, Postal Code: H3Z2Y7, Phone Number:','New York','10116',NULL),(3,'2C2AC9ADC0BE','Sai dd','DUMMY357@GMAIL.COM',40.00,'stripe','pending',NULL,'2025-10-31 11:37:14','2025-10-31 11:37:14','124 Main ST, Montreal, Province: Quebec, Postal Code: H3Z2Y7, Phone Number:','New York','10116',NULL),(4,'F2AC0F318992','Sai dd','DUMMY357@GMAIL.COM',40.00,'stripe','pending',NULL,'2025-10-31 11:37:17','2025-10-31 11:37:17','124 Main ST, Montreal, Province: Quebec, Postal Code: H3Z2Y7, Phone Number:','New York','10116',NULL),(5,'666BAD684238','Sai dd','DUMMY357@GMAIL.COM',40.00,'stripe','pending',NULL,'2025-10-31 11:39:02','2025-10-31 11:39:02','124 Main ST, Montreal, Province: Quebec, Postal Code: H3Z2Y7, Phone Number:','New York','10116',NULL),(6,'4EA2BD86D8D8','Musthafa Shaik','musthafa.shaik@gmai.com',80.00,'stripe','pending',NULL,'2025-10-31 11:54:02','2025-10-31 11:54:02','124 Main ST, Montreal, Province: Quebec, Postal Code: H3Z2Y7, Phone Number:','New York','10116',NULL),(7,'6F7FFFB4A3F3','Sai dd','DUMMY357@GMAIL.COM',20.00,'stripe','pending',NULL,'2025-10-31 12:00:40','2025-10-31 12:00:40','1233 County Road 115','Abbeville','36310-6141',NULL),(8,'610583D8AC42','Sai dd','DUMMY357@GMAIL.COM',20.00,'paypal','pending',NULL,'2025-11-01 03:44:28','2025-11-01 03:44:28','124 Main ST, Montreal, Province: Quebec, Postal Code: H3Z2Y7, Phone Number:','New York','10116',NULL),(9,'40A2CA8A629D','Sai dd','DUMMY357@GMAIL.COM',20.00,'paypal','pending',NULL,'2025-11-01 03:56:00','2025-11-01 03:56:00','124 Main ST, Montreal, Province: Quebec, Postal Code: H3Z2Y7, Phone Number:','New York','10116',NULL),(10,'779A60584DD7','Sai dd','DUMMY357@GMAIL.COM',42.00,'paypal','pending',NULL,'2025-11-01 04:07:43','2025-11-01 04:07:43','124 Main ST, Montreal, Province: Quebec, Postal Code: H3Z2Y7, Phone Number:','New York','10116',NULL),(11,'3598244A20DE','Sai dd','DUMMY357@GMAIL.COM',42.00,'paypal','pending',NULL,'2025-11-01 04:17:45','2025-11-01 04:17:45','124 Main ST, Montreal, Province: Quebec, Postal Code: H3Z2Y7, Phone Number:','New York','10116',NULL),(12,'D9FCDA8C1A4F','Sai dd','DUMMY357@GMAIL.COM',42.00,'paypal','pending',NULL,'2025-11-01 04:20:30','2025-11-01 04:20:30','124 Main ST, Montreal, Province: Quebec, Postal Code: H3Z2Y7, Phone Number:','New York','10116',NULL),(13,'750B62D18407','Sai dd','DUMMY357@GMAIL.COM',42.00,'paypal','pending',NULL,'2025-11-01 04:21:36','2025-11-01 04:21:36','124 Main ST, Montreal, Province: Quebec, Postal Code: H3Z2Y7, Phone Number:','New York','10116',NULL),(14,'9C7743D3C441','Sai dd','DUMMY357@GMAIL.COM',42.00,'paypal','pending',NULL,'2025-11-01 04:24:23','2025-11-01 04:24:23','124 Main ST, Montreal, Province: Quebec, Postal Code: H3Z2Y7, Phone Number:','New York','10116',NULL),(15,'4E8E4BE92D1B','Sai dd','DUMMY357@GMAIL.COM',42.00,'paypal','pending',NULL,'2025-11-01 04:24:49','2025-11-01 04:24:49','124 Main ST, Montreal, Province: Quebec, Postal Code: H3Z2Y7, Phone Number:','New York','10116',NULL),(16,'B9F9F6E61C74','Sai dd','DUMMY357@GMAIL.COM',42.00,'paypal','pending',NULL,'2025-11-01 04:26:41','2025-11-01 04:26:41','124 Main ST, Montreal, Province: Quebec, Postal Code: H3Z2Y7, Phone Number:','New York','10116',NULL),(17,'6F6BE66941F6','Sai dd','DUMMY357@GMAIL.COM',42.00,'paypal','pending',NULL,'2025-11-01 04:36:12','2025-11-01 04:36:12','124 Main ST, Montreal, Province: Quebec, Postal Code: H3Z2Y7, Phone Number:','New York','10116',NULL),(18,'B8473CF1F7DA','Sai dd','DUMMY357@GMAIL.COM',150.00,'stripe','pending',NULL,'2025-11-01 05:27:22','2025-11-01 05:27:22','124 Main ST, Montreal, Province: Quebec, Postal Code: H3Z2Y7, Phone Number:','New York','10116',NULL),(19,'A8675E3C2090','Sai dd','DUMMY357@GMAIL.COM',80.00,'stripe','pending',NULL,'2025-11-01 05:54:02','2025-11-01 05:54:02','124 Main ST, Montreal, Province: Quebec, Postal Code: H3Z2Y7, Phone Number:','New York','10116',NULL),(20,'BC288DA7DA72','Sai dd','DUMMY357@GMAIL.COM',150.00,'stripe','pending',NULL,'2025-11-01 06:08:34','2025-11-01 06:08:34','124 Main ST, Montreal, Province: Quebec, Postal Code: H3Z2Y7, Phone Number:','New York','10116',NULL),(21,'0844D0BA59F2','Sai dd','DUMMY357@GMAIL.COM',42.00,'paypal','pending',NULL,'2025-11-01 06:09:49','2025-11-01 06:09:49','124, 43A GLENEYRE ST, ST. JOHN\'S, N','New York','10116',NULL),(22,'EF7605CB1167','Sai dd','DUMMY357@GMAIL.COM',440.00,'paypal','pending',NULL,'2025-11-01 11:35:38','2025-11-01 11:35:38','124 Main ST, Montreal, Province: Quebec, Postal Code: H3Z2Y7, Phone Number:','New York','10116',NULL),(23,'B95AE73ED2CC','Musthafa Shaik','musthafa.shaik@gmai.com',150.00,'stripe','paid',NULL,'2025-11-03 04:28:42','2025-11-03 09:08:01','124, 43A GLENEYRE ST, ST. JOHN\'S, N','New York','10116',NULL),(24,'B255F7ECA0E8','Musthafa Shaik','musthafa.shaik@gmai.com',4650.00,'stripe','paid',NULL,'2025-11-03 06:25:45','2025-11-03 09:47:07','7/4321-1','Gannavarm','521101',NULL),(25,'5ADE007A89ED','Sai dd','DUMMY357@GMAIL.COM',300.00,'stripe','pending',NULL,'2025-11-03 07:35:01','2025-11-03 07:35:01','124 Main ST, Montreal, Province: Quebec, Postal Code: H3Z2Y7, Phone Number:','New York','10116',NULL),(26,'A12E105AEBF5','Musthafa Shaik','musthafa.shaik@gmai.com',88.00,'stripe','paid',NULL,'2025-11-03 09:11:04','2025-11-03 09:11:27','7/4321-1','Gannavarm','521101',NULL),(27,'075525F5D411','Shaik Musthafa','musthafa.shaik000@gmai.com',800.00,'paypal','failed',NULL,'2025-11-03 10:28:25','2025-11-03 11:20:58','7/4321-1','Gannavarm','521101',NULL),(28,'DFE1B0813741','Musthafa Shaik','musthafa.shaik@gmai.com',210.00,'paypal','failed',NULL,'2025-11-04 05:30:25','2025-11-04 05:33:45','7/4321-1','Gannavarm','521101',NULL),(29,'2C29273A7AB4','Sai dd','DUMMY357@GMAIL.COM',122.00,'stripe','paid',NULL,'2025-11-04 06:23:18','2025-11-04 07:21:33','124 Main ST, Montreal, Province: Quebec, Postal Code: H3Z2Y7, Phone Number:','New York','10116',NULL),(30,'65C30CE1DF8D','Sai dd','DUMMY357@GMAIL.COM',340.00,'stripe','pending',NULL,'2025-11-04 08:00:07','2025-11-04 09:04:08','124 Main ST, Montreal, Province: Quebec, Postal Code: H3Z2Y7, Phone Number:','New York','10116',NULL),(31,'988DEFD642A9','Sai dd','DUMMY357@GMAIL.COM',40.00,'stripe','paid',NULL,'2025-11-04 08:04:28','2025-11-04 09:37:39','124 Main ST, Montreal, Province: Quebec, Postal Code: H3Z2Y7, Phone Number:','New York','10116',NULL),(32,'D45169051DAF','Musthafa Shaik','musthafa.shaik@gmai.com',210.00,'stripe','paid',NULL,'2025-11-04 09:14:38','2025-11-04 09:14:51','7/4321-1','Gannavarm','521101',NULL),(33,'C74C24BA55DC','Sai dd','DUMMY357@GMAIL.COM',150.00,'stripe','paid',NULL,'2025-11-04 09:24:41','2025-11-04 09:24:50','124 Main ST, Montreal, Province: Quebec, Postal Code: H3Z2Y7, Phone Number:','New York','10116',NULL),(34,'C6EB43E12BE2','Sai dd','DUMMY357@GMAIL.COM',180.00,'stripe','pending',NULL,'2025-11-04 09:52:11','2025-11-04 09:52:11','124 Main ST, Montreal, Province: Quebec, Postal Code: H3Z2Y7, Phone Number:','New York','10116',NULL),(35,'3C13806F8561','Sai dd','DUMMY357@GMAIL.COM',20.00,'stripe','pending',NULL,'2025-11-04 10:33:59','2025-11-04 10:33:59','124 Main ST, Montreal, Province: Quebec, Postal Code: H3Z2Y7, Phone Number:','New York','10116',NULL),(36,'EB2E0E62DF2C','Sai dd','DUMMY357@GMAIL.COM',450.00,'stripe','pending',NULL,'2025-11-04 10:53:11','2025-11-04 10:53:11','124 Main ST, Montreal, Province: Quebec, Postal Code: H3Z2Y7, Phone Number:','New York','10116',NULL),(37,'E56F4CC497E4','Sai dd','DUMMY357@GMAIL.COM',80.00,'stripe','pending',NULL,'2025-11-04 11:30:50','2025-11-04 11:30:50','124 Main ST, Montreal, Province: Quebec, Postal Code: H3Z2Y7, Phone Number:','New York','10116',NULL),(38,'EB50A7E2A4C2','Sai dd','DUMMY357@GMAIL.COM',320.00,'paypal','failed',NULL,'2025-11-05 05:35:21','2025-11-05 05:36:24','124 Main ST, Montreal, Province: Quebec, Postal Code: H3Z2Y7, Phone Number:','New York','10116',NULL),(39,'0FF54A13779A','Sai dd','DUMMY357@GMAIL.COM',150.00,'paypal','pending',NULL,'2025-11-05 07:21:57','2025-11-05 07:21:57','124 Main ST, Montreal, Province: Quebec, Postal Code: H3Z2Y7, Phone Number:','New York','10116',NULL),(40,'D7FF03964679','Sai dd','musthafa.shaik@gmai.com',180.00,'stripe','pending',NULL,'2025-11-05 08:03:42','2025-11-05 08:03:42','1233 County Road 115','Abbeville','36310-6141',NULL),(41,'195C52B089AC','Musthafa Shaik','musthafa.shaik@gmai.com',42.00,'stripe','pending',NULL,'2025-11-05 10:16:53','2025-11-05 10:16:53','7/4321-1','Gannavarm','521101',NULL),(42,'15318B74B17B','Musthafa Shaik','musthafa.shaik@gmai.com',600.00,'stripe','pending',NULL,'2025-11-05 10:24:29','2025-11-05 10:24:29','7/4321-1','Gannavarm','521101',NULL),(43,'4D5E76BBD00A','Musthafa Shaik','DUMMY357@GMAIL.COM',42.00,'stripe','pending',NULL,'2025-11-06 05:36:40','2025-11-06 05:36:40','124 Main ST, Montreal, Province: Quebec, Postal Code: H3Z2Y7, Phone Number:','New York','10116',NULL),(44,'DDE7614890DC','Sai dd','DUMMY357@GMAIL.COM',150.00,'stripe','pending',NULL,'2025-11-06 07:07:25','2025-11-06 07:07:25','124 Main ST, Montreal, Province: Quebec, Postal Code: H3Z2Y7, Phone Number:','New York','10116',NULL),(45,'C9B0CCB9AE1C','Sai dd','DUMMY357@GMAIL.COM',20.00,'stripe','pending',NULL,'2025-11-06 07:22:57','2025-11-06 07:22:57','124 Main ST, Montreal, Province: Quebec, Postal Code: H3Z2Y7, Phone Number:','New York','10116',NULL),(46,'4C62955E683B','Sai dd','DUMMY357@GMAIL.COM',100.00,'stripe','pending',NULL,'2025-11-06 07:29:29','2025-11-06 07:29:29','124 Main ST, Montreal, Province: Quebec, Postal Code: H3Z2Y7, Phone Number:','New York','10116',NULL),(47,'8776BB347763','Sai dd','DUMMY357@GMAIL.COM',20.00,'stripe','pending',NULL,'2025-11-06 07:39:27','2025-11-06 07:39:27','124 Main ST, Montreal, Province: Quebec, Postal Code: H3Z2Y7, Phone Number:','New York','10116',NULL),(48,'9AC182ADA2D0','Sai dd','DUMMY357@GMAIL.COM',42.00,'stripe','pending',NULL,'2025-11-06 07:48:32','2025-11-06 07:48:32','124 Main ST, Montreal, Province: Quebec, Postal Code: H3Z2Y7, Phone Number:','New York','10116',NULL),(49,'9C6A46F2105C','Sai dd','DUMMY357@GMAIL.COM',84.00,'stripe','pending',NULL,'2025-11-06 07:52:18','2025-11-06 07:52:18','124 Main ST, Montreal, Province: Quebec, Postal Code: H3Z2Y7, Phone Number:','New York','10116',NULL),(50,'AB76A10620C0','Sai dd','DUMMY357@GMAIL.COM',20.00,'stripe','pending',NULL,'2025-11-06 08:00:50','2025-11-06 08:00:50','124 Main ST, Montreal, Province: Quebec, Postal Code: H3Z2Y7, Phone Number:','New York','10116',NULL),(51,'C5881FE78EE2','Sai dd','DUMMY357@GMAIL.COM',42.00,'stripe','pending',NULL,'2025-11-06 08:07:53','2025-11-06 08:07:53','124 Main ST, Montreal, Province: Quebec, Postal Code: H3Z2Y7, Phone Number:','New York','10116',NULL),(52,'8B32EB573E7C','Sai dd','DUMMY357@GMAIL.COM',42.00,'stripe','pending',NULL,'2025-11-06 08:10:37','2025-11-06 08:10:37','124 Main ST, Montreal, Province: Quebec, Postal Code: H3Z2Y7, Phone Number:','New York','10116',NULL),(53,'B0835E5EB4F8','Sai dd','DUMMY357@GMAIL.COM',20.00,'stripe','pending',NULL,'2025-11-06 08:18:01','2025-11-06 08:18:01','124 Main ST, Montreal, Province: Quebec, Postal Code: H3Z2Y7, Phone Number:','New York','10116',NULL),(54,'1A80930E825E','Sai dd','DUMMY357@GMAIL.COM',20.00,'stripe','pending',NULL,'2025-11-06 08:22:05','2025-11-06 08:22:05','124 Main ST, Montreal, Province: Quebec, Postal Code: H3Z2Y7, Phone Number:','New York','10116',NULL),(55,'D58D10E40B10','Sai dd','DUMMY357@GMAIL.COM',80.00,'stripe','paid','pi_3SQOYjFqmeKxNjQe1cavAs9v','2025-11-06 08:28:36','2025-11-06 11:29:58','124 Main ST, Montreal, Province: Quebec, Postal Code: H3Z2Y7, Phone Number:','New York','10116',NULL),(56,'9D748B6C9AB4','Sai dd','DUMMY357@GMAIL.COM',20.00,'stripe','paid','pi_3SQOabFqmeKxNjQe14huh2hw','2025-11-06 08:30:33','2025-11-06 11:33:54','124 Main ST, Montreal, Province: Quebec, Postal Code: H3Z2Y7, Phone Number:','New York','10116',NULL),(57,'3AB0970C26A6','Sai dd','DUMMY357@GMAIL.COM',150.00,'stripe','pending',NULL,'2025-11-06 10:04:55','2025-11-06 10:04:55','124 Main ST, Montreal, Province: Quebec, Postal Code: H3Z2Y7, Phone Number:','New York','10116',NULL),(58,'39416E0405E0','Sai dd','DUMMY357@GMAIL.COM',80.00,'stripe','pending',NULL,'2025-11-06 10:08:41','2025-11-06 10:08:41','124 Main ST, Montreal, Province: Quebec, Postal Code: H3Z2Y7, Phone Number:','New York','10116',NULL),(59,'841E1C2B7EDE','Sai dd','DUMMY357@GMAIL.COM',80.00,'stripe','pending',NULL,'2025-11-06 10:08:52','2025-11-06 10:08:52','124 Main ST, Montreal, Province: Quebec, Postal Code: H3Z2Y7, Phone Number:','New York','10116',NULL),(60,'43FD342A5D1C','Sai dd','DUMMY357@GMAIL.COM',42.00,'stripe','paid','pi_3SQQD0FqmeKxNjQe1oFx7Fag','2025-11-06 10:14:01','2025-11-06 11:14:56','124 Main ST, Montreal, Province: Quebec, Postal Code: H3Z2Y7, Phone Number:','New York','10116',NULL),(61,'FD40F467EB21','Sai dd','DUMMY357@GMAIL.COM',20.00,'stripe','paid','pi_3SQQH0FqmeKxNjQe0vpAyonv','2025-11-06 10:18:25','2025-11-06 11:19:05','124 Main ST, Montreal, Province: Quebec, Postal Code: H3Z2Y7, Phone Number:','New York','10116',NULL),(63,'EEB54C93B199','Sai dd','DUMMY357@GMAIL.COM',20.00,'stripe','paid','pi_3SQR8NFqmeKxNjQe1MJXfaFQ','2025-11-06 11:13:34','2025-11-06 11:13:44','124 Main ST, Montreal, Province: Quebec, Postal Code: H3Z2Y7, Phone Number:','New York','10116',NULL),(64,'5761DB1BC5A0','Musthafa Shaik','musthafa.shaik@gmai.com',166.00,'stripe','paid','pi_3SQROKFqmeKxNjQe1Tlzi1J2','2025-11-06 11:29:47','2025-11-06 11:30:13','7/4321-1','Gannavarm','521101',NULL),(65,'EFB530BDCD13','Sai dd','DUMMY357@GMAIL.COM',150.00,'stripe','failed',NULL,'2025-11-06 11:40:03','2025-11-06 11:40:10','124 Main ST, Montreal, Province: Quebec, Postal Code: H3Z2Y7, Phone Number:','New York','10116',NULL),(66,'EC028AED520E','saleem','saleem@gmail.com',42.00,'stripe','pending',NULL,'2025-11-06 11:50:11','2025-11-06 11:50:11','7/4321-1','Gannavarm','521101',NULL),(67,'67D8FA929E0A','Sai dd','DUMMY357@GMAIL.COM',150.00,'stripe','pending',NULL,'2025-11-06 11:50:35','2025-11-06 11:50:35','124 Main ST, Montreal, Province: Quebec, Postal Code: H3Z2Y7, Phone Number:','New York','10116',NULL),(68,'794A79403688','Sai dd','DUMMY357@GMAIL.COM',140.00,'stripe','failed',NULL,'2025-11-07 03:58:07','2025-11-07 03:58:17','124 Main ST, Montreal, Province: Quebec, Postal Code: H3Z2Y7, Phone Number:','New York','10116',NULL),(69,'09C2884A18C4','Musthafa Shaik','musthafa.shaik@gmai.com',150.00,'stripe','paid','pi_3SQhg2FqmeKxNjQe0bytOcSP','2025-11-07 04:53:24','2025-11-07 04:53:36','7/4321-1','Gannavarm','521101',NULL),(70,'A3FDBCDD47EF','Musthafa Shaik','musthafa.shaik@gmai.com',150.00,'stripe','paid','pi_3SQiI0FqmeKxNjQe04odBcSP','2025-11-07 05:32:15','2025-11-07 05:32:50','7/4321-1','Gannavarm','521101',NULL),(71,'B28C2B3E1A74','Sai dd','DUMMY357@GMAIL.COM',20.00,'stripe','failed',NULL,'2025-11-07 05:36:44','2025-11-07 06:07:34','124 Main ST, Montreal, Province: Quebec, Postal Code: H3Z2Y7, Phone Number:','New York','10116',NULL),(72,'A8A6542AC56D','Musthafa Shaik','musthafa.shaik@gmai.com',750.00,'stripe','paid','pi_3SQisoFqmeKxNjQe0pEBtPYc','2025-11-07 06:08:36','2025-11-07 10:31:43','7/4321-1','Gannavarm','521101',NULL),(73,'F2A1D1F15CD7','Sai dd','DUMMY357@GMAIL.COM',20.00,'stripe','failed',NULL,'2025-11-07 10:39:06','2025-11-07 10:44:02','124 Main ST, Montreal, Province: Quebec, Postal Code: H3Z2Y7, Phone Number:','New York','10116',NULL),(74,'27F4B98C6DA8','Musthafa Shaik','musthafa.shaik@gmai.com',150.00,'stripe','paid','pi_3SQnINFqmeKxNjQe1wfSVbaH','2025-11-07 10:53:15','2025-11-07 10:53:32','7/4321-1','Gannavarm','521101',NULL),(75,'27411BABEF50','Musthafa Shaik','musthafa.shaik000@gmai.com',80.00,'stripe','paid','pi_3SQnP6FqmeKxNjQe1KEAenSJ','2025-11-07 11:00:21','2025-11-07 11:00:29','7/4321-1','Gannavarm','521101',NULL),(76,'51626553CC93','Sai dd','DUMMY357@GMAIL.COM',162.00,'stripe','paid','pi_3SQnpEFqmeKxNjQe0BTxg5DC','2025-11-07 11:21:05','2025-11-07 11:27:29','124 Main ST, Montreal, Province: Quebec, Postal Code: H3Z2Y7, Phone Number:','New York','10116',NULL),(77,'2C95E1514526','Musthafa Shaik','musthafa.shaik@gmai.com',20.00,'stripe','failed',NULL,'2025-11-10 09:15:31','2025-11-10 09:18:54','7/4321-1','Gannavarm','521101',NULL),(78,'86CF834CAA4D','Musthafa Shaik','musthafa.shaik@gmai.com',168.00,'stripe','pending',NULL,'2025-11-10 09:28:07','2025-11-10 09:28:07','7/4321-1','Gannavarm','521101',NULL),(79,'3828B15F0AE1','Musthafa Shaik','musthafa.shaik@chandusoft.com',170.00,'stripe','paid','pi_3SRt3iFqmeKxNjQe0JZPQDis','2025-11-10 11:14:49','2025-11-10 11:15:00','7/4321-1','Gannavarm','521101',NULL);
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pages`
--

DROP TABLE IF EXISTS `pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `status` enum('draft','published','archived') DEFAULT 'draft',
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `content_html` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pages`
--

LOCK TABLES `pages` WRITE;
/*!40000 ALTER TABLE `pages` DISABLE KEYS */;
INSERT INTO `pages` VALUES (3,'Public Services','contact-services','archived','2025-10-28 09:49:00','Our Contact Service available 24/7. '),(11,'Services','services','draft','2025-10-24 12:23:47','<!DOCTYPE html>\r\n<html lang=\"en\">\r\n<head>\r\n    <link rel=\"stylesheet\" href=\"/styles.css\">\r\n    <link rel=\"stylesheet\" href=\"https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css\">\r\n     <meta charset=\"UTF-8\">\r\n     <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\r\n    <title>Chandusoft</title>\r\n    <link rel=\"stylesheet\" href=\"styles.css\">\r\n</head>\r\n<body>\r\n     <div id=\"header\"></div>\r\n     <?php include(\"header.php\"); ?>\r\n    <main>\r\n<section id=\"Services\">\r\n    <h2 style=\"color: #2d5be3;\"></h2>\r\n    <div class=\"services-container\">\r\n\r\n        <div class=\"service-card\">\r\n            <i class=\"fas fa-building icon-blue\"></i>\r\n            <h3>Enterprise Application Solution</h3>\r\n            <p>Robust enterprise apps for seamless business operations.</p>\r\n        </div>\r\n\r\n        <div class=\"service-card\">\r\n            <i class=\"fas fa-mobile-alt icon-green\"></i>\r\n            <h3>Mobile Application Solution</h3>\r\n            <p>Cross-platform mobile apps with modern UI/UX.</p>\r\n        </div>\r\n\r\n        <div class=\"service-card\">\r\n            <i class=\"fas fa-laptop icon-black\"></i>\r\n            <h3>Web Portal Design & Solution</h3>\r\n            <p>Custom web portals for business and customer engagement.</p>\r\n        </div>\r\n\r\n        <div class=\"service-card\">\r\n            <i class=\"fas fa-tools icon-yellow\"></i>\r\n            <h3>Web Portal Maintenance & Content Management</h3>\r\n            <p>Continuous support, updates, and content handling.</p>\r\n        </div>\r\n\r\n        <div class=\"service-card\">\r\n            <i class=\"fas fa-vial icon-purple\"></i>\r\n            <h3>QA & Testing</h3>\r\n            <p>Quality assurance and testing for bug-free releases.</p>\r\n        </div>\r\n\r\n        <div class=\"service-card\">\r\n            <i class=\"fas fa-phone icon-red\"></i>\r\n            <h3>Business Process Outsourcing</h3>\r\n            <p>End-to-end BPO services with 24/7 operations.</p>\r\n        </div>\r\n\r\n    </div>\r\n</section>\r\n</main>\r\n    <div id=\"footer\"></div>\r\n    <?php include(\"footer.php\"); ?>\r\n <script src=\"include.js\"></script>\r\n   \r\n</body>\r\n</html>\r\n'),(12,'Deals','deals','published','2025-10-27 11:09:28','We Provide Best Deals Day By Day '),(13,'FAQ','faq','archived','2025-11-03 16:11:54','Hello '),(14,'services1','services1','archived','2025-10-28 09:47:46','mobile applications'),(15,'services2','services3','archived','2025-10-29 13:12:15',''),(16,'Sample','Sample','archived','2025-10-29 13:16:42','Nothing'),(17,'Sample1','sample1','draft','2025-10-29 14:14:30','Sample'),(18,'z','z','archived','2025-11-10 16:32:21','Operation Z[fauzi]');
/*!40000 ALTER TABLE `pages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `site_settings`
--

DROP TABLE IF EXISTS `site_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `site_settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL,
  `value` text NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `site_settings`
--

LOCK TABLES `site_settings` WRITE;
/*!40000 ALTER TABLE `site_settings` DISABLE KEYS */;
INSERT INTO `site_settings` VALUES (1,'site_name','Chandusoft','2025-10-23 07:26:02','2025-11-04 10:53:34'),(2,'logo_path','uploads/logo_1761292683.jpg','2025-10-23 07:26:02','2025-10-24 07:58:03');
/*!40000 ALTER TABLE `site_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `role` enum('admin','editor') NOT NULL DEFAULT 'editor',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin@example.com','admin','$2y$10$CPk2b70fz3Ev0y7DF1lrdu2D89PNvNY0PDo4MD6qr2UkAEtk6xshC','2025-10-03 09:50:35','admin'),(8,'musthafa.shaik@gmai.com','Musthafa','$2y$10$1lIhm20WEB/rMa2f.NUfrOoSpz8zFdaIkG3l1ZS3OrzDdRbaFrw8m','2025-10-03 11:20:04','editor'),(10,'musthafa.shaik999@gmai.com','sk musthafa','$2y$10$bJDTQvObz0bkf.yAo2E54ecRg8VKJxvm.2oBZI5pwEzc/mIq3Zlo.','2025-10-04 03:56:28','editor'),(11,'jaisai@gmail.com','jaisai','$2y$10$PbRdYwq2T7TbyefxL9Z88OJUBU3SLYTSHrefBQ8OAQktxmwxyMYiu','2025-10-04 10:26:31','editor'),(12,'saleem@gmail.com','saleem','$2y$10$x2AEl4c87X3uQIw3CxaNqeQ3gIwUy6yIh4ebJgyC6Lenm6Y2eCofy','2025-10-04 10:33:10','editor'),(13,'jafar@gmail.com','jafar','$2y$10$Uq5rliaPbqxO2Nq/maaC4.H.I2lJ8jmwP0We.oN/OIJk8FZxbiS7q','2025-10-04 10:43:15','admin'),(14,'user@gmail.com','user','$2y$10$.3Ne6OU9ErNKhrKTB2CZqeZCNU2nV1qRG5PrLMrisixK5.APEc.kW','2025-10-06 11:14:58','editor'),(15,'musthafa.shaik@chandusoft.com','musthafa','$2y$10$WWrMvHkA5Nh4.FUhB0fduuIYQk3k2vMvYxJKTYybFgMA.qbNyUiHy','2025-10-07 09:00:53','admin'),(16,'Basha@gmail.com','Basha','$2y$10$T.C9A8Tk04FfUgzcphIdrOcvkedH6ElJl0zW2d7lvsJg0Ied70io2','2025-11-05 03:58:17','editor');
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

-- Dump completed on 2025-11-10 16:46:02
