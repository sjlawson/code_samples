/*
SQLyog Enterprise v11.33 (64 bit)
MySQL - 5.5.41-0ubuntu0.14.04.1 : Database - revive_internal
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`revive_internal` /*!40100 DEFAULT CHARACTER SET utf8 */;

USE `revive_internal`;

/*Table structure for table `accounts` */

DROP TABLE IF EXISTS `accounts`;

CREATE TABLE `accounts` (
  `accountsID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Table Surrogate Key (SK)',
  `name` varchar(64) NOT NULL COMMENT 'Name of the account owner.',
  `address` varchar(128) NOT NULL COMMENT 'Address of the account owner.',
  `city` varchar(64) NOT NULL COMMENT 'City of the account owner.',
  `state` char(2) NOT NULL COMMENT 'State of the account owner.',
  `postal` varchar(16) NOT NULL COMMENT 'Postal code of the account owner.',
  `email` varchar(128) NOT NULL COMMENT 'Email address for the account owner.',
  `phone` varchar(16) NOT NULL COMMENT 'Phone number of the account owner.',
  `fax` varchar(16) DEFAULT NULL COMMENT 'Fax number for the account owner.',
  `website` varchar(256) DEFAULT NULL COMMENT 'The website of the account owner.',
  `dateAdded` date NOT NULL,
  PRIMARY KEY (`accountsID`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

/*Table structure for table `component_types` */

DROP TABLE IF EXISTS `component_types`;

CREATE TABLE `component_types` (
  `componentTypesID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Table Surrogate Key (SK)',
  `name` varchar(32) NOT NULL COMMENT 'Name of the component type.',
  `description` varchar(256) DEFAULT NULL COMMENT 'Description of the component type.',
  PRIMARY KEY (`componentTypesID`),
  UNIQUE KEY `name_UNIQUE` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

/*Table structure for table `components` */

DROP TABLE IF EXISTS `components`;

CREATE TABLE `components` (
  `componentsID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Table Surrogate Key (SK)',
  `componentTypesID` int(10) unsigned NOT NULL COMMENT 'Table Foreign Key (FK)',
  `name` varchar(64) NOT NULL COMMENT 'Name of the component.',
  `partNumber` varchar(64) DEFAULT NULL COMMENT 'Part number of the component.',
  `dateAdded` date NOT NULL,
  PRIMARY KEY (`componentsID`),
  KEY `componentTypesID` (`componentTypesID`),
  CONSTRAINT `fk_components_componentTypesID` FOREIGN KEY (`componentTypesID`) REFERENCES `component_types` (`componentTypesID`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

/*Table structure for table `configurations` */

DROP TABLE IF EXISTS `configurations`;

CREATE TABLE `configurations` (
  `configurationsID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Table Surrogate Key (SK)',
  `name` varchar(36) NOT NULL,
  `dateCreated` date NOT NULL COMMENT 'Date this configuration was created.',
  PRIMARY KEY (`configurationsID`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;

/*Table structure for table `configurations_components` */

DROP TABLE IF EXISTS `configurations_components`;

CREATE TABLE `configurations_components` (
  `configurationsComponentsID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Table Surrogate Key (SK)',
  `configurationsID` int(10) unsigned NOT NULL COMMENT 'Table Foreign Key (FK)',
  `componentsID` int(10) unsigned NOT NULL COMMENT 'Table Foreign Key (FK)',
  PRIMARY KEY (`configurationsComponentsID`),
  KEY `configurationsID` (`configurationsID`),
  KEY `componentsID` (`componentsID`),
  CONSTRAINT `fk_configurations_components_componentsID` FOREIGN KEY (`componentsID`) REFERENCES `components` (`componentsID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_configurations_components_configurationsID` FOREIGN KEY (`configurationsID`) REFERENCES `configurations` (`configurationsID`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=utf8;

/*Table structure for table `configurations_managed_files` */

DROP TABLE IF EXISTS `configurations_managed_files`;

CREATE TABLE `configurations_managed_files` (
  `configurationsManagedFilesID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Table Surrogate Key (SK)',
  `configurationsID` int(10) unsigned NOT NULL COMMENT 'Table Foreign Key (FK)',
  `managedFilesID` int(10) unsigned NOT NULL COMMENT 'Table Foreign Key (FK)',
  PRIMARY KEY (`configurationsManagedFilesID`),
  KEY `configurationsID` (`configurationsID`),
  KEY `managedFilesID` (`managedFilesID`),
  CONSTRAINT `fk_configurations_managed_files_configurationsID` FOREIGN KEY (`configurationsID`) REFERENCES `configurations` (`configurationsID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_configurations_managed_files_managedFilesID` FOREIGN KEY (`managedFilesID`) REFERENCES `managed_files` (`managedFilesID`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=490 DEFAULT CHARSET=utf8;

/*Table structure for table `contact_types` */

DROP TABLE IF EXISTS `contact_types`;

CREATE TABLE `contact_types` (
  `contactTypesID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Table Surrogate Key (SK)',
  `name` varchar(32) NOT NULL COMMENT 'Name for this contact type',
  PRIMARY KEY (`contactTypesID`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

/*Table structure for table `contacts` */

DROP TABLE IF EXISTS `contacts`;

CREATE TABLE `contacts` (
  `contactsID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Table Surrogate Key (SK)',
  `accountsID` int(10) unsigned NOT NULL COMMENT 'Table Foreign Key (FK)',
  `contactTypesID` int(10) unsigned NOT NULL COMMENT 'Table Foreign Key (FK)',
  `firstName` varchar(32) NOT NULL COMMENT 'First name of the contact.',
  `lastName` varchar(32) NOT NULL COMMENT 'Last name of the contact.',
  `email` varchar(64) DEFAULT NULL COMMENT 'Email address of the contact.',
  `phone` varchar(16) DEFAULT NULL COMMENT 'Phone number of the contact.',
  PRIMARY KEY (`contactsID`),
  KEY `accountsID` (`accountsID`),
  KEY `contactsID` (`contactsID`),
  KEY `contactTypesID` (`contactTypesID`),
  CONSTRAINT `fk_accounts_contacts_accountsID` FOREIGN KEY (`accountsID`) REFERENCES `accounts` (`accountsID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_accounts_contacts_contactTypesID` FOREIGN KEY (`contactTypesID`) REFERENCES `contact_types` (`contactTypesID`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `fault_types` */

DROP TABLE IF EXISTS `fault_types`;

CREATE TABLE `fault_types` (
  `faultTypesID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Table Surrogate Key (SK)',
  `name` varchar(32) NOT NULL COMMENT 'Name of the reason type. (populates select lists to allow filtering by type)',
  PRIMARY KEY (`faultTypesID`),
  UNIQUE KEY `name_UNIQUE` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

/*Table structure for table `locations` */

DROP TABLE IF EXISTS `locations`;

CREATE TABLE `locations` (
  `locationsID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Table Surrogate Key (SK)',
  `accountsID` int(10) unsigned NOT NULL COMMENT 'Table Foreign Key (FK)',
  `name` varchar(64) NOT NULL COMMENT 'Name of the location.',
  `phone` varchar(16) NOT NULL,
  `address` varchar(128) NOT NULL COMMENT 'Address of the location.',
  `city` varchar(64) NOT NULL COMMENT 'City of the location',
  `state` char(2) NOT NULL COMMENT 'State of the location.',
  `postal` char(6) NOT NULL COMMENT 'Postal code of the location.',
  `latitude` float(10,6) DEFAULT NULL COMMENT 'Latitude of the location.',
  `longitude` float(10,6) DEFAULT NULL COMMENT 'Longitude of the location.',
  `dateAdded` date NOT NULL,
  `position` text,
  PRIMARY KEY (`locationsID`),
  KEY `accountsID` (`accountsID`),
  CONSTRAINT `fk_locations_accountsID` FOREIGN KEY (`accountsID`) REFERENCES `accounts` (`accountsID`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=319 DEFAULT CHARSET=utf8;

/*Table structure for table `machines` */

DROP TABLE IF EXISTS `machines`;

CREATE TABLE `machines` (
  `machinesID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Table Surrogate Key (SK)',
  `machineID` char(36) NOT NULL COMMENT 'Table Business Key (BK)',
  `controlUnitSerial` varchar(64) DEFAULT NULL COMMENT 'Serial number for the control unit assigned to this machine.',
  `dateCreated` date NOT NULL,
  `configurationsID` int(10) unsigned DEFAULT NULL COMMENT 'Table Foreign Key (FK)',
  `dateConfigured` date DEFAULT NULL COMMENT 'The date the machine was last configured	',
  `locationsID` int(10) unsigned DEFAULT NULL COMMENT 'Table Foreign Key (FK)',
  `dateDeployed` date DEFAULT NULL COMMENT 'Date the machine was last deployed.',
  `dateAdded` date NOT NULL COMMENT 'Date machine was added to database.',
  `caseID` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`machinesID`),
  KEY `machineID` (`machineID`),
  KEY `locationsID` (`locationsID`),
  KEY `configurationsID` (`configurationsID`),
  CONSTRAINT `fk_machines_configurationsID` FOREIGN KEY (`configurationsID`) REFERENCES `configurations` (`configurationsID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_machines_locationsID` FOREIGN KEY (`locationsID`) REFERENCES `locations` (`locationsID`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=329 DEFAULT CHARSET=utf8;

/*Table structure for table `machines_history` */

DROP TABLE IF EXISTS `machines_history`;

CREATE TABLE `machines_history` (
  `machinesHistoryID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Table Surrogate Key (SK)',
  `machinesID` int(10) unsigned NOT NULL COMMENT 'Table Foreign Key (FK)',
  `json` varchar(512) NOT NULL COMMENT 'JSON string containing original machine name/value pairs, as of the date/time added field.',
  `faultTypesID` int(10) unsigned DEFAULT NULL COMMENT 'Table Foreign Key (FK)',
  `notes` text COMMENT 'Any notes regarding this machine.',
  `dateTimeModified` datetime NOT NULL COMMENT 'Date and time machine was modified.',
  PRIMARY KEY (`machinesHistoryID`),
  KEY `machinesID` (`machinesID`),
  KEY `faulTypesID` (`faultTypesID`),
  CONSTRAINT `fk_machines_history_faultTypesID` FOREIGN KEY (`faultTypesID`) REFERENCES `fault_types` (`faultTypesID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_machines_history_machinesID` FOREIGN KEY (`machinesID`) REFERENCES `machines` (`machinesID`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=713 DEFAULT CHARSET=utf8;

/*Table structure for table `managed_file_types` */

DROP TABLE IF EXISTS `managed_file_types`;

CREATE TABLE `managed_file_types` (
  `managedFileTypesID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Table Surrogate Key (SK)',
  `name` varchar(32) NOT NULL COMMENT 'Name of the managed file type.',
  `machineName` varchar(32) NOT NULL COMMENT 'Machine name of the managed file type.',
  `description` varchar(256) DEFAULT NULL COMMENT 'Description of managed file type.',
  PRIMARY KEY (`managedFileTypesID`),
  UNIQUE KEY `name_UNIQUE` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

/*Table structure for table `managed_files` */

DROP TABLE IF EXISTS `managed_files`;

CREATE TABLE `managed_files` (
  `managedFilesID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Table Surrogate Key (SK)',
  `managedFileTypesID` int(10) unsigned DEFAULT NULL COMMENT 'Table Foreign Key',
  `name` varchar(32) NOT NULL COMMENT 'Name of the managed file.',
  `version` varchar(16) NOT NULL COMMENT 'Version of the managed file.',
  `dateAdded` date NOT NULL COMMENT 'Date the managed file was added.',
  PRIMARY KEY (`managedFilesID`),
  KEY `managedFileTypesID` (`managedFileTypesID`),
  CONSTRAINT `fk_managed_files_managedFileTypesID` FOREIGN KEY (`managedFileTypesID`) REFERENCES `managed_file_types` (`managedFileTypesID`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf8;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
