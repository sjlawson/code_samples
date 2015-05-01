/*
SQLyog Enterprise v11.33 (64 bit)
MySQL - 5.5.41-0ubuntu0.14.04.1 : Database - revive_api
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`revive_api` /*!40100 DEFAULT CHARACTER SET utf8 */;

USE `revive_api`;

/*Table structure for table `log_levels` */

DROP TABLE IF EXISTS `log_levels`;

CREATE TABLE `log_levels` (
  `logLevelsID` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  PRIMARY KEY (`logLevelsID`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

/*Table structure for table `logs` */

DROP TABLE IF EXISTS `logs`;

CREATE TABLE `logs` (
  `logsID` bigint(20) NOT NULL AUTO_INCREMENT,
  `logLevelsID` int(11) NOT NULL,
  `statusCode` varchar(6) NOT NULL,
  `uuid` char(36) NOT NULL,
  `request` varchar(1000) NOT NULL,
  `response` varchar(500) NOT NULL,
  `datetimeCreated` datetime NOT NULL,
  PRIMARY KEY (`logsID`),
  UNIQUE KEY `UNIQ_F08FC65CD17F50A6` (`uuid`)
) ENGINE=InnoDB AUTO_INCREMENT=106023 DEFAULT CHARSET=utf8;

/*Table structure for table `process_business_keys` */

DROP TABLE IF EXISTS `process_business_keys`;

CREATE TABLE `process_business_keys` (
  `processBusinessKeysID` smallint(11) unsigned NOT NULL AUTO_INCREMENT,
  `processName` varchar(64) NOT NULL,
  PRIMARY KEY (`processBusinessKeysID`),
  KEY `processName` (`processName`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=263 DEFAULT CHARSET=utf8;

/*Table structure for table `process_data_values` */

DROP TABLE IF EXISTS `process_data_values`;

CREATE TABLE `process_data_values` (
  `processDataValuesID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `machineID` char(36) NOT NULL,
  `processID` char(10) NOT NULL,
  `processBusinessKeysID` smallint(11) unsigned NOT NULL,
  `processName` varchar(64) NOT NULL,
  `processValue` varchar(64) NOT NULL,
  `processTimestamp` datetime NOT NULL,
  `datetimeAdded` datetime NOT NULL,
  PRIMARY KEY (`processDataValuesID`),
  KEY `processName` (`processName`),
  KEY `processValue` (`processValue`),
  KEY `processID` (`processID`),
  KEY `machineID` (`machineID`),
  KEY `fk_process_bussiness_key_idx` (`processBusinessKeysID`)
) ENGINE=InnoDB AUTO_INCREMENT=28242147 DEFAULT CHARSET=utf8;

/*Table structure for table `processes` */

DROP TABLE IF EXISTS `processes`;

CREATE TABLE `processes` (
  `processesID` int(11) NOT NULL AUTO_INCREMENT,
  `processID` char(10) DEFAULT NULL,
  `machineID` char(36) DEFAULT NULL,
  `locationsID` int(11) DEFAULT NULL,
  `configurationsID` int(11) DEFAULT NULL,
  `reviveSuccessful` tinyint(1) DEFAULT NULL,
  `processDatetime` datetime DEFAULT NULL,
  PRIMARY KEY (`processesID`),
  KEY `processID` (`processID`),
  KEY `machineID` (`machineID`),
  KEY `locationsID` (`locationsID`),
  KEY `configurationsID` (`configurationsID`)
) ENGINE=InnoDB AUTO_INCREMENT=30850 DEFAULT CHARSET=utf8;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
