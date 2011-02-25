-- phpMyAdmin SQL Dump
-- version 2.11.11.3
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Feb 25, 2011 at 05:35 PM
-- Server version: 5.0.84
-- PHP Version: 5.2.17

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `mensa1`
--

-- --------------------------------------------------------

--
-- Table structure for table `Adresse`
--

DROP TABLE IF EXISTS `Adresse`;
CREATE TABLE IF NOT EXISTS `Adresse` (
  `idMembre` int(10) unsigned NOT NULL,
  `debut` date NOT NULL,
  `fin` date NOT NULL default '0000-00-00',
  `adresse1` varchar(150) collate latin1_general_ci default NULL,
  `adresse2` varchar(150) collate latin1_general_ci default NULL,
  `idVille` int(10) NOT NULL default '0',
  `confidentiel` char(1) collate latin1_general_ci default NULL,
  `conf_web` char(1) collate latin1_general_ci default NULL,
  `annuaire` char(1) collate latin1_general_ci default NULL,
  `carte` char(1) collate latin1_general_ci default NULL,
  `complement` varchar(255) collate latin1_general_ci default NULL,
  `remarques` mediumtext collate latin1_general_ci,
  PRIMARY KEY  (`idMembre`,`debut`,`idVille`),
  KEY `idMembre` (`idMembre`),
  KEY `idVille` (`idVille`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Categorie`
--

DROP TABLE IF EXISTS `Categorie`;
CREATE TABLE IF NOT EXISTS `Categorie` (
  `idCategorie` int(10) unsigned NOT NULL auto_increment,
  `categorie` varchar(50) collate latin1_general_ci default NULL,
  PRIMARY KEY  (`idCategorie`),
  UNIQUE KEY `categorie` (`categorie`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci COMMENT='Cette table contient les catégories de contact : Tel pro,etc' AUTO_INCREMENT=15 ;

-- --------------------------------------------------------

--
-- Table structure for table `Competence`
--

DROP TABLE IF EXISTS `Competence`;
CREATE TABLE IF NOT EXISTS `Competence` (
  `idCompetence` char(1) collate latin1_general_ci NOT NULL,
  `code_competence` char(2) collate latin1_general_ci NOT NULL default '',
  `competence` varchar(20) collate latin1_general_ci default NULL,
  PRIMARY KEY  (`code_competence`),
  KEY `idCompetence` (`idCompetence`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Contact`
--

DROP TABLE IF EXISTS `Contact`;
CREATE TABLE IF NOT EXISTS `Contact` (
  `idContact` int(10) unsigned NOT NULL auto_increment,
  `idMembre` int(10) unsigned NOT NULL,
  `idCategorie` int(10) unsigned NOT NULL,
  `adresse` varchar(255) collate latin1_general_ci default NULL,
  `creation` date default NULL,
  `maj` date default NULL,
  `suppression` date default NULL,
  `confidentiel` char(1) collate latin1_general_ci default NULL,
  `conf_web` char(1) collate latin1_general_ci default NULL,
  `annuaire` char(1) collate latin1_general_ci default NULL,
  `remarques` mediumtext collate latin1_general_ci,
  PRIMARY KEY  (`idContact`),
  KEY `adresse` (`adresse`),
  KEY `idMembre` (`idMembre`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci COMMENT='Cette table contient les Tels, emails, ICQ, etc des membres' AUTO_INCREMENT=24094 ;

-- --------------------------------------------------------

--
-- Table structure for table `Cotisation`
--

DROP TABLE IF EXISTS `Cotisation`;
CREATE TABLE IF NOT EXISTS `Cotisation` (
  `idMembre` int(10) unsigned NOT NULL,
  `debut` date NOT NULL default '0000-00-00',
  `fin` date NOT NULL default '0000-00-00',
  `tarif` char(3) collate latin1_general_ci default NULL,
  `idRegion` char(3) collate latin1_general_ci default NULL,
  `montant` decimal(5,2) default NULL,
  PRIMARY KEY  (`idMembre`,`debut`),
  KEY `fin` (`fin`),
  KEY `debut` (`debut`),
  KEY `idMembre` (`idMembre`),
  KEY `idRegion` (`idRegion`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Departement`
--

DROP TABLE IF EXISTS `Departement`;
CREATE TABLE IF NOT EXISTS `Departement` (
  `departement` varchar(100) collate latin1_general_ci default NULL,
  `code` int(10) unsigned NOT NULL default '0',
  `region` char(3) collate latin1_general_ci default NULL,
  PRIMARY KEY  (`code`),
  KEY `region` (`region`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Interet`
--

DROP TABLE IF EXISTS `Interet`;
CREATE TABLE IF NOT EXISTS `Interet` (
  `idInteret` int(10) NOT NULL auto_increment,
  `description` varchar(255) collate latin1_general_ci default NULL,
  PRIMARY KEY  (`idInteret`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=7462 ;

-- --------------------------------------------------------

--
-- Table structure for table `Interet_membre`
--

DROP TABLE IF EXISTS `Interet_membre`;
CREATE TABLE IF NOT EXISTS `Interet_membre` (
  `idInteret` int(10) unsigned NOT NULL,
  `idMembre` int(10) unsigned NOT NULL,
  `competence` char(1) collate latin1_general_ci NOT NULL default '-',
  `niveau_interet` char(1) collate latin1_general_ci NOT NULL default '0',
  `suppression` date NOT NULL default '0000-00-00',
  `commentaire` mediumtext collate latin1_general_ci,
  PRIMARY KEY  (`idInteret`,`idMembre`),
  KEY `idInteret` (`idInteret`),
  KEY `idMembre` (`idMembre`),
  KEY `competence` (`competence`),
  KEY `niveau_interet` (`niveau_interet`),
  KEY `suppression` (`suppression`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Langue`
--

DROP TABLE IF EXISTS `Langue`;
CREATE TABLE IF NOT EXISTS `Langue` (
  `idLangue` char(3) collate latin1_general_ci NOT NULL,
  `langue` varchar(50) collate latin1_general_ci default NULL,
  `commentaire` varchar(255) collate latin1_general_ci default NULL,
  PRIMARY KEY  (`idLangue`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Langue_membre`
--

DROP TABLE IF EXISTS `Langue_membre`;
CREATE TABLE IF NOT EXISTS `Langue_membre` (
  `idLangue` char(3) collate latin1_general_ci NOT NULL,
  `idMembre` int(10) unsigned NOT NULL,
  `idNiveau` char(1) collate latin1_general_ci default NULL,
  `suppression` date NOT NULL default '0000-00-00',
  PRIMARY KEY  (`idLangue`,`idMembre`),
  KEY `idLangue` (`idLangue`),
  KEY `idMembre` (`idMembre`),
  KEY `idNiveau` (`idNiveau`),
  KEY `suppression` (`suppression`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Membre`
--

DROP TABLE IF EXISTS `Membre`;
CREATE TABLE IF NOT EXISTS `Membre` (
  `idMembre` int(10) unsigned NOT NULL,
  `creation` date NOT NULL default '0000-00-00',
  `civilite` varchar(4) collate latin1_general_ci default NULL,
  `nom` varchar(50) collate latin1_general_ci default NULL,
  `prenom` varchar(50) collate latin1_general_ci default NULL,
  `particule` varchar(3) collate latin1_general_ci default NULL,
  `enfant` varchar(50) collate latin1_general_ci default NULL,
  `idSituation_familiale` int(10) default NULL,
  `idWeb` varchar(50) collate latin1_general_ci default NULL,
  `passWeb` char(32) collate latin1_general_ci default NULL,
  `password` char(32) collate latin1_general_ci default NULL,
  `idRegion` char(3) collate latin1_general_ci default NULL,
  `nom_reel` varchar(50) collate latin1_general_ci default NULL,
  `prenom_reel` varchar(50) collate latin1_general_ci default NULL,
  `date_naissance` date default NULL,
  `conf_naissance` char(1) collate latin1_general_ci NOT NULL default '0',
  `idVille_naissance` int(10) default NULL,
  `devise` text collate latin1_general_ci,
  `droits` int(8) NOT NULL default '0',
  `conf_web` char(1) collate latin1_general_ci NOT NULL default '0',
  `photo` varchar(255) collate latin1_general_ci default NULL,
  PRIMARY KEY  (`idMembre`),
  KEY `idSituation_familiale` (`idSituation_familiale`),
  KEY `idRegion` (`idRegion`),
  KEY `conf_web` (`conf_web`),
  KEY `idWeb` (`idWeb`),
  KEY `passWeb` (`passWeb`),
  KEY `password` (`password`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Niveau_interet`
--

DROP TABLE IF EXISTS `Niveau_interet`;
CREATE TABLE IF NOT EXISTS `Niveau_interet` (
  `idNiveau_interet` char(1) collate latin1_general_ci NOT NULL,
  `Niveau_interet` varchar(30) collate latin1_general_ci default NULL,
  PRIMARY KEY  (`idNiveau_interet`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Niveau_langue`
--

DROP TABLE IF EXISTS `Niveau_langue`;
CREATE TABLE IF NOT EXISTS `Niveau_langue` (
  `idNiveau` char(1) collate latin1_general_ci NOT NULL,
  `niveau` varchar(15) collate latin1_general_ci default NULL,
  PRIMARY KEY  (`idNiveau`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Pays`
--

DROP TABLE IF EXISTS `Pays`;
CREATE TABLE IF NOT EXISTS `Pays` (
  `idPays` int(10) unsigned NOT NULL auto_increment,
  `pays` varchar(100) collate latin1_general_ci default NULL,
  PRIMARY KEY  (`idPays`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=214 ;

-- --------------------------------------------------------

--
-- Table structure for table `Region`
--

DROP TABLE IF EXISTS `Region`;
CREATE TABLE IF NOT EXISTS `Region` (
  `idRegion` char(3) collate latin1_general_ci NOT NULL,
  `region` char(100) collate latin1_general_ci default NULL,
  `idPresident` int(10) unsigned default NULL,
  `idTresorier` int(10) unsigned default NULL,
  `idSecretaire` int(10) unsigned default NULL,
  `Commentaire` mediumtext collate latin1_general_ci,
  PRIMARY KEY  (`idRegion`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Situation_familiale`
--

DROP TABLE IF EXISTS `Situation_familiale`;
CREATE TABLE IF NOT EXISTS `Situation_familiale` (
  `idSituation` int(10) unsigned NOT NULL auto_increment,
  `situation_familiale` varchar(50) collate latin1_general_ci default NULL,
  PRIMARY KEY  (`idSituation`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=60 ;

-- --------------------------------------------------------

--
-- Table structure for table `Ville`
--

DROP TABLE IF EXISTS `Ville`;
CREATE TABLE IF NOT EXISTS `Ville` (
  `idVille` int(10) unsigned NOT NULL auto_increment,
  `ville` varchar(150) collate latin1_general_ci default NULL,
  `cp` varchar(10) collate latin1_general_ci default NULL,
  `idDepartement` int(10) unsigned default NULL,
  `idPays` int(10) default NULL,
  PRIMARY KEY  (`idVille`),
  KEY `idDepartement` (`idDepartement`),
  KEY `idPays` (`idPays`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=100300 ;
