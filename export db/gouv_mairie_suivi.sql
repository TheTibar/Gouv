-- phpMyAdmin SQL Dump
-- version OVH
-- https://www.phpmyadmin.net/
--
-- Hôte : reivaxweiradmind.mysql.db
-- Généré le :  lun. 07 sep. 2020 à 17:08
-- Version du serveur :  5.6.48-log
-- Version de PHP :  7.2.31

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données :  `reivaxweiradmind`
--

-- --------------------------------------------------------

--
-- Structure de la table `gouv_mairie_suivi`
--

CREATE TABLE `gouv_mairie_suivi` (
  `suivi_key` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `suivi_value` int(11) NOT NULL,
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `gouv_mairie_suivi`
--

INSERT INTO `gouv_mairie_suivi` (`suivi_key`, `suivi_value`, `created_on`) VALUES
('LAST_PAGE', 1203, '2020-09-07 16:25:29'),
('CURRENT_PAGE', 1107, '2020-09-07 16:26:16');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
