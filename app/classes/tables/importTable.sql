-- phpMyAdmin SQL Dump
-- version 4.9.5
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jan 15, 2021 at 11:10 PM
-- Server version: 5.7.30
-- PHP Version: 7.4.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `family`
--

-- --------------------------------------------------------

--
-- Table structure for table `account`
--

CREATE TABLE `account` (
  `no` int(11) NOT NULL,
  `id` varchar(255) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` text NOT NULL,
  `token` text,
  `type` text,
  `secretWord` text NOT NULL,
  `status` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `account`
--

INSERT INTO `account` (`no`, `id`, `email`, `password`, `token`, `type`, `secretWord`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(17, '470258OLAWALE', 'modernman00@yahoo.com', '$2y$15$n7MkKtpSb0k8NoCJiu2MzOaPVmueTxYbpFx4iMMaUfOeuMNB7wXHO', 'AAE14758', '9090@', 'ENIOLA', 'approved', '2020-11-07 13:03:42', '2020-12-07 00:19:22', '2020-12-07 00:19:22'),
(18, '15430JUMOKE', 'wale@showalinvest.com', '$2y$15$s1Zd4Tswp6/UP1Ykc08JVOrGUMkpjM8VSBaml6uqYURx1RC/BVajG', '3DBA065A', '9090@', 'ENIOLA', 'approved', '2020-11-07 23:37:16', '2020-12-14 13:06:48', '2020-12-14 13:06:48'),
(19, '244681DAVID', 'woguns@ymail.com', '$2y$15$wNiSkimSmcYCKCfW4wwqkO4zHtnPhUQivdRE3bfp.IbzIppSOK7Fu', '4DFD5181', 'member', 'eniola', 'approved', '2020-12-04 13:08:09', '2020-12-06 01:18:02', '2020-12-06 01:18:02'),
(20, '644506NIYI', 'wale@loaneasyfinance.com', '$2y$10$HYuAVPHMHhEPl3cmHNtrxunyYBCIKUlhAHK27gvT1lHZg/r56I3hK', 'F08DA7EA', 'member', 'NATIONAL', 'new', '2020-12-09 22:29:38', '2020-12-11 14:56:15', '2020-12-11 14:56:15'),
(98, '92098OLUSOLA', 'waleolaogunrac@gmail.com', '$2y$15$3M0sMH7o0GJz1D3cvluK1.5xlDLdB6Fxx3O8vZArBJ7OQUktrG9WK', '33CD43E1', 'member', 'polhi', 'approved', '2020-12-09 23:48:49', '2021-01-09 21:46:58', '2021-01-09 21:46:58'),
(102, '794979KEHINDE', 'eniolaoguns@gmail.com', '$2y$10$PorxiVdxagtNF8rG98cZs.iOUOWRDNrgjUA2KlbI4wTlUhYkqNoQ6', NULL, 'member', 'niyi', 'approved', '2020-12-10 00:30:15', '2020-12-10 00:48:50', '2020-12-10 00:48:50');

-- --------------------------------------------------------

--
-- Table structure for table `comment`
--

CREATE TABLE `comment` (
  `comment_no` int(11) NOT NULL,
  `id` varchar(20) NOT NULL,
  `post_no` varchar(30) NOT NULL,
  `fullName` text,
  `comment` text,
  `profileImg` text,
  `post_time` text,
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_updated` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `date_deleted` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `comment`
--

INSERT INTO `comment` (`comment_no`, `id`, `post_no`, `fullName`, `comment`, `profileImg`, `post_time`, `date_created`, `date_updated`, `date_deleted`) VALUES
(83, '470258OLAWALE', '19', 'OLAWALE ', 'gowon was arrested', '', '1607078819', '2020-12-04 10:47:07', NULL, NULL),
(84, '470258OLAWALE', '20', 'OLAWALE OLAOGUN', 'NIGERIA', '', '1607085077', '2020-12-04 12:31:28', NULL, NULL),
(85, '244681DAVID', '21', 'DAVID OLAOGUN', 'JUMOKE', '', '1607091402', '2020-12-04 14:17:56', NULL, NULL),
(86, '244681DAVID', '22', 'DAVID OLAOGUN', 'hhdhfdjdkfd', '', '1607093728', '2020-12-04 14:56:42', NULL, NULL),
(87, '244681DAVID', '21', 'DAVID OLAOGUN', 'cjcjjcc', '', '1607093803', '2020-12-04 14:57:17', NULL, NULL),
(88, '470258OLAWALE', '34', 'OLAWALE OLAOGUN', 'IP changed', 'SAM_0083.JPG', '1607215287', '2020-12-06 00:41:37', NULL, NULL),
(89, '470258OLAWALE', '34', 'OLAWALE OLAOGUN', 'Account is active   The latest statement is based on the plan end date of 1st Jan 2021. ', 'SAM_0083.JPG', '1607215400', '2020-12-06 00:43:28', NULL, NULL),
(90, '470258OLAWALE', '34', 'OLAWALE OLAOGUN', 'Account is active   The latest statement is based on the plan end date of 1st Jan 2021. ', 'SAM_0083.JPG', '1607215400', '2020-12-06 00:44:35', NULL, NULL),
(91, '470258OLAWALE', '34', 'OLAWALE OLAOGUN', 'gowon was arrested', 'SAM_0083.JPG', '1607216812', '2020-12-06 01:07:14', NULL, NULL),
(92, '470258OLAWALE', '33', 'OLAWALE OLAOGUN', 'careless', 'SAM_0083.JPG', '1607217363', '2020-12-06 01:16:51', NULL, NULL),
(93, '470258OLAWALE', '32', 'OLAWALE OLAOGUN', 'admint', 'SAM_0083.JPG', '1607217411', '2020-12-06 01:17:07', NULL, NULL),
(94, '244681DAVID', '34', 'DAVID OLAOGUN', 'could do harm than good', 'SAM_0678.JPG', '1607217514', '2020-12-06 01:19:37', NULL, NULL),
(95, '244681DAVID', '33', 'DAVID OLAOGUN', 'love it', 'SAM_0678.JPG', '1607217578', '2020-12-06 01:19:56', NULL, NULL),
(96, '244681DAVID', '35', 'DAVID OLAOGUN', 'gowon was arrested', 'SAM_0678.JPG', '1607217751', '2020-12-06 01:22:51', NULL, NULL),
(97, '244681DAVID', '38', 'DAVID OLAOGUN', 'I like your hair', 'SAM_0070.JPG', '1607274796', '2020-12-06 17:13:30', NULL, NULL),
(98, '470258OLAWALE', '38', 'OLAWALE OLAOGUN', 'what a great treat ', 'SAM_0083.JPG', '1607300399', '2020-12-07 00:20:45', NULL, NULL),
(99, '244681DAVID', '40', 'DAVID OLAOGUN', 'Php is a powerful language', 'SAM_0070.JPG', '1607300489', '2020-12-07 00:22:23', NULL, NULL),
(100, '470258OLAWALE', '39', 'OLAWALE OLAOGUN', 'Really  I like it too', 'SAM_0083.JPG', '1607300472', '2020-12-07 00:22:37', NULL, NULL),
(101, '470258OLAWALE', '40', 'OLAWALE OLAOGUN', 'I agree ', 'SAM_0083.JPG', '1607300557', '2020-12-07 00:23:04', NULL, NULL),
(102, '244681DAVID', '40', 'DAVID OLAOGUN', '', 'SAM_0070.JPG', '1607300590', '2020-12-07 00:23:18', NULL, NULL),
(103, '244681DAVID', '40', 'DAVID OLAOGUN', 'did you check it out', 'SAM_0070.JPG', '1607300598', '2020-12-07 00:23:30', NULL, NULL),
(104, '470258OLAWALE', '40', 'OLAWALE OLAOGUN', 'Yes  I did... awesome ', 'SAM_0083.JPG', '1607300584', '2020-12-07 00:23:48', NULL, NULL),
(107, '794979KEHINDE', '1000', 'KEHINDE', 'Your comment will show here', NULL, NULL, '2020-12-10 00:30:15', NULL, NULL),
(108, '92098OLUSOLA', '37', 'OLUSOLA OLAOGUN', 'nice pics Jummy', 'books.jpg', '1607562841', '2020-12-10 01:14:36', NULL, NULL),
(109, '92098OLUSOLA', '124', 'OLUSOLA OLAOGUN', 'Olawale s wife', 'books.jpg', '1607563154', '2020-12-10 01:19:33', NULL, NULL),
(110, '92098OLUSOLA', '40', 'OLUSOLA OLAOGUN', 'iT LOOKS NICES ', 'books.jpg', '1607563206', '2020-12-10 01:20:32', NULL, NULL),
(111, '92098OLUSOLA', '125', 'OLUSOLA OLAOGUN', 'It worked', 'books.jpg', '1607563232', '2020-12-10 01:24:04', NULL, NULL),
(112, '92098OLUSOLA', '125', 'OLUSOLA OLAOGUN', 'it didnt work', 'books.jpg', '1607566079', '2020-12-10 02:08:15', NULL, NULL),
(113, '92098OLUSOLA', '125', 'OLUSOLA OLAOGUN', 'IP changed', 'books.jpg', '1607566235', '2020-12-10 02:10:39', NULL, NULL),
(114, '92098OLUSOLA', '125', 'OLUSOLA OLAOGUN', 'I think  it worked', 'books.jpg', '1607566358', '2020-12-10 02:12:43', NULL, NULL),
(115, '92098OLUSOLA', '125', 'OLUSOLA OLAOGUN', 'I think  it worked', 'books.jpg', '1607566358', '2020-12-10 02:12:54', NULL, NULL),
(116, '15430JUMOKE', '119', 'JUMOKE OLAOGUN', 'ssgsgsgsgsgsg', 'SAM_0070.JPG', '1607636734', '2020-12-10 21:47:37', NULL, NULL),
(117, '15430JUMOKE', '123', 'JUMOKE OLAOGUN', 'It is not easy to bear that name', 'SAM_0070.JPG', '1607636857', '2020-12-10 21:48:00', NULL, NULL),
(118, '15430JUMOKE', '126', 'JUMOKE OLAOGUN', 'its not easy to bear that name', 'SAM_0070.JPG', '1607636880', '2020-12-10 21:48:28', NULL, NULL),
(119, '15430JUMOKE', '126', 'JUMOKE OLAOGUN', 'it will bring down the stress', 'SAM_0070.JPG', '1607636998', '2020-12-10 21:50:11', NULL, NULL),
(120, '15430JUMOKE', '127', 'JUMOKE OLAOGUN', 'welldone Jummy', 'SAM_0070.JPG', '1607642486', '2020-12-10 23:21:45', NULL, NULL),
(121, '15430JUMOKE', '128', 'JUMOKE OLAOGUN', 'Check out my code', 'SAM_0070.JPG', '1607642820', '2020-12-10 23:27:17', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `contact`
--

CREATE TABLE `contact` (
  `no` int(11) NOT NULL,
  `id` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `postcode` varchar(50) NOT NULL,
  `region` text NOT NULL,
  `email` varchar(50) NOT NULL,
  `country` varchar(40) NOT NULL,
  `mobile` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `contact`
--

INSERT INTO `contact` (`no`, `id`, `address`, `postcode`, `region`, `email`, `country`, `mobile`, `created_at`, `updated_at`, `deleted_at`) VALUES
(7, '470258OLAWALE', '26 linden way', 'SN55DE', 'LONDON', 'modernman00@yahoo.com', 'UNITED KINGDOM', '447809650816', '2020-11-07 13:03:42', NULL, NULL),
(8, '15430JUMOKE', '26 linden way', 'SN55DE', 'LONDON', 'wale@showalinvest.com', 'UNITED KINGDOM', '447809650816', '2020-11-07 23:37:16', '2020-12-10 20:28:02', '2020-12-10 20:28:02'),
(10, '644506NIYI', '26 Linden way  Peatmoor', 'SN55DE', 'State', 'wale@loaneasyfinance.com', 'UK', '447809650816', '2020-12-09 22:29:38', NULL, NULL),
(82, '92098OLUSOLA', '26 LINDEN WAY', 'SN5 5DE', 'Wiltshire', 'waleolaogunrac@gmail.com', 'Nigeria', '44780965816', '2020-12-09 23:42:02', NULL, NULL),
(92, '794979KEHINDE', '26 LINDEN WAY', 'SN5 5DE', 'Wiltshire', 'eniolaoguns@gmail.com', 'Canada', '44780965816', '2020-12-10 00:30:15', NULL, NULL),
(93, '244681DAVID', '26 LINDEN WAY', 'SN5 5DE', 'Wiltshire', 'woguns@ymail.com', 'Canada', '44780965816', '2020-12-10 00:30:15', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `images`
--

CREATE TABLE `images` (
  `no` int(11) NOT NULL,
  `id` varchar(30) NOT NULL,
  `photo` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `images`
--

INSERT INTO `images` (`no`, `id`, `photo`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '15430JUMOKE', 'Screenshot 2020-12-08 at 14.32.56.png', '2020-12-14 02:00:03', NULL, NULL),
(2, '15430JUMOKE', 'Screenshot 2020-12-10 at 11.45.17.png', '2020-12-14 02:00:03', NULL, NULL),
(3, '15430JUMOKE', 'Screenshot 2020-12-08 at 14.32.56.png', '2020-12-14 02:02:14', NULL, NULL),
(4, '15430JUMOKE', 'Screenshot 2020-12-10 at 11.45.17.png', '2020-12-14 02:02:14', NULL, NULL),
(5, '92098OLUSOLA', 'SAM_0914.JPG', '2021-01-03 16:17:15', NULL, NULL),
(6, '92098OLUSOLA', 'SAM_0915.JPG', '2021-01-03 16:17:15', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `interest`
--

CREATE TABLE `interest` (
  `no` int(11) NOT NULL,
  `id` varchar(255) NOT NULL,
  `favSport` text NOT NULL,
  `footballTeam` text NOT NULL,
  `passion` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `interest`
--

INSERT INTO `interest` (`no`, `id`, `favSport`, `footballTeam`, `passion`, `created_at`, `updated_at`, `deleted_at`) VALUES
(20, '470258OLAWALE', 'FOOTBAL', 'CHELSEA', 'singign', '2020-11-07 13:03:42', NULL, NULL),
(21, '15430JUMOKE', 'FOOTBAL', 'CHELSEA', 'SINGING', '2020-11-07 23:37:16', NULL, NULL),
(22, '244681DAVID', 'FOOTBALL', 'CHELSEA', 'technology', '2020-12-04 13:08:09', NULL, NULL),
(23, '644506NIYI', 'FOOTBALL', 'CHELSEA', 'TRAVELLING', '2020-12-09 22:29:38', NULL, NULL),
(101, '92098OLUSOLA', 'FOOTBALL', 'BARCELONA', 'TECHNOLOGY', '2020-12-09 23:48:49', NULL, NULL),
(105, '794979KEHINDE', 'FOOTBALL', 'BARCELONA', 'TECHNOLOGY', '2020-12-10 00:30:15', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `kids`
--

CREATE TABLE `kids` (
  `no` int(11) NOT NULL,
  `id` varchar(255) NOT NULL,
  `kid_name` text,
  `kid_email` text,
  `kid_linked` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `kids`
--

INSERT INTO `kids` (`no`, `id`, `kid_name`, `kid_email`, `kid_linked`, `created_at`, `updated_at`, `deleted_at`) VALUES
(6, '470258OLAWALE', 'eni', 'none@yahoo.com', NULL, '2020-11-07 13:03:42', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `otherFamily`
--

CREATE TABLE `otherFamily` (
  `no` int(11) NOT NULL,
  `id` varchar(255) NOT NULL,
  `spouseName` text,
  `spouseMobile` text NOT NULL,
  `fatherName` text NOT NULL,
  `fatherMobile` text NOT NULL,
  `motherName` text NOT NULL,
  `motherMobile` text NOT NULL,
  `motherMaiden` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `otherFamily`
--

INSERT INTO `otherFamily` (`no`, `id`, `spouseName`, `spouseMobile`, `fatherName`, `fatherMobile`, `motherName`, `motherMobile`, `motherMaiden`, `created_at`, `updated_at`, `deleted_at`) VALUES
(20, '470258OLAWALE', 'AJIBIKE OLAOGUN', 'bikeoguns@yahoo.co.uk', 'OLUYOMI OLAOGUN', '2348036416079', 'IYABO OLAOGUN', 'NONE@GMAIL.COM', 'MAYUNGBE', '2020-11-07 13:03:42', '2020-12-07 12:55:20', '2020-12-07 12:55:20'),
(21, '15430JUMOKE', 'AJIBIKE OLAOGUN', 'bikeoguns@yahoo.co.uk', 'OLUYOMI OLAOGUN', 'NONE@GMAIL.COM', 'IYABO OLAOGUN', 'NONE@GMAIL.COM', 'MAYUNGBE', '2020-11-07 23:37:16', NULL, NULL),
(22, '244681DAVID', 'none', 'modernman00@yahoo.com', 'OLAWALE OLAOGUN', 'modernman00@yahoo.com', 'IYABO OLAOGUN', 'bikeoguns@yahoo.co.uk', 'MAYUNGBE', '2020-12-04 13:08:09', NULL, NULL),
(23, '462361FEMI', 'Not completed', 'Not completed', 'OLUYOMI OLAOGUN', '2348036416079', 'IYABO OLAOGUN', '2348032326767', 'MAYUNGBE', '2020-12-09 23:29:14', NULL, NULL),
(43, '92098OLUSOLA', 'Not completed', 'Not completed', 'OLUYOMI OLAOGUN', '2348036416079', 'IYABO OLAOGUN', '2348032326767', 'MAYUNGBE', '2020-12-09 23:36:00', NULL, NULL),
(46, '794979KEHINDE', 'BIMPE OLAOGUN', '2348036416079', 'SIJI OLAOGUN', '447809650816', 'EMMA OLAOGUN', '2348032326767', 'MAYUNGBE', '2020-12-10 00:30:15', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `personal`
--

CREATE TABLE `personal` (
  `no` int(11) NOT NULL,
  `id` varchar(255) NOT NULL,
  `firstName` text NOT NULL,
  `lastName` text NOT NULL,
  `alias` text NOT NULL,
  `day` int(11) NOT NULL,
  `month` text NOT NULL,
  `year` int(11) NOT NULL,
  `kids` int(11) NOT NULL,
  `gender` text,
  `noSiblings` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `personal`
--

INSERT INTO `personal` (`no`, `id`, `firstName`, `lastName`, `alias`, `day`, `month`, `year`, `kids`, `gender`, `noSiblings`, `created_at`, `updated_at`, `deleted_at`) VALUES
(9, '470258OLAWALE', 'OLAWALE', 'OLAOGUN', 'MODERNMAN', 0, '', 0, 2, 'Male', 2, '2020-11-07 13:03:42', NULL, NULL),
(10, '15430JUMOKE', 'JUMOKE', 'OLAOGUN', 'Modernman', 0, '', 0, 0, 'Female', 2, '2020-11-07 23:37:16', NULL, NULL),
(11, '244681DAVID', 'DAVID', 'OLAOGUN', 'OLUTOBS', 0, '', 0, 0, 'Female', 2, '2020-12-04 13:08:09', NULL, NULL),
(12, '644506NIYI', 'NIYI', 'OLAOGUN', 'NEYO', 15, 'JULY', 1982, 0, 'Male', 0, '2020-12-09 22:29:38', NULL, NULL),
(90, '92098OLUSOLA', 'OLUSOLA', 'OLAOGUN', 'FEMBA', 15, 'JULY', 1972, 1, 'Female', 1, '2020-12-09 23:48:49', NULL, NULL),
(94, '794979KEHINDE', 'KEHINDE', 'OLAOGUN', 'FEMBA', 15, 'JULY', 1976, 1, 'Female', 1, '2020-12-10 00:30:15', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `post`
--

CREATE TABLE `post` (
  `post_no` int(11) NOT NULL,
  `id` varchar(255) DEFAULT NULL,
  `fullName` text,
  `postMessage` varchar(255) DEFAULT NULL,
  `profileImg` text,
  `post_like` int(11) DEFAULT NULL,
  `post_img0` text,
  `post_img1` text,
  `post_img2` text,
  `post_img3` text,
  `post_img4` text,
  `post_img5` text,
  `post_time` int(11) DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_updated` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `date_deleted` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `post`
--

INSERT INTO `post` (`post_no`, `id`, `fullName`, `postMessage`, `profileImg`, `post_like`, `post_img0`, `post_img1`, `post_img2`, `post_img3`, `post_img4`, `post_img5`, `post_time`, `date_created`, `date_updated`, `date_deleted`) VALUES
(33, '470258OLAWALE', 'OLAWALE OLAOGUN', 'checking something now', 'SAM_0083.JPG', NULL, 'SAM_0618.JPG', NULL, NULL, NULL, NULL, NULL, 1607214393, '2020-12-06 00:38:01', NULL, NULL),
(34, '470258OLAWALE', 'OLAWALE OLAOGUN', 'checking something now', 'SAM_0083.JPG', NULL, 'SAM_0618.JPG', NULL, NULL, NULL, NULL, NULL, 1607214393, '2020-12-06 00:38:24', NULL, NULL),
(35, '244681DAVID', 'DAVID OLAOGUN', 'name is david', 'SAM_0678.JPG', NULL, 'SAM_0678.JPG', NULL, NULL, NULL, NULL, NULL, 1607217706, '2020-12-06 01:22:31', NULL, NULL),
(36, '244681DAVID', 'DAVID OLAOGUN', 'happy birthday Odunayo', 'SAM_0070.JPG', NULL, 'SAM_0070.JPG', 'SAM_0071.JPG', NULL, NULL, NULL, NULL, 1607270395, '2020-12-06 16:00:23', '2020-12-10 22:27:58', '2020-12-10 22:27:58'),
(37, '244681DAVID', 'DAVID OLAOGUN', 'XMAS MUMMY', 'SAM_0070.JPG', NULL, 'SAM_0083.JPG', NULL, NULL, NULL, NULL, NULL, 1607271352, '2020-12-06 16:16:56', NULL, NULL),
(38, '244681DAVID', 'DAVID OLAOGUN', 'Olutobi is doing her hair', 'SAM_0070.JPG', NULL, '_C230013.JPG', NULL, NULL, NULL, NULL, NULL, 1607274660, '2020-12-06 17:13:15', NULL, NULL),
(39, '470258OLAWALE', 'OLAWALE OLAOGUN', 'I love my family so much', 'SAM_0083.JPG', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1607300445, '2020-12-07 00:21:12', NULL, NULL),
(40, '244681DAVID', 'DAVID OLAOGUN', 'Segun is a good boy', 'SAM_0070.JPG', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1607300454, '2020-12-07 00:21:29', NULL, NULL),
(41, '644506NIYI', 'NIYI', 'Hey, welcome to your page', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-12-09 22:29:38', NULL, NULL),
(89, '462361FEMI', 'FEMI', 'Hey, welcome to your page', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-12-09 23:29:14', NULL, NULL),
(119, '92098OLUSOLA', 'OLUSOLA', 'Hey, welcome to your page', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-12-09 23:48:49', NULL, NULL),
(123, '794979KEHINDE', 'KEHINDE', 'Hey, welcome to your page', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2020-12-10 00:30:15', NULL, NULL),
(124, '92098OLUSOLA', 'OLUSOLA OLAOGUN', 'Welldone Ajibike Olaogun', 'books.jpg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1607563118, '2020-12-10 01:19:14', NULL, NULL),
(125, '92098OLUSOLA', 'OLUSOLA OLAOGUN', 'Welldone AJIBIKE OLAOGUN  OLAWALE S FRIEND', 'books.jpg', NULL, 'books.jpg', NULL, NULL, NULL, NULL, NULL, 1607563173, '2020-12-10 01:20:06', NULL, NULL),
(126, '92098OLUSOLA', 'OLUSOLA OLAOGUN', 'testing pag reloading', 'books.jpg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1607565582, '2020-12-10 02:07:59', NULL, NULL),
(127, '15430JUMOKE', 'JUMOKE OLAOGUN', 'Am got into Cambridge  ', 'SAM_0070.JPG', NULL, 'books.jpg', NULL, NULL, NULL, NULL, NULL, 1607642425, '2020-12-10 23:21:25', NULL, NULL),
(128, '15430JUMOKE', 'JUMOKE OLAOGUN', 'Thanks to everyone ', 'SAM_0070.JPG', NULL, 'Screenshot 2020-11-29 at 19.13.27.png', NULL, NULL, NULL, NULL, NULL, 1607642785, '2020-12-10 23:27:00', NULL, NULL),
(129, '92098OLUSOLA', 'OLUSOLA OLAOGUN', 'It is the new year', 'books.jpg', NULL, 'SAM_0926.JPG', 'SAM_0927.JPG', NULL, NULL, NULL, NULL, 1609690635, '2021-01-03 16:17:45', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `profile_pics`
--

CREATE TABLE `profile_pics` (
  `no` int(11) NOT NULL,
  `id` varchar(100) NOT NULL,
  `img` text,
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_updated` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `date_deleted` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `profile_pics`
--

INSERT INTO `profile_pics` (`no`, `id`, `img`, `date_created`, `date_updated`, `date_deleted`) VALUES
(23, '470258OLAWALE', 'SAM_0083.JPG', '2020-12-04 12:18:27', NULL, NULL),
(25, '244681DAVID', 'SAM_0678.JPG', '2020-12-04 14:16:03', NULL, NULL),
(26, '15430JUMOKE', 'SAM_0070.JPG', '2020-12-06 13:47:16', NULL, NULL),
(27, '92098OLUSOLA', 'books.jpg', '2020-12-09 23:48:49', NULL, NULL),
(31, '794979KEHINDE', 'books.jpg', '2020-12-10 00:30:15', NULL, NULL),
(32, '644506NIYI', 'books.jpg', '2020-12-10 00:30:15', NULL, NULL),
(33, '92098OLUSOLA', 'SAM_0961.JPG', '2021-01-09 21:45:22', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `siblings`
--

CREATE TABLE `siblings` (
  `no` int(11) NOT NULL,
  `id` varchar(255) NOT NULL,
  `sibling_name` text,
  `sibling_email` text,
  `sibling_linked` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `siblings`
--

INSERT INTO `siblings` (`no`, `id`, `sibling_name`, `sibling_email`, `sibling_linked`, `created_at`, `updated_at`, `deleted_at`) VALUES
(5, '470258OLAWALE', 'eni', 'none@yahoo.com', NULL, '2020-11-07 13:03:42', NULL, NULL),
(6, '15430JUMOKE', 'OLUWATOBI OLAOGUN', 'oolutobs@gmail.com', NULL, '2020-11-07 23:37:16', NULL, NULL),
(7, '244681DAVID', 'Jumoke Olaogun', 'eniola@gmail.com', NULL, '2020-12-04 13:08:10', NULL, NULL),
(8, '794979KEHINDE', 'Jumoke Olaogun', 'eniola@gmail.com', NULL, '2020-12-04 13:08:10', NULL, NULL),
(9, '644506NIYI', 'Jumoke Olaogun', 'eniola@gmail.com', NULL, '2020-12-04 13:08:10', NULL, NULL),
(10, '92098OLUSOLA', 'OLUWATOBI OLAOGUN', 'oolutobs@gmail.com', NULL, '2020-11-07 23:37:16', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `uploadPics`
--

CREATE TABLE `uploadPics` (
  `no` int(11) NOT NULL,
  `id` varchar(25) NOT NULL,
  `uploadPics` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `work`
--

CREATE TABLE `work` (
  `no` int(11) NOT NULL,
  `id` varchar(255) NOT NULL,
  `employmentStatus` text NOT NULL,
  `occupation` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `work`
--

INSERT INTO `work` (`no`, `id`, `employmentStatus`, `occupation`, `created_at`, `updated_at`, `deleted_at`) VALUES
(8, '470258OLAWALE', 'Self employed', 'ACCOUNTANT', '2020-11-07 13:03:42', NULL, NULL),
(9, '15430JUMOKE', 'Self employed', 'ACCOUNTANT', '2020-11-07 23:37:16', NULL, NULL),
(10, '244681DAVID', 'Unemployed', 'ACCOUNTANT', '2020-12-04 13:08:09', NULL, NULL),
(11, '644506NIYI', 'Self employed', 'ACCOUNTANT', '2020-12-09 22:29:38', NULL, NULL),
(59, '462361FEMI', 'Full time employment', 'ACCOUNTANT', '2020-12-09 23:29:14', NULL, NULL),
(83, '92098OLUSOLA', 'Full time employment', 'DOCTOR', '2020-12-09 23:42:02', NULL, NULL),
(93, '794979KEHINDE', 'Full time employment', 'FINANCE', '2020-12-10 00:30:15', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `account`
--
ALTER TABLE `account`
  ADD PRIMARY KEY (`no`),
  ADD KEY `id` (`id`);

--
-- Indexes for table `comment`
--
ALTER TABLE `comment`
  ADD PRIMARY KEY (`comment_no`);

--
-- Indexes for table `contact`
--
ALTER TABLE `contact`
  ADD PRIMARY KEY (`no`),
  ADD KEY `id` (`id`);

--
-- Indexes for table `images`
--
ALTER TABLE `images`
  ADD PRIMARY KEY (`no`);

--
-- Indexes for table `interest`
--
ALTER TABLE `interest`
  ADD PRIMARY KEY (`no`),
  ADD KEY `id` (`id`);

--
-- Indexes for table `kids`
--
ALTER TABLE `kids`
  ADD PRIMARY KEY (`no`),
  ADD KEY `id` (`id`);

--
-- Indexes for table `otherFamily`
--
ALTER TABLE `otherFamily`
  ADD PRIMARY KEY (`no`),
  ADD KEY `id` (`id`);

--
-- Indexes for table `personal`
--
ALTER TABLE `personal`
  ADD PRIMARY KEY (`no`),
  ADD KEY `id` (`id`);

--
-- Indexes for table `post`
--
ALTER TABLE `post`
  ADD PRIMARY KEY (`post_no`);

--
-- Indexes for table `profile_pics`
--
ALTER TABLE `profile_pics`
  ADD PRIMARY KEY (`no`);

--
-- Indexes for table `siblings`
--
ALTER TABLE `siblings`
  ADD PRIMARY KEY (`no`),
  ADD KEY `id` (`id`);

--
-- Indexes for table `uploadPics`
--
ALTER TABLE `uploadPics`
  ADD PRIMARY KEY (`no`),
  ADD KEY `id` (`id`);

--
-- Indexes for table `work`
--
ALTER TABLE `work`
  ADD PRIMARY KEY (`no`),
  ADD KEY `id` (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `account`
--
ALTER TABLE `account`
  MODIFY `no` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=103;

--
-- AUTO_INCREMENT for table `comment`
--
ALTER TABLE `comment`
  MODIFY `comment_no` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=122;

--
-- AUTO_INCREMENT for table `contact`
--
ALTER TABLE `contact`
  MODIFY `no` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=94;

--
-- AUTO_INCREMENT for table `images`
--
ALTER TABLE `images`
  MODIFY `no` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `interest`
--
ALTER TABLE `interest`
  MODIFY `no` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=106;

--
-- AUTO_INCREMENT for table `kids`
--
ALTER TABLE `kids`
  MODIFY `no` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `otherFamily`
--
ALTER TABLE `otherFamily`
  MODIFY `no` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `personal`
--
ALTER TABLE `personal`
  MODIFY `no` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=95;

--
-- AUTO_INCREMENT for table `post`
--
ALTER TABLE `post`
  MODIFY `post_no` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=130;

--
-- AUTO_INCREMENT for table `profile_pics`
--
ALTER TABLE `profile_pics`
  MODIFY `no` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `siblings`
--
ALTER TABLE `siblings`
  MODIFY `no` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `uploadPics`
--
ALTER TABLE `uploadPics`
  MODIFY `no` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `work`
--
ALTER TABLE `work`
  MODIFY `no` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=94;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `account`
--
ALTER TABLE `account`
  ADD CONSTRAINT `account_ibfk_1` FOREIGN KEY (`id`) REFERENCES `personal` (`id`);

--
-- Constraints for table `contact`
--
ALTER TABLE `contact`
  ADD CONSTRAINT `contact_ibfk_1` FOREIGN KEY (`id`) REFERENCES `personal` (`id`);

--
-- Constraints for table `interest`
--
ALTER TABLE `interest`
  ADD CONSTRAINT `interest_ibfk_1` FOREIGN KEY (`id`) REFERENCES `personal` (`id`);

--
-- Constraints for table `kids`
--
ALTER TABLE `kids`
  ADD CONSTRAINT `kids_ibfk_1` FOREIGN KEY (`id`) REFERENCES `personal` (`id`);

--
-- Constraints for table `otherFamily`
--
ALTER TABLE `otherFamily`
  ADD CONSTRAINT `otherfamily_ibfk_1` FOREIGN KEY (`id`) REFERENCES `personal` (`id`);

--
-- Constraints for table `siblings`
--
ALTER TABLE `siblings`
  ADD CONSTRAINT `siblings_ibfk_1` FOREIGN KEY (`id`) REFERENCES `personal` (`id`);

--
-- Constraints for table `work`
--
ALTER TABLE `work`
  ADD CONSTRAINT `work_ibfk_1` FOREIGN KEY (`id`) REFERENCES `personal` (`id`);