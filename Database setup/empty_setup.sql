-- phpMyAdmin SQL Dump
-- version 3.X.XX
-- http://www.phpmyadmin.net
--
-- Host: XXX
-- Generation Time: XXX
-- Server version: 5.X.XX
-- PHP Version: 5.X.XX

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `MYSQL_DATABASE`
--

-- --------------------------------------------------------

--
-- Table structure for table `peopletomeet`
--

DROP TABLE IF EXISTS `peopletomeet`;
CREATE TABLE IF NOT EXISTS `peopletomeet` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created` datetime NOT NULL,
  `order` int(11) NOT NULL,
  `userid` int(11) NOT NULL,
  `usertomeetid` int(11) NOT NULL,
  `private` int(11) NOT NULL,
  `hidden` int(11) NOT NULL,
  `fullname` varchar(255) NOT NULL,
  `picture_url` varchar(255) NOT NULL,
  `big_picture_url` varchar(255) NOT NULL,
  `firstname` varchar(255) NOT NULL,
  `lastname` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `bio` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `linkedin_id` varchar(255) NOT NULL,
  `linkedin_username` varchar(255) NOT NULL,
  `linkedin_name` varchar(255) NOT NULL,
  `linkedin_img_url` varchar(255) NOT NULL,
  `twitter_id` bigint(20) NOT NULL,
  `twitter_username` varchar(255) NOT NULL,
  `twitter_name` varchar(255) NOT NULL,
  `twitter_img_url` varchar(255) NOT NULL,
  `twitter_verified` int(11) NOT NULL,
  `facebook_id` bigint(20) NOT NULL,
  `facebook_username` varchar(255) NOT NULL,
  `facebook_name` varchar(255) NOT NULL,
  `facebook_img_url` varchar(255) NOT NULL,
  `facebook_gender` varchar(255) NOT NULL,
  `web` varchar(255) NOT NULL,
  `reason` varchar(511) NOT NULL,
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `userid` (`userid`),
  KEY `usertomeetid` (`usertomeetid`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=183 ;


-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created` datetime NOT NULL,
  `last_login` datetime NOT NULL,
  `private` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `verified` int(11) NOT NULL,
  `password` varchar(255) NOT NULL,
  `fullname` varchar(255) NOT NULL,
  `picture_url` varchar(255) NOT NULL,
  `big_picture_url` varchar(255) NOT NULL,
  `firstname` varchar(255) NOT NULL,
  `lastname` varchar(255) NOT NULL,
  `bio` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `linkedin_id` varchar(255) NOT NULL,
  `linkedin_token` varchar(255) NOT NULL,
  `linkedin_token_secret` varchar(255) NOT NULL,
  `linkedin_token_expires` bigint(20) NOT NULL,
  `linkedin_name` varchar(255) NOT NULL,
  `linkedin_username` varchar(255) NOT NULL,
  `linkedin_img_url` varchar(255) NOT NULL,
  `twitter_id` bigint(20) NOT NULL,
  `twitter_token` varchar(255) NOT NULL,
  `twitter_token_secret` varchar(255) NOT NULL,
  `twitter_name` varchar(255) NOT NULL,
  `twitter_username` varchar(255) NOT NULL,
  `twitter_img_url` varchar(255) NOT NULL,
  `twitter_verified` int(11) NOT NULL,
  `facebook_id` bigint(11) NOT NULL,
  `facebook_name` varchar(255) NOT NULL,
  `facebook_username` varchar(255) NOT NULL,
  `facebook_img_url` varchar(255) NOT NULL,
  `facebook_gender` varchar(255) NOT NULL,
  `web` varchar(255) NOT NULL,
  `interested_in` text NOT NULL,
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=86 ;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `created`, `last_login`, `private`, `email`, `verified`, `password`, `fullname`, `picture_url`, `big_picture_url`, `firstname`, `lastname`, `bio`, `location`, `linkedin_id`, `linkedin_token`, `linkedin_token_secret`, `linkedin_token_expires`, `linkedin_name`, `linkedin_username`, `linkedin_img_url`, `twitter_id`, `twitter_token`, `twitter_token_secret`, `twitter_name`, `twitter_username`, `twitter_img_url`, `twitter_verified`, `facebook_id`, `facebook_name`, `facebook_username`, `facebook_img_url`, `facebook_gender`, `web`, `interested_in`, `last_updated`) VALUES
(41, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, 'team@whoyoumeet.com', 1, '', 'Who You Meet team', 'http://a0.twimg.com/profile_images/2916952790/e3cb7188e5e84d50a3fd49f9944c69e4_normal.png', 'https://si0.twimg.com/profile_images/2916952790/e3cb7188e5e84d50a3fd49f9944c69e4_bigger.png', '', '', 'We help you to meet more people you need.', 'Internet', '', '', '', 0, '', '0', '', 924243253, '', '', 'Who You Meet team', 'WhoYouMeet', 'http://a0.twimg.com/profile_images/2916952790/e3cb7188e5e84d50a3fd49f9944c69e4_normal.png', 0, 0, '', '', '', '', 'http://whoyoumeet.com', 'Helping people meet more interesting people!', '2012-11-20 20:01:01');

-- !!! You MUST have this record with the id of 41
-- It is not even in the config file, it's right in the code, where it should be :)
-- Eugene

-- --------------------------------------------------------

--
-- Table structure for table `user_keys`
--

DROP TABLE IF EXISTS `user_keys`;
CREATE TABLE IF NOT EXISTS `user_keys` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL,
  `key` varchar(255) NOT NULL,
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`userid`,`key`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=33 ;

--
-- Constraints for table `peopletomeet`
--
ALTER TABLE `peopletomeet`
  ADD CONSTRAINT `peopletomeet_ibfk_1` FOREIGN KEY (`userid`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
