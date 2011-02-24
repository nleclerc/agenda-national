-- phpMyAdmin SQL Dump
-- version 3.3.2deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Feb 24, 2011 at 10:29 PM
-- Server version: 5.1.41
-- PHP Version: 5.3.2-1ubuntu4.7

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `mensa_calendar`
--

-- --------------------------------------------------------

DROP TABLE IF EXISTS `event`;
DROP TRIGGER IF EXISTS `event_before_insert_created_date`;

DROP TABLE IF EXISTS `event_participation`;


--
-- Table structure for table `event`
--

CREATE TABLE `event` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `author_id` int(11) NOT NULL,
  `region_id` char(3) NOT NULL,
  `creation_date` timestamp NULL DEFAULT NULL,
  `modification_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `start_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `title` varchar(256) NOT NULL,
  `location` varchar(256) NOT NULL,
  `description` text NOT NULL,
  `max_participants` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `start_date` (`start_date`),
  KEY `id_author` (`author_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- trigger to set creation_date automatically.
CREATE TRIGGER event_before_insert_created_date BEFORE INSERT ON `event`
FOR EACH ROW SET NEW.creation_date = CURRENT_TIMESTAMP;



-- --------------------------------------------------------

--
-- Table structure for table `event_participation`
--

CREATE TABLE `event_participation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `creation_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `event_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniqueness` (`event_id`,`member_id`),
  KEY `event_id` (`event_id`),
  KEY `member_id` (`member_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


