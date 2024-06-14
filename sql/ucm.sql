/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
/*Table structure for table `actualite` */

DROP TABLE IF EXISTS `actualite`;

CREATE TABLE `actualite` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uuid` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mot_cle` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `detail` longtext COLLATE utf8mb4_unicode_ci,
  `path` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `active` tinyint(1) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `ressource_type` enum('RUBRIQUE','DEPARTEMENT','MENTION','NIVEAU','SEMESTRE') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `resource_uuid` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_54928197D17F50A6` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `actualite` */

/*Table structure for table `article` */

DROP TABLE IF EXISTS `article`;

CREATE TABLE `article` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uuid` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mot_cle` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `detail` longtext COLLATE utf8mb4_unicode_ci,
  `path` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content_header` longtext COLLATE utf8mb4_unicode_ci,
  `content_footer` longtext COLLATE utf8mb4_unicode_ci,
  `content_left` longtext COLLATE utf8mb4_unicode_ci,
  `content_right` longtext COLLATE utf8mb4_unicode_ci,
  `active` tinyint(1) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `ressource_type` enum('RUBRIQUE','DEPARTEMENT','MENTION','NIVEAU','SEMESTRE') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `resource_uuid` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_23A0E66D17F50A6` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `article` */

/*Table structure for table `concours` */

DROP TABLE IF EXISTS `concours`;

CREATE TABLE `concours` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `niveau_id` int(11) NOT NULL,
  `libelle` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime DEFAULT NULL,
  `deliberation` decimal(10,2) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_4FAE5196B3E9C81` (`niveau_id`),
  CONSTRAINT `FK_3D6AF1FFB3E9C81` FOREIGN KEY (`niveau_id`) REFERENCES `niveau` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `concours` */

/*Table structure for table `concours_matiere` */

DROP TABLE IF EXISTS `concours_matiere`;

CREATE TABLE `concours_matiere` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `concours_id` int(11) NOT NULL,
  `mention_id` int(11) NOT NULL,
  `libelle` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_71A45644D11E3C7` (`concours_id`),
  KEY `IDX_71A456447A4147F0` (`mention_id`),
  CONSTRAINT `FK_71A456447A4147F0` FOREIGN KEY (`mention_id`) REFERENCES `mention` (`id`),
  CONSTRAINT `FK_71A45644D11E3C7` FOREIGN KEY (`concours_id`) REFERENCES `concours` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `concours_matiere` */

/*Table structure for table `departement` */

DROP TABLE IF EXISTS `departement`;

CREATE TABLE `departement` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uuid` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nom` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_C1765B63D17F50A6` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `departement` */

/*Table structure for table `doctrine_migration_versions` */

DROP TABLE IF EXISTS `doctrine_migration_versions`;

