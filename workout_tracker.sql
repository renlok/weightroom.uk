-- phpMyAdmin SQL Dump
-- version 4.0.10deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Feb 25, 2015 at 10:09 PM
-- Server version: 5.5.41-0ubuntu0.14.04.1
-- PHP Version: 5.5.9-1ubuntu4.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `workout_tracker`
--

-- --------------------------------------------------------

--
-- Table structure for table `exercises`
--

CREATE TABLE IF NOT EXISTS `exercises` (
  `exercise_id` int(15) NOT NULL AUTO_INCREMENT,
  `exercise_name` varchar(70) NOT NULL,
  `user_id` int(15) NOT NULL,
  PRIMARY KEY (`exercise_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `exercises`
--

INSERT INTO `exercises` (`exercise_id`, `exercise_name`, `user_id`) VALUES
(1, 'deadlift\r', 1),
(2, 'Overhead Press\r', 1);

-- --------------------------------------------------------

--
-- Table structure for table `exercise_records`
--

CREATE TABLE IF NOT EXISTS `exercise_records` (
  `pr_id` int(15) NOT NULL AUTO_INCREMENT,
  `exercise_id` int(15) NOT NULL,
  `user_id` int(15) NOT NULL,
  `pr_date` date NOT NULL,
  `pr_weight` decimal(20,3) NOT NULL,
  `pr_reps` int(15) NOT NULL,
  PRIMARY KEY (`pr_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

--
-- Dumping data for table `exercise_records`
--

INSERT INTO `exercise_records` (`pr_id`, `exercise_id`, `user_id`, `pr_date`, `pr_weight`, `pr_reps`) VALUES
(1, 1, 1, '2015-02-25', 90.000, 5),
(2, 1, 1, '2015-02-25', 120.000, 3),
(3, 1, 1, '2015-02-25', 130.000, 2),
(4, 1, 1, '2015-02-25', 140.000, 1),
(5, 2, 1, '2015-02-25', 40.000, 5);

-- --------------------------------------------------------

--
-- Table structure for table `logs`
--

CREATE TABLE IF NOT EXISTS `logs` (
  `log_id` int(15) NOT NULL AUTO_INCREMENT,
  `user_id` int(15) NOT NULL,
  `log_date` date NOT NULL,
  `log_text` text NOT NULL,
  `log_comment` text NOT NULL,
  `log_weight` decimal(5,3) NOT NULL,
  PRIMARY KEY (`log_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `logs`
--

INSERT INTO `logs` (`log_id`, `user_id`, `log_date`, `log_text`, `log_comment`, `log_weight`) VALUES
(1, 1, '2015-02-25', '#deadlift\r\n40kg x 5\r\n70kg x 5\r\n90kg x 5\r\n100kg x 3\r\n120kg x 3\r\n130kg x 2\r\n140kg x 1\r\n\r\n#Overhead Press\r\n20kg x 5\r\n30kg x5\r\n40kg x5x3\r\n', '', 87.000);

-- --------------------------------------------------------

--
-- Table structure for table `log_exercises`
--

CREATE TABLE IF NOT EXISTS `log_exercises` (
  `logex_id` int(15) NOT NULL AUTO_INCREMENT,
  `logex_date` date NOT NULL,
  `log_id` int(15) NOT NULL,
  `user_id` int(15) NOT NULL,
  `exercise_id` int(15) NOT NULL,
  `logex_volume` decimal(20,3) NOT NULL,
  `logex_reps` int(15) NOT NULL,
  `logex_sets` int(15) NOT NULL,
  `logex_comment` text NOT NULL,
  PRIMARY KEY (`logex_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=11 ;

--
-- Dumping data for table `log_exercises`
--

INSERT INTO `log_exercises` (`logex_id`, `logex_date`, `log_id`, `user_id`, `exercise_id`, `logex_volume`, `logex_reps`, `logex_sets`, `logex_comment`) VALUES
(9, '2015-02-25', 1, 1, 1, 2060.000, 24, 7, '\r'),
(10, '2015-02-25', 1, 1, 2, 850.000, 15, 5, '');

-- --------------------------------------------------------

--
-- Table structure for table `log_items`
--

CREATE TABLE IF NOT EXISTS `log_items` (
  `logitem_id` int(15) NOT NULL AUTO_INCREMENT,
  `logitem_date` date NOT NULL,
  `log_id` int(15) NOT NULL,
  `user_id` int(15) NOT NULL,
  `exercise_id` int(15) NOT NULL,
  `logitem_weight` decimal(20,3) NOT NULL,
  `logitem_reps` int(15) NOT NULL,
  `logitem_sets` int(15) NOT NULL,
  `logitem_comment` text NOT NULL,
  PRIMARY KEY (`logitem_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=21 ;

--
-- Dumping data for table `log_items`
--

INSERT INTO `log_items` (`logitem_id`, `logitem_date`, `log_id`, `user_id`, `exercise_id`, `logitem_weight`, `logitem_reps`, `logitem_sets`, `logitem_comment`) VALUES
(11, '2015-02-25', 1, 1, 1, 40.000, 5, 1, '\r'),
(12, '2015-02-25', 1, 1, 1, 70.000, 5, 1, '\r'),
(13, '2015-02-25', 1, 1, 1, 90.000, 5, 1, '\r'),
(14, '2015-02-25', 1, 1, 1, 100.000, 3, 1, '\r'),
(15, '2015-02-25', 1, 1, 1, 120.000, 3, 1, '\r'),
(16, '2015-02-25', 1, 1, 1, 130.000, 2, 1, '\r'),
(17, '2015-02-25', 1, 1, 1, 140.000, 1, 1, '\r'),
(18, '2015-02-25', 1, 1, 2, 20.000, 5, 1, '\r'),
(19, '2015-02-25', 1, 1, 2, 30.000, 5, 1, '\r'),
(20, '2015-02-25', 1, 1, 2, 40.000, 5, 3, '\r');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int(15) NOT NULL AUTO_INCREMENT,
  `user_name` varchar(75) NOT NULL,
  `user_pass` varchar(150) NOT NULL,
  `user_hash` varchar(150) NOT NULL,
  `user_email` varchar(150) NOT NULL,
  `user_weight` decimal(5,3) NOT NULL,
  `user_unit` int(1) NOT NULL DEFAULT '1',
  `user_lastlogin` date NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `user_name`, `user_pass`, `user_hash`, `user_email`, `user_weight`, `user_unit`, `user_lastlogin`) VALUES
(1, 'renlok', '$2a$08$zVHu4ChYn8ZS6ppYksCXAeRgTkAkaPIiQYmlT11Ogtif1MnIgi5QC', 'klu3s', 'hopher10@googlemail.com', 87.000, 1, '2015-02-25');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
