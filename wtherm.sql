-- phpMyAdmin SQL Dump
-- version 4.0.4
-- http://www.phpmyadmin.net
--
-- Machine: localhost
-- Genereertijd: 16 jan 2015 om 21:25
-- Serverversie: 5.6.12-log
-- PHP-versie: 5.4.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Databank: `wtherm`
--
CREATE DATABASE IF NOT EXISTS `wtherm` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `wtherm`;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `log`
--

CREATE TABLE IF NOT EXISTS `log` (
  `Time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `T` float(3,1) NOT NULL,
  `T_target` float(3,1) NOT NULL,
  `T_o` float(3,1) NOT NULL,
  `Heating` tinyint(1) NOT NULL,
  PRIMARY KEY (`Time`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Gegevens worden uitgevoerd voor tabel `log`
--

INSERT INTO `log` (`Time`, `T`, `T_target`, `T_o`, `Heating`) VALUES
('2015-01-16 14:30:00', 15.0, 15.0, 5.5, 0);

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `schedule`
--

CREATE TABLE IF NOT EXISTS `schedule` (
  `T_target` float(3,1) NOT NULL,
  `time` varchar(5) NOT NULL,
  `day` int(1) NOT NULL,
  UNIQUE KEY `timeday` (`time`,`day`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Gegevens worden uitgevoerd voor tabel `schedule`
--

INSERT INTO `schedule` (`T_target`, `time`, `day`) VALUES
(21.0, '06:00', 1),
(15.0, '21:00', 1);

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `status`
--

CREATE TABLE IF NOT EXISTS `status` (
  `T` float(3,1) NOT NULL,
  `T_o` float(3,1) NOT NULL,
  `T_target` float(3,1) NOT NULL,
  `Override` tinyint(1) NOT NULL,
  `Heating` tinyint(1) NOT NULL,
  `Last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `Last_update` (`Last_update`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Gegevens worden uitgevoerd voor tabel `status`
--

INSERT INTO `status` (`T`, `T_o`, `T_target`, `Override`, `Heating`, `Last_update`) VALUES
(17.5, 5.0, 15.0, 1, 0, '2015-01-16 15:00:00');

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
