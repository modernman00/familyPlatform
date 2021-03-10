-- phpMyAdmin SQL Dump
-- version 4.9.7
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Mar 09, 2021 at 01:48 PM
-- Server version: 5.7.32
-- PHP Version: 8.0.0

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
  `password` text,
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
(1, '432292OLAWALE', 'modernman00@yahoo.com', '$2y$15$6MMH9nnUgAwxywaZ16f7zeb4KJqdgC6KFmL24WGlqE7vmFiLqhN9.', 'AA74DBCA', '9090@', 'pol', 'new', '2021-02-15 23:21:43', '2021-02-17 20:43:21', '2021-02-17 20:43:21'),
(2, '117540OLAWALE', 'woguns@ymail.com', '$2y$15$jT6uFMl1EmGsdrJ9qLkGnudwPxFl6GnobKL4tPkNoISGBaoqQfAD.', '3011E603', 'member', 'pol', 'approved', '2021-02-15 23:39:16', '2021-02-17 20:46:59', '2021-02-17 20:46:59'),
(3, '870016OLAWALE', 'wale@loaneasyfinance.com', '$2y$10$jgGrX1veVwjTl0hBHfJwC././sXBB9n0n.e5KVSOAlaAkDJOED2cW', 'E1E79468', 'member', 'pol', 'approved', '2021-02-15 23:42:31', '2021-02-17 20:50:03', '2021-02-17 20:50:03'),
(4, '397755OLUSOLA', 'waleolaogunrac@gmail.com', '$2y$10$jdD9c8xMhjoNTFwGfmW45OHFh.KJ15zTeY2WuJkkFrdMUCNQci6oq', NULL, 'member', 'opl', 'new', '2021-02-15 23:49:10', '2021-02-15 23:58:53', '2021-02-15 23:58:53'),
(5, '531422OLUSOLA', 'aji@loaneasyfinance.com', '$2y$10$JApGNehdesicovN5KhMLQ.KTqWJwVI12OaHXykf0cR7oTudHKJM96', NULL, 'member', 'opl', 'new', '2021-02-15 23:51:03', '2021-02-15 23:59:22', '2021-02-15 23:59:22');

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
(1, '432292OLAWALE', '1000', 'OLAWALE', 'Your comment will show here', 'WhatsApp Image 2021 01 24 at 11.59.09  7 .jpeg', NULL, '2021-02-15 23:21:43', NULL, NULL),
(2, '117540OLAWALE', '1000', 'OLAWALE', 'Your comment will show here', 'WhatsApp Image 2021 01 24 at 11.59.09  7 .jpeg', NULL, '2021-02-15 23:39:16', NULL, NULL),
(3, '870016OLAWALE', '1000', 'OLAWALE', 'Your comment will show here', 'WhatsApp Image 2021 01 24 at 11.59.09  7 .jpeg', NULL, '2021-02-15 23:42:31', NULL, NULL),
(4, '397755OLUSOLA', '1000', 'OLUSOLA', 'Your comment will show here', 'WhatsApp Image 2021 01 24 at 11.59.09  2 .jpeg', NULL, '2021-02-15 23:49:10', NULL, NULL),
(5, '531422OLUSOLA', '1000', 'OLUSOLA', 'Your comment will show here', 'WhatsApp Image 2021 01 24 at 11.59.09  2 .jpeg', NULL, '2021-02-15 23:51:03', NULL, NULL),
(6, '870016OLAWALE', '7', 'ADESOJI OLAOGUN', 'NICE PICS', 'WhatsApp Image 2021 01 24 at 11.59.09  2 .jpeg', '1613595143', '2021-02-17 20:52:44', NULL, NULL),
(7, '117540OLAWALE', '7', 'SEGUN OLAOGUN', 'Thanks', 'WhatsApp Image 2021 01 24 at 11.59.09  7 .jpeg', '1613595176', '2021-02-17 20:54:09', NULL, NULL),
(8, '870016OLAWALE', '8', 'ADESOJI OLAOGUN', 'just checking', 'SAM_0915.JPG', '1613595835', '2021-02-17 21:04:09', NULL, NULL),
(9, '117540OLAWALE', '8', 'SEGUN OLAOGUN', 'Are you sure ', 'SAM_0866.JPG', '1613595857', '2021-02-17 21:04:27', NULL, NULL),
(10, '117540OLAWALE', '10', 'SEGUN OLAOGUN', 'Account is active   The latest statement is based on the plan end date of 1st Jan 2021. ', 'Jibs.jpeg', '1613602127', '2021-02-17 22:57:21', NULL, NULL);

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
(1, '432292OLAWALE', '26 LINDEN WAY', 'SN5 5DE', 'Wiltshire', 'modernman00@yahoo.com', 'UK', '01793321653', '2021-02-15 23:21:43', NULL, NULL),
(2, '117540OLAWALE', '26 LINDEN WAY', 'SN5 5DE', 'Wiltshire', 'modernman00@yahoo.com', 'UK', '01793321653', '2021-02-15 23:39:16', NULL, NULL),
(3, '870016OLAWALE', '26 LINDEN WAY', 'SN5 5DE', 'Wiltshire', 'modernman00@yahoo.com', 'UK', '01793321653', '2021-02-15 23:42:31', NULL, NULL),
(4, '397755OLUSOLA', '26 LINDEN WAY', 'SN5 5DE', 'Wiltshire', 'modernman00@yahoo.com', 'UK', '01793321653', '2021-02-15 23:49:10', NULL, NULL),
(5, '531422OLUSOLA', '26 LINDEN WAY', 'SN5 5DE', 'Wiltshire', 'modernman00@yahoo.com', 'UK', '01793321653', '2021-02-15 23:51:03', NULL, NULL);

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
(1, '117540OLAWALE', 'WhatsApp Image 2021-01-24 at 14.01.45 (9).jpeg', '2021-02-17 20:17:54', NULL, NULL),
(2, '117540OLAWALE', 'Jibs.jpeg', '2021-02-17 22:19:33', NULL, NULL);

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
(1, '432292OLAWALE', 'FOOTBALL', 'BARCELONA', 'SINGING', '2021-02-15 23:21:43', NULL, NULL),
(2, '117540OLAWALE', 'FOOTBALL', 'BARCELONA', 'SINGING', '2021-02-15 23:39:16', NULL, NULL),
(3, '870016OLAWALE', 'FOOTBALL', 'BARCELONA', 'SINGING', '2021-02-15 23:42:31', NULL, NULL),
(4, '397755OLUSOLA', 'FOOTBALL', 'BARCELONA', 'SINGING', '2021-02-15 23:49:10', NULL, NULL),
(5, '531422OLUSOLA', 'FOOTBALL', 'BARCELONA', 'SINGING', '2021-02-15 23:51:03', NULL, NULL);

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
(1, '432292OLAWALE', 'Olutobi Olaogun', 'oolutobs@gmail.com', NULL, '2021-02-15 23:21:43', NULL, NULL),
(2, '432292OLAWALE', 'Oladele Olaogun', 'sportcarexpert@gmail.com', NULL, '2021-02-15 23:21:43', NULL, NULL),
(3, '870016OLAWALE', 'Olutobi Olaogun', 'oolutobs@gmail.com', NULL, '2021-02-15 23:42:31', NULL, NULL),
(4, '870016OLAWALE', 'Jumoke Olaogun', 'jummyoguns03@yahoo.com', NULL, '2021-02-15 23:42:31', NULL, NULL),
(5, '397755OLUSOLA', 'IRE OLAOGUN', 'ire@yahoo.com', NULL, '2021-02-15 23:49:10', NULL, NULL),
(6, '397755OLUSOLA', 'ORE OLAOGUN', 'ore@yahoo.com', NULL, '2021-02-15 23:49:10', NULL, NULL),
(7, '397755OLUSOLA', 'ABIOLA OLAOGUN', 'abiola@gmail.com', NULL, '2021-02-15 23:49:10', NULL, NULL),
(8, '531422OLUSOLA', 'IRE OLAOGUN', 'ire@yahoo.com', NULL, '2021-02-15 23:51:03', NULL, NULL),
(9, '531422OLUSOLA', 'ORE OLAOGUN', 'ore@yahoo.com', NULL, '2021-02-15 23:51:03', NULL, NULL),
(10, '531422OLUSOLA', 'ABIOLA OLAOGUN', 'abiola@gmail.com', NULL, '2021-02-15 23:51:03', NULL, NULL);

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
(1, '432292OLAWALE', '11', '11', 'OLUYOMI OLAOGUN', '2348036416079', 'IYABO OLAOGUN', '01793321653', 'MAYUNGBE', '2021-02-15 23:21:43', NULL, NULL),
(2, '117540OLAWALE', '11', '11', 'OLUYOMI OLAOGUN', '2348036416079', 'IYABO OLAOGUN', '01793321653', 'MAYUNGBE', '2021-02-15 23:39:16', NULL, NULL),
(3, '870016OLAWALE', '11', '11', 'OLUYOMI OLAOGUN', '2348036416079', 'IYABO OLAOGUN', '01793321653', 'MAYUNGBE', '2021-02-15 23:42:31', NULL, NULL),
(4, '397755OLUSOLA', 'TOYIN OLAOGUN', '01793321653', 'OLUYOMI OLAOGUN', '2348036416079', 'IYABO OLAOGUN', '01793321653', 'MAYUNGBE', '2021-02-15 23:49:10', NULL, NULL),
(5, '531422OLUSOLA', 'TOYIN OLAOGUN', '01793321653', 'OLUYOMI OLAOGUN', '2348036416079', 'IYABO OLAOGUN', '01793321653', 'MAYUNGBE', '2021-02-15 23:51:03', NULL, NULL);

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
(1, '432292OLAWALE', 'OLAWALE', 'OLAOGUN', 'MODERNMAN', 15, 'JULY', 1979, 3, 'Male', 3, '2021-02-15 23:21:43', NULL, NULL),
(2, '117540OLAWALE', 'SEGUN', 'OLAOGUN', 'MODERNMAN', 15, 'JULY', 1979, 3, 'Male', 3, '2021-02-15 23:39:16', '2021-02-17 20:48:13', '2021-02-17 20:48:13'),
(3, '870016OLAWALE', 'ADESOJI', 'OLAOGUN', 'MODERNMAN', 15, 'JULY', 1979, 3, 'Male', 3, '2021-02-15 23:42:31', '2021-02-17 20:48:20', '2021-02-17 20:48:20'),
(4, '397755OLUSOLA', 'FEMI', 'OLAOGUN', 'SHO', 14, 'AUGUST', 1979, 3, 'Male', 3, '2021-02-15 23:49:10', '2021-02-17 20:48:29', '2021-02-17 20:48:29'),
(5, '531422OLUSOLA', 'OLUSOLA', 'OLAOGUN', 'SHO', 14, 'AUGUST', 1979, 3, 'Male', 3, '2021-02-15 23:51:03', NULL, NULL);

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
(1, '432292OLAWALE', 'OLAWALE', 'Hey, welcome to your page', 'WhatsApp Image 2021 01 24 at 11.59.09  7 .jpeg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2021-02-15 23:21:43', NULL, NULL),
(2, '117540OLAWALE', 'OLAWALE', 'Hey, welcome to your page', 'WhatsApp Image 2021 01 24 at 11.59.09  7 .jpeg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2021-02-15 23:39:16', NULL, NULL),
(3, '870016OLAWALE', 'OLAWALE', 'Hey, welcome to your page', 'WhatsApp Image 2021 01 24 at 11.59.09  7 .jpeg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2021-02-15 23:42:31', NULL, NULL),
(4, '397755OLUSOLA', 'OLUSOLA', 'Hey, welcome to your page', 'WhatsApp Image 2021 01 24 at 11.59.09  2 .jpeg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2021-02-15 23:49:10', NULL, NULL),
(5, '531422OLUSOLA', 'OLUSOLA', 'Hey, welcome to your page', 'WhatsApp Image 2021 01 24 at 11.59.09  2 .jpeg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2021-02-15 23:51:03', NULL, NULL),
(6, '117540OLAWALE', 'OLAWALE OLAOGUN', 'Am doing great', 'WhatsApp Image 2021 01 24 at 11.59.09  7 .jpeg', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1613593024, '2021-02-17 20:17:16', NULL, NULL),
(7, '117540OLAWALE', 'SEGUN OLAOGUN', 'Interesting job', 'WhatsApp Image 2021 01 24 at 11.59.09  7 .jpeg', NULL, 'WhatsApp Image 2021-01-24 at 14.01.45 (9).jpeg', NULL, NULL, NULL, NULL, NULL, 1613594915, '2021-02-17 20:49:21', NULL, NULL),
(8, '117540OLAWALE', 'SEGUN OLAOGUN', 'when are we paying our next tuition fees ', 'SAM_0866.JPG', NULL, 'SAM_0795.JPG', NULL, NULL, NULL, NULL, NULL, 1613595653, '2021-02-17 21:01:33', NULL, NULL),
(9, '870016OLAWALE', 'ADESOJI OLAOGUN', 'Checking', 'SAM_0882.JPG', NULL, 'WhatsApp Image 2021-01-24 at 14.01.44.jpeg', 'WhatsApp Image 2021-01-24 at 14.01.44 (1).jpeg', NULL, NULL, NULL, NULL, 1613596845, '2021-02-17 21:22:21', NULL, NULL),
(10, '870016OLAWALE', 'ADESOJI OLAOGUN', 'this is my pics', 'SojProfile.jpeg', NULL, 'shoSojWal4.jpeg', 'ShoSojWal3.jpeg', 'shoSojWal2.jpeg', NULL, NULL, NULL, 1613597963, '2021-02-17 21:39:46', NULL, NULL),
(11, '117540OLAWALE', 'SEGUN OLAOGUN', 'Barquet is watching for the arrow to point in the right place', 'Jibs.jpeg', NULL, 'FembaNSoj.jpeg', 'shoSojWal1.jpeg', NULL, NULL, NULL, NULL, 1613687717, '2021-02-18 23:00:32', NULL, NULL),
(12, '117540OLAWALE', 'SEGUN OLAOGUN', 'let it go ', 'Jibs.jpeg', NULL, 'Jibs.jpeg', 'FembaNSoj.jpeg', 'shoSojWal1.jpeg', NULL, NULL, NULL, 1613692179, '2021-02-19 00:01:24', NULL, NULL),
(13, '117540OLAWALE', 'SEGUN OLAOGUN', 'Am glad the upload is a success', 'Jibs.jpeg', NULL, 'allthefamily2.jpeg', 'allthefamily.jpeg', 'thebrother2.jpeg', NULL, NULL, NULL, 1613692884, '2021-02-19 00:06:06', NULL, NULL),
(14, '117540OLAWALE', 'SEGUN OLAOGUN', 'still testing', 'Jibs.jpeg', NULL, 'sojandfemba2.jpeg', 'FembaShoSojWale.jpeg', NULL, NULL, NULL, NULL, 1613693166, '2021-02-19 00:06:54', NULL, NULL),
(15, '117540OLAWALE', 'SEGUN OLAOGUN', 'Post here', 'Jibs.jpeg', NULL, 'family1.jpeg', 'family2.jpeg', NULL, NULL, NULL, NULL, 1613693375, '2021-02-19 00:29:57', NULL, NULL),
(16, '117540OLAWALE', 'SEGUN OLAOGUN', 'it is heavy', 'Jibs.jpeg', NULL, 'family3.jpeg', 'family4.jpeg', NULL, NULL, NULL, NULL, 1613694598, '2021-02-19 00:31:01', NULL, NULL),
(17, '117540OLAWALE', 'SEGUN OLAOGUN', 'Post it here now', 'Jibs.jpeg', NULL, 'ShoSoj1.jpeg', 'ShoSoj2.jpeg', NULL, NULL, NULL, NULL, 1613694661, '2021-02-19 01:05:29', NULL, NULL),
(18, '117540OLAWALE', 'SEGUN OLAOGUN', 'Post here', 'Jibs.jpeg', NULL, 'ShoSoj2.jpeg', 'ShoSoj3.jpeg', NULL, NULL, NULL, NULL, 1613696809, '2021-02-19 01:07:43', NULL, NULL),
(19, '117540OLAWALE', 'SEGUN OLAOGUN', 'Post here', 'Jibs.jpeg', NULL, 'ShoSoj2.jpeg', 'ShoSoj3.jpeg', NULL, NULL, NULL, NULL, 1613696863, '2021-02-19 01:10:23', NULL, NULL),
(20, '117540OLAWALE', 'SEGUN OLAOGUN', 'Post here', 'Jibs.jpeg', NULL, 'family4.jpeg', 'ShoSoj1.jpeg', 'ShoSoj2.jpeg', NULL, NULL, NULL, 1613697202, '2021-02-19 01:13:35', NULL, NULL),
(21, '117540OLAWALE', 'SEGUN OLAOGUN', 'Working up the chair', 'Jibs.jpeg', NULL, 'shoSoj4.jpeg', 'shoSoj5.jpeg', NULL, NULL, NULL, NULL, 1613697215, '2021-02-19 01:15:31', NULL, NULL),
(22, '117540OLAWALE', 'SEGUN OLAOGUN', 'Sho and Soj arrived today', 'Jibs.jpeg', NULL, 'ShoSoj6.jpeg', 'ShoSoj7.jpeg', 'ShoSoj8.jpeg', 'ShoSoj9.jpeg', 'ShoSoj10.jpeg', NULL, 1613697332, '2021-02-19 01:17:16', NULL, NULL),
(23, '117540OLAWALE', 'SEGUN OLAOGUN', 'It is a beautiful day', 'Jibs.jpeg', NULL, 'ShoSoj12.jpeg', 'ShoSoj13.jpeg', 'ShoSoj14.jpeg', NULL, NULL, NULL, 1613730090, '2021-02-19 10:22:56', NULL, NULL);

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
(1, '267446OLAWALE', 'WhatsApp Image 2021 01 24 at 11.59.09  7 .jpeg', '2021-02-15 23:21:42', NULL, NULL),
(2, '432292OLAWALE', 'WhatsApp Image 2021 01 24 at 11.59.09  7 .jpeg', '2021-02-15 23:39:15', NULL, NULL),
(3, '117540OLAWALE', 'WhatsApp Image 2021 01 24 at 11.59.09  7 .jpeg', '2021-02-15 23:42:31', NULL, NULL),
(4, '870016OLAWALE', 'WhatsApp Image 2021 01 24 at 11.59.09  2 .jpeg', '2021-02-15 23:49:09', NULL, NULL),
(5, '397755OLUSOLA', 'WhatsApp Image 2021 01 24 at 11.59.09  2 .jpeg', '2021-02-15 23:51:02', NULL, NULL),
(6, '117540OLAWALE', 'SAM_0866.JPG', '2021-02-17 21:00:48', NULL, NULL),
(7, '870016OLAWALE', 'WhatsApp Image 2021 01 24 at 14.03.02.jpeg', '2021-02-17 21:02:08', NULL, NULL),
(8, '870016OLAWALE', 'SAM_0915.JPG', '2021-02-17 21:02:46', NULL, NULL),
(9, '870016OLAWALE', 'SAM_0902.JPG', '2021-02-17 21:19:28', NULL, NULL),
(10, '870016OLAWALE', 'SAM_0882.JPG', '2021-02-17 21:19:45', NULL, NULL),
(11, '870016OLAWALE', 'WhatsApp Image 2021 01 24 at 14.03.02.jpeg', '2021-02-17 21:22:48', NULL, NULL),
(12, '870016OLAWALE', 'authenticationCode.png', '2021-02-17 21:23:05', NULL, NULL),
(13, '117540OLAWALE', 'Screenshot 2021 02 16 at 18.41.24.png', '2021-02-17 21:30:28', NULL, NULL),
(14, '117540OLAWALE', 'ShoTakingSelfy.jpeg', '2021-02-17 21:38:50', NULL, NULL),
(15, '870016OLAWALE', 'SojProfile.jpeg', '2021-02-17 21:39:23', NULL, NULL),
(16, '117540OLAWALE', 'Jibs.jpeg', '2021-02-17 22:06:00', NULL, NULL);

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
(1, '432292OLAWALE', 'TOPE OLAOGUN', 'topsy@gmail.com', NULL, '2021-02-15 23:21:43', NULL, NULL),
(2, '432292OLAWALE', 'FEMI OLAOGUN', 'femmyoguns@gmail.com', NULL, '2021-02-15 23:21:43', NULL, NULL),
(3, '870016OLAWALE', 'TOPE OLAOGUN', 'topsy@gmail.com', NULL, '2021-02-15 23:42:31', NULL, NULL),
(4, '870016OLAWALE', 'OLUSOLA OLAOGUN', 'solaolaogun2013@gmail.com', NULL, '2021-02-15 23:42:31', NULL, NULL),
(5, '397755OLUSOLA', 'TOPE OLAOGUN', 'topsy@gmail.com', NULL, '2021-02-15 23:49:10', NULL, NULL),
(6, '397755OLUSOLA', 'FEMI OLAOGUN', 'femmyoguns@gmail.com', NULL, '2021-02-15 23:49:10', NULL, NULL),
(7, '397755OLUSOLA', 'OLAWALE OLAOGUN', 'modernman00@yahoo.com', NULL, '2021-02-15 23:49:10', NULL, NULL),
(8, '531422OLUSOLA', 'TOPE OLAOGUN', 'topsy@gmail.com', NULL, '2021-02-15 23:51:03', NULL, NULL),
(9, '531422OLUSOLA', 'FEMI OLAOGUN', 'femmyoguns@gmail.com', NULL, '2021-02-15 23:51:03', NULL, NULL),
(10, '531422OLUSOLA', 'OLAWALE OLAOGUN', 'modernman00@yahoo.com', NULL, '2021-02-15 23:51:03', NULL, NULL);

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
(1, '432292OLAWALE', 'Full time employment', 'ACCOUNTANT', '2021-02-15 23:21:43', NULL, NULL),
(2, '117540OLAWALE', 'Full time employment', 'ACCOUNTANT', '2021-02-15 23:39:16', NULL, NULL),
(3, '870016OLAWALE', 'Full time employment', 'ACCOUNTANT', '2021-02-15 23:42:31', NULL, NULL),
(4, '397755OLUSOLA', 'Student', 'SUPPLY CHAIN PROFESSIONAL', '2021-02-15 23:49:10', NULL, NULL),
(5, '531422OLUSOLA', 'Student', 'SUPPLY CHAIN PROFESSIONAL', '2021-02-15 23:51:03', NULL, NULL);

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
  MODIFY `no` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `comment`
--
ALTER TABLE `comment`
  MODIFY `comment_no` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `contact`
--
ALTER TABLE `contact`
  MODIFY `no` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `images`
--
ALTER TABLE `images`
  MODIFY `no` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `interest`
--
ALTER TABLE `interest`
  MODIFY `no` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `kids`
--
ALTER TABLE `kids`
  MODIFY `no` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `otherFamily`
--
ALTER TABLE `otherFamily`
  MODIFY `no` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `personal`
--
ALTER TABLE `personal`
  MODIFY `no` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `post`
--
ALTER TABLE `post`
  MODIFY `post_no` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `profile_pics`
--
ALTER TABLE `profile_pics`
  MODIFY `no` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

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
  MODIFY `no` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

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
-- Constraints for table `siblings`
--
ALTER TABLE `siblings`
  ADD CONSTRAINT `siblings_ibfk_1` FOREIGN KEY (`id`) REFERENCES `personal` (`id`);