CREATE TABLE `doctrine_migration_versions` (
  `version` varchar(191) COLLATE utf8_unicode_ci NOT NULL,
  `executed_at` datetime DEFAULT NULL,
  `execution_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Data for the table `doctrine_migration_versions` */

insert  into `doctrine_migration_versions`(`version`,`executed_at`,`execution_time`) values 
('DoctrineMigrations\\Version20210309194237','2021-04-20 15:31:21',488),
('DoctrineMigrations\\Version20210309195916','2021-03-09 21:59:26',553),
('DoctrineMigrations\\Version20210316112522','2021-03-16 13:25:38',1960),
('DoctrineMigrations\\Version20210319170339','2021-03-19 19:03:50',1823),
('DoctrineMigrations\\Version20210319175704','2021-03-19 23:03:22',5376),
('DoctrineMigrations\\Version20210327094036','2021-04-20 15:06:51',3073),
('DoctrineMigrations\\Version20210403073254','2021-04-20 15:06:54',604),
('DoctrineMigrations\\Version20210405145322','2021-04-20 15:06:55',441),
('DoctrineMigrations\\Version20210405165949','2021-04-07 14:07:11',2098),
('DoctrineMigrations\\Version20210407124811','2021-04-07 14:48:19',862),
('DoctrineMigrations\\Version20210407125158','2021-04-07 14:52:03',826),
('DoctrineMigrations\\Version20210408130745','2021-04-20 15:06:55',5780),
('DoctrineMigrations\\Version20210411074137','2021-04-20 15:34:42',18),
('DoctrineMigrations\\Version20210418135330','2021-04-26 15:13:16',2604),
('DoctrineMigrations\\Version20210424182447','2021-05-01 17:27:47',26416),
('DoctrineMigrations\\Version20210429193341','2021-05-01 17:28:13',4074),
('DoctrineMigrations\\Version20210501173646','2021-05-01 19:42:50',1066),
('DoctrineMigrations\\Version20210501174936','2021-05-01 19:51:55',2056);

/*Table structure for table `document` */

DROP TABLE IF EXISTS `document`;

CREATE TABLE `document` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `actualite_id` int(11) DEFAULT NULL,
  `article_id` int(11) DEFAULT NULL,
  `rubrique_id` int(11) DEFAULT NULL,
  `uuid` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ordre` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_D8698A76D17F50A6` (`uuid`),
  KEY `IDX_D8698A76A2843073` (`actualite_id`),
  KEY `IDX_D8698A767294869C` (`article_id`),
  KEY `IDX_D8698A763BD38833` (`rubrique_id`),
  CONSTRAINT `FK_D8698A763BD38833` FOREIGN KEY (`rubrique_id`) REFERENCES `rubrique` (`id`),
  CONSTRAINT `FK_D8698A767294869C` FOREIGN KEY (`article_id`) REFERENCES `article` (`id`),
  CONSTRAINT `FK_D8698A76A2843073` FOREIGN KEY (`actualite_id`) REFERENCES `actualite` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `document` */

/*Table structure for table `matiere` */

DROP TABLE IF EXISTS `matiere`;

CREATE TABLE `matiere` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `unite_enseignements_id` int(11) NOT NULL,
  `uuid` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nom` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `credit` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_9014574AD17F50A6` (`uuid`),
  KEY `IDX_9014574A41D534A9` (`unite_enseignements_id`),
  CONSTRAINT `FK_9014574A41D534A9` FOREIGN KEY (`unite_enseignements_id`) REFERENCES `unite_enseignements` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `matiere` */

/*Table structure for table `medias` */

DROP TABLE IF EXISTS `medias`;

CREATE TABLE `medias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `document_id` int(11) DEFAULT NULL,
  `uuid` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `path` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ordre` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_12D2AF81D17F50A6` (`uuid`),
  KEY `IDX_12D2AF81C33F7837` (`document_id`),
  CONSTRAINT `FK_12D2AF81C33F7837` FOREIGN KEY (`document_id`) REFERENCES `document` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `medias` */

/*Table structure for table `mention` */

DROP TABLE IF EXISTS `mention`;

CREATE TABLE `mention` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `departement_id` int(11) NOT NULL,
  `uuid` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nom` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `active` tinyint(1) DEFAULT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `objectif` longtext COLLATE utf8mb4_unicode_ci,
  `dmio` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `admission` longtext COLLATE utf8mb4_unicode_ci,
  `diplomes` longtext COLLATE utf8mb4_unicode_ci,
  `debouches` longtext COLLATE utf8mb4_unicode_ci,
  `path` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_E20259CDD17F50A6` (`uuid`),
  KEY `IDX_E20259CDCCF9E01E` (`departement_id`),
  CONSTRAINT `FK_E20259CDCCF9E01E` FOREIGN KEY (`departement_id`) REFERENCES `departement` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `mention` */

/*Table structure for table `niveau` */

DROP TABLE IF EXISTS `niveau`;

CREATE TABLE `niveau` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uuid` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `libelle` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `systeme` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `diplome` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_4BDFF36BD17F50A6` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `niveau` */

/*Table structure for table `profil` */

DROP TABLE IF EXISTS `profil`;

CREATE TABLE `profil` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('ENABLED','DISABLED') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `profil` */

insert  into `profil`(`id`,`name`,`status`,`created_at`,`updated_at`) values 
(1,'Administrateur','ENABLED','2021-03-20 23:51:35','2021-03-20 23:51:35'),
(2,'Secretariat','ENABLED','2021-03-21 00:46:10','2021-03-21 00:46:10');

/*Table structure for table `profil_has_roles` */

DROP TABLE IF EXISTS `profil_has_roles`;

CREATE TABLE `profil_has_roles` (
  `profil_id` int(11) NOT NULL,
  `roles_id` int(11) NOT NULL,
  PRIMARY KEY (`profil_id`,`roles_id`),
  KEY `IDX_E5CC0859275ED078` (`profil_id`),
  KEY `IDX_E5CC085938C751C4` (`roles_id`),
  CONSTRAINT `FK_F832F583275ED078` FOREIGN KEY (`profil_id`) REFERENCES `profil` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_F832F58338C751C4` FOREIGN KEY (`roles_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `profil_has_roles` */

insert  into `profil_has_roles`(`profil_id`,`roles_id`) values 
(1,1),
(1,2),
(1,3),
(1,5),
(1,6),
(1,7),
(1,10);

/*Table structure for table `roles` */

DROP TABLE IF EXISTS `roles`;

CREATE TABLE `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `roles` */

insert  into `roles`(`id`,`name`,`code`,`created_at`,`updated_at`) values 
(1,'Modification etudiant','EDIT_STUDENT','2021-03-20 00:47:28','2021-03-20 08:42:29'),
(2,'Création etudiant','CREATE_STUDENT','2021-03-20 08:35:52','2021-03-20 08:42:08'),
(3,'Suppression etudiant','DELETE_STUDENT','2021-03-20 08:43:08','2021-03-20 08:43:08'),
(4,'Création rubrique','CREATE_RUBRIQUE','2021-03-20 08:43:37','2021-03-21 00:18:59'),
(5,'Modification rubrique','EDIT_RUBRIQUE','2021-03-20 08:44:04','2021-03-21 00:18:49'),
(6,'Suppression rubrique','DELETE_RUBRIQUE','2021-03-20 08:44:44','2021-03-20 08:44:44'),
(7,'Création note','CREATE_NOTE','2021-03-21 00:44:02','2021-03-21 00:44:02'),
(8,'Creation enseignant','CREATE_ENSEIGNANT','2021-03-22 10:52:37','2021-03-22 10:52:37'),
(9,'Modification enseignant','EDIT_ENSEIGNANT','2021-03-22 10:55:01','2021-03-22 10:55:01'),
(10,'Accès au back office','ROLE_ADMIN','2021-04-07 14:08:23','2021-04-07 14:08:23');

/*Table structure for table `rubrique` */

DROP TABLE IF EXISTS `rubrique`;

CREATE TABLE `rubrique` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uuid` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `parent` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `details` longtext COLLATE utf8mb4_unicode_ci,
  `active` tinyint(1) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_8FA4097CD17F50A6` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `rubrique` */

/*Table structure for table `secteur` */

DROP TABLE IF EXISTS `secteur`;

CREATE TABLE `secteur` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mention_id` int(11) NOT NULL,
  `uuid` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nom` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_8045251FD17F50A6` (`uuid`),
  KEY `IDX_8045251F7A4147F0` (`mention_id`),
  CONSTRAINT `FK_8045251F7A4147F0` FOREIGN KEY (`mention_id`) REFERENCES `mention` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `secteur` */

/*Table structure for table `semestre` */

DROP TABLE IF EXISTS `semestre`;

CREATE TABLE `semestre` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uuid` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `libelle` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_71688FBCD17F50A6` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `semestre` */

/*Table structure for table `unite_enseignements` */

DROP TABLE IF EXISTS `unite_enseignements`;

CREATE TABLE `unite_enseignements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mention_id` int(11) NOT NULL,
  `niveau_id` int(11) NOT NULL,
  `semestre_id` int(11) NOT NULL,
  `uuid` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `libelle` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_FD2B4376D17F50A6` (`uuid`),
  KEY `IDX_FD2B43767A4147F0` (`mention_id`),
  KEY `IDX_FD2B4376B3E9C81` (`niveau_id`),
  KEY `IDX_FD2B43765577AFDB` (`semestre_id`),
  CONSTRAINT `FK_FD2B43765577AFDB` FOREIGN KEY (`semestre_id`) REFERENCES `semestre` (`id`),
  CONSTRAINT `FK_FD2B43767A4147F0` FOREIGN KEY (`mention_id`) REFERENCES `mention` (`id`),
  CONSTRAINT `FK_FD2B4376B3E9C81` FOREIGN KEY (`niveau_id`) REFERENCES `niveau` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `unite_enseignements` */

/*Table structure for table `user` */

DROP TABLE IF EXISTS `user`;

CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `roles` json NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_verified` tinyint(1) NOT NULL,
  `first_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `profil_id` int(11) NOT NULL,
  `login` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` smallint(6) NOT NULL,
  `last_connected_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_8D93D649E7927C74` (`email`),
  KEY `IDX_8D93D649275ED078` (`profil_id`),
  CONSTRAINT `FK_8D93D649275ED078` FOREIGN KEY (`profil_id`) REFERENCES `profil` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `user` */

insert  into `user`(`id`,`email`,`roles`,`password`,`is_verified`,`first_name`,`last_name`,`profil_id`,`login`,`status`,`last_connected_at`) values 
(1,'admin@ucm.mg','[\"ROLE_ADMIN\"]','$argon2id$v=19$m=65536,t=4,p=1$GpexzO1ODRhjv40ks4MSqw$rYUKHsJamcdxTuax8yIlWh4QeKSoR20FhjlBmUnVw2k',0,'admin','admin',1,'admin@ucm.mg',1,NULL);

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
