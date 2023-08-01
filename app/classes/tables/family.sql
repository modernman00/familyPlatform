-- phpMyAdmin SQL Dump
-- version 5.1.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: May 24, 2021 at 01:54 PM
-- Server version: 5.7.32
-- PHP Version: 8.0.2

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

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
(1, '432292OLAWALE', 'modernman00@yahoo.com', '$2y$12$20jZM8.3Lq7FPUQA67.0euNtc4gBFH8f3Is4yZjIwn3lzt461J.pe', '5CDE0F5E', '9090@', 'pol', 'approved', '2021-02-15 23:21:43', '2021-05-16 15:34:47', '2021-05-16 15:34:47'),
(2, '117540OLAWALE', 'woguns@ymail.com', '$2y$12$1KJGcR/HkUBQWRrJ5NoJN.eomnb//FkdwE2Z2YxRjfJpS8tkn0mx.', 'A582FADD', 'member', 'pol', 'approved', '2021-02-15 23:39:16', '2021-05-24 10:34:56', '2021-05-24 10:34:56'),
(3, '870016OLAWALE', 'wale@loaneasyfinance.com', '$2y$12$HG8joopthSZWcrHfZsqsKe6u1gUE1nnZQq4JRpwTPrt0zMmx2kSk6', '392ED62F', 'member', 'pol', 'approved', '2021-02-15 23:42:31', '2021-05-23 21:09:31', '2021-05-23 21:09:31'),
(4, '397755OLUSOLA', 'waleolaogunrac@gmail.com', '$2y$10$jdD9c8xMhjoNTFwGfmW45OHFh.KJ15zTeY2WuJkkFrdMUCNQci6oq', NULL, 'member', 'opl', 'new', '2021-02-15 23:49:10', '2021-03-27 14:17:05', '2021-03-27 14:17:05'),
(5, '531422OLUSOLA', 'aji@loaneasyfinance.com', '$2y$10$JApGNehdesicovN5KhMLQ.KTqWJwVI12OaHXykf0cR7oTudHKJM96', NULL, 'member', 'opl', 'new', '2021-02-15 23:51:03', '2021-03-27 14:17:08', '2021-03-27 14:17:08');

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
(10, '117540OLAWALE', '10', 'SEGUN OLAOGUN', 'Account is active   The latest statement is based on the plan end date of 1st Jan 2021. ', 'Jibs.jpeg', '1613602127', '2021-02-17 22:57:21', NULL, NULL),
(11, '117540OLAWALE', '1', 'SEGUN OLAOGUN', 'JUMOKE', 'Jibs.jpeg', '1616760472', '2021-03-26 18:48:22', NULL, NULL),
(12, '117540OLAWALE', '1', 'SEGUN OLAOGUN', 'JUMOKE', 'Jibs.jpeg', '1616784792', '2021-03-26 18:53:20', NULL, NULL),
(13, '432292OLAWALE', '1', 'OLAWALE OLAOGUN', 'Account is active   The latest statement is based on the plan end date of 1st Jan 2021. ', 'jibssoj.jpeg', '1618234193', '2021-04-12 13:39:30', NULL, NULL),
(14, '432292OLAWALE', '1', 'OLAWALE OLAOGUN', 'JUMOKE', 'jibssoj.jpeg', '1618234889', '2021-04-12 13:41:34', NULL, NULL),
(15, '432292OLAWALE', '1', 'OLAWALE OLAOGUN', 'I think  it worked', 'jibssoj.jpeg', '1618234909', '2021-04-12 13:42:05', NULL, NULL),
(16, '432292OLAWALE', '1', 'OLAWALE OLAOGUN', 'gowon was arrested', 'jibssoj.jpeg', '1618234932', '2021-04-12 13:42:19', NULL, NULL),
(17, '432292OLAWALE', '1', 'OLAWALE OLAOGUN', 'IP changed', 'jibssoj.jpeg', '1618234939', '2021-04-12 13:42:27', NULL, NULL),
(18, '432292OLAWALE', '2', 'OLAWALE OLAOGUN', 'bggfrgrr', 'jibssoj.jpeg', '1618247207', '2021-04-12 17:07:49', NULL, NULL),
(19, '432292OLAWALE', '30', 'OLAWALE OLAOGUN', 'I think you should weight yourself', NULL, '1618273298', '2021-04-13 00:21:55', NULL, NULL),
(20, '432292OLAWALE', '30', 'OLAWALE OLAOGUN', 'vvhvh', NULL, '1618274151', '2021-04-13 00:36:53', NULL, NULL),
(21, '870016OLAWALE', '30', 'ADESOJI OLAOGUN', 'thank you mate1', NULL, '1618274390', '2021-04-13 00:40:03', NULL, NULL),
(22, '870016OLAWALE', '31', 'ADESOJI OLAOGUN', 'hghvu', NULL, '1618274426', '2021-04-13 00:40:32', NULL, NULL),
(23, '870016OLAWALE', '31', 'ADESOJI OLAOGUN', 'Yes  I am', NULL, '1618274432', '2021-04-13 00:40:43', NULL, NULL),
(24, '870016OLAWALE', '30', 'ADESOJI OLAOGUN', 'so you think so', NULL, '1618274443', '2021-04-13 00:40:51', NULL, NULL),
(25, '870016OLAWALE', '29', 'ADESOJI OLAOGUN', 'dgd', NULL, '1618274451', '2021-04-13 00:41:00', NULL, NULL),
(26, '870016OLAWALE', '31', 'ADESOJI OLAOGUN', 'JUMOKE', NULL, '1618313124', '2021-04-13 11:25:31', NULL, NULL),
(27, '870016OLAWALE', '30', 'ADESOJI OLAOGUN', 'chineka', NULL, '1618313269', '2021-04-13 11:28:00', NULL, NULL),
(28, '870016OLAWALE', '30', 'ADESOJI OLAOGUN', 'whats', NULL, '1618313721', '2021-04-13 11:35:33', NULL, NULL),
(29, '870016OLAWALE', '25', 'ADESOJI OLAOGUN', 'kiloshe', NULL, '1618313733', '2021-04-13 11:35:53', NULL, NULL),
(30, '117540OLAWALE', '32', 'SEGUN OLAOGUN', 'is it really working', NULL, '1618315745', '2021-04-13 12:09:16', NULL, NULL),
(31, '870016OLAWALE', '32', 'ADESOJI OLAOGUN', 'Yes  it is mate', NULL, '1618315765', '2021-04-13 12:09:33', NULL, NULL),
(32, '117540OLAWALE', '32', 'SEGUN OLAOGUN', 'Am not so sure', NULL, '1618315756', '2021-04-13 12:09:46', NULL, NULL),
(33, '117540OLAWALE', '32', 'SEGUN OLAOGUN', 'I will ask dad about it.', NULL, '1618315786', '2021-04-13 12:10:54', NULL, NULL),
(34, '870016OLAWALE', '32', 'ADESOJI OLAOGUN', 'Okay. maybe you should', NULL, '1618315871', '2021-04-13 12:11:37', NULL, NULL),
(35, '870016OLAWALE', '32', 'ADESOJI OLAOGUN', 'Yes  i asked Olutobi', NULL, '1618316141', '2021-04-13 12:19:39', NULL, NULL),
(36, '117540OLAWALE', '32', 'SEGUN OLAOGUN', 'Okay. she is here with me. ', NULL, '1618316395', '2021-04-13 12:20:19', NULL, NULL),
(37, '870016OLAWALE', '33', 'ADESOJI OLAOGUN', 'I like it', NULL, '1618316485', '2021-04-13 12:21:52', NULL, NULL),
(38, '117540OLAWALE', '33', 'SEGUN OLAOGUN', 'really', NULL, '1618316485', '2021-04-13 12:22:04', NULL, NULL),
(39, '870016OLAWALE', '1', 'ADESOJI OLAOGUN', 'it was very good', NULL, '1618325323', '2021-04-13 15:09:16', NULL, NULL),
(40, '870016OLAWALE', '1', 'ADESOJI OLAOGUN', 'really good', NULL, '1618326575', '2021-04-13 15:13:05', NULL, NULL),
(41, '870016OLAWALE', '1', 'ADESOJI OLAOGUN', 'JUMOKE', NULL, '1618326792', '2021-04-13 15:14:45', NULL, NULL),
(42, '870016OLAWALE', '1', 'ADESOJI OLAOGUN', 'JUMOKE', NULL, '1618326885', '2021-04-13 15:14:46', NULL, NULL),
(43, '870016OLAWALE', '1', 'ADESOJI OLAOGUN', 'JUMOKE', NULL, '1618326886', '2021-04-13 15:15:43', NULL, NULL),
(44, '870016OLAWALE', '1', 'ADESOJI OLAOGUN', '', NULL, '1618327232', '2021-04-13 15:21:29', NULL, NULL),
(45, '870016OLAWALE', '1', 'ADESOJI OLAOGUN', 'JUMOKE', NULL, '1618327289', '2021-04-13 15:21:37', NULL, NULL),
(46, '870016OLAWALE', '33', 'ADESOJI OLAOGUN', 'I think  it worked', NULL, '1618327297', '2021-04-13 15:29:26', NULL, NULL),
(47, '870016OLAWALE', '33', 'ADESOJI OLAOGUN', 'thinking', NULL, '1618327766', '2021-04-13 15:32:59', NULL, NULL),
(48, '870016OLAWALE', '33', 'ADESOJI OLAOGUN', 'Account is active   The latest statement is based on the plan end date of 1st Jan 2021. ', NULL, '1618330667', '2021-04-13 16:17:55', NULL, NULL),
(49, '870016OLAWALE', '33', 'ADESOJI OLAOGUN', 'Account is active   The latest statement is based on the plan end date of 1st Jan 2021. and it is going to', NULL, '1618333858', '2021-04-13 17:11:15', NULL, NULL),
(59, '870016OLAWALE', '33', 'ADESOJI OLAOGUN', 'Wife is beautiful', NULL, '1618352625', '2021-04-13 22:25:47', NULL, NULL),
(60, '870016OLAWALE', '33', 'ADESOJI OLAOGUN', '', NULL, '1618354370', '2021-04-13 22:54:01', NULL, NULL),
(61, '870016OLAWALE', '29', 'ADESOJI OLAOGUN', '  testing it', NULL, '1618356414', '2021-04-13 23:27:13', NULL, NULL),
(62, '870016OLAWALE', '29', 'ADESOJI OLAOGUN', 'gowon was arrested', NULL, '1618356433', '2021-04-13 23:27:33', NULL, NULL),
(63, '870016OLAWALE', '22', 'ADESOJI OLAOGUN', 'JUMOKE', NULL, '1618441678', '2021-04-14 23:08:08', NULL, NULL),
(64, '870016OLAWALE', '33', 'ADESOJI OLAOGUN', 'jfjdjdk', NULL, '1618614071', '2021-04-16 23:01:24', NULL, NULL),
(65, '870016OLAWALE', '29', 'ADESOJI OLAOGUN', '  Not really', NULL, '1619718356', '2021-04-29 17:46:13', NULL, NULL),
(66, '960448AJIBIKE', '1000', 'Ajibike', 'Your comment will show here', NULL, NULL, '2021-05-11 21:10:57', NULL, NULL),
(67, '908713AJIBIKE', '1000', 'Ajibike', 'Your comment will show here', NULL, NULL, '2021-05-11 21:17:00', NULL, NULL),
(68, '994165AJIBIKE', '1000', 'ajibike', 'Your comment will show here', NULL, NULL, '2021-05-11 21:23:08', NULL, NULL),
(69, '973892AJIBIKE', '1000', 'ajibike', 'Your comment will show here', NULL, NULL, '2021-05-11 21:25:38', NULL, NULL),
(70, '990098AJIBIKE', '1000', 'ajibike', 'Your comment will show here', NULL, NULL, '2021-05-11 21:27:04', NULL, NULL),
(71, '973168AJIBIKE', '1000', 'ajibike', 'Your comment will show here', NULL, NULL, '2021-05-11 21:27:29', NULL, NULL),
(72, '975270AJIBIKE', '1000', 'ajibike', 'Your comment will show here', NULL, NULL, '2021-05-11 21:27:55', NULL, NULL),
(73, '975098AJIBIKE', '1000', 'ajibike', 'Your comment will show here', NULL, NULL, '2021-05-11 21:28:22', NULL, NULL),
(74, '969610AJIBIKE', '1000', 'ajibike', 'Your comment will show here', NULL, NULL, '2021-05-11 21:28:49', NULL, NULL),
(75, '915128AJIBIKE', '1000', 'ajibike', 'Your comment will show here', NULL, NULL, '2021-05-11 21:29:16', NULL, NULL),
(76, '980988AJIBIKE', '1000', 'ajibike', 'Your comment will show here', NULL, NULL, '2021-05-11 21:29:43', NULL, NULL),
(77, '933515AJIBIKE', '1000', 'ajibike', 'Your comment will show here', NULL, NULL, '2021-05-11 21:30:10', NULL, NULL),
(78, '986424AJIBIKE', '1000', 'ajibike', 'Your comment will show here', NULL, NULL, '2021-05-11 21:30:36', NULL, NULL),
(79, '972831AJIBIKE', '1000', 'ajibike', 'Your comment will show here', NULL, NULL, '2021-05-11 21:31:03', NULL, NULL),
(80, '916858AJIBIKE', '1000', 'ajibike', 'Your comment will show here', NULL, NULL, '2021-05-11 21:31:30', NULL, NULL),
(81, '971907AJIBIKE', '1000', 'ajibike', 'Your comment will show here', NULL, NULL, '2021-05-11 21:31:56', NULL, NULL),
(82, '955681AJIBIKE', '1000', 'ajibike', 'Your comment will show here', NULL, NULL, '2021-05-11 21:32:21', NULL, NULL),
(83, '955345AJIBIKE', '1000', 'ajibike', 'Your comment will show here', NULL, NULL, '2021-05-11 21:32:46', NULL, NULL),
(84, '945013AJIBIKE', '1000', 'ajibike', 'Your comment will show here', NULL, NULL, '2021-05-11 21:33:16', NULL, NULL),
(85, '954041TOYIN', '1000', 'Toyin', 'Your comment will show here', NULL, NULL, '2021-05-12 22:10:13', NULL, NULL),
(86, '969261FIRSTNAME', '1000', 'firstName', 'Your comment will show here', NULL, NULL, '2021-05-13 09:03:21', NULL, NULL),
(87, '994199FIRSTNAME', '1000', 'firstName', 'Your comment will show here', NULL, NULL, '2021-05-13 09:25:12', NULL, NULL),
(88, '936184FIRSTNAME', '1000', 'firstName', 'Your comment will show here', NULL, NULL, '2021-05-13 09:34:31', NULL, NULL),
(89, '906346FIRSTNAME', '1000', 'firstName', 'Your comment will show here', NULL, NULL, '2021-05-13 09:36:28', NULL, NULL),
(90, '976028FIRSTNAME', '1000', 'firstName', 'Your comment will show here', NULL, NULL, '2021-05-13 09:37:45', NULL, NULL),
(91, '903870FIRSTNAME', '1000', 'firstName', 'Your comment will show here', NULL, NULL, '2021-05-13 10:12:59', NULL, NULL),
(92, '919377FIRSTNAME', '1000', 'firstName', 'Your comment will show here', NULL, NULL, '2021-05-13 10:14:33', NULL, NULL),
(93, '932523FIRSTNAME', '1000', 'firstName', 'Your comment will show here', NULL, NULL, '2021-05-13 10:24:33', NULL, NULL),
(94, '917518FIRSTNAME', '1000', 'firstName', 'Your comment will show here', NULL, NULL, '2021-05-13 10:38:55', NULL, NULL),
(95, '923859FIRSTNAME', '1000', 'firstName', 'Your comment will show here', NULL, NULL, '2021-05-13 10:39:29', NULL, NULL),
(96, '997946FIRSTNAME', '1000', 'firstName', 'Your comment will show here', NULL, NULL, '2021-05-13 10:40:21', NULL, NULL),
(97, '962342FIRSTNAME', '1000', 'firstName', 'Your comment will show here', NULL, NULL, '2021-05-13 10:45:58', NULL, NULL),
(98, '956008FIRSTNAME', '1000', 'firstName', 'Your comment will show here', NULL, NULL, '2021-05-13 10:48:23', NULL, NULL),
(99, '977013FIRSTNAME', '1000', 'firstName', 'Your comment will show here', NULL, NULL, '2021-05-13 10:51:36', NULL, NULL),
(100, '961541FIRSTNAME', '1000', 'firstName', 'Your comment will show here', NULL, NULL, '2021-05-13 10:53:24', NULL, NULL),
(101, '936531FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', NULL, '2021-05-13 11:07:30', NULL, NULL),
(102, '944283FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', NULL, '2021-05-13 12:24:52', NULL, NULL),
(103, '995145FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', NULL, '2021-05-13 12:31:08', NULL, NULL),
(104, '994516FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', NULL, '2021-05-13 12:35:14', NULL, NULL),
(105, '988884FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', NULL, '2021-05-13 12:36:44', NULL, NULL),
(106, '998301FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', NULL, '2021-05-13 14:34:14', NULL, NULL),
(107, '950281FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', NULL, '2021-05-13 14:34:42', NULL, NULL),
(108, '916152FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', NULL, '2021-05-13 14:35:41', NULL, NULL),
(109, '988785FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', NULL, '2021-05-13 14:36:20', NULL, NULL),
(110, '944513FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', NULL, '2021-05-13 14:36:45', NULL, NULL),
(111, '983410FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', NULL, '2021-05-13 14:37:26', NULL, NULL),
(112, '983829FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', NULL, '2021-05-13 14:38:01', NULL, NULL),
(113, '997034FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', NULL, '2021-05-13 14:38:48', NULL, NULL),
(114, '967496FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', NULL, '2021-05-13 15:41:58', NULL, NULL),
(115, '989556FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', NULL, '2021-05-13 15:43:48', NULL, NULL),
(116, '977004FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', NULL, '2021-05-13 15:44:11', NULL, NULL),
(117, '931864FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', NULL, '2021-05-13 15:44:46', NULL, NULL),
(118, '903453FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', NULL, '2021-05-13 15:45:50', NULL, NULL),
(119, '949623FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', NULL, '2021-05-13 15:47:33', NULL, NULL),
(120, '982712FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', NULL, '2021-05-13 15:49:31', NULL, NULL),
(121, '911383FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', NULL, '2021-05-13 15:52:07', NULL, NULL),
(122, '958026FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', NULL, '2021-05-13 15:54:09', NULL, NULL),
(123, '940409FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', NULL, '2021-05-13 15:55:03', NULL, NULL),
(124, '958138FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', NULL, '2021-05-13 15:55:52', NULL, NULL),
(125, '917783FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', NULL, '2021-05-13 17:26:18', NULL, NULL),
(126, '998041FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', NULL, '2021-05-13 17:27:00', NULL, NULL),
(127, '984557FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', NULL, '2021-05-13 22:07:08', NULL, NULL),
(128, '973295FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', NULL, '2021-05-13 22:07:27', NULL, NULL),
(129, '951057FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', NULL, '2021-05-13 22:09:22', NULL, NULL),
(130, '979659FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', NULL, '2021-05-13 22:10:00', NULL, NULL),
(131, '963468FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', NULL, '2021-05-13 22:12:57', NULL, NULL),
(132, '911388FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', NULL, '2021-05-13 22:14:06', NULL, NULL),
(133, '907887FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', NULL, '2021-05-13 22:14:16', NULL, NULL),
(134, '938185FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', NULL, '2021-05-13 22:17:40', NULL, NULL),
(135, '945851FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', NULL, '2021-05-13 22:19:56', NULL, NULL),
(136, '924699FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', NULL, '2021-05-13 22:20:05', NULL, NULL),
(137, '939287FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', NULL, '2021-05-13 22:20:18', NULL, NULL),
(138, '975046FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', NULL, '2021-05-13 22:22:35', NULL, NULL),
(139, '996031FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', NULL, '2021-05-13 22:29:05', NULL, NULL),
(140, '941825FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', NULL, '2021-05-13 23:00:30', NULL, NULL),
(141, '929225FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', NULL, '2021-05-13 23:00:51', NULL, NULL),
(142, '939683FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', NULL, '2021-05-13 23:01:39', NULL, NULL),
(143, '930497FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', NULL, '2021-05-13 23:02:44', NULL, NULL),
(144, '946829FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', NULL, '2021-05-13 23:15:02', NULL, NULL),
(145, '940840FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', NULL, '2021-05-13 23:36:12', NULL, NULL),
(146, '920518FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', NULL, '2021-05-13 23:40:10', NULL, NULL),
(147, '976277FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', NULL, '2021-05-13 23:43:08', NULL, NULL),
(148, '918111FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', NULL, '2021-05-13 23:43:41', NULL, NULL),
(149, '984468FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', NULL, '2021-05-13 23:54:14', NULL, NULL),
(150, '983669FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', NULL, '2021-05-13 23:57:22', NULL, NULL),
(151, '117540OLAWALE', '33', 'SEGUN OLAOGUN', 'JUMOKE', NULL, '1621184877', '2021-05-16 17:22:09', NULL, NULL),
(152, '117540OLAWALE', '32', 'SEGUN OLAOGUN', 'Account is active - The latest statement is based on the plan end date of 1st Jan 2021. ', NULL, '1621185729', '2021-05-16 17:22:23', NULL, NULL),
(153, '117540OLAWALE', '33', 'SEGUN OLAOGUN', 'gowon was arrested', NULL, '1621261034', '2021-05-17 14:17:57', NULL, NULL),
(154, '117540OLAWALE', '12', 'SEGUN OLAOGUN', 'seriously', NULL, '1621271396', '2021-05-17 17:27:36', NULL, NULL),
(155, '117540OLAWALE', '33', 'SEGUN OLAOGUN', '  really', NULL, '1621272461', '2021-05-17 17:31:12', NULL, NULL),
(156, '117540OLAWALE', '33', 'SEGUN OLAOGUN', 'JUMOKE', NULL, '1621272864', '2021-05-17 17:34:34', NULL, NULL),
(157, '117540OLAWALE', '33', 'SEGUN OLAOGUN', 'Testing it', NULL, '1621272875', '2021-05-17 17:34:53', NULL, NULL),
(158, '117540OLAWALE', '32', 'SEGUN OLAOGUN', 'JUMOKE', NULL, '1621292342', '2021-05-17 22:59:46', NULL, NULL),
(159, '117540OLAWALE', '86', 'SEGUN OLAOGUN', '', NULL, '1621356250', '2021-05-18 16:44:29', NULL, NULL),
(160, '117540OLAWALE', '86', 'SEGUN OLAOGUN', 'e choke', NULL, '1621358210', '2021-05-18 17:17:14', NULL, NULL),
(161, '117540OLAWALE', '87', 'SEGUN OLAOGUN', 'not necessary', NULL, '1621358234', '2021-05-18 17:17:26', NULL, NULL),
(162, '117540OLAWALE', '87', 'SEGUN OLAOGUN', 'hdhdh', NULL, '1621365323', '2021-05-18 19:15:40', NULL, NULL),
(163, '117540OLAWALE', '87', 'SEGUN OLAOGUN', 'unable', NULL, '1621365344', '2021-05-18 19:15:57', NULL, NULL),
(164, '117540OLAWALE', '86', 'SEGUN OLAOGUN', 'echoke', NULL, '1621365357', '2021-05-18 19:16:05', NULL, NULL),
(165, '117540OLAWALE', '87', 'SEGUN OLAOGUN', 'enjoyment mann', NULL, '1621418973', '2021-05-19 10:10:29', NULL, NULL),
(166, '117540OLAWALE', '86', 'SEGUN OLAOGUN', 'really', NULL, '1621419029', '2021-05-19 10:10:47', NULL, NULL),
(167, '117540OLAWALE', '87', 'SEGUN OLAOGUN', '', NULL, '1621420262', '2021-05-19 10:31:19', NULL, NULL),
(168, '117540OLAWALE', '87', 'SEGUN OLAOGUN', 'IT IS GOING TO BE FINE', NULL, '1621420279', '2021-05-19 10:34:06', NULL, NULL),
(169, '117540OLAWALE', '87', 'SEGUN OLAOGUN', 'IT IS GOING TO BE FINE', NULL, '1621420279', '2021-05-19 10:36:28', NULL, NULL),
(170, '117540OLAWALE', '87', 'SEGUN OLAOGUN', 'SORRY', NULL, '1621420603', '2021-05-19 10:36:56', NULL, NULL),
(171, '117540OLAWALE', '87', 'SEGUN OLAOGUN', 'SORRY', NULL, '1621420603', '2021-05-19 10:37:50', NULL, NULL),
(172, '117540OLAWALE', '87', 'SEGUN OLAOGUN', 'hhjjjh', NULL, '1621440559', '2021-05-19 16:09:23', NULL, NULL),
(173, '117540OLAWALE', '87', 'SEGUN OLAOGUN', 'jjhjh', NULL, '1621440858', '2021-05-19 16:14:23', NULL, NULL),
(174, '117540OLAWALE', '87', 'SEGUN OLAOGUN', 'jhhjjhjh', NULL, '1621440858', '2021-05-19 16:14:42', NULL, NULL),
(175, '117540OLAWALE', '87', 'SEGUN OLAOGUN', 'jjjkkjkjkj', NULL, '1621440893', '2021-05-19 16:15:06', NULL, NULL),
(176, '117540OLAWALE', '87', 'SEGUN OLAOGUN', 'cccc', NULL, '1621442063', '2021-05-19 16:34:26', NULL, NULL),
(177, '117540OLAWALE', '87', 'SEGUN OLAOGUN', 'llkkkllk', NULL, '1621461318', '2021-05-19 21:55:24', NULL, NULL),
(178, '117540OLAWALE', '87', 'SEGUN OLAOGUN', 'kk', NULL, '1621461465', '2021-05-19 21:58:04', NULL, NULL),
(179, '117540OLAWALE', '87', 'SEGUN OLAOGUN', 'jhhj', NULL, '1621465753', '2021-05-19 23:09:20', NULL, NULL),
(180, '117540OLAWALE', '87', 'SEGUN OLAOGUN', 'jjj', NULL, '1621465904', '2021-05-19 23:11:54', NULL, NULL),
(181, '117540OLAWALE', '87', 'SEGUN OLAOGUN', '', NULL, '1621465981', '2021-05-19 23:13:05', NULL, NULL),
(182, '117540OLAWALE', '87', 'SEGUN OLAOGUN', 'Jesus', NULL, '1621465981', '2021-05-19 23:13:25', NULL, NULL),
(183, '117540OLAWALE', '87', 'SEGUN OLAOGUN', 'sanitation', NULL, '1621466049', '2021-05-19 23:17:50', NULL, NULL),
(184, '117540OLAWALE', '87', 'SEGUN OLAOGUN', 'sanitation', NULL, '1621466049', '2021-05-19 23:17:52', NULL, NULL),
(185, '117540OLAWALE', '87', 'SEGUN OLAOGUN', 'sanitation', NULL, '1621466049', '2021-05-19 23:18:10', NULL, NULL),
(186, '117540OLAWALE', '87', 'SEGUN OLAOGUN', 'JUMOKE', NULL, '1621466314', '2021-05-19 23:18:38', NULL, NULL),
(187, '117540OLAWALE', '87', 'SEGUN OLAOGUN', 'JUMOKE', NULL, '1621466321', '2021-05-19 23:19:14', NULL, NULL),
(188, '117540OLAWALE', '87', 'SEGUN OLAOGUN', 'enjoyinh', NULL, '1621466394', '2021-05-19 23:20:03', NULL, NULL),
(189, '117540OLAWALE', '87', 'SEGUN OLAOGUN', 'enjoyinh', NULL, '1621466394', '2021-05-19 23:20:08', NULL, NULL),
(190, '117540OLAWALE', '87', 'SEGUN OLAOGUN', 'ghhhhg', NULL, '1621467059', '2021-05-19 23:31:16', NULL, NULL),
(191, '117540OLAWALE', '87', 'SEGUN OLAOGUN', 'ghhhhg', NULL, '1621467059', '2021-05-19 23:31:17', NULL, NULL),
(192, '117540OLAWALE', '87', 'SEGUN OLAOGUN', 'SOJ', NULL, '1621503035', '2021-05-20 09:30:48', NULL, NULL),
(193, '117540OLAWALE', '87', 'SEGUN OLAOGUN', 'SOJ', NULL, '1621503035', '2021-05-20 09:31:52', NULL, NULL),
(194, '117540OLAWALE', '87', 'SEGUN OLAOGUN', 'hdhdhd', NULL, '1621503119', '2021-05-20 09:32:05', NULL, NULL),
(195, '117540OLAWALE', '87', 'SEGUN OLAOGUN', 'JUMOKE', NULL, '1621503232', '2021-05-20 09:33:52', NULL, NULL),
(196, '117540OLAWALE', '87', 'SEGUN OLAOGUN', 'gowon was arrested', NULL, '1621503237', '2021-05-20 09:34:05', NULL, NULL),
(197, '117540OLAWALE', '87', 'SEGUN OLAOGUN', 'oladele', NULL, '1621503253', '2021-05-20 09:34:13', NULL, NULL),
(198, '117540OLAWALE', '87', 'SEGUN OLAOGUN', 'tunji', NULL, '1621503261', '2021-05-20 09:34:21', NULL, NULL),
(199, '117540OLAWALE', '87', 'SEGUN OLAOGUN', 'why is he a crook', NULL, '1621503315', '2021-05-20 09:35:24', NULL, NULL),
(200, '117540OLAWALE', '87', 'SEGUN OLAOGUN', 'I don t know', NULL, '1621503324', '2021-05-20 09:35:32', NULL, NULL),
(201, '117540OLAWALE', '87', 'SEGUN OLAOGUN', 'You don t know   ', NULL, '1621503332', '2021-05-20 09:35:44', NULL, NULL),
(202, '117540OLAWALE', '87', 'SEGUN OLAOGUN', 'I think you should definitely know', NULL, '1621503359', '2021-05-20 09:36:11', NULL, NULL),
(203, '117540OLAWALE', '86', 'SEGUN OLAOGUN', 'so  what is the score', NULL, '1621503371', '2021-05-20 09:36:25', NULL, NULL),
(204, '117540OLAWALE', '86', 'SEGUN OLAOGUN', 'I dont the answer to the question mate....', NULL, '1621503385', '2021-05-20 09:36:40', NULL, NULL),
(205, '117540OLAWALE', '85', 'SEGUN OLAOGUN', 'I think you should definitely know', NULL, '1621503400', '2021-05-20 09:36:49', NULL, NULL),
(206, '117540OLAWALE', '85', 'SEGUN OLAOGUN', 'How should I know', NULL, '1621503409', '2021-05-20 09:37:02', NULL, NULL),
(207, '117540OLAWALE', '88', 'SEGUN OLAOGUN', 'welldone Jummy', NULL, '1621504132', '2021-05-20 09:49:06', NULL, NULL),
(208, '117540OLAWALE', '87', 'SEGUN OLAOGUN', 'amazing', NULL, '1621723093', '2021-05-22 22:38:14', NULL, NULL),
(209, '117540OLAWALE', '87', 'SEGUN OLAOGUN', 'is he really a crook', NULL, '1621723106', '2021-05-22 22:38:37', NULL, NULL),
(210, '117540OLAWALE', '86', 'SEGUN OLAOGUN', 'Let us check why it is like that', NULL, '1621723117', '2021-05-22 22:38:59', NULL, NULL),
(211, '117540OLAWALE', '88', 'SEGUN OLAOGUN', 'Is that correct', NULL, '1621809831', '2021-05-23 22:43:51', NULL, NULL),
(212, '117540OLAWALE', '88', 'SEGUN OLAOGUN', 'super good', NULL, '1621809847', '2021-05-23 22:44:07', NULL, NULL),
(213, '117540OLAWALE', '88', 'SEGUN OLAOGUN', 'reallt', NULL, '1621809859', '2021-05-23 22:44:19', NULL, NULL),
(214, '117540OLAWALE', '88', 'SEGUN OLAOGUN', 'the tennis player', NULL, '1621853865', '2021-05-24 10:57:45', NULL, NULL),
(215, '117540OLAWALE', '88', 'SEGUN OLAOGUN', 'ambitious', NULL, '1621853877', '2021-05-24 10:57:57', NULL, NULL),
(216, '117540OLAWALE', '90', 'SEGUN OLAOGUN', 'this place looks nice', NULL, '1621863170', '2021-05-24 13:32:50', NULL, NULL);

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
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `no` int(11) NOT NULL,
  `id` text NOT NULL,
  `eventId` varchar(20) NOT NULL,
  `eventName` text,
  `eventDate` date DEFAULT NULL,
  `eventType` text,
  `eventGroup` text,
  `eventDescription` text,
  `eventFrequency` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`no`, `id`, `eventId`, `eventName`, `eventDate`, `eventType`, `eventGroup`, `eventDescription`, `eventFrequency`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '870016OLAWALE', '', 'olawale', '2022-04-20', 'Anniversary', 'Canada', 'ddfdfdd', 'Annually', '2021-04-16 08:49:14', '2021-04-19 23:05:29', '2021-04-19 23:05:29'),
