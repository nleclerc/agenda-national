-- phpMyAdmin SQL Dump
-- version 3.3.2deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Feb 25, 2011 at 11:23 AM
-- Server version: 5.1.41
-- PHP Version: 5.3.2-1ubuntu4.7

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `mensa_agenda_idf`
--

-- --------------------------------------------------------

--
-- Table structure for table `categorie`
--

DROP TABLE IF EXISTS `categorie`;
CREATE TABLE IF NOT EXISTS `categorie` (
  `idCategorie` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `categorie` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  PRIMARY KEY (`idCategorie`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10 ;

-- --------------------------------------------------------

--
-- Table structure for table `iActivite`
--

DROP TABLE IF EXISTS `iActivite`;
CREATE TABLE IF NOT EXISTS `iActivite` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `annee` int(11) unsigned NOT NULL,
  `mois` tinyint(4) unsigned NOT NULL,
  `jour` smallint(5) unsigned NOT NULL,
  `membre` int(10) DEFAULT NULL,
  `titre` text CHARACTER SET latin1 COLLATE latin1_german1_ci,
  `texte` longtext CHARACTER SET latin1 COLLATE latin1_german1_ci,
  `id_mensa_fr` int(10) DEFAULT NULL,
  `limite` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=927 ;

-- --------------------------------------------------------

--
-- Table structure for table `iAnnonce`
--

DROP TABLE IF EXISTS `iAnnonce`;
CREATE TABLE IF NOT EXISTS `iAnnonce` (
  `idAnnonce` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `annonce` text CHARACTER SET latin1 COLLATE latin1_general_ci,
  `membre` int(10) DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `categorie` int(10) DEFAULT NULL,
  PRIMARY KEY (`idAnnonce`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=82 ;

-- --------------------------------------------------------

--
-- Table structure for table `iInscription`
--

DROP TABLE IF EXISTS `iInscription`;
CREATE TABLE IF NOT EXISTS `iInscription` (
  `id` int(11) NOT NULL,
  `ref` int(11) NOT NULL,
  PRIMARY KEY (`id`,`ref`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `mensa_idf`
--

DROP TABLE IF EXISTS `mensa_idf`;
CREATE TABLE IF NOT EXISTS `mensa_idf` (
  `ref` int(10) unsigned NOT NULL DEFAULT '0',
  `civilite` varchar(4) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `particule` varchar(2) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `prenom` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `nom` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `region` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `naissance` char(19) CHARACTER SET latin1 COLLATE latin1_german1_ci DEFAULT NULL,
  `creation` char(19) CHARACTER SET latin1 COLLATE latin1_german1_ci DEFAULT NULL,
  `fin` char(19) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `mail1` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `mail2` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `mailpro` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `tel1` varchar(15) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `tel2` varchar(15) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `telpro` varchar(15) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `adresse1` varchar(150) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `adresse2` varchar(150) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `cp` varchar(10) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `ville` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `pays` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`ref`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `mensa_idf_old`
--

DROP TABLE IF EXISTS `mensa_idf_old`;
CREATE TABLE IF NOT EXISTS `mensa_idf_old` (
  `ref` int(10) unsigned NOT NULL DEFAULT '0',
  `civilite` varchar(4) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `particule` varchar(2) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `prenom` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `nom` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `region` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `naissance` char(19) CHARACTER SET latin1 COLLATE latin1_german1_ci DEFAULT NULL,
  `creation` char(19) CHARACTER SET latin1 COLLATE latin1_german1_ci DEFAULT NULL,
  `fin` char(19) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `mail1` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `mail2` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `mailpro` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `tel1` varchar(15) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `tel2` varchar(15) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `telpro` varchar(15) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `adresse1` varchar(150) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `adresse2` varchar(150) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `cp` varchar(10) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `ville` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `pays` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`ref`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
