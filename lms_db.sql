/*
SQLyog Community v13.1.9 (64 bit)
MySQL - 10.4.22-MariaDB : Database - lms_db
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`lms_db` /*!40100 DEFAULT CHARACTER SET utf8mb4 */;

USE `lms_db`;

/*Table structure for table `books` */

DROP TABLE IF EXISTS `books`;

CREATE TABLE `books` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `author` varchar(150) NOT NULL,
  `genre` varchar(150) DEFAULT NULL,
  `quantity` int(11) DEFAULT 0,
  `cover_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4;

/*Data for the table `books` */

insert  into `books`(`id`,`title`,`author`,`genre`,`quantity`,`cover_image`,`created_at`) values 
(1,'Harry Potter and the Sorcerer\'s Stone','J.K. Rowling','Fantasy, Action',5,NULL,'2026-02-20 10:45:52'),
(2,'The Hobbit','J.R.R. Tolkien','Action, Fantasy',3,NULL,'2026-02-20 10:45:52'),
(3,'Clean Code','Robert C. Martin','Science, Fiction',2,NULL,'2026-02-20 10:45:52'),
(4,'Introduction to Algorithms','Thomas H. Cormen','Math, Physics, Science',1,NULL,'2026-02-20 10:45:52'),
(5,'Harry Potter and the Sorcerer\'s Stone','J.K. Rowling','Fantasy, Action',5,NULL,'2026-02-20 10:46:03'),
(6,'The Hobbit','J.R.R. Tolkien','Action, Fantasy',3,NULL,'2026-02-20 10:46:03'),
(7,'Clean Code','Robert C. Martin','Science, Fiction',2,NULL,'2026-02-20 10:46:03'),
(8,'Introduction to Algorithms','Thomas H. Cormen','Math, Physics, Science',1,NULL,'2026-02-20 10:46:03');

/*Table structure for table `transactions` */

DROP TABLE IF EXISTS `transactions`;

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `issue_date` date NOT NULL,
  `return_date` date DEFAULT NULL,
  `status` enum('borrowed','returned','overdue') DEFAULT 'borrowed',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_user` (`user_id`),
  KEY `fk_book` (`book_id`),
  CONSTRAINT `fk_book` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;

/*Data for the table `transactions` */

insert  into `transactions`(`id`,`user_id`,`book_id`,`issue_date`,`return_date`,`status`,`created_at`) values 
(1,2,1,'2026-02-20',NULL,'borrowed','2026-02-20 10:45:52'),
(2,2,1,'2026-02-20',NULL,'borrowed','2026-02-20 10:46:03');

/*Table structure for table `users` */

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4;

/*Data for the table `users` */

insert  into `users`(`id`,`name`,`email`,`password`,`role`,`created_at`) values 
-- (1,'Admin User','admin@lms.com','admin123','admin','2026-02-20 10:45:52'),
(2,'John Doe','john@example.com','123456','user','2026-02-20 10:45:52'),
(3,'Jane Smith','jane@example.com','123456','user','2026-02-20 10:45:52');

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