(2, '870016OLAWALE', '', 'olawale', '2022-04-22', 'Anniversary', 'Canada', 'ddfdfdd', 'Annually', '2021-04-16 08:49:45', '2021-04-19 23:05:29', '2021-04-19 23:05:29'),
(3, '870016OLAWALE', '', 'olawale', '2021-04-28', 'Anniversary', 'Canada', 'ddfdfdd', 'Annually', '2021-04-16 08:51:19', '2021-04-19 23:05:29', '2021-04-19 23:05:29'),
(4, '870016OLAWALE', '', 'olawale', '2021-04-23', 'Anniversary', 'Canada', 'ddfdfdd', 'Annually', '2021-04-16 08:51:42', '2021-04-19 23:05:29', '2021-04-19 23:05:29'),
(5, '870016OLAWALE', '', 'olawale', '2022-04-26', 'Anniversary', 'Canada', 'ddfdfdd', 'Annually', '2021-04-16 09:05:36', '2021-04-19 23:05:29', '2021-04-19 23:05:29');

-- --------------------------------------------------------

--
-- Table structure for table `images`
--

CREATE TABLE `images` (
  `no` int(11) NOT NULL,
  `id` varchar(30) NOT NULL,
  `img` text,
  `where_from` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `images`
--

INSERT INTO `images` (`no`, `id`, `img`, `where_from`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '117540OLAWALE', 'WhatsApp Image 2021-01-24 at 14.01.45 (9).jpeg', '', '2021-02-17 20:17:54', NULL, NULL),
(2, '117540OLAWALE', 'Jibs.jpeg', '', '2021-02-17 22:19:33', NULL, NULL),
(3, '432292OLAWALE', 'shoSojWal2.jpeg', '', '2021-04-12 14:27:03', NULL, NULL),
(4, '432292OLAWALE', 'ShoSojWal3.jpeg', '', '2021-04-12 14:27:03', NULL, NULL),
(5, '432292OLAWALE', 'shoSojWal4.jpeg', '', '2021-04-12 14:27:03', NULL, NULL),
(6, '432292OLAWALE', 'oladeleNFayo.jpeg', 'profile', '2021-04-13 00:31:34', NULL, NULL),
(7, '397755OLUSOLA', 'oladeleNFayo.jpeg', 'profile', '2021-04-13 00:31:34', NULL, NULL),
(8, '531422OLUSOLA', 'oladeleNFayo.jpeg', 'profile', '2021-04-13 00:31:34', NULL, NULL),
(9, '870016OLAWALE', 'Jibs.jpeg', '', '2021-02-17 22:19:33', NULL, NULL),
(10, '117540OLAWALE', 'dede.jpeg', 'profile', '2021-05-17 13:40:13', NULL, NULL);

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
  `spouseMobile` text,
  `spouseEmail` varchar(20) DEFAULT NULL,
  `fatherName` text,
  `fatherMobile` text,
  `fatherEmail` varchar(50) DEFAULT NULL,
  `motherName` text,
  `motherMobile` text,
  `motherEmail` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `otherFamily`
--

INSERT INTO `otherFamily` (`no`, `id`, `spouseName`, `spouseMobile`, `spouseEmail`, `fatherName`, `fatherMobile`, `fatherEmail`, `motherName`, `motherMobile`, `motherEmail`, `motherMaiden`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '432292OLAWALE', 'AJIBIKE OLAOGUN', '11', '', 'ADESOJI OLAOGUN', '2348036416079', '', 'IYABO OLAOGUN', '01793321653', '', 'MAYUNGBE', '2021-02-15 23:21:43', '2021-05-04 22:42:04', '2021-05-04 22:42:04'),
(2, '117540OLAWALE', '11', '11', '', 'OLUYOMI OLAOGUN', '2348036416078', '', 'IYABO OLAOGUN', '01793321653', '', 'MAYUNGBE', '2021-02-15 23:39:16', '2021-05-05 20:32:15', '2021-05-05 20:32:15'),
(3, '870016OLAWALE', '11', '11', '', 'OLUYOMI OLAOGUN', '2348036416079', '', 'IYABO OLAOGUN', '01793321653', '', 'MAYUNGBE', '2021-02-15 23:42:31', NULL, NULL),
(4, '397755OLUSOLA', 'TOYIN OLAOGUN', '01793321653', '', 'OLUYOMI OLAOGUN', '2348036416079', '', 'IYABO OLAOGUN', '01793321653', '', 'MAYUNGBE', '2021-02-15 23:49:10', NULL, NULL),
(5, '531422OLUSOLA', 'TOYIN OLAOGUN', '01793321653', '', 'OLUYOMI OLAOGUN', '2348036416079', '', 'IYABO OLAOGUN', '01793321653', '', 'MAYUNGBE', '2021-02-15 23:51:03', NULL, NULL);

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
  `siblings` int(11) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `personal`
--

INSERT INTO `personal` (`no`, `id`, `firstName`, `lastName`, `alias`, `day`, `month`, `year`, `kids`, `gender`, `siblings`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '432292OLAWALE', 'OLAWALE', 'OLAOGUN', 'MODERNMAN', 15, 'JULY', 1979, 3, 'Male', 3, '2021-02-15 23:21:43', NULL, NULL),
(2, '117540OLAWALE', 'SEGUN', 'OLAOGUN', 'MODERNMAN', 15, 'JULY', 1979, 3, 'Male', 3, '2021-02-15 23:39:16', '2021-02-17 20:48:13', '2021-02-17 20:48:13'),
(3, '870016OLAWALE', 'ADESOJI', 'OLAOGUN', 'MODERNMAN', 15, 'JULY', 1979, 3, 'Male', 3, '2021-02-15 23:42:31', '2021-02-17 20:48:20', '2021-02-17 20:48:20'),
(4, '397755OLUSOLA', 'FEMI', 'OLAOGUN', 'SHO', 14, 'AUGUST', 1979, 3, 'Male', 3, '2021-02-15 23:49:10', '2021-02-17 20:48:29', '2021-02-17 20:48:29'),
(5, '531422OLUSOLA', 'OLUSOLA', 'OLAOGUN', 'SHO', 14, 'AUGUST', 1979, 3, 'Male', 3, '2021-02-15 23:51:03', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `pma__bookmark`
--

CREATE TABLE `pma__bookmark` (
  `id` int(10) UNSIGNED NOT NULL,
  `dbase` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
  `user` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
  `label` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `query` text COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Bookmarks';

-- --------------------------------------------------------

--
-- Table structure for table `pma__central_columns`
--

CREATE TABLE `pma__central_columns` (
  `db_name` varchar(64) COLLATE utf8_bin NOT NULL,
  `col_name` varchar(64) COLLATE utf8_bin NOT NULL,
  `col_type` varchar(64) COLLATE utf8_bin NOT NULL,
  `col_length` text COLLATE utf8_bin,
  `col_collation` varchar(64) COLLATE utf8_bin NOT NULL,
  `col_isNull` tinyint(1) NOT NULL,
  `col_extra` varchar(255) COLLATE utf8_bin DEFAULT '',
  `col_default` text COLLATE utf8_bin
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Central list of columns';

-- --------------------------------------------------------

--
-- Table structure for table `pma__column_info`
--

CREATE TABLE `pma__column_info` (
  `id` int(5) UNSIGNED NOT NULL,
  `db_name` varchar(64) COLLATE utf8_bin NOT NULL DEFAULT '',
  `table_name` varchar(64) COLLATE utf8_bin NOT NULL DEFAULT '',
  `column_name` varchar(64) COLLATE utf8_bin NOT NULL DEFAULT '',
  `comment` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `mimetype` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `transformation` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
  `transformation_options` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
  `input_transformation` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
  `input_transformation_options` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Column information for phpMyAdmin';

-- --------------------------------------------------------

--
-- Table structure for table `pma__designer_settings`
--

CREATE TABLE `pma__designer_settings` (
  `username` varchar(64) COLLATE utf8_bin NOT NULL,
  `settings_data` text COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Settings related to Designer';

-- --------------------------------------------------------

--
-- Table structure for table `pma__export_templates`
--

CREATE TABLE `pma__export_templates` (
  `id` int(5) UNSIGNED NOT NULL,
  `username` varchar(64) COLLATE utf8_bin NOT NULL,
  `export_type` varchar(10) COLLATE utf8_bin NOT NULL,
  `template_name` varchar(64) COLLATE utf8_bin NOT NULL,
  `template_data` text COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Saved export templates';

-- --------------------------------------------------------

--
-- Table structure for table `pma__favorite`
--

CREATE TABLE `pma__favorite` (
  `username` varchar(64) COLLATE utf8_bin NOT NULL,
  `tables` text COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Favorite tables';

-- --------------------------------------------------------

--
-- Table structure for table `pma__history`
--

CREATE TABLE `pma__history` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `username` varchar(64) COLLATE utf8_bin NOT NULL DEFAULT '',
  `db` varchar(64) COLLATE utf8_bin NOT NULL DEFAULT '',
  `table` varchar(64) COLLATE utf8_bin NOT NULL DEFAULT '',
  `timevalue` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `sqlquery` text COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='SQL history for phpMyAdmin';

-- --------------------------------------------------------

--
-- Table structure for table `pma__navigationhiding`
--

CREATE TABLE `pma__navigationhiding` (
  `username` varchar(64) COLLATE utf8_bin NOT NULL,
  `item_name` varchar(64) COLLATE utf8_bin NOT NULL,
  `item_type` varchar(64) COLLATE utf8_bin NOT NULL,
  `db_name` varchar(64) COLLATE utf8_bin NOT NULL,
  `table_name` varchar(64) COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Hidden items of navigation tree';

-- --------------------------------------------------------

--
-- Table structure for table `pma__pdf_pages`
--

CREATE TABLE `pma__pdf_pages` (
  `db_name` varchar(64) COLLATE utf8_bin NOT NULL DEFAULT '',
  `page_nr` int(10) UNSIGNED NOT NULL,
  `page_descr` varchar(50) CHARACTER SET utf8 NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='PDF relation pages for phpMyAdmin';

-- --------------------------------------------------------

--
-- Table structure for table `pma__recent`
--

CREATE TABLE `pma__recent` (
  `username` varchar(64) COLLATE utf8_bin NOT NULL,
  `tables` text COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Recently accessed tables';

--
-- Dumping data for table `pma__recent`
--

INSERT INTO `pma__recent` (`username`, `tables`) VALUES
('root', '[{\"db\":\"family\",\"table\":\"profile_pics\"},{\"db\":\"family\",\"table\":\"post\"},{\"db\":\"family\",\"table\":\"personal\"},{\"db\":\"family\",\"table\":\"otherFamily\"},{\"db\":\"family\",\"table\":\"kids\"},{\"db\":\"family\",\"table\":\"interest\"},{\"db\":\"family\",\"table\":\"images\"},{\"db\":\"family\",\"table\":\"events\"},{\"db\":\"family\",\"table\":\"contact\"},{\"db\":\"family\",\"table\":\"comment\"}]');

-- --------------------------------------------------------

--
-- Table structure for table `pma__relation`
--

CREATE TABLE `pma__relation` (
  `master_db` varchar(64) COLLATE utf8_bin NOT NULL DEFAULT '',
  `master_table` varchar(64) COLLATE utf8_bin NOT NULL DEFAULT '',
  `master_field` varchar(64) COLLATE utf8_bin NOT NULL DEFAULT '',
  `foreign_db` varchar(64) COLLATE utf8_bin NOT NULL DEFAULT '',
  `foreign_table` varchar(64) COLLATE utf8_bin NOT NULL DEFAULT '',
  `foreign_field` varchar(64) COLLATE utf8_bin NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Relation table';

-- --------------------------------------------------------

--
-- Table structure for table `pma__savedsearches`
--

CREATE TABLE `pma__savedsearches` (
  `id` int(5) UNSIGNED NOT NULL,
  `username` varchar(64) COLLATE utf8_bin NOT NULL DEFAULT '',
  `db_name` varchar(64) COLLATE utf8_bin NOT NULL DEFAULT '',
  `search_name` varchar(64) COLLATE utf8_bin NOT NULL DEFAULT '',
  `search_data` text COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Saved searches';

-- --------------------------------------------------------

--
-- Table structure for table `pma__table_coords`
--

CREATE TABLE `pma__table_coords` (
  `db_name` varchar(64) COLLATE utf8_bin NOT NULL DEFAULT '',
  `table_name` varchar(64) COLLATE utf8_bin NOT NULL DEFAULT '',
  `pdf_page_number` int(11) NOT NULL DEFAULT '0',
  `x` float UNSIGNED NOT NULL DEFAULT '0',
  `y` float UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Table coordinates for phpMyAdmin PDF output';

-- --------------------------------------------------------

--
-- Table structure for table `pma__table_info`
--

CREATE TABLE `pma__table_info` (
  `db_name` varchar(64) COLLATE utf8_bin NOT NULL DEFAULT '',
  `table_name` varchar(64) COLLATE utf8_bin NOT NULL DEFAULT '',
  `display_field` varchar(64) COLLATE utf8_bin NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Table information for phpMyAdmin';

-- --------------------------------------------------------

--
-- Table structure for table `pma__table_uiprefs`
--

CREATE TABLE `pma__table_uiprefs` (
  `username` varchar(64) COLLATE utf8_bin NOT NULL,
  `db_name` varchar(64) COLLATE utf8_bin NOT NULL,
  `table_name` varchar(64) COLLATE utf8_bin NOT NULL,
  `prefs` text COLLATE utf8_bin NOT NULL,
  `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Tables'' UI preferences';

--
-- Dumping data for table `pma__table_uiprefs`
--

INSERT INTO `pma__table_uiprefs` (`username`, `db_name`, `table_name`, `prefs`, `last_update`) VALUES
('root', 'family', 'account', '{\"sorted_col\":\"`created_at` DESC\"}', '2021-05-24 13:43:16'),
('root', 'family', 'post', '{\"sorted_col\":\"`date_created`  DESC\"}', '2021-05-24 13:46:28');

-- --------------------------------------------------------

--
-- Table structure for table `pma__tracking`
--

CREATE TABLE `pma__tracking` (
  `db_name` varchar(64) COLLATE utf8_bin NOT NULL,
  `table_name` varchar(64) COLLATE utf8_bin NOT NULL,
  `version` int(10) UNSIGNED NOT NULL,
  `date_created` datetime NOT NULL,
  `date_updated` datetime NOT NULL,
  `schema_snapshot` text COLLATE utf8_bin NOT NULL,
  `schema_sql` text COLLATE utf8_bin,
  `data_sql` longtext COLLATE utf8_bin,
  `tracking` set('UPDATE','REPLACE','INSERT','DELETE','TRUNCATE','CREATE DATABASE','ALTER DATABASE','DROP DATABASE','CREATE TABLE','ALTER TABLE','RENAME TABLE','DROP TABLE','CREATE INDEX','DROP INDEX','CREATE VIEW','ALTER VIEW','DROP VIEW') COLLATE utf8_bin DEFAULT NULL,
  `tracking_active` int(1) UNSIGNED NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Database changes tracking for phpMyAdmin';

-- --------------------------------------------------------

--
-- Table structure for table `pma__userconfig`
--

CREATE TABLE `pma__userconfig` (
  `username` varchar(64) COLLATE utf8_bin NOT NULL,
  `timevalue` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `config_data` text COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='User preferences storage for phpMyAdmin';

--
-- Dumping data for table `pma__userconfig`
--

INSERT INTO `pma__userconfig` (`username`, `timevalue`, `config_data`) VALUES
('root', '2021-05-24 10:45:01', '{\"Console\\/Mode\":\"collapse\"}');

-- --------------------------------------------------------

--
-- Table structure for table `pma__usergroups`
--

CREATE TABLE `pma__usergroups` (
  `usergroup` varchar(64) COLLATE utf8_bin NOT NULL,
  `tab` varchar(64) COLLATE utf8_bin NOT NULL,
  `allowed` enum('Y','N') COLLATE utf8_bin NOT NULL DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='User groups with configured menu items';

-- --------------------------------------------------------

--
-- Table structure for table `pma__users`
--

CREATE TABLE `pma__users` (
  `username` varchar(64) COLLATE utf8_bin NOT NULL,
  `usergroup` varchar(64) COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Users and their assignments to user groups';

-- --------------------------------------------------------

--
-- Table structure for table `post`
--

CREATE TABLE `post` (
  `post_no` int(11) NOT NULL,
  `id` varchar(255) DEFAULT NULL,
  `fullName` text,
  `postMessage` varchar(255) DEFAULT NULL,
  `post_likes` int(10) DEFAULT '0',
  `profileImg` text,
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

INSERT INTO `post` (`post_no`, `id`, `fullName`, `postMessage`, `post_likes`, `profileImg`, `post_img0`, `post_img1`, `post_img2`, `post_img3`, `post_img4`, `post_img5`, `post_time`, `date_created`, `date_updated`, `date_deleted`) VALUES
(1, '432292OLAWALE', 'OLAWALE', 'Hey, welcome to your page', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2021-02-15 23:21:43', NULL, NULL),
(2, '117540OLAWALE', 'OLAWALE', 'Hey, welcome to your page', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2021-02-15 23:39:16', NULL, NULL),
(3, '870016OLAWALE', 'OLAWALE', 'Hey, welcome to your page', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2021-02-15 23:42:31', '2021-04-12 14:09:01', '2021-04-12 14:09:01'),
(4, '397755OLUSOLA', 'OLUSOLA', 'Hey, welcome to your page', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2021-02-15 23:49:10', '2021-04-12 14:08:58', '2021-04-12 14:08:58'),
(5, '531422OLUSOLA', 'OLUSOLA', 'Hey, welcome to your page', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2021-02-15 23:51:03', '2021-04-12 14:08:54', '2021-04-12 14:08:54'),
(6, '117540OLAWALE', 'OLAWALE OLAOGUN', 'Am doing great', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1613593024, '2021-02-17 20:17:16', '2021-04-12 14:08:50', '2021-04-12 14:08:50'),
(7, '117540OLAWALE', 'SEGUN OLAOGUN', 'Interesting job', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1613594915, '2021-02-17 20:49:21', '2021-04-12 14:09:11', '2021-04-12 14:09:11'),
(8, '117540OLAWALE', 'SEGUN OLAOGUN', 'when are we paying our next tuition fees ', 0, NULL, 'SAM_0795.JPG', NULL, NULL, NULL, NULL, NULL, 1613595653, '2021-02-17 21:01:33', NULL, NULL),
(9, '870016OLAWALE', 'ADESOJI OLAOGUN', 'Checking', 0, NULL, NULL, 'WhatsApp Image 2021-01-24 at 14.01.44 (1).jpeg', NULL, NULL, NULL, NULL, 1613596845, '2021-02-17 21:22:21', '2021-04-12 14:09:07', '2021-04-12 14:09:07'),
(10, '870016OLAWALE', 'ADESOJI OLAOGUN', 'this is my pics', 0, NULL, 'shoSojWal4.jpeg', 'ShoSojWal3.jpeg', 'shoSojWal2.jpeg', NULL, NULL, NULL, 1613597963, '2021-02-17 21:39:46', NULL, NULL),
(11, '117540OLAWALE', 'SEGUN OLAOGUN', 'Barquet is watching for the arrow to point in the right place', 0, NULL, 'FembaNSoj.jpeg', 'shoSojWal1.jpeg', NULL, NULL, NULL, NULL, 1613687717, '2021-02-18 23:00:32', NULL, NULL),
(12, '117540OLAWALE', 'SEGUN OLAOGUN', 'let it go ', 0, NULL, 'Jibs.jpeg', 'FembaNSoj.jpeg', 'shoSojWal1.jpeg', NULL, NULL, NULL, 1613692179, '2021-02-19 00:01:24', NULL, NULL),
(13, '117540OLAWALE', 'SEGUN OLAOGUN', 'Am glad the upload is a success', 0, NULL, 'allthefamily2.jpeg', 'allthefamily.jpeg', 'thebrother2.jpeg', NULL, NULL, NULL, 1613692884, '2021-02-19 00:06:06', NULL, NULL),
(14, '117540OLAWALE', 'SEGUN OLAOGUN', 'still testing', 0, NULL, 'sojandfemba2.jpeg', 'FembaShoSojWale.jpeg', NULL, NULL, NULL, NULL, 1613693166, '2021-02-19 00:06:54', NULL, NULL),
(15, '117540OLAWALE', 'SEGUN OLAOGUN', 'Post here', 0, NULL, 'family1.jpeg', 'family2.jpeg', NULL, NULL, NULL, NULL, 1613693375, '2021-02-19 00:29:57', NULL, NULL),
(16, '117540OLAWALE', 'SEGUN OLAOGUN', 'it is heavy', 0, NULL, 'family3.jpeg', 'family4.jpeg', NULL, NULL, NULL, NULL, 1613694598, '2021-02-19 00:31:01', NULL, NULL),
(17, '117540OLAWALE', 'SEGUN OLAOGUN', 'Post it here now', 0, NULL, 'ShoSoj1.jpeg', 'ShoSoj2.jpeg', NULL, NULL, NULL, NULL, 1613694661, '2021-02-19 01:05:29', NULL, NULL),
(18, '117540OLAWALE', 'SEGUN OLAOGUN', 'Post here', 0, NULL, 'ShoSoj2.jpeg', 'ShoSoj3.jpeg', NULL, NULL, NULL, NULL, 1613696809, '2021-02-19 01:07:43', NULL, NULL),
(19, '117540OLAWALE', 'SEGUN OLAOGUN', 'Post here', 0, NULL, 'ShoSoj2.jpeg', 'ShoSoj3.jpeg', NULL, NULL, NULL, NULL, 1613696863, '2021-02-19 01:10:23', NULL, NULL),
(20, '117540OLAWALE', 'SEGUN OLAOGUN', 'Post here', 0, NULL, 'family4.jpeg', 'ShoSoj1.jpeg', 'ShoSoj2.jpeg', NULL, NULL, NULL, 1613697202, '2021-02-19 01:13:35', NULL, NULL),
(21, '117540OLAWALE', 'SEGUN OLAOGUN', 'Working up the chair', 0, NULL, 'shoSoj4.jpeg', 'shoSoj5.jpeg', NULL, NULL, NULL, NULL, 1613697215, '2021-02-19 01:15:31', NULL, NULL),
(22, '117540OLAWALE', 'SEGUN OLAOGUN', 'Sho and Soj arrived today', 0, NULL, 'ShoSoj6.jpeg', 'ShoSoj7.jpeg', 'ShoSoj8.jpeg', 'ShoSoj9.jpeg', 'ShoSoj10.jpeg', NULL, 1613697332, '2021-02-19 01:17:16', NULL, NULL),
(23, '117540OLAWALE', 'SEGUN OLAOGUN', 'It is a beautiful day', 0, NULL, 'ShoSoj12.jpeg', 'ShoSoj13.jpeg', 'ShoSoj14.jpeg', NULL, NULL, NULL, 1613730090, '2021-02-19 10:22:56', NULL, NULL),
(24, '117540OLAWALE', 'SEGUN OLAOGUN', 'Post herevjukvukv', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1616784643, '2021-03-26 18:52:44', NULL, NULL),
(25, '117540OLAWALE', 'SEGUN OLAOGUN', 'Post here', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1616785173, '2021-03-26 19:00:18', NULL, NULL),
(26, '432292OLAWALE', 'OLAWALE OLAOGUN', 'I am the admin man here', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1618233675, '2021-04-12 13:21:41', NULL, NULL),
(27, '432292OLAWALE', 'OLAWALE OLAOGUN', 'I love my family', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1618235003, '2021-04-12 13:43:43', NULL, NULL),
(28, '432292OLAWALE', 'OLAWALE OLAOGUN', 'Greetings to everyone', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1618253631, '2021-04-12 18:54:08', NULL, NULL),
(29, '432292OLAWALE', 'OLAWALE OLAOGUN', 'It is very interesting and good to hear from you. ', 0, NULL, 'the4brothers.jpeg', NULL, NULL, NULL, NULL, NULL, 1618253959, '2021-04-12 19:00:15', NULL, NULL),
(30, '432292OLAWALE', 'OLAWALE OLAOGUN', 'I went to ground zero', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1618273258, '2021-04-13 00:21:38', NULL, NULL),
(31, '870016OLAWALE', 'ADESOJI OLAOGUN', 'Am Adesoji ', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1618274403, '2021-04-13 00:40:26', NULL, NULL),
(32, '870016OLAWALE', 'ADESOJI OLAOGUN', 'Wow  it is working', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1618315590, '2021-04-13 12:06:56', NULL, NULL),
(33, '117540OLAWALE', 'SEGUN OLAOGUN', 'OLUTOBI IS WORKING HARD', 0, NULL, 'tobs.jpeg', NULL, NULL, NULL, NULL, NULL, 1618316419, '2021-04-13 12:21:24', NULL, NULL),
(84, '117540OLAWALE', 'SEGUN OLAOGUN', 'Post here', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1621350107, '2021-05-18 15:08:31', NULL, NULL),
(85, '117540OLAWALE', 'SEGUN OLAOGUN', 'Post here', 13, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1621350107, '2021-05-18 15:08:54', '2021-05-20 09:37:07', '2021-05-20 09:37:07'),
(86, '117540OLAWALE', 'SEGUN OLAOGUN', 'Hey Soj is learning how to use Mac', 72, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1621350534, '2021-05-18 15:09:15', '2021-05-18 19:15:26', '2021-05-18 19:15:26'),
(87, '117540OLAWALE', 'SEGUN OLAOGUN', 'Baba Ijesha is a crook', 39, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1621350555, '2021-05-18 15:33:38', '2021-05-19 23:31:11', '2021-05-19 23:31:11'),
(88, '117540OLAWALE', 'SEGUN OLAOGUN', 'My daughter olajumoke finishes school today', 11, NULL, '20140902_063945.jpg', NULL, NULL, NULL, NULL, NULL, 1621503422, '2021-05-20 09:48:24', '2021-05-24 11:57:24', '2021-05-24 11:57:24'),
(89, '117540OLAWALE', 'SEGUN OLAOGUN', 'It is going to be fine', 1, NULL, 'images.jpeg', NULL, NULL, NULL, NULL, NULL, 1621858247, '2021-05-24 12:11:10', '2021-05-24 12:11:13', '2021-05-24 12:11:13'),
(90, '117540OLAWALE', 'SEGUN OLAOGUN', 'Am testing multiple pics', 1, NULL, 'imageresizer.jpeg', 'Former_Courage_Brewery,_Bristol_-_geograph.org.uk_-_182194.jpg', NULL, NULL, NULL, NULL, 1621863127, '2021-05-24 13:32:36', '2021-05-24 13:32:52', '2021-05-24 13:32:52');

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
(1, '531422OLUSOLA', 'jibssoj.jpeg', '2021-02-15 23:21:42', '2021-04-22 17:45:26', '2021-04-22 17:45:26'),
(2, '432292OLAWALE', 'oladeleNFayo.jpeg', '2021-02-15 23:39:15', '2021-04-13 00:31:34', '2021-04-13 00:31:34'),
(3, '117540OLAWALE', 'dede.jpeg', '2021-02-15 23:42:31', '2021-05-17 13:40:13', '2021-05-17 13:40:13'),
(4, '870016OLAWALE', 'walesoj.jpeg', '2021-02-15 23:49:09', '2021-04-12 12:37:42', '2021-04-12 12:37:42'),
(5, '397755OLUSOLA', 'walesoj.jpeg', '2021-02-15 23:51:02', '2021-04-12 12:36:41', '2021-04-12 12:36:41');

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
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`no`);

--
-- Indexes for table `images`
--
ALTER TABLE `images`
  ADD PRIMARY KEY (`no`),
  ADD KEY `id` (`id`);

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
-- Indexes for table `pma__bookmark`
--
ALTER TABLE `pma__bookmark`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pma__central_columns`
--
ALTER TABLE `pma__central_columns`
  ADD PRIMARY KEY (`db_name`,`col_name`);

--
-- Indexes for table `pma__column_info`
--
ALTER TABLE `pma__column_info`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `db_name` (`db_name`,`table_name`,`column_name`);

--
-- Indexes for table `pma__designer_settings`
--
ALTER TABLE `pma__designer_settings`
  ADD PRIMARY KEY (`username`);

--
-- Indexes for table `pma__export_templates`
--
ALTER TABLE `pma__export_templates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `u_user_type_template` (`username`,`export_type`,`template_name`);

--
-- Indexes for table `pma__favorite`
--
ALTER TABLE `pma__favorite`
  ADD PRIMARY KEY (`username`);

--
-- Indexes for table `pma__history`
--
ALTER TABLE `pma__history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `username` (`username`,`db`,`table`,`timevalue`);

--
-- Indexes for table `pma__navigationhiding`
--
ALTER TABLE `pma__navigationhiding`
  ADD PRIMARY KEY (`username`,`item_name`,`item_type`,`db_name`,`table_name`);

--
-- Indexes for table `pma__pdf_pages`
--
ALTER TABLE `pma__pdf_pages`
  ADD PRIMARY KEY (`page_nr`),
  ADD KEY `db_name` (`db_name`);

--
-- Indexes for table `pma__recent`
--
ALTER TABLE `pma__recent`
  ADD PRIMARY KEY (`username`);

--
-- Indexes for table `pma__relation`
--
ALTER TABLE `pma__relation`
  ADD PRIMARY KEY (`master_db`,`master_table`,`master_field`),
  ADD KEY `foreign_field` (`foreign_db`,`foreign_table`);

--
-- Indexes for table `pma__savedsearches`
--
ALTER TABLE `pma__savedsearches`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `u_savedsearches_username_dbname` (`username`,`db_name`,`search_name`);

--
-- Indexes for table `pma__table_coords`
--
ALTER TABLE `pma__table_coords`
  ADD PRIMARY KEY (`db_name`,`table_name`,`pdf_page_number`);

--
-- Indexes for table `pma__table_info`
--
ALTER TABLE `pma__table_info`
  ADD PRIMARY KEY (`db_name`,`table_name`);

--
-- Indexes for table `pma__table_uiprefs`
--
ALTER TABLE `pma__table_uiprefs`
  ADD PRIMARY KEY (`username`,`db_name`,`table_name`);

--
-- Indexes for table `pma__tracking`
--
ALTER TABLE `pma__tracking`
  ADD PRIMARY KEY (`db_name`,`table_name`,`version`);

--
-- Indexes for table `pma__userconfig`
--
ALTER TABLE `pma__userconfig`
  ADD PRIMARY KEY (`username`);

--
-- Indexes for table `pma__usergroups`
--
ALTER TABLE `pma__usergroups`
  ADD PRIMARY KEY (`usergroup`,`tab`,`allowed`);

--
-- Indexes for table `pma__users`
--
ALTER TABLE `pma__users`
  ADD PRIMARY KEY (`username`,`usergroup`);

--
-- Indexes for table `post`
--
ALTER TABLE `post`
  ADD PRIMARY KEY (`post_no`),
  ADD KEY `id` (`id`);

--
-- Indexes for table `profile_pics`
--
ALTER TABLE `profile_pics`
  ADD PRIMARY KEY (`no`),
  ADD KEY `id` (`id`);

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
  MODIFY `no` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91;

--
-- AUTO_INCREMENT for table `comment`
--
ALTER TABLE `comment`
  MODIFY `comment_no` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=217;

--
-- AUTO_INCREMENT for table `contact`
--
ALTER TABLE `contact`
  MODIFY `no` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `no` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `images`
--
ALTER TABLE `images`
  MODIFY `no` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `interest`
--
ALTER TABLE `interest`
  MODIFY `no` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91;

--
-- AUTO_INCREMENT for table `kids`
--
ALTER TABLE `kids`
  MODIFY `no` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `otherFamily`
--
ALTER TABLE `otherFamily`
  MODIFY `no` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `personal`
--
ALTER TABLE `personal`
  MODIFY `no` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `pma__bookmark`
--
ALTER TABLE `pma__bookmark`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pma__column_info`
--
ALTER TABLE `pma__column_info`
  MODIFY `id` int(5) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pma__export_templates`
--
ALTER TABLE `pma__export_templates`
  MODIFY `id` int(5) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pma__history`
--
ALTER TABLE `pma__history`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pma__pdf_pages`
--
ALTER TABLE `pma__pdf_pages`
  MODIFY `page_nr` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pma__savedsearches`
--
ALTER TABLE `pma__savedsearches`
  MODIFY `id` int(5) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `post`
--
ALTER TABLE `post`
  MODIFY `post_no` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91;

--
-- AUTO_INCREMENT for table `profile_pics`
--
ALTER TABLE `profile_pics`
  MODIFY `no` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

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
  MODIFY `no` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91;

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
-- Constraints for table `images`
--
ALTER TABLE `images`
  ADD CONSTRAINT `images_ibfk_1` FOREIGN KEY (`id`) REFERENCES `personal` (`id`);

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
-- Constraints for table `post`
--
ALTER TABLE `post`
  ADD CONSTRAINT `post_ibfk_1` FOREIGN KEY (`id`) REFERENCES `personal` (`id`);

--
-- Constraints for table `profile_pics`
--
ALTER TABLE `profile_pics`
  ADD CONSTRAINT `profile_pics_ibfk_1` FOREIGN KEY (`id`) REFERENCES `personal` (`id`);

--
-- Constraints for table `siblings`
--
ALTER TABLE `siblings`
  ADD CONSTRAINT `siblings_ibfk_1` FOREIGN KEY (`id`) REFERENCES `personal` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
