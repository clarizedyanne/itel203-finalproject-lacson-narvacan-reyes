-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: olis_coffee
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `menu_items`
--

DROP TABLE IF EXISTS `menu_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `menu_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `category` varchar(100) NOT NULL,
  `subcategory` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `price_variant` varchar(50) DEFAULT NULL,
  `is_available` tinyint(1) DEFAULT 1,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=183 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `menu_items`
--

LOCK TABLES `menu_items` WRITE;
/*!40000 ALTER TABLE `menu_items` DISABLE KEYS */;
INSERT INTO `menu_items` VALUES (1,'Yangnyeom (Spicy Korean)','Main','For Sharing','Flavored Boneless Chicken Bites',279.00,'',1,'dish_69f2de37464234.99418482.png','2026-04-28 06:16:05','2026-04-30 04:44:39'),(2,'Garlic Parmesan','Main','For Sharing','Flavored Boneless Chicken Bites',279.00,'',1,'dish_69f054608513d3.57680336.png','2026-04-28 06:16:05','2026-04-28 06:32:00'),(3,'Hickory Barbecue','Main','For Sharing','Flavored Boneless Chicken Bites',279.00,'',1,'dish_69f2de1bc6af68.67709395.png','2026-04-28 06:16:05','2026-04-30 04:44:11'),(4,'Spicy Salted Egg','Main','For Sharing','Flavored Boneless Chicken Bites',279.00,'',1,'dish_69f2de2921c7b0.38680629.png','2026-04-28 06:16:05','2026-04-30 04:44:25'),(5,'Chicken Fingers w/ Rice','Main','Rice Meal','Served with buttered vegetables',189.00,'',1,'dish_69f2dd9960ef06.42219870.png','2026-04-28 06:16:05','2026-04-30 04:42:01'),(6,'Burger Steak w/ Egg','Main','Rice Meal','Served with buttered vegetables',194.00,'',1,'dish_69f2da3f69c145.07567983.png','2026-04-28 06:16:05','2026-04-30 04:28:05'),(7,'2pcs Grilled Porkchop w/ Mushroom Gravy','Main','Rice Meal','Served with buttered vegetables',214.00,'',1,'dish_69f2dd656eba65.40028283.png','2026-04-28 06:16:05','2026-04-30 04:41:09'),(8,'Chicken Fillet Ala King','Main','Rice Meal','Served with buttered vegetables',199.00,'',1,'dish_69f2dd9070a332.21769160.png','2026-04-28 06:16:05','2026-04-30 04:41:52'),(9,'Breaded Porkchop w/ Egg','Main','Rice Meal','Served with buttered vegetables',194.00,'',1,'dish_69f2dd84bfaac2.12768790.png','2026-04-28 06:16:05','2026-04-30 04:41:40'),(10,'Fish Fillet w/ Rice in Tartar Sauce','Main','Rice Meal','Served with buttered vegetables',194.00,'',1,'dish_69f2dda43f2752.88246953.png','2026-04-28 06:16:05','2026-04-30 04:42:12'),(11,'Flavored Chicken Bites w/ Rice','Main','Rice Meal','Yangnyeom, Garlic Parmesan, Hickory BBQ, Spicy Salted Egg',199.00,'',1,'dish_69f2ddb0939cf3.20980923.png','2026-04-28 06:16:05','2026-04-30 04:42:24'),(12,'4pcs Chicken Wings w/ Rice','Main','Rice Meal','Yangnyeom, Garlic Parmesan, Hickory BBQ, Spicy Salted Egg',199.00,'',1,'dish_69f2dd76c8e797.74696707.png','2026-04-28 06:16:05','2026-04-30 04:41:26'),(13,'Yangnyeom Wings (Spicy Korean) 6pcs','Main','Chicken Wings','Flavored Chicken Wings',239.00,'6pcs',1,'dish_69f2a6c2662d62.65184370.png','2026-04-28 06:16:05','2026-04-30 00:52:50'),(14,'Garlic Parmesan Wings','Main','Chicken Wings','Flavored Chicken Wings',239.00,'6pcs',1,'dish_69f2dde4d2a752.66117791.png','2026-04-28 06:16:05','2026-04-30 04:43:16'),(15,'Hickory Barbecue Wings 6pcs','Main','Chicken Wings','Flavored Chicken Wings',239.00,'6pcs',1,'dish_69f2a6a7cfb3f1.56741678.png','2026-04-28 06:16:05','2026-04-30 00:52:07'),(16,'Spicy Salted Egg Wings 6pcs','Main','Chicken Wings','Flavored Chicken Wings',239.00,'6pcs',1,'dish_69f2a6b4a42523.02972511.png','2026-04-28 06:16:05','2026-04-30 00:52:30'),(17,'Yangnyeom Wings (Spicy Korean) 12pcs','Main','Chicken Wings','Flavored Chicken Wings',459.00,'12pcs',1,'dish_69f2a6bb63f025.41837652.png','2026-04-28 06:16:05','2026-04-30 00:52:44'),(18,'Garlic Parmesan Wings 12pcs','Main','Chicken Wings','Flavored Chicken Wings',459.00,'12pcs',1,'dish_69f2a6ca44a513.40589184.png','2026-04-28 06:16:05','2026-04-30 00:49:47'),(19,'Hickory Barbecue Wings 12pcs','Main','Chicken Wings','Flavored Chicken Wings',459.00,'12pcs',1,'dish_69f2a68a8ed776.16798591.png','2026-04-28 06:16:05','2026-04-30 00:51:56'),(20,'Spicy Salted Egg Wings 12pcs','Main','Chicken Wings','Flavored Chicken Wings',459.00,'12pcs',1,'dish_69f2a6ad9a2d21.06703426.png','2026-04-28 06:16:05','2026-04-30 00:52:13'),(21,'Nachos','Snacks','Snacks','',198.00,'',1,'dish_69f2de86458a82.62569481.png','2026-04-28 06:16:05','2026-04-30 04:45:58'),(22,'Chicken Fingers','Snacks','Snacks','',189.00,'',1,'dish_69f2de616d2526.90095475.png','2026-04-28 06:16:05','2026-04-30 04:45:21'),(23,'Cheesy Bacon Fries','Snacks','Snacks','',198.00,'',1,'dish_69f2de56833a85.07179254.png','2026-04-28 06:16:05','2026-04-30 04:45:10'),(24,'Fish & Fries','Snacks','Snacks','',198.00,'',1,'dish_69f2de6d7c0d43.76552189.png','2026-04-28 06:16:05','2026-04-30 04:45:33'),(25,'Flavored Fries','Snacks','Snacks','Barbecue · Cheese · Sour Cream',159.00,'',1,'dish_69f2de7513a410.44591278.png','2026-04-28 06:16:05','2026-04-30 04:45:41'),(26,'Flavored Mojos','Snacks','Snacks','Barbecue · Cheese · Sour Cream',189.00,'',1,'dish_69f2de7c676605.70863540.png','2026-04-28 06:16:05','2026-04-30 04:45:48'),(27,'Gourmet Tuyo Pasta','Pasta','Pasta','Served with Garlic Bread',189.00,'',1,'dish_69f2deb1422309.23695228.png','2026-04-28 06:16:05','2026-04-30 04:46:41'),(28,'Alfredo (White Sauce)','Pasta','Pasta','Served with Garlic Bread',194.00,'',1,'dish_69f2de9c264902.97304238.png','2026-04-28 06:16:05','2026-04-30 04:46:20'),(29,'Meat Sauce Spaghetti','Pasta','Pasta','Served with Garlic Bread',189.00,'',1,'dish_69f2debd8d74c9.20103159.png','2026-04-28 06:16:05','2026-04-30 04:46:53'),(30,'Lasagna','Pasta','Pasta','Served with Garlic Bread',194.00,'',1,'dish_69f2deb77174b5.29777119.png','2026-04-28 06:16:05','2026-04-30 04:46:47'),(31,'Aligue Pasta','Pasta','Pasta','Served with Garlic Bread',189.00,'',1,'dish_69f2dea4e694f8.29223643.png','2026-04-28 06:16:05','2026-04-30 04:46:28'),(32,'Shrimp Aglio Olio','Pasta','Pasta','Served with Garlic Bread',194.00,'',1,'dish_69f2dec94700c2.98994513.png','2026-04-28 06:16:05','2026-04-30 04:47:05'),(33,'Chicken Oriental Pasta','Pasta','Pasta','Served with Garlic Bread',189.00,'',1,'dish_69f2deab5135e5.29085966.png','2026-04-28 06:16:05','2026-04-30 04:46:35'),(34,'Pulled Pork BBQ','Burgers','Burgers/Sandwiches','Served with Fries',189.00,'',1,'dish_69f2df0196afe7.11688839.png','2026-04-28 06:16:05','2026-04-30 04:48:01'),(35,'Dori Fish Burger','Burgers','Burgers/Sandwiches','Served with Fries',189.00,'',1,'dish_69f2def9a88d91.29937984.png','2026-04-28 06:16:05','2026-04-30 04:47:53'),(36,'Cheeseburger','Burgers','Burgers/Sandwiches','Served with Fries',194.00,'',1,'dish_69f2dedfaa3d78.84239321.png','2026-04-28 06:16:05','2026-04-30 04:47:27'),(37,'Bacon Cheeseburger','Burgers','Burgers/Sandwiches','Served with Fries',209.00,'',1,'dish_69f2ded9dbe266.53144214.png','2026-04-28 06:16:05','2026-04-30 04:47:21'),(38,'Crispy Chicken Burger','Burgers','Burgers/Sandwiches','Served with Fries',194.00,'',1,'dish_69f2def0044977.57483279.png','2026-04-28 06:16:05','2026-04-30 04:47:44'),(39,'Clubhouse Sandwich','Burgers','Burgers/Sandwiches','Served with Fries',194.00,'',1,'dish_69f2dee88b9054.96557693.png','2026-04-28 06:16:05','2026-04-30 04:47:36'),(40,'Macaroni Salad','Salads','Salads','',169.00,'',1,'dish_69f2df224247b2.93952986.png','2026-04-28 06:16:06','2026-04-30 04:48:34'),(41,'Kani Salad','Salads','Salads','Lettuce, Cucumber, Carrots, Mango, Crab Sticks, Roasted Sesame dressing',189.00,'',1,'dish_69f2df18bdeb57.10392158.png','2026-04-28 06:16:06','2026-04-30 04:48:24'),(42,'Chicken Caesar Salad','Salads','Salads','Romaine Lettuce, Chicken breast, Croutons, Parmesan, Caesar dressing, bacon bits',209.00,'',1,'dish_69f2df123f1f10.14908855.png','2026-04-28 06:16:06','2026-04-30 04:48:18'),(43,'All Cheese','Pizza','Classic','New York Style',329.00,'12\"',1,'dish_69f2df31474ea9.91277407.png','2026-04-28 06:16:06','2026-04-30 04:48:49'),(44,'All Cheese','Pizza','Classic','New York Style',449.00,'16\"',1,'dish_69f2df59747447.87687871.png','2026-04-28 06:16:06','2026-04-30 04:49:29'),(45,'American Ham and Cheese','Pizza','Classic','New York Style',349.00,'12\"',1,'dish_69f2dfd09ee7b2.55194360.png','2026-04-28 06:16:06','2026-04-30 04:51:28'),(46,'American Ham and Cheese','Pizza','Classic','New York Style',469.00,'16\"',1,'dish_69f2dfd667cf03.85084290.png','2026-04-28 06:16:06','2026-04-30 04:51:34'),(47,'Hawaiian','Pizza','Classic','New York Style',359.00,'12\"',1,'dish_69f2dfe0e907e1.53704204.png','2026-04-28 06:16:06','2026-04-30 04:51:44'),(48,'Hawaiian','Pizza','Classic','New York Style',479.00,'16\"',1,'dish_69f2dfe6cc13a2.25063311.png','2026-04-28 06:16:06','2026-04-30 04:51:50'),(49,'New York\'s Pepperoni','Pizza','Premium','New York Style',389.00,'12\"',1,'dish_69f2e084835638.17935284.png','2026-04-28 06:16:06','2026-04-30 04:54:28'),(50,'New York\'s Pepperoni','Pizza','Premium','New York Style',499.00,'16\"',1,'dish_69f2e08d08e4b0.21268937.png','2026-04-28 06:16:06','2026-04-30 04:54:37'),(51,'Hawaiian Supreme','Pizza','Premium','New York Style',399.00,'12\"',1,'dish_69f2e06a629708.16160002.png','2026-04-28 06:16:06','2026-04-30 04:54:02'),(52,'Hawaiian Supreme','Pizza','Premium','New York Style',509.00,'16\"',1,'dish_69f2e076e8b069.48416172.png','2026-04-28 06:16:06','2026-04-30 04:54:14'),(53,'All Meat','Pizza','Premium','New York Style',399.00,'12\"',1,'dish_69f2e03ba71046.05906238.png','2026-04-28 06:16:06','2026-04-30 04:53:15'),(54,'All Meat','Pizza','Premium','New York Style',509.00,'16\"',1,'dish_69f2e04b224ab1.05807393.png','2026-04-28 06:16:06','2026-04-30 04:53:31'),(55,'New York\'s Special','Pizza','Classic','Everything on it',399.00,'12\"',1,'dish_69f2f461438c33.87464870.png','2026-04-28 06:16:06','2026-04-30 06:19:13'),(56,'New York\'s Special','Pizza','Premium','Everything on it',509.00,'16\"',1,'dish_69f2e09f1c1a30.76174959.png','2026-04-28 06:16:06','2026-04-30 04:54:55'),(59,'Pulled Pork BBQ Pizza','Pizza','Premium','New York Style',399.00,'12\"',1,'dish_69f2e0a995aa43.34912773.png','2026-04-28 06:16:06','2026-04-30 04:55:05'),(60,'Pulled Pork BBQ Pizza','Pizza','Premium','New York Style',509.00,'16\"',1,'dish_69f2e0b4ce8ac9.17823777.png','2026-04-28 06:16:06','2026-04-30 04:55:16'),(61,'4 Cheese Pizza','Pizza','Latest Special','New York Style',409.00,'12\"',1,'dish_69f2dff1214634.66764532.png','2026-04-28 06:16:06','2026-04-30 04:52:18'),(62,'4 Cheese Pizza','Pizza','Latest Special','New York Style',529.00,'16\"',1,'dish_69f2e00a2435e1.64634964.png','2026-04-28 06:16:06','2026-04-30 04:52:26'),(63,'Garlic Shrimp Pizza','Pizza','Latest Special','New York Style',409.00,'12\"',1,'dish_69f2e013d47fd8.98560717.png','2026-04-28 06:16:06','2026-04-30 04:52:35'),(64,'Garlic Shrimp Pizza','Pizza','Latest Special','New York Style',529.00,'16\"',1,'dish_69f2e02f842871.20586270.png','2026-04-28 06:16:06','2026-04-30 04:53:03'),(65,'Pearl Milk Tea','Drinks','Artisan Tea','Free Pearl Sinker',95.00,'16oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(66,'Pearl Milk Tea','Drinks','Artisan Tea','Free Pearl Sinker',105.00,'22oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(67,'Earl Grey Milk Tea','Drinks','Artisan Tea','Free Pearl Sinker',105.00,'16oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(68,'Earl Grey Milk Tea','Drinks','Artisan Tea','Free Pearl Sinker',115.00,'22oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(69,'Ceylon Milk Tea','Drinks','Artisan Tea','Free Pearl Sinker',105.00,'16oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(70,'Ceylon Milk Tea','Drinks','Artisan Tea','Free Pearl Sinker',115.00,'22oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(71,'Sun Moon Milk Tea','Drinks','Artisan Tea','Free Pearl Sinker',105.00,'16oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(72,'Sun Moon Milk Tea','Drinks','Artisan Tea','Free Pearl Sinker',115.00,'22oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(73,'Jasmine Milk Tea','Drinks','Artisan Tea','Free Pearl Sinker',105.00,'16oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(74,'Jasmine Milk Tea','Drinks','Artisan Tea','Free Pearl Sinker',115.00,'22oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(75,'Cookies and Cream','Drinks','Artisan Tea','Free Pearl Sinker',105.00,'16oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(76,'Cookies and Cream','Drinks','Artisan Tea','Free Pearl Sinker',115.00,'22oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(77,'Wintermelon','Drinks','Milk Tea','Free Pearl Sinker',85.00,'16oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(78,'Wintermelon','Drinks','Milk Tea','Free Pearl Sinker',95.00,'22oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(79,'Okinawa','Drinks','Milk Tea','Free Pearl Sinker',85.00,'16oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(80,'Okinawa','Drinks','Milk Tea','Free Pearl Sinker',95.00,'22oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(81,'Taro','Drinks','Milk Tea','Free Pearl Sinker',85.00,'16oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(82,'Taro','Drinks','Milk Tea','Free Pearl Sinker',95.00,'22oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(83,'Dark Chocolate','Drinks','Milk Tea','Free Pearl Sinker',95.00,'16oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(84,'Dark Chocolate','Drinks','Milk Tea','Free Pearl Sinker',105.00,'22oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(85,'Red Velvet Milk Tea','Drinks','Milk Tea','Free Pearl Sinker',95.00,'16oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(86,'Red Velvet Milk Tea','Drinks','Milk Tea','Free Pearl Sinker',105.00,'22oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(87,'Matcha Milk Tea','Drinks','Milk Tea','Free Pearl Sinker',95.00,'16oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(88,'Matcha Milk Tea','Drinks','Milk Tea','Free Pearl Sinker',105.00,'22oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(89,'Brown Sugar Milk','Drinks','Milk Tea','Free Pearl Sinker',95.00,'16oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(90,'Brown Sugar Milk','Drinks','Milk Tea','Free Pearl Sinker',105.00,'22oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(91,'Earl Grey Hot Tea','Drinks','Hot Tea','',95.00,'12oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(92,'Earl Grey Hot Tea','Drinks','Hot Tea','',105.00,'16oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(93,'Ceylon Hot Tea','Drinks','Hot Tea','',95.00,'12oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(94,'Ceylon Hot Tea','Drinks','Hot Tea','',105.00,'16oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(95,'Sun Moon Hot Tea','Drinks','Hot Tea','',95.00,'12oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(96,'Sun Moon Hot Tea','Drinks','Hot Tea','',105.00,'16oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(97,'Jasmine Hot Tea','Drinks','Hot Tea','',95.00,'12oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(98,'Jasmine Hot Tea','Drinks','Hot Tea','',105.00,'16oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(99,'Classic Cheesecake','Drinks','Cheesecake','Free Pearl Sinker',125.00,'16oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(100,'Classic Cheesecake','Drinks','Cheesecake','Free Pearl Sinker',140.00,'22oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(101,'Earl Grey Cheesecake','Drinks','Cheesecake','Free Pearl Sinker',125.00,'16oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(102,'Earl Grey Cheesecake','Drinks','Cheesecake','Free Pearl Sinker',140.00,'22oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(103,'Sun Moon Cheesecake','Drinks','Cheesecake','Free Pearl Sinker',125.00,'16oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(104,'Sun Moon Cheesecake','Drinks','Cheesecake','Free Pearl Sinker',140.00,'22oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(105,'Red Velvet Cheesecake','Drinks','Cheesecake','Free Pearl Sinker',125.00,'16oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(106,'Red Velvet Cheesecake','Drinks','Cheesecake','Free Pearl Sinker',140.00,'22oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(107,'Dark Choco Cheesecake','Drinks','Cheesecake','Free Pearl Sinker',125.00,'16oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(108,'Dark Choco Cheesecake','Drinks','Cheesecake','Free Pearl Sinker',140.00,'22oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(109,'Oreo Cheesecake','Drinks','Cheesecake','Free Pearl Sinker',125.00,'16oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(110,'Oreo Cheesecake','Drinks','Cheesecake','Free Pearl Sinker',140.00,'22oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(111,'Okinawa Cheesecake','Drinks','Cheesecake','Free Pearl Sinker',125.00,'16oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(112,'Okinawa Cheesecake','Drinks','Cheesecake','Free Pearl Sinker',140.00,'22oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(113,'Taro Cheesecake','Drinks','Cheesecake','Free Pearl Sinker',125.00,'16oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(114,'Taro Cheesecake','Drinks','Cheesecake','Free Pearl Sinker',140.00,'22oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(115,'Matcha Cheesecake','Drinks','Cheesecake','Free Pearl Sinker',125.00,'16oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(116,'Matcha Cheesecake','Drinks','Cheesecake','Free Pearl Sinker',140.00,'22oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(117,'Classic RSC','Drinks','Rock Salt & Cheese','Free Pearl Sinker',125.00,'16oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(118,'Classic RSC','Drinks','Rock Salt & Cheese','Free Pearl Sinker',140.00,'22oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(119,'Earl Grey RSC','Drinks','Rock Salt & Cheese','Free Pearl Sinker',125.00,'16oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(120,'Earl Grey RSC','Drinks','Rock Salt & Cheese','Free Pearl Sinker',140.00,'22oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(121,'SunMoon RSC','Drinks','Rock Salt & Cheese','Free Pearl Sinker',125.00,'16oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(122,'SunMoon RSC','Drinks','Rock Salt & Cheese','Free Pearl Sinker',140.00,'22oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(123,'Okinawa RSC','Drinks','Rock Salt & Cheese','Free Pearl Sinker',125.00,'16oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(124,'Okinawa RSC','Drinks','Rock Salt & Cheese','Free Pearl Sinker',140.00,'22oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(125,'Dark Choco RSC','Drinks','Rock Salt & Cheese','Free Pearl Sinker',125.00,'16oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(126,'Dark Choco RSC','Drinks','Rock Salt & Cheese','Free Pearl Sinker',140.00,'22oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(127,'Americano','Drinks','Hot Drinks','',105.00,'12oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(128,'Americano','Drinks','Hot Drinks','',120.00,'16oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(129,'Latte','Drinks','Hot Drinks','',120.00,'12oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(130,'Latte','Drinks','Hot Drinks','',135.00,'16oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(131,'Cappuccino','Drinks','Hot Drinks','',120.00,'12oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(132,'Cappuccino','Drinks','Hot Drinks','',135.00,'16oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(133,'Hot Choco','Drinks','Hot Drinks','',125.00,'12oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(134,'Hot Choco','Drinks','Hot Drinks','',140.00,'16oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(135,'Green Tea Latte','Drinks','Hot Drinks','',125.00,'12oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(136,'Green Tea Latte','Drinks','Hot Drinks','',140.00,'16oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(137,'Mocha','Drinks','Hot Drinks','',130.00,'12oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(138,'Mocha','Drinks','Hot Drinks','',145.00,'16oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(139,'Caramel Macchiato','Drinks','Hot Drinks','',130.00,'12oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(140,'Caramel Macchiato','Drinks','Hot Drinks','',145.00,'16oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(141,'Hazelnut Latte','Drinks','Hot Drinks','',130.00,'12oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(142,'Hazelnut Latte','Drinks','Hot Drinks','',145.00,'16oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(143,'Vanilla Latte','Drinks','Hot Drinks','',130.00,'12oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(144,'Vanilla Latte','Drinks','Hot Drinks','',145.00,'16oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(145,'Iced Americano','Drinks','Iced Drinks','',105.00,'16oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(146,'Iced Americano','Drinks','Iced Drinks','',120.00,'22oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(147,'Iced Latte','Drinks','Iced Drinks','',125.00,'16oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(148,'Iced Latte','Drinks','Iced Drinks','',140.00,'22oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(149,'Iced Caramel Macchiato','Drinks','Iced Drinks','',130.00,'16oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(150,'Iced Caramel Macchiato','Drinks','Iced Drinks','',145.00,'22oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(151,'Iced Mocha','Drinks','Iced Drinks','',130.00,'16oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(152,'Iced Mocha','Drinks','Iced Drinks','',145.00,'22oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(153,'Iced Hazelnut','Drinks','Iced Drinks','',130.00,'16oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(154,'Iced Hazelnut','Drinks','Iced Drinks','',145.00,'22oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(155,'Iced Matcha Latte','Drinks','Iced Drinks','',130.00,'16oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(156,'Iced Matcha Latte','Drinks','Iced Drinks','',145.00,'22oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(157,'Chocolate Chip Mocha','Drinks','Ice Blended','',130.00,'16oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(158,'Chocolate Chip Mocha','Drinks','Ice Blended','',145.00,'22oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(159,'Mocha Frappe','Drinks','Ice Blended','',130.00,'16oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(160,'Mocha Frappe','Drinks','Ice Blended','',145.00,'22oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(161,'Espresso Hazelnut Frappe','Drinks','Ice Blended','',130.00,'16oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(162,'Espresso Hazelnut Frappe','Drinks','Ice Blended','',145.00,'22oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(163,'Caramel Frappe','Drinks','Ice Blended','',130.00,'16oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(164,'Caramel Frappe','Drinks','Ice Blended','',145.00,'22oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(165,'Java Chip','Drinks','Ice Blended','',130.00,'16oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(166,'Java Chip','Drinks','Ice Blended','',145.00,'22oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(167,'Coffee Jelly Frappe','Drinks','Ice Blended','',135.00,'16oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(168,'Coffee Jelly Frappe','Drinks','Ice Blended','',150.00,'22oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(169,'Dark Choco Espresso','Drinks','Ice Blended','',135.00,'16oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(170,'Dark Choco Espresso','Drinks','Ice Blended','',150.00,'22oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(171,'Chocolate Milkshake','Drinks','Cream Based','',115.00,'16oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(172,'Chocolate Milkshake','Drinks','Cream Based','',130.00,'22oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(173,'Vanilla Milkshake','Drinks','Cream Based','',115.00,'16oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(174,'Vanilla Milkshake','Drinks','Cream Based','',130.00,'22oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(175,'Oreo Cream Frappe','Drinks','Cream Based','',125.00,'16oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(176,'Oreo Cream Frappe','Drinks','Cream Based','',140.00,'22oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(177,'Strawberry Milkshake','Drinks','Cream Based','',125.00,'16oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(178,'Strawberry Milkshake','Drinks','Cream Based','',140.00,'22oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(179,'Mango Milkshake','Drinks','Cream Based','',125.00,'16oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(180,'Mango Milkshake','Drinks','Cream Based','',140.00,'22oz',1,NULL,'2026-04-28 06:16:06','2026-04-28 06:16:06'),(182,'Carbonara Pizza (White Sauce)','Pizza','Classic','White Sauce',509.00,'16\"',1,'dish_69f2e32fa73f89.44264407.png','2026-04-30 05:05:51','2026-04-30 06:28:58');
/*!40000 ALTER TABLE `menu_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `menu_item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `unit_price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `menu_item_id` (`menu_item_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=65 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_items`
--

LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
INSERT INTO `order_items` VALUES (1,1,36,1,194.00,194.00),(2,1,37,1,209.00,209.00),(3,1,38,1,194.00,194.00),(4,2,37,1,209.00,209.00),(5,2,76,1,115.00,115.00),(6,3,2,1,279.00,279.00),(7,3,7,1,214.00,214.00),(8,3,8,1,199.00,199.00),(9,3,18,1,459.00,459.00),(10,3,23,1,198.00,198.00),(11,3,28,1,194.00,194.00),(12,3,32,1,194.00,194.00),(13,3,33,1,189.00,189.00),(14,3,54,1,509.00,509.00),(15,3,108,1,140.00,140.00),(16,4,67,1,105.00,105.00),(17,4,68,1,115.00,115.00),(18,4,69,1,105.00,105.00),(19,4,71,1,105.00,105.00),(20,4,75,1,105.00,105.00),(21,4,76,1,115.00,115.00),(22,5,36,1,194.00,194.00),(23,5,39,1,194.00,194.00),(24,6,18,1,459.00,459.00),(25,6,37,1,209.00,209.00),(26,6,42,1,209.00,209.00),(27,6,43,1,329.00,329.00),(28,6,108,1,140.00,140.00),(29,7,38,1,194.00,194.00),(30,7,116,1,140.00,140.00),(31,8,11,1,199.00,199.00),(32,8,155,1,130.00,130.00),(33,9,62,1,529.00,529.00),(34,9,65,1,95.00,95.00),(35,9,108,1,140.00,140.00),(36,9,158,2,145.00,290.00),(37,10,37,1,209.00,209.00),(38,11,34,4,189.00,756.00),(39,12,21,1,198.00,198.00),(40,12,28,2,194.00,388.00),(41,12,34,1,189.00,189.00),(42,12,48,1,479.00,479.00),(43,12,62,1,529.00,529.00),(44,13,39,1,194.00,194.00),(45,13,40,1,169.00,169.00),(46,13,49,1,389.00,389.00),(47,13,64,1,529.00,529.00),(48,14,3,1,279.00,279.00),(49,14,6,1,194.00,194.00),(50,14,15,1,239.00,239.00),(51,14,24,1,198.00,198.00),(52,14,33,1,189.00,189.00),(53,14,36,1,194.00,194.00),(54,14,37,1,209.00,209.00),(55,14,40,1,169.00,169.00),(56,14,46,1,469.00,469.00),(57,14,60,1,509.00,509.00),(58,14,64,1,529.00,529.00),(59,14,69,1,105.00,105.00),(60,15,1,1,279.00,279.00),(61,15,6,2,194.00,388.00),(62,15,34,1,189.00,189.00),(63,15,168,1,150.00,150.00),(64,15,170,1,150.00,150.00);
/*!40000 ALTER TABLE `order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `reservation_id` int(11) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','confirmed','preparing','ready','completed','cancelled') DEFAULT 'pending',
  `payment_method` enum('gcash','cash') DEFAULT 'cash',
  `payment_status` enum('unpaid','paid','refunded') DEFAULT 'unpaid',
  `paymongo_payment_id` varchar(64) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `fk_order_reservation` (`reservation_id`),
  CONSTRAINT `fk_order_reservation` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES (1,2,NULL,697.00,'pending','gcash','unpaid',NULL,'','2026-04-30 05:39:30'),(2,2,NULL,424.00,'pending','cash','unpaid',NULL,'','2026-04-30 07:19:09'),(3,1,NULL,2675.00,'pending','gcash','unpaid',NULL,'','2026-05-08 12:50:45'),(4,1,NULL,750.00,'pending','gcash','unpaid',NULL,'','2026-05-08 12:53:55'),(5,2,NULL,488.00,'pending','gcash','unpaid',NULL,'','2026-05-09 06:21:09'),(6,4,32,1446.00,'pending','gcash','unpaid',NULL,'','2026-05-16 09:34:29'),(7,4,33,434.00,'pending','gcash','unpaid',NULL,'','2026-05-16 09:45:48'),(8,4,34,429.00,'pending','cash','unpaid',NULL,'','2026-05-16 09:48:27'),(9,4,35,1154.00,'pending','gcash','unpaid',NULL,'','2026-05-16 09:50:36'),(10,4,36,309.00,'pending','gcash','unpaid',NULL,'','2026-05-16 09:51:46'),(11,4,38,856.00,'pending','gcash','unpaid',NULL,'','2026-05-16 09:56:10'),(12,4,39,1883.00,'pending','gcash','paid',NULL,'','2026-05-16 10:04:45'),(13,4,41,1381.00,'pending','gcash','paid',NULL,'','2026-05-16 10:22:25'),(14,5,43,3383.00,'pending','gcash','paid',NULL,'','2026-05-18 08:30:53'),(15,5,44,1256.00,'pending','gcash','paid',NULL,'','2026-05-18 08:32:05');
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reservations`
--

DROP TABLE IF EXISTS `reservations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reservations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `res_date` date NOT NULL,
  `res_time` time NOT NULL,
  `pax` int(11) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `notes` text DEFAULT NULL,
  `order_id` int(11) DEFAULT NULL,
  `payment_method` enum('gcash','cash') DEFAULT 'gcash',
  `payment_status` enum('unpaid','paid','refunded') DEFAULT 'unpaid',
  `paymongo_payment_id` varchar(64) DEFAULT NULL,
  `status` enum('pending','confirmed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `fk_reservation_order` (`order_id`),
  CONSTRAINT `fk_reservation_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  CONSTRAINT `reservations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reservations`
--

LOCK TABLES `reservations` WRITE;
/*!40000 ALTER TABLE `reservations` DISABLE KEYS */;
INSERT INTO `reservations` VALUES (28,1,'2026-05-16','20:00:00',2,'09216930526','',NULL,'gcash','paid',NULL,'pending','2026-05-09 06:38:11'),(30,2,'2026-05-16','20:00:00',1,'09216930526','',NULL,'gcash','paid',NULL,'pending','2026-05-14 00:54:22'),(31,4,'2026-05-22','18:00:00',1,'09216930526','',NULL,'gcash','unpaid',NULL,'cancelled','2026-05-16 09:33:03'),(32,4,'2026-05-22','18:00:00',1,'09216930526','',6,'gcash','unpaid',NULL,'cancelled','2026-05-16 09:33:34'),(33,4,'2026-05-29','11:00:00',2,'09216930526','',7,'gcash','unpaid',NULL,'cancelled','2026-05-16 09:45:34'),(34,4,'2026-05-24','13:00:00',3,'09216930526','',8,'cash','unpaid',NULL,'cancelled','2026-05-16 09:48:03'),(35,4,'2026-06-27','21:00:00',4,'09215932062','',9,'gcash','unpaid',NULL,'cancelled','2026-05-16 09:49:55'),(36,4,'2026-06-20','19:00:00',1,'09215932062','',10,'gcash','unpaid',NULL,'cancelled','2026-05-16 09:51:36'),(37,4,'2026-05-22','12:00:00',4,'09216586425','',NULL,'gcash','paid',NULL,'pending','2026-05-16 09:54:41'),(38,4,'2026-05-30','17:00:00',4,'09216586425','',11,'gcash','unpaid',NULL,'cancelled','2026-05-16 09:55:53'),(39,4,'2026-05-23','19:00:00',9,'09216930526','',12,'gcash','paid',NULL,'cancelled','2026-05-16 10:04:15'),(40,4,'2026-05-20','18:00:00',3,'09216930526','',NULL,'gcash','paid',NULL,'pending','2026-05-16 10:17:44'),(41,4,'2026-05-25','17:00:00',3,'09216930526','',13,'gcash','paid',NULL,'pending','2026-05-16 10:22:10'),(42,5,'2026-05-20','12:00:00',4,'09215932062','',NULL,'gcash','unpaid',NULL,'cancelled','2026-05-18 08:28:58'),(43,5,'2026-05-21','16:00:00',2,'09216586425','',14,'gcash','paid',NULL,'pending','2026-05-18 08:29:32'),(44,5,'2026-05-25','11:00:00',7,'09216586425','',15,'gcash','paid',NULL,'pending','2026-05-18 08:31:43'),(45,4,'2026-05-23','19:00:00',7,'09216930526','',NULL,'gcash','paid',NULL,'pending','2026-05-18 08:48:21'),(46,4,'2026-06-20','18:00:00',4,'09216930526','',NULL,'gcash','paid',NULL,'pending','2026-05-18 08:49:11');
/*!40000 ALTER TABLE `reservations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','customer') DEFAULT 'customer',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `profile_pic` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Claire Redfield','admin@oliscoffee.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','admin','2026-04-28 06:16:05','user_1_1778244094.jpg'),(2,'Jennifer Marie Lacson','customer@email.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','customer','2026-04-28 06:16:05',NULL),(3,'Lorenzo Aurin','enzo@gmail.com','$2y$10$voL0y8XB.S2KdFSCX4TAhu1OkV8liHIQjUyCY1/5Z59JudjN33PmG','customer','2026-04-30 07:27:50',NULL),(4,'Clarize Dyanne Reyes','ayis9626@gmail.com','$2y$10$iWtMCtmVoEkqHmjwGnMeue.KWStAdm3pgghnhnd4.PaAmJX7t7TiG','customer','2026-04-30 08:07:35',NULL),(5,'Lea Chelsy Narvacan','narvacanchelsy@gmail.com','$2y$10$6s2hFAOETgdIZFdmf30cweI1IS4IuZrV6vCIaqWyXBA.TE8/./I12','customer','2026-04-30 08:09:12',NULL);
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

-- Dump completed on 2026-05-18 16:50:03
