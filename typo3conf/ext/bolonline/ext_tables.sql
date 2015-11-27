-- phpMyAdmin SQL Dump
-- version 3.2.5
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 22. Februar 2011 um 16:49
-- Server Version: 5.1.41
-- PHP-Version: 5.3.2-1ubuntu4.7

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Datenbank: `BlumenbachOnline_productive`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tx_bolonline_Hauptkategorie`
--

CREATE TABLE IF NOT EXISTS `tx_bolonline_Hauptkategorie` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `kategorie` tinytext,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tx_bolonline_HauptkategorieZuordnung`
--

CREATE TABLE IF NOT EXISTS `tx_bolonline_HauptkategorieZuordnung` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `kategorie_id` bigint(20) NOT NULL,
  `kerndaten_id` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kerndaten_id` (`kerndaten_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tx_bolonline_Kerndaten`
--

CREATE TABLE IF NOT EXISTS `tx_bolonline_Kerndaten` (
  `kerndaten_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT '0',
  `pid` int(11) NOT NULL DEFAULT '0',
  `tstamp` int(11) NOT NULL DEFAULT '0',
  `crdate` int(11) NOT NULL DEFAULT '0',
  `cruser_id` int(11) NOT NULL DEFAULT '0',
  `a0` text,
  `a1` text,
  `a2` text,
  `a3` text,
  `a5` text,
  `a7a` text,
  `a7b` text,
  `a8` text,
  `a9a` text,
  `a9b` text,
  `a9c` text,
  `a12a` text,
  `a12b` text,
  `a12c` text,
  `a12d` text,
  `a12e` text,
  `a12f` text,
  `a12g` text,
  `a12h` text,
  `a12i` text,
  `a13a` text,
  `a13b` text,
  `a14a` text,
  `a14b` text,
  `a15a` text,
  `a15b` text,
  `a16a` text,
  `a16b` text,
  `a17` text,
  `a18` text,
  `a19` text,
  `a20` text,
  `a21` text,
  `a22` text,
  `a23` text,
  `a24a` text,
  `a24b` text,
  `f1` text,
  `f2a` text,
  `f2b` text,
  `f3a` text,
  `f3b` text,
  `f4` text,
  `f5` text,
  `f6` text,
  `f7` text,
  `f8` text,
  `f9` text,
  PRIMARY KEY (`kerndaten_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tx_bolonline_Mediafiles`
--

CREATE TABLE IF NOT EXISTS `tx_bolonline_Mediafiles` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `kerndaten_id` bigint(20) DEFAULT '0',
  `file_uri` tinytext,
  `block_nr` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tx_bolonline_Mediafiles_PartI`
--

CREATE TABLE IF NOT EXISTS `tx_bolonline_Mediafiles_PartI` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `partI_id` bigint(20) DEFAULT '0',
  `file_uri` tinytext,
  `block_nr` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tx_bolonline_Mediafiles_PartII`
--

CREATE TABLE IF NOT EXISTS `tx_bolonline_Mediafiles_PartII` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `partII_id` bigint(20) DEFAULT '0',
  `file_uri` tinytext,
  `block_nr` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tx_bolonline_Mediafiles_PartIII`
--

CREATE TABLE IF NOT EXISTS `tx_bolonline_Mediafiles_PartIII` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `partIII_id` bigint(20) DEFAULT '0',
  `file_uri` tinytext,
  `block_nr` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tx_bolonline_Mediafiles_PartIV`
--

CREATE TABLE IF NOT EXISTS `tx_bolonline_Mediafiles_PartIV` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `partIV_id` bigint(20) DEFAULT '0',
  `file_uri` tinytext,
  `block_nr` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tx_bolonline_PartI`
--

CREATE TABLE IF NOT EXISTS `tx_bolonline_PartI` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `kerndaten_id` bigint(20) DEFAULT '0',
  `block_nr` tinyint(3) unsigned NOT NULL,
  `a4` text,
  `e1` text,
  `e2` text,
  `e3` text,
  `e4a` text,
  `e4b` text,
  `e5a` text,
  `e5b` text,
  `e5c` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tx_bolonline_PartII`
--

CREATE TABLE IF NOT EXISTS `tx_bolonline_PartII` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `kerndaten_id` bigint(20) DEFAULT '0',
  `block_nr` tinyint(3) unsigned NOT NULL,
  `a6` text,
  `b1` text,
  `b2` text,
  `b3a` text,
  `b3b` text,
  `b3c` text,
  `b4a` text,
  `b4b` text,
  `b4c` text,
  `b5` text,
  `b6` text,
  `b7a` text,
  `b7b` text,
  `b8` text,
  `b9a` text,
  `b9b` text,
  `b10a` text,
  `b10b` text,
  `b11a` text,
  `b11b` text,
  `b11c` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tx_bolonline_PartIII`
--

CREATE TABLE IF NOT EXISTS `tx_bolonline_PartIII` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `kerndaten_id` bigint(20) DEFAULT '0',
  `block_nr` tinyint(3) unsigned NOT NULL,
  `a10` text,
  `c1` text,
  `c2` text,
  `c3` text,
  `c4` text,
  `c5` text,
  `c6a` text,
  `c6b` text,
  `c7a` text,
  `c7b` text,
  `c7c` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tx_bolonline_PartIII_associations`
--

CREATE TABLE IF NOT EXISTS `tx_bolonline_PartIII_associations` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `partIII_id` bigint(20) DEFAULT '0',
  `partII_id` bigint(20) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='1:n-Verknuepfungen moeglich';

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tx_bolonline_PartIV`
--

CREATE TABLE IF NOT EXISTS `tx_bolonline_PartIV` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `kerndaten_id` bigint(20) DEFAULT '0',
  `block_nr` tinyint(3) unsigned NOT NULL,
  `a11` text,
  `d1` text,
  `d2` text,
  `d3a` text,
  `d3b` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tx_bolonline_PartIV_associations`
--

CREATE TABLE IF NOT EXISTS `tx_bolonline_PartIV_associations` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `partIV_id` bigint(20) DEFAULT '0',
  `partII_id` bigint(20) DEFAULT '0',
  `partI_id` bigint(20) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='wenn 0, dann keine Verknuepfung mit id vorhanden!';
