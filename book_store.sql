/*
SQLyog Enterprise v12.5.1 (64 bit)
MySQL - 5.7.36 : Database - book_store
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
/*Table structure for table `activity_log` */

DROP TABLE IF EXISTS `activity_log`;

CREATE TABLE `activity_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `details` text COLLATE utf8mb4_unicode_ci,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_action` (`action`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `fk_activity_log_user` FOREIGN KEY (`user_id`) REFERENCES `customer` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=57 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `activity_log` */

insert  into `activity_log`(`id`,`user_id`,`action`,`details`,`ip_address`,`user_agent`,`created_at`) values 
(1,1,'register','New user registered',NULL,NULL,'2024-07-18 01:07:47'),
(2,1,'login','User logged in',NULL,NULL,'2024-07-18 01:08:09'),
(3,1,'author_added','Added author: Wardina (ID: 1)',NULL,NULL,'2024-07-18 01:08:49'),
(4,1,'author_added','Added author: Wardina (ID: 2)',NULL,NULL,'2024-07-18 01:09:39'),
(5,1,'author_added','Added author: Wardina (ID: 3)',NULL,NULL,'2024-07-18 01:11:53'),
(6,1,'login','User logged in',NULL,NULL,'2024-07-18 01:18:08'),
(7,1,'publisher_added','Added publisher: Springer (ID: 1)',NULL,NULL,'2024-07-18 01:22:01'),
(8,1,'book_added','Added book: Sawa suka Programming (ID: 2)',NULL,NULL,'2024-07-18 01:28:02'),
(9,1,'book_added','Added book: Sawa suka Programming (ID: 3)',NULL,NULL,'2024-07-18 01:29:42'),
(10,1,'login','User logged in',NULL,NULL,'2024-07-19 15:56:06'),
(11,1,'login','User logged in',NULL,NULL,'2024-07-19 17:37:03'),
(12,1,'book_updated','Updated book: Sawa suka Programming (ID: 2)',NULL,NULL,'2024-07-19 18:17:11'),
(13,1,'book_updated','Updated book: Sawa suka Programming (ID: 2)',NULL,NULL,'2024-07-19 18:18:31'),
(14,1,'book_updated','Updated book: Sawa suka Programming (ID: 2)',NULL,NULL,'2024-07-19 18:19:10'),
(15,1,'book_updated','Updated book: Sawa suka Programming (ID: 2)',NULL,NULL,'2024-07-19 18:20:18'),
(16,1,'book_updated','Updated book: Sawa suka Programming (ID: 2)',NULL,NULL,'2024-07-19 18:21:20'),
(17,1,'book_updated','Updated book: Sawa suka Programming (ID: 2)',NULL,NULL,'2024-07-19 18:22:39'),
(18,1,'book_updated','Updated book: Sawa suka Programming (ID: 2)',NULL,NULL,'2024-07-19 18:24:16'),
(19,1,'book_updated','Updated book: Saya suka Programming (ID: 2)',NULL,NULL,'2024-07-19 18:24:33'),
(20,1,'book_updated','Updated book: Saya suka Programming (ID: 2)',NULL,NULL,'2024-07-19 18:24:52'),
(21,1,'author_added','Added author: Aishah Ibrahim (ID: 4)',NULL,NULL,'2024-07-19 18:54:04'),
(22,1,'author_added','Added author: Amirul Hadi (ID: 5)',NULL,NULL,'2024-07-19 18:54:45'),
(23,1,'author_added','Added author: Buku Fixi (ID: 6)',NULL,NULL,'2024-07-19 18:55:44'),
(24,1,'book_added','Added book: The Silent Storm (ID: 4)',NULL,NULL,'2024-07-19 19:00:52'),
(25,1,'book_added','Added book: The Silent Storm (ID: 5)',NULL,NULL,'2024-07-19 19:03:18'),
(26,1,'book_deleted','Deleted book: The Silent Storm (ID: 4)',NULL,NULL,'2024-07-19 19:03:36'),
(27,1,'book_deleted','Deleted book: The Silent Storm (ID: 5)',NULL,NULL,'2024-07-19 19:06:23'),
(28,1,'order_status_updated','Updated order status: Order ID 4, New status: Shipped',NULL,NULL,'2024-07-19 19:29:25'),
(29,1,'order_status_updated','Updated order status: Order ID 4, New status: Shipped',NULL,NULL,'2024-07-19 19:29:32'),
(30,1,'order_status_updated','Updated order status: Order ID 5, New status: Delivered',NULL,NULL,'2024-07-19 19:29:38'),
(31,1,'order_status_updated','Updated order status: Order ID 5, New status: Delivered',NULL,NULL,'2024-07-19 19:29:46'),
(32,1,'order_status_updated','Updated order status: Order ID 4, New status: Cancelled',NULL,NULL,'2024-07-19 19:29:51'),
(33,1,'order_status_updated','Updated order status: Order ID 5, New status: Processing',NULL,NULL,'2024-07-19 19:29:58'),
(34,1,'order_status_updated','Updated order status: Order ID 5, New status: Cancelled',NULL,NULL,'2024-07-19 19:30:03'),
(35,1,'order_status_updated','Updated order status: Order ID 5, New status: Shipped',NULL,NULL,'2024-07-19 19:30:06'),
(36,1,'order_status_updated','Updated order status: Order ID 5, New status: Processing',NULL,NULL,'2024-07-19 19:30:09'),
(37,1,'order_status_updated','Updated order status: Order ID 5, New status: Processing',NULL,NULL,'2024-07-19 19:30:15'),
(38,1,'order_status_updated','Updated order status: Order ID 4, New status: Delivered',NULL,NULL,'2024-07-19 19:30:19'),
(39,1,'order_status_updated','Updated order status: Order ID 4, New status: Delivered',NULL,NULL,'2024-07-19 19:31:24'),
(40,1,'order_status_updated','Updated order status: Order ID 4, New status: Delivered',NULL,NULL,'2024-07-19 19:31:37'),
(41,1,'book_updated','Updated book: Saya suka Programming (ID: 2)',NULL,NULL,'2024-07-19 19:31:58'),
(42,1,'admin_login','Admin logged in',NULL,NULL,'2024-07-19 22:39:45'),
(43,1,'order_status_updated','Updated order status: Order ID 4, New status: Shipped',NULL,NULL,'2024-07-19 22:47:18'),
(44,1,'order_status_updated','Updated order status: Order ID 4, New status: Processing',NULL,NULL,'2024-07-19 22:47:23'),
(45,1,'order_status_updated','Updated order status: Order ID 4, New status: Cancelled',NULL,NULL,'2024-07-19 22:47:25'),
(46,1,'admin_login','Admin logged in',NULL,NULL,'2024-07-19 23:11:41'),
(47,1,'admin_login','Admin logged in',NULL,NULL,'2024-07-19 23:13:58'),
(48,1,'admin_login','Admin logged in',NULL,NULL,'2024-07-19 23:38:39'),
(49,1,'order_status_updated','Updated order status: Order ID 4, New status: Processing',NULL,NULL,'2024-07-19 23:38:54'),
(50,1,'order_status_updated','Updated order status: Order ID 4, New status: Delivered',NULL,NULL,'2024-07-19 23:38:57'),
(51,1,'admin_login','Admin logged in',NULL,NULL,'2024-07-19 23:40:54'),
(52,1,'admin_login','Admin logged in',NULL,NULL,'2024-07-19 23:41:14'),
(53,1,'author_updated','Updated author: Aishah Ibrahim (ID: 4)',NULL,NULL,'2024-07-20 00:08:28'),
(54,1,'author_updated','Updated author: Aishah Ibrahim (ID: 4)',NULL,NULL,'2024-07-20 00:09:13'),
(55,1,'author_updated','Updated author: Aishah Ibrahim (ID: 4)',NULL,NULL,'2024-07-20 00:09:31'),
(56,1,'publisher_updated','Updated publisher: Springer (ID: 1)',NULL,NULL,'2024-07-20 00:15:18');

/*Table structure for table `author` */

DROP TABLE IF EXISTS `author`;

CREATE TABLE `author` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `a_name` varchar(100) NOT NULL,
  `a_email` varchar(100) NOT NULL,
  `address` varchar(200) DEFAULT NULL,
  `phone` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

/*Data for the table `author` */

insert  into `author`(`id`,`a_name`,`a_email`,`address`,`phone`) values 
(1,'Wardina','wardina@gmail.com','Malaysia','8383883'),
(2,'Wardina','wardina@gmail.com','Malaysia','8383883'),
(3,'Wardina','wardina@gmail.com','Malaysia','8383883'),
(4,'Aishah Ibrahim','Ibrahim@gmail.com','Malaysia','283383833'),
(5,'Amirul Hadi','hadi@gmail.com','Malaysia','595885'),
(6,'Buku Fixi','fixi@gmail.com','Malaysia','383883');

/*Table structure for table `book` */

DROP TABLE IF EXISTS `book`;

CREATE TABLE `book` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `isbn` varchar(20) NOT NULL,
  `book_title` varchar(255) NOT NULL,
  `category` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `copyright_date` date NOT NULL,
  `year` year(4) NOT NULL,
  `page_count` int(11) NOT NULL,
  `p_id` int(11) NOT NULL,
  `s_id` int(11) NOT NULL,
  `a_id` int(11) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `publishere_idx` (`p_id`),
  KEY `addded_by_idx` (`s_id`),
  CONSTRAINT `addded_by` FOREIGN KEY (`s_id`) REFERENCES `staff` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `publisher` FOREIGN KEY (`p_id`) REFERENCES `publisher` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

/*Data for the table `book` */

insert  into `book`(`id`,`isbn`,`book_title`,`category`,`price`,`copyright_date`,`year`,`page_count`,`p_id`,`s_id`,`a_id`,`is_active`) values 
(2,'1234567890128','Saya suka Programming','Book',38.00,'2024-07-18',2023,450,1,1,1,1),
(3,'1234567890128','Sawa suka Programming','Book',38.00,'2024-07-18',2023,450,1,1,1,1);

/*Table structure for table `book_author` */

DROP TABLE IF EXISTS `book_author`;

CREATE TABLE `book_author` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `b_id` int(11) NOT NULL,
  `a_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_book_author_book` (`b_id`),
  KEY `idx_book_author_author` (`a_id`),
  CONSTRAINT `fk_book_author_author` FOREIGN KEY (`a_id`) REFERENCES `author` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_book_author_book` FOREIGN KEY (`b_id`) REFERENCES `book` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `book_author` */

/*Table structure for table `customer` */

DROP TABLE IF EXISTS `customer`;

CREATE TABLE `customer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `c_name` varchar(50) NOT NULL,
  `c_email` varchar(50) NOT NULL,
  `c_password` varchar(100) NOT NULL,
  `c_phone` varchar(45) NOT NULL,
  `c_address` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

/*Data for the table `customer` */

insert  into `customer`(`id`,`c_name`,`c_email`,`c_password`,`c_phone`,`c_address`) values 
(1,'Aisya','aisya@gmail.com','$2y$10$jjbrIsWcGnAezUntZUOinuTMQS8nLTVJ.S9Cs2692jtHO1DIZxkOi','818818191','Malaysia');

/*Table structure for table `order` */

DROP TABLE IF EXISTS `order`;

CREATE TABLE `order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `c_id` int(11) NOT NULL,
  `o_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `total_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `o_status` enum('Pending','Processing','Shipped','Delivered','Cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pending',
  `o_paymentstatus` enum('Unpaid','Paid','Refunded') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Unpaid',
  `shipping_address` text COLLATE utf8mb4_unicode_ci,
  `billing_address` text COLLATE utf8mb4_unicode_ci,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_customer_id` (`c_id`),
  KEY `idx_order_date` (`o_date`),
  KEY `idx_status` (`o_status`),
  KEY `idx_payment_status` (`o_paymentstatus`),
  CONSTRAINT `fk_order_customer` FOREIGN KEY (`c_id`) REFERENCES `customer` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `order` */

insert  into `order`(`id`,`c_id`,`o_date`,`total_amount`,`o_status`,`o_paymentstatus`,`shipping_address`,`billing_address`,`notes`,`created_at`,`updated_at`) values 
(4,1,'2024-07-19 08:07:22',190.00,'Delivered','Unpaid',NULL,NULL,NULL,'2024-07-19 16:07:22','2024-07-19 23:38:57'),
(5,1,'2024-07-19 09:25:43',76.00,'Processing','Unpaid',NULL,NULL,NULL,'2024-07-19 17:25:43','2024-07-19 19:30:09');

/*Table structure for table `order_item` */

DROP TABLE IF EXISTS `order_item`;

CREATE TABLE `order_item` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `o_id` int(11) NOT NULL,
  `b_id` int(11) NOT NULL,
  `quantity` int(10) unsigned NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_order_id` (`o_id`),
  KEY `idx_book_id` (`b_id`),
  CONSTRAINT `fk_order_item_book` FOREIGN KEY (`b_id`) REFERENCES `book` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_order_item_order` FOREIGN KEY (`o_id`) REFERENCES `order` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `order_item` */

insert  into `order_item`(`id`,`o_id`,`b_id`,`quantity`,`price`,`created_at`,`updated_at`) values 
(5,4,2,2,38.00,'2024-07-19 16:07:22','2024-07-19 16:07:22'),
(6,4,3,3,38.00,'2024-07-19 16:07:22','2024-07-19 16:07:22'),
(7,5,2,1,38.00,'2024-07-19 17:25:43','2024-07-19 17:25:43'),
(8,5,3,1,38.00,'2024-07-19 17:25:43','2024-07-19 17:25:43');

/*Table structure for table `payment` */

DROP TABLE IF EXISTS `payment`;

CREATE TABLE `payment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `o_id` int(11) NOT NULL,
  `p_date` datetime NOT NULL,
  `p_amount` decimal(10,2) NOT NULL,
  `p_method` varchar(50) NOT NULL,
  `confirmed_by` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_idx` (`o_id`),
  KEY `staff_idx` (`confirmed_by`),
  CONSTRAINT `order` FOREIGN KEY (`o_id`) REFERENCES `order` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `staff` FOREIGN KEY (`confirmed_by`) REFERENCES `staff` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `payment` */

/*Table structure for table `publisher` */

DROP TABLE IF EXISTS `publisher`;

CREATE TABLE `publisher` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `p_name` varchar(45) NOT NULL,
  `p_address` varchar(255) NOT NULL,
  `p_state` varchar(50) NOT NULL,
  `p_phone` varchar(45) NOT NULL,
  `p_email` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

/*Data for the table `publisher` */

insert  into `publisher`(`id`,`p_name`,`p_address`,`p_state`,`p_phone`,`p_email`) values 
(1,'Springer','Malaysia','Perak','0802828282','springer@gmail.com');

/*Table structure for table `review` */

DROP TABLE IF EXISTS `review`;

CREATE TABLE `review` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `c_id` int(11) NOT NULL,
  `b_id` int(11) NOT NULL,
  `r_rating` int(11) NOT NULL,
  `r_text` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_customer` (`c_id`),
  KEY `idx_book` (`b_id`),
  CONSTRAINT `fk_review_book` FOREIGN KEY (`b_id`) REFERENCES `book` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_review_customer` FOREIGN KEY (`c_id`) REFERENCES `customer` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `review` */

insert  into `review`(`id`,`c_id`,`b_id`,`r_rating`,`r_text`,`created_at`) values 
(1,1,3,2,'This is okay','2024-07-19 18:41:31'),
(2,1,2,5,'Excellent','2024-07-19 22:48:53');

/*Table structure for table `staff` */

DROP TABLE IF EXISTS `staff`;

CREATE TABLE `staff` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `s_name` varchar(100) NOT NULL,
  `s_email` varchar(100) NOT NULL,
  `s_phone` varchar(45) NOT NULL,
  `s_password` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

/*Data for the table `staff` */

insert  into `staff`(`id`,`s_name`,`s_email`,`s_phone`,`s_password`) values 
(1,'Wardina','wardina@gmail.com','39393993','$2y$10$jjbrIsWcGnAezUntZUOinuTMQS8nLTVJ.S9Cs2692jtHO1DIZxkOi'),
(1,'Irdina','irdina@gmail.com','39393993','$2y$10$jjbrIsWcGnAezUntZUOinuTMQS8nLTVJ.S9Cs2692jtHO1DIZxkOi'),
(1,'Aisya','aisya@gmail.com','39393993','$2y$10$jjbrIsWcGnAezUntZUOinuTMQS8nLTVJ.S9Cs2692jtHO1DIZxkOi');

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
