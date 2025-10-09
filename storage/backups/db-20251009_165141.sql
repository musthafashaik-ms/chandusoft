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
) ENGINE=InnoDB AUTO_INCREMENT=80 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `leads`
--

LOCK TABLES `leads` WRITE;
/*!40000 ALTER TABLE `leads` DISABLE KEYS */;
INSERT INTO `leads` VALUES (1,'Musthafa Shaik','musthafa.shaik@gmai.com','Hello','2025-10-04 06:21:17',NULL),(2,'Nagur Basha','nb@gmail.com','Hero','2025-10-04 06:24:13',NULL),(3,'Haseena','h@gmail.com','Queen','2025-10-04 06:25:44',NULL),(4,'Jafar','j@gmail.com','Strong','2025-10-04 06:26:12',NULL),(5,'Mohammad','m@gmail.com','Teacher','2025-10-04 06:27:13',NULL),(6,'Abu Bakr','ab@gmail.com','Favorite','2025-10-04 06:28:18',NULL),(7,'Omar','o@gmail.com','Fear','2025-10-04 06:30:05',NULL),(8,'Ali','a@gmail.com','Faith','2025-10-04 06:30:25',NULL),(9,'Michael Scott','michael@example.com','Can you provide more details about your services?','2025-10-04 07:06:14',NULL),(10,'Pam Beesly','pam@example.com','I am interested in collaboration','2025-10-04 07:07:05',NULL),(11,'Jim Halpert','jim@example.com','Please send me a pricing list.','2025-10-04 07:07:47',NULL),(12,'Dwight Schrute','dwight@example.com','Looking for bulk services.','2025-10-04 07:08:58',NULL),(13,'Angela Martin','angela@example.com','Can we schedule a meeting?','2025-10-04 07:09:49',NULL),(14,'Kevin Malone','kevin@example.com','Need more information about your products.\'','2025-10-04 07:10:50',NULL),(15,'Oscar Martine','oscar@example.com','I have some questions before signing up','2025-10-04 07:11:35',NULL),(16,'Stanley Hudson','stanley@example.com','Please contact me soon.','2025-10-04 07:12:24',NULL),(17,'Phyllis Vance','phyllis@example.com','Interested in a long-term contract.','2025-10-04 07:13:04',NULL),(18,'Meredith Palmer','meredith@example.com','Can I get a quotation?','2025-10-04 07:13:47',NULL),(19,'phani','phani@gmail.com','Hero','2025-10-06 06:30:26',NULL),(20,'chaitanya','chaitanya@gmail.com','hi','2025-10-06 07:21:38',NULL),(21,'Musthafa Shaik','musthafa.shaik@gmai.com','KK','2025-10-06 10:51:18',NULL),(22,'user','user@gmail.com','Editor','2025-10-06 11:12:57',NULL),(32,'Sameer Md','sameermd@gmail.com','Hi welcome!!!','2025-10-07 06:32:59',NULL),(33,'phani','kumar@gmail.com','hii','2025-10-07 06:38:05',NULL),(34,'phani','kumar@gmail.com','hii','2025-10-07 06:38:50',NULL),(35,'saleem','saleem12@gmail.com','Saleem Bashaa....','2025-10-07 07:12:21',NULL),(37,'jaisai','sai@gmail.com','sai bhai','2025-10-07 07:42:59',NULL),(42,'Musthafa Shaik','shaik@gmail.com','Created by musthafa','2025-10-07 08:52:44',NULL),(43,'Musthafa Shaik','shaik@gmail.com','Created by musthafa','2025-10-07 08:52:46',NULL),(44,'musthafa','musthafa.shaik@chandusoft.com','g','2025-10-07 09:02:18',NULL),(45,'Sameer ','sameer0@gmail.com','zero','2025-10-07 09:06:08',NULL),(46,'saleemm','saleemm@gmail.com','hi','2025-10-07 10:12:49',NULL),(47,'kk','k@gmail.com','k','2025-10-07 10:41:58',NULL),(52,'aa','aa@gmail.com','aa','2025-10-07 10:49:23',NULL),(53,'aa','DUMMY357@GMAIL.COM','rghtr','2025-10-07 10:50:06',NULL),(55,'cc','DUMMY357@GMAIL.COM','fde','2025-10-07 10:52:38',NULL),(57,'musthafa','musthafa.shaik63@gmai.com','Boom','2025-10-07 11:02:18',NULL),(61,'Musthafa Shaik','sample123@gmail.com','Hey','2025-10-07 11:08:39',NULL),(62,' Shaik','s@gmail.com','hello','2025-10-07 11:15:52',NULL),(67,'Shaik','sk@gmail.com','Hello','2025-10-07 11:19:25',NULL),(70,'Sai dd','sai258@gmail.com','\\asdfevgf','2025-10-07 11:31:51',NULL),(71,'fg','DUMMY357@GMAIL.COM','dgfd','2025-10-07 11:33:09',NULL),(73,'asfg','fgdg@gmail.com','hsdghtgh','2025-10-07 11:34:02',NULL),(75,'Shaik Musthafa','musthafa.shaik01@gmail.com','Good Evening Sir','2025-10-07 11:48:25',NULL),(76,'zz','z@gmail.com','z+','2025-10-08 03:54:44',NULL),(78,'Musthafa Shaik','DUMMY357@GMAIL.COM','ytjt','2025-10-08 04:25:35',NULL),(79,'Musthafa Shaik','m@gmail.com','ms','2025-10-08 05:33:46',NULL);
/*!40000 ALTER TABLE `leads` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pages`
--

LOCK TABLES `pages` WRITE;
/*!40000 ALTER TABLE `pages` DISABLE KEYS */;
INSERT INTO `pages` VALUES (3,'Public Services','contact-services','published','2025-10-09 10:21:01','Our Contact Service available 24/7. '),(11,'Services','services','draft','2025-10-09 10:17:10','Good Service will provide '),(12,'Deals','aboutus','published','2025-10-09 10:20:30','We Provide Best Deals Day By Day '),(13,'FAQ','faq','published','2025-10-09 10:33:25','');
/*!40000 ALTER TABLE `pages` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin@example.com','admin','$2y$10$CPk2b70fz3Ev0y7DF1lrdu2D89PNvNY0PDo4MD6qr2UkAEtk6xshC','2025-10-03 09:50:35','admin'),(8,'musthafa.shaik@gmai.com','Musthafa','$2y$10$1lIhm20WEB/rMa2f.NUfrOoSpz8zFdaIkG3l1ZS3OrzDdRbaFrw8m','2025-10-03 11:20:04','editor'),(10,'musthafa.shaik999@gmai.com','sk musthafa','$2y$10$bJDTQvObz0bkf.yAo2E54ecRg8VKJxvm.2oBZI5pwEzc/mIq3Zlo.','2025-10-04 03:56:28','editor'),(11,'jaisai@gmail.com','jaisai','$2y$10$PbRdYwq2T7TbyefxL9Z88OJUBU3SLYTSHrefBQ8OAQktxmwxyMYiu','2025-10-04 10:26:31','editor'),(12,'saleem@gmail.com','saleem','$2y$10$x2AEl4c87X3uQIw3CxaNqeQ3gIwUy6yIh4ebJgyC6Lenm6Y2eCofy','2025-10-04 10:33:10','editor'),(13,'jafar@gmail.com','jafar','$2y$10$Uq5rliaPbqxO2Nq/maaC4.H.I2lJ8jmwP0We.oN/OIJk8FZxbiS7q','2025-10-04 10:43:15','admin'),(14,'user@gmail.com','user','$2y$10$.3Ne6OU9ErNKhrKTB2CZqeZCNU2nV1qRG5PrLMrisixK5.APEc.kW','2025-10-06 11:14:58','editor'),(15,'musthafa.shaik@chandusoft.com','musthafa','$2y$10$WWrMvHkA5Nh4.FUhB0fduuIYQk3k2vMvYxJKTYybFgMA.qbNyUiHy','2025-10-07 09:00:53','admin');
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

-- Dump completed on 2025-10-09 16:51:46
