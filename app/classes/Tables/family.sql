-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Sep 16, 2024 at 03:56 PM
-- Server version: 8.4.2
-- PHP Version: 8.3.11

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
  `no` int NOT NULL,
  `id` varchar(255) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` text,
  `token` text,
  `type` text,
  `status` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `account`
--

INSERT INTO `account` (`no`, `id`, `email`, `password`, `token`, `type`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '432292OLAWALE', 'modernman00@yahoo.com', '$2y$12$iAc2SKOMVbLj14/w7iyEneZ7pbSCvV27.5h1e4ohLBoghzOggft3.', '2733E880', '9090@', 'approved', '2021-02-15 23:21:43', '2024-09-11 23:44:08', '2024-09-11 23:44:08'),
(2, '117540OLAWALE', 'woguns@ymail.com', '$2y$12$83tdTcK1gTXDv03ifngvBu.AstXj2gaHOqk8zhGE44D3foAIVHoVe', '542D2516', 'member', 'approved', '2021-02-15 23:39:16', '2024-06-18 11:23:50', '2024-06-18 11:23:50'),
(3, '870016OLAWALE', 'wale@loaneasyfinance.com', '$2y$12$RGFnyhoyvI0Aqk1w5.Gru.FoGWvZ9nknXosU1QlINkdWQ4Qu7d/Mq', '71119CE7', 'member', 'approved', '2021-02-15 23:42:31', '2024-06-13 23:43:49', '2024-06-13 23:43:49'),
(4, '397755OLUSOLA', 'waleolaogunrac@gmail.com', '$2y$12$6EGbyFJ1Y5K5sDz/Bi2Mw.DpeRoJtJZSs7zfWwfAYuPoHRWdNop9O', 'A98F64D8', 'member', 'approved', '2021-02-15 23:49:10', '2024-06-13 09:19:29', '2024-06-13 09:19:29'),
(6, '929019DAYO', 'segunswindon@gmail.comxx', '$2y$12$SEkU174rgmhBypo3lzJUku13zJQSBtcWhU765o5auZFYhyFViJ77S', NULL, 'member', 'approved', '2021-09-21 22:39:56', '2023-07-22 16:15:27', '2023-07-22 16:15:27'),
(9, '936779SHABA', 'topsy@gmail.comxx', '$2y$12$bc/.tSAwQN.IsBQw2ELzUeNhX/.vEoyKaR5wkDbsjqSN4ySFw7d16', '6EA72CE1', 'member', 'approved', '2022-07-21 00:20:44', '2023-07-22 16:15:35', '2023-07-22 16:15:35'),
(10, '954243OLAOLUWA', 'eniolaoguns@gmail.comxxx', '$2y$10$HorQkwwJ81diMWK7KAT5GeKDrzst6zA3hlm8UHKwz1EopQT/eqKfS', NULL, 'member', 'approved', '2022-07-21 08:06:51', '2023-07-22 16:15:45', '2023-07-22 16:15:45'),
(17, '937619LAFANE', 'retailwally@gmail.com', '$2y$12$ILiRooIHV/rA4fCsOjhDfeemV8s1SHzXqXlu1FMZoX1HrrIdro6Li', 'C8BC3F82', 'member', 'approved', '2023-02-15 00:47:04', '2024-09-12 17:53:08', '2024-09-12 17:53:08'),
(18, '964649OLAWALE', 'investwally2@gmail.comxx', '$2y$10$YNmFCAVsuGTavR6tyJKTWODCo40gdF9chp8cgShw4PTcceZPhu/pK', NULL, 'member', 'approved', '2023-07-07 21:43:20', '2023-07-22 16:15:53', '2023-07-22 16:15:53'),
(19, '915784TESTING', 'investwally3@gmail.comxx', '$2y$10$aOxi8crqbbVr29AOCi8eauPhLXMtRcV605SWxCi1zJZEbyrkXzpTO', NULL, 'member', 'approved', '2023-07-11 22:25:01', '2023-08-24 00:20:12', '2023-08-24 00:20:12'),
(20, '974628TESTING', 'investwally@gmail.com', '$2y$10$KXETF/r78pABnXCfeWVrWu7lRvPWK4KfmZVeX/tEaDfOlgyFC8r5u', NULL, 'member', 'approved', '2023-07-12 17:59:46', '2023-08-24 00:20:22', '2023-08-24 00:20:22'),
(21, '941501TOBI', 'oolutobs@gmail.comx', '$2y$10$g.k0EU.a/M9ahdzOIzC8gedB75JSYvRLFcd1KR3NHXvOkHp0vV.Ta', NULL, 'member', 'new', '2023-07-30 20:01:19', '2023-08-01 13:27:54', '2023-08-01 13:27:54'),
(22, '995544TESTING', 'loaneasyfinance16@gmail.com', '$2y$10$Ozuuk/9PkMvCZCHP4wZ6F.9/58hvtvcWR42ur.X/rb.c12vLIs69i', NULL, 'member', 'approved', '2023-08-01 01:50:38', '2023-08-24 00:21:15', '2023-08-24 00:21:15'),
(23, '962673TOBI', 'oolutobs@gmail.com', '$2y$10$beIPSnVSFE/B95LSzFDIvuwBl9cYwy5oZgMFlTxRMd.4pW847OxNK', NULL, 'member', 'new', '2023-08-01 13:29:58', NULL, NULL),
(24, '957154TESTING', 'Testing@olaogun.com', '$2y$10$2DHOfxkjGgcCRjr2eADoXe1OeiL0SL.2npmj4s1JQamEL7anWfkkm', NULL, 'member', 'new', '2023-08-01 21:29:11', NULL, NULL),
(26, '994428TESTING', 'Testing23@gmail.com', '$2y$10$dYgFCarvozrl519F.6wny.TJZnX6n76yS.nfDlNgQZOEy8K3JB282', NULL, 'member', 'new', '2023-08-01 22:06:14', NULL, NULL),
(27, '999881TESTING', 'Testing2963@gmail.com', '$2y$10$AhERTlxICIX7Vg6x45y.w.VxyL3OOLUi47phbVgMfW0z7Al0kgrCu', NULL, 'member', 'new', '2023-08-01 22:11:49', NULL, NULL),
(28, '985172TESTING', 'Testing2f963@gmail.com', '$2y$10$Jp.FDTXwF7cAIsvQ6JdeeOtv8C9bFM9RFRHoDOFhnATAf4qIRHVSy', NULL, 'member', 'new', '2023-08-01 22:13:27', NULL, NULL),
(29, '948473TESTING', 'Testing2vf963@gmail.com', '$2y$10$7UuWRTc.AgyTEPC8Ax42VOhYNaLs7kWTcRBJJkFZtmF/O06auL6ma', NULL, 'member', 'new', '2023-08-01 22:14:17', NULL, NULL),
(32, '992501TESTING', 'bikeogunss@gmail.com', '$2y$10$TN66.HFk7rYfSnig09P2k.HodjTX2noOOUh41eV5aELTBq//n8phW', NULL, 'member', 'new', '2023-08-26 17:00:20', '2023-08-26 17:04:15', '2023-08-26 17:04:15'),
(33, '918627OLAWALE', 'waleolaogunracdel@gmail.com', '$2y$10$CsgCaeAbyfAiDWDmGT3Q3uVgR2lkaWiHIxvQzWO0k3IzAG4O28k0q', NULL, 'member', 'new', '2023-08-26 18:06:54', '2023-08-26 18:08:10', '2023-08-26 18:08:10'),
(34, '950455OLAWALE', 'waleolaogunracdelata@gmail.com', '$2y$10$xBYXHxqB5Lf4BQMDTl/Z6eqp4ou4jlpTnreXEmeGkRrzXz4FDc6Ri', NULL, 'member', 'new', '2023-08-26 18:08:47', NULL, NULL),
(35, '913895OLAWALE', 'waleolaogunracelata@gmail.com', '$2y$10$8EN/e3ubj7KeuIiFkeMXO.A9NqSqAnk/y/zVhV7SPRZAmVFd1I26G', NULL, 'member', 'new', '2023-08-26 18:10:09', NULL, NULL),
(36, '974368OLAWALE', 'waleolffff@gmail.com', '$2y$10$PcWNu9137vATJhn/emj5J.boPgsQodRNe58IXOBQOdWgPL7YoO2uG', NULL, 'member', 'new', '2023-08-27 10:48:02', NULL, NULL),
(37, '935796TESTING', 'wjhwjhwrb@gmail.com', '$2y$10$/S0LnQporGKlVAe/h/GIDu29EzaxkQsc3lWsZLVZh1sPS5tIjGQhy', NULL, 'member', 'new', '2023-08-27 10:51:50', NULL, NULL),
(38, '948454TESTING', 'Testing90873737@gmail.com', '$2y$10$g2XX.KMf3TS9dCj2YjfsLeI8zScS1OyXXI/tKwRNdiF6p3clBM8gO', NULL, 'member', 'new', '2023-08-27 10:57:20', NULL, NULL),
(45, '972748TESTING', 'Testingwewe@gmail.com', '$2y$10$XX.pMhJ3wzU0kWJLSq3sN.vmac8Pmy3s.wHtvCWYR.jOtCB4A.Np6', NULL, 'member', 'new', '2023-08-27 12:01:19', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `comment`
--

CREATE TABLE `comment` (
  `comment_no` int NOT NULL,
  `id` varchar(20) NOT NULL,
  `post_no` varchar(30) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `fullName` text,
  `comment` text,
  `profileImg` text,
  `picture` text,
  `post_time` text,
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_updated` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `date_deleted` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `comment`
--

INSERT INTO `comment` (`comment_no`, `id`, `post_no`, `fullName`, `comment`, `profileImg`, `picture`, `post_time`, `date_created`, `date_updated`, `date_deleted`) VALUES
(2, '117540OLAWALE', '1000', 'OLAWALE', 'Your comment will show here', 'WhatsApp Image 2021 01 24 at 11.59.09  7 .jpeg', '0', NULL, '2021-02-15 23:39:16', NULL, NULL),
(3, '870016OLAWALE', '1000', 'OLAWALE', 'Your comment will show here', 'WhatsApp Image 2021 01 24 at 11.59.09  7 .jpeg', '0', NULL, '2021-02-15 23:42:31', NULL, NULL),
(4, '397755OLUSOLA', '1000', 'OLUSOLA', 'Your comment will show here', 'WhatsApp Image 2021 01 24 at 11.59.09  2 .jpeg', '0', NULL, '2021-02-15 23:49:10', NULL, NULL),
(5, '531422OLUSOLA', '1000', 'OLUSOLA', 'Your comment will show here', 'WhatsApp Image 2021 01 24 at 11.59.09  2 .jpeg', '0', NULL, '2021-02-15 23:51:03', NULL, NULL),
(6, '870016OLAWALE', '505', 'ADESOJI OLAOGUN', 'NICE PICS', 'WhatsApp Image 2021 01 24 at 11.59.09  2 .jpeg', '0', '1613595143', '2021-02-17 20:52:44', '2021-06-28 10:05:20', '2021-06-28 10:05:20'),
(7, '117540OLAWALE', '7', 'SEGUN OLAOGUN', 'Thanks', 'WhatsApp Image 2021 01 24 at 11.59.09  7 .jpeg', '0', '1613595176', '2021-02-17 20:54:09', NULL, NULL),
(8, '870016OLAWALE', '8', 'ADESOJI OLAOGUN', 'just checking', 'SAM_0915.JPG', '0', '1613595835', '2021-02-17 21:04:09', NULL, NULL),
(9, '117540OLAWALE', '8', 'SEGUN OLAOGUN', 'Are you sure ', 'SAM_0866.JPG', '0', '1613595857', '2021-02-17 21:04:27', NULL, NULL),
(10, '117540OLAWALE', '10', 'SEGUN OLAOGUN', 'Account is active   The latest statement is based on the plan end date of 1st Jan 2021. ', 'Jibs.jpeg', '0', '1613602127', '2021-02-17 22:57:21', NULL, NULL),
(11, '117540OLAWALE', '1', 'SEGUN OLAOGUN', 'JUMOKE', 'Jibs.jpeg', '0', '1616760472', '2021-03-26 18:48:22', NULL, NULL),
(12, '117540OLAWALE', '1', 'SEGUN OLAOGUN', 'JUMOKE', 'Jibs.jpeg', '0', '1616784792', '2021-03-26 18:53:20', NULL, NULL),
(13, '432292OLAWALE', '1', 'OLAWALE OLAOGUN', 'Account is active   The latest statement is based on the plan end date of 1st Jan 2021. ', 'jibssoj.jpeg', '0', '1618234193', '2021-04-12 13:39:30', NULL, NULL),
(14, '432292OLAWALE', '1', 'OLAWALE OLAOGUN', 'JUMOKE', 'jibssoj.jpeg', '0', '1618234889', '2021-04-12 13:41:34', NULL, NULL),
(15, '432292OLAWALE', '1', 'OLAWALE OLAOGUN', 'I think  it worked', 'jibssoj.jpeg', '0', '1618234909', '2021-04-12 13:42:05', NULL, NULL),
(16, '432292OLAWALE', '1', 'OLAWALE OLAOGUN', 'gowon was arrested', 'jibssoj.jpeg', '0', '1618234932', '2021-04-12 13:42:19', NULL, NULL),
(17, '432292OLAWALE', '1', 'OLAWALE OLAOGUN', 'IP changed', 'jibssoj.jpeg', '0', '1618234939', '2021-04-12 13:42:27', NULL, NULL),
(18, '432292OLAWALE', '2', 'OLAWALE OLAOGUN', 'bggfrgrr', 'jibssoj.jpeg', '0', '1618247207', '2021-04-12 17:07:49', NULL, NULL),
(19, '432292OLAWALE', '30', 'OLAWALE OLAOGUN', 'I think you should weight yourself', NULL, '0', '1618273298', '2021-04-13 00:21:55', NULL, NULL),
(20, '432292OLAWALE', '30', 'OLAWALE OLAOGUN', 'vvhvh', NULL, '0', '1618274151', '2021-04-13 00:36:53', NULL, NULL),
(21, '870016OLAWALE', '30', 'ADESOJI OLAOGUN', 'thank you mate1', NULL, '0', '1618274390', '2021-04-13 00:40:03', NULL, NULL),
(22, '870016OLAWALE', '31', 'ADESOJI OLAOGUN', 'hghvu', NULL, '0', '1618274426', '2021-04-13 00:40:32', NULL, NULL),
(23, '870016OLAWALE', '31', 'ADESOJI OLAOGUN', 'Yes  I am', NULL, '0', '1618274432', '2021-04-13 00:40:43', NULL, NULL),
(24, '870016OLAWALE', '30', 'ADESOJI OLAOGUN', 'so you think so', NULL, '0', '1618274443', '2021-04-13 00:40:51', NULL, NULL),
(25, '870016OLAWALE', '29', 'ADESOJI OLAOGUN', 'dgd', NULL, '0', '1618274451', '2021-04-13 00:41:00', NULL, NULL),
(26, '870016OLAWALE', '31', 'ADESOJI OLAOGUN', 'JUMOKE', NULL, '0', '1618313124', '2021-04-13 11:25:31', NULL, NULL),
(27, '870016OLAWALE', '30', 'ADESOJI OLAOGUN', 'chineka', NULL, '0', '1618313269', '2021-04-13 11:28:00', NULL, NULL),
(28, '870016OLAWALE', '30', 'ADESOJI OLAOGUN', 'whats', NULL, '0', '1618313721', '2021-04-13 11:35:33', NULL, NULL),
(29, '870016OLAWALE', '25', 'ADESOJI OLAOGUN', 'kiloshe', NULL, '0', '1618313733', '2021-04-13 11:35:53', NULL, NULL),
(30, '117540OLAWALE', '32', 'SEGUN OLAOGUN', 'is it really working', NULL, '0', '1618315745', '2021-04-13 12:09:16', NULL, NULL),
(31, '870016OLAWALE', '32', 'ADESOJI OLAOGUN', 'Yes  it is mate', NULL, '0', '1618315765', '2021-04-13 12:09:33', NULL, NULL),
(32, '117540OLAWALE', '32', 'SEGUN OLAOGUN', 'Am not so sure', NULL, '0', '1618315756', '2021-04-13 12:09:46', NULL, NULL),
(33, '117540OLAWALE', '32', 'SEGUN OLAOGUN', 'I will ask dad about it.', NULL, '0', '1618315786', '2021-04-13 12:10:54', NULL, NULL),
(34, '870016OLAWALE', '32', 'ADESOJI OLAOGUN', 'Okay. maybe you should', NULL, '0', '1618315871', '2021-04-13 12:11:37', NULL, NULL),
(35, '870016OLAWALE', '32', 'ADESOJI OLAOGUN', 'Yes  i asked Olutobi', NULL, '0', '1618316141', '2021-04-13 12:19:39', NULL, NULL),
(36, '117540OLAWALE', '32', 'SEGUN OLAOGUN', 'Okay. she is here with me. ', NULL, '0', '1618316395', '2021-04-13 12:20:19', NULL, NULL),
(37, '870016OLAWALE', '33', 'ADESOJI OLAOGUN', 'I like it', NULL, '0', '1618316485', '2021-04-13 12:21:52', NULL, NULL),
(38, '117540OLAWALE', '33', 'SEGUN OLAOGUN', 'really', NULL, '0', '1618316485', '2021-04-13 12:22:04', NULL, NULL),
(39, '870016OLAWALE', '1', 'ADESOJI OLAOGUN', 'it was very good', NULL, '0', '1618325323', '2021-04-13 15:09:16', NULL, NULL),
(40, '870016OLAWALE', '1', 'ADESOJI OLAOGUN', 'really good', NULL, '0', '1618326575', '2021-04-13 15:13:05', NULL, NULL),
(41, '870016OLAWALE', '1', 'ADESOJI OLAOGUN', 'JUMOKE', NULL, '0', '1618326792', '2021-04-13 15:14:45', NULL, NULL),
(42, '870016OLAWALE', '1', 'ADESOJI OLAOGUN', 'JUMOKE', NULL, '0', '1618326885', '2021-04-13 15:14:46', NULL, NULL),
(43, '870016OLAWALE', '1', 'ADESOJI OLAOGUN', 'JUMOKE', NULL, '0', '1618326886', '2021-04-13 15:15:43', NULL, NULL),
(44, '870016OLAWALE', '1', 'ADESOJI OLAOGUN', '', NULL, '0', '1618327232', '2021-04-13 15:21:29', NULL, NULL),
(45, '870016OLAWALE', '1', 'ADESOJI OLAOGUN', 'JUMOKE', NULL, '0', '1618327289', '2021-04-13 15:21:37', NULL, NULL),
(46, '870016OLAWALE', '33', 'ADESOJI OLAOGUN', 'I think  it worked', NULL, '0', '1618327297', '2021-04-13 15:29:26', NULL, NULL),
(47, '870016OLAWALE', '33', 'ADESOJI OLAOGUN', 'thinking', NULL, '0', '1618327766', '2021-04-13 15:32:59', NULL, NULL),
(48, '870016OLAWALE', '33', 'ADESOJI OLAOGUN', 'Account is active   The latest statement is based on the plan end date of 1st Jan 2021. ', NULL, '0', '1618330667', '2021-04-13 16:17:55', NULL, NULL),
(49, '870016OLAWALE', '33', 'ADESOJI OLAOGUN', 'Account is active   The latest statement is based on the plan end date of 1st Jan 2021. and it is going to', NULL, '0', '1618333858', '2021-04-13 17:11:15', NULL, NULL),
(59, '870016OLAWALE', '33', 'ADESOJI OLAOGUN', 'Wife is beautiful', NULL, '0', '1618352625', '2021-04-13 22:25:47', NULL, NULL),
(60, '870016OLAWALE', '33', 'ADESOJI OLAOGUN', '', NULL, '0', '1618354370', '2021-04-13 22:54:01', NULL, NULL),
(61, '870016OLAWALE', '29', 'ADESOJI OLAOGUN', '  testing it', NULL, '0', '1618356414', '2021-04-13 23:27:13', NULL, NULL),
(62, '870016OLAWALE', '29', 'ADESOJI OLAOGUN', 'gowon was arrested', NULL, '0', '1618356433', '2021-04-13 23:27:33', NULL, NULL),
(63, '870016OLAWALE', '22', 'ADESOJI OLAOGUN', 'JUMOKE', NULL, '0', '1618441678', '2021-04-14 23:08:08', NULL, NULL),
(64, '870016OLAWALE', '33', 'ADESOJI OLAOGUN', 'jfjdjdk', NULL, '0', '1618614071', '2021-04-16 23:01:24', NULL, NULL),
(65, '870016OLAWALE', '29', 'ADESOJI OLAOGUN', '  Not really', NULL, '0', '1619718356', '2021-04-29 17:46:13', NULL, NULL),
(66, '960448AJIBIKE', '1000', 'Ajibike', 'Your comment will show here', NULL, '0', NULL, '2021-05-11 21:10:57', NULL, NULL),
(67, '908713AJIBIKE', '1000', 'Ajibike', 'Your comment will show here', NULL, '0', NULL, '2021-05-11 21:17:00', NULL, NULL),
(68, '994165AJIBIKE', '1000', 'ajibike', 'Your comment will show here', NULL, '0', NULL, '2021-05-11 21:23:08', NULL, NULL),
(69, '973892AJIBIKE', '1000', 'ajibike', 'Your comment will show here', NULL, '0', NULL, '2021-05-11 21:25:38', NULL, NULL),
(70, '990098AJIBIKE', '1000', 'ajibike', 'Your comment will show here', NULL, '0', NULL, '2021-05-11 21:27:04', NULL, NULL),
(71, '973168AJIBIKE', '1000', 'ajibike', 'Your comment will show here', NULL, '0', NULL, '2021-05-11 21:27:29', NULL, NULL),
(72, '975270AJIBIKE', '1000', 'ajibike', 'Your comment will show here', NULL, '0', NULL, '2021-05-11 21:27:55', NULL, NULL),
(73, '975098AJIBIKE', '1000', 'ajibike', 'Your comment will show here', NULL, '0', NULL, '2021-05-11 21:28:22', NULL, NULL),
(74, '969610AJIBIKE', '1000', 'ajibike', 'Your comment will show here', NULL, '0', NULL, '2021-05-11 21:28:49', NULL, NULL),
(75, '915128AJIBIKE', '1000', 'ajibike', 'Your comment will show here', NULL, '0', NULL, '2021-05-11 21:29:16', NULL, NULL),
(76, '980988AJIBIKE', '1000', 'ajibike', 'Your comment will show here', NULL, '0', NULL, '2021-05-11 21:29:43', NULL, NULL),
(77, '933515AJIBIKE', '1000', 'ajibike', 'Your comment will show here', NULL, '0', NULL, '2021-05-11 21:30:10', NULL, NULL),
(78, '986424AJIBIKE', '1000', 'ajibike', 'Your comment will show here', NULL, '0', NULL, '2021-05-11 21:30:36', NULL, NULL),
(79, '972831AJIBIKE', '1000', 'ajibike', 'Your comment will show here', NULL, '0', NULL, '2021-05-11 21:31:03', NULL, NULL),
(80, '916858AJIBIKE', '1000', 'ajibike', 'Your comment will show here', NULL, '0', NULL, '2021-05-11 21:31:30', NULL, NULL),
(81, '971907AJIBIKE', '1000', 'ajibike', 'Your comment will show here', NULL, '0', NULL, '2021-05-11 21:31:56', NULL, NULL),
(82, '955681AJIBIKE', '1000', 'ajibike', 'Your comment will show here', NULL, '0', NULL, '2021-05-11 21:32:21', NULL, NULL),
(83, '955345AJIBIKE', '1000', 'ajibike', 'Your comment will show here', NULL, '0', NULL, '2021-05-11 21:32:46', NULL, NULL),
(84, '945013AJIBIKE', '1000', 'ajibike', 'Your comment will show here', NULL, '0', NULL, '2021-05-11 21:33:16', NULL, NULL),
(85, '954041TOYIN', '1000', 'Toyin', 'Your comment will show here', NULL, '0', NULL, '2021-05-12 22:10:13', NULL, NULL),
(86, '969261FIRSTNAME', '1000', 'firstName', 'Your comment will show here', NULL, '0', NULL, '2021-05-13 09:03:21', NULL, NULL),
(87, '994199FIRSTNAME', '1000', 'firstName', 'Your comment will show here', NULL, '0', NULL, '2021-05-13 09:25:12', NULL, NULL),
(88, '936184FIRSTNAME', '1000', 'firstName', 'Your comment will show here', NULL, '0', NULL, '2021-05-13 09:34:31', NULL, NULL),
(89, '906346FIRSTNAME', '1000', 'firstName', 'Your comment will show here', NULL, '0', NULL, '2021-05-13 09:36:28', NULL, NULL),
(90, '976028FIRSTNAME', '1000', 'firstName', 'Your comment will show here', NULL, '0', NULL, '2021-05-13 09:37:45', NULL, NULL),
(91, '903870FIRSTNAME', '1000', 'firstName', 'Your comment will show here', NULL, '0', NULL, '2021-05-13 10:12:59', NULL, NULL),
(92, '919377FIRSTNAME', '1000', 'firstName', 'Your comment will show here', NULL, '0', NULL, '2021-05-13 10:14:33', NULL, NULL),
(93, '932523FIRSTNAME', '1000', 'firstName', 'Your comment will show here', NULL, '0', NULL, '2021-05-13 10:24:33', NULL, NULL),
(94, '917518FIRSTNAME', '1000', 'firstName', 'Your comment will show here', NULL, '0', NULL, '2021-05-13 10:38:55', NULL, NULL),
(95, '923859FIRSTNAME', '1000', 'firstName', 'Your comment will show here', NULL, '0', NULL, '2021-05-13 10:39:29', NULL, NULL),
(96, '997946FIRSTNAME', '1000', 'firstName', 'Your comment will show here', NULL, '0', NULL, '2021-05-13 10:40:21', NULL, NULL),
(97, '962342FIRSTNAME', '1000', 'firstName', 'Your comment will show here', NULL, '0', NULL, '2021-05-13 10:45:58', NULL, NULL),
(98, '956008FIRSTNAME', '1000', 'firstName', 'Your comment will show here', NULL, '0', NULL, '2021-05-13 10:48:23', NULL, NULL),
(99, '977013FIRSTNAME', '1000', 'firstName', 'Your comment will show here', NULL, '0', NULL, '2021-05-13 10:51:36', NULL, NULL),
(100, '961541FIRSTNAME', '1000', 'firstName', 'Your comment will show here', NULL, '0', NULL, '2021-05-13 10:53:24', NULL, NULL),
(101, '936531FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', '0', NULL, '2021-05-13 11:07:30', NULL, NULL),
(102, '944283FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', '0', NULL, '2021-05-13 12:24:52', NULL, NULL),
(103, '995145FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', '0', NULL, '2021-05-13 12:31:08', NULL, NULL),
(104, '994516FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', '0', NULL, '2021-05-13 12:35:14', NULL, NULL),
(105, '988884FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', '0', NULL, '2021-05-13 12:36:44', NULL, NULL),
(106, '998301FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', '0', NULL, '2021-05-13 14:34:14', NULL, NULL),
(107, '950281FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', '0', NULL, '2021-05-13 14:34:42', NULL, NULL),
(108, '916152FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', '0', NULL, '2021-05-13 14:35:41', NULL, NULL),
(109, '988785FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', '0', NULL, '2021-05-13 14:36:20', NULL, NULL),
(110, '944513FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', '0', NULL, '2021-05-13 14:36:45', NULL, NULL),
(111, '983410FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', '0', NULL, '2021-05-13 14:37:26', NULL, NULL),
(112, '983829FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', '0', NULL, '2021-05-13 14:38:01', NULL, NULL),
(113, '997034FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', '0', NULL, '2021-05-13 14:38:48', NULL, NULL),
(114, '967496FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', '0', NULL, '2021-05-13 15:41:58', NULL, NULL),
(115, '989556FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', '0', NULL, '2021-05-13 15:43:48', NULL, NULL),
(116, '977004FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', '0', NULL, '2021-05-13 15:44:11', NULL, NULL),
(117, '931864FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', '0', NULL, '2021-05-13 15:44:46', NULL, NULL),
(118, '903453FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', '0', NULL, '2021-05-13 15:45:50', NULL, NULL),
(119, '949623FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', '0', NULL, '2021-05-13 15:47:33', NULL, NULL),
(120, '982712FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', '0', NULL, '2021-05-13 15:49:31', NULL, NULL),
(121, '911383FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', '0', NULL, '2021-05-13 15:52:07', NULL, NULL),
(122, '958026FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', '0', NULL, '2021-05-13 15:54:09', NULL, NULL),
(123, '940409FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', '0', NULL, '2021-05-13 15:55:03', NULL, NULL),
(124, '958138FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', '0', NULL, '2021-05-13 15:55:52', NULL, NULL),
(125, '917783FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', '0', NULL, '2021-05-13 17:26:18', NULL, NULL),
(126, '998041FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', '0', NULL, '2021-05-13 17:27:00', NULL, NULL),
(127, '984557FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', '0', NULL, '2021-05-13 22:07:08', NULL, NULL),
(128, '973295FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', '0', NULL, '2021-05-13 22:07:27', NULL, NULL),
(129, '951057FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', '0', NULL, '2021-05-13 22:09:22', NULL, NULL),
(130, '979659FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', '0', NULL, '2021-05-13 22:10:00', NULL, NULL),
(131, '963468FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', '0', NULL, '2021-05-13 22:12:57', NULL, NULL),
(132, '911388FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', '0', NULL, '2021-05-13 22:14:06', NULL, NULL),
(133, '907887FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', '0', NULL, '2021-05-13 22:14:16', NULL, NULL),
(134, '938185FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', '0', NULL, '2021-05-13 22:17:40', NULL, NULL),
(135, '945851FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', '0', NULL, '2021-05-13 22:19:56', NULL, NULL),
(136, '924699FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', '0', NULL, '2021-05-13 22:20:05', NULL, NULL),
(137, '939287FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', '0', NULL, '2021-05-13 22:20:18', NULL, NULL),
(138, '975046FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', '0', NULL, '2021-05-13 22:22:35', NULL, NULL),
(139, '996031FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', '0', NULL, '2021-05-13 22:29:05', NULL, NULL),
(140, '941825FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', '0', NULL, '2021-05-13 23:00:30', NULL, NULL),
(141, '929225FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', '0', NULL, '2021-05-13 23:00:51', NULL, NULL),
(142, '939683FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', '0', NULL, '2021-05-13 23:01:39', NULL, NULL),
(143, '930497FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', '0', NULL, '2021-05-13 23:02:44', NULL, NULL),
(144, '946829FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', '0', NULL, '2021-05-13 23:15:02', NULL, NULL),
(145, '940840FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', '0', NULL, '2021-05-13 23:36:12', NULL, NULL),
(146, '920518FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', '0', NULL, '2021-05-13 23:40:10', NULL, NULL),
(147, '976277FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', '0', NULL, '2021-05-13 23:43:08', NULL, NULL),
(148, '918111FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', '0', NULL, '2021-05-13 23:43:41', NULL, NULL),
(149, '984468FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', '0', NULL, '2021-05-13 23:54:14', NULL, NULL),
(150, '983669FIRSTNAME', '1000', 'firstName', 'Your comment will show here', '/avatar/avatarF.png', '0', NULL, '2021-05-13 23:57:22', NULL, NULL),
(151, '117540OLAWALE', '33', 'SEGUN OLAOGUN', 'JUMOKE', NULL, '0', '1621184877', '2021-05-16 17:22:09', NULL, NULL),
(152, '117540OLAWALE', '32', 'SEGUN OLAOGUN', 'Account is active - The latest statement is based on the plan end date of 1st Jan 2021. ', NULL, '0', '1621185729', '2021-05-16 17:22:23', NULL, NULL),
(153, '117540OLAWALE', '33', 'SEGUN OLAOGUN', 'gowon was arrested', NULL, '0', '1621261034', '2021-05-17 14:17:57', NULL, NULL),
(154, '117540OLAWALE', '12', 'SEGUN OLAOGUN', 'seriously', NULL, '0', '1621271396', '2021-05-17 17:27:36', NULL, NULL),
(155, '117540OLAWALE', '33', 'SEGUN OLAOGUN', '  really', NULL, '0', '1621272461', '2021-05-17 17:31:12', NULL, NULL),
(156, '117540OLAWALE', '33', 'SEGUN OLAOGUN', 'JUMOKE', NULL, '0', '1621272864', '2021-05-17 17:34:34', NULL, NULL),
(157, '117540OLAWALE', '33', 'SEGUN OLAOGUN', 'Testing it', NULL, '0', '1621272875', '2021-05-17 17:34:53', NULL, NULL),
(158, '117540OLAWALE', '32', 'SEGUN OLAOGUN', 'JUMOKE', NULL, '0', '1621292342', '2021-05-17 22:59:46', NULL, NULL),
(159, '117540OLAWALE', '86', 'SEGUN OLAOGUN', '', NULL, '0', '1621356250', '2021-05-18 16:44:29', NULL, NULL),
(160, '117540OLAWALE', '86', 'SEGUN OLAOGUN', 'e choke', NULL, '0', '1621358210', '2021-05-18 17:17:14', NULL, NULL),
(161, '117540OLAWALE', '87', 'SEGUN OLAOGUN', 'not necessary', NULL, '0', '1621358234', '2021-05-18 17:17:26', NULL, NULL),
(162, '117540OLAWALE', '87', 'SEGUN OLAOGUN', 'hdhdh', NULL, '0', '1621365323', '2021-05-18 19:15:40', NULL, NULL),
(163, '117540OLAWALE', '87', 'SEGUN OLAOGUN', 'unable', NULL, '0', '1621365344', '2021-05-18 19:15:57', NULL, NULL),
(164, '117540OLAWALE', '86', 'SEGUN OLAOGUN', 'echoke', NULL, '0', '1621365357', '2021-05-18 19:16:05', NULL, NULL),
(165, '117540OLAWALE', '87', 'SEGUN OLAOGUN', 'enjoyment mann', NULL, '0', '1621418973', '2021-05-19 10:10:29', NULL, NULL),
(166, '117540OLAWALE', '86', 'SEGUN OLAOGUN', 'really', NULL, '0', '1621419029', '2021-05-19 10:10:47', NULL, NULL),
(167, '117540OLAWALE', '87', 'SEGUN OLAOGUN', '', NULL, '0', '1621420262', '2021-05-19 10:31:19', NULL, NULL),
(168, '117540OLAWALE', '87', 'SEGUN OLAOGUN', 'IT IS GOING TO BE FINE', NULL, '0', '1621420279', '2021-05-19 10:34:06', NULL, NULL),
(169, '117540OLAWALE', '87', 'SEGUN OLAOGUN', 'IT IS GOING TO BE FINE', NULL, '0', '1621420279', '2021-05-19 10:36:28', NULL, NULL),
(170, '117540OLAWALE', '87', 'SEGUN OLAOGUN', 'SORRY', NULL, '0', '1621420603', '2021-05-19 10:36:56', NULL, NULL),
(171, '117540OLAWALE', '87', 'SEGUN OLAOGUN', 'SORRY', NULL, '0', '1621420603', '2021-05-19 10:37:50', NULL, NULL),
(172, '117540OLAWALE', '87', 'SEGUN OLAOGUN', 'hhjjjh', NULL, '0', '1621440559', '2021-05-19 16:09:23', NULL, NULL),
(173, '117540OLAWALE', '87', 'SEGUN OLAOGUN', 'jjhjh', NULL, '0', '1621440858', '2021-05-19 16:14:23', NULL, NULL),
(174, '117540OLAWALE', '87', 'SEGUN OLAOGUN', 'jhhjjhjh', NULL, '0', '1621440858', '2021-05-19 16:14:42', NULL, NULL),
(175, '117540OLAWALE', '87', 'SEGUN OLAOGUN', 'jjjkkjkjkj', NULL, '0', '1621440893', '2021-05-19 16:15:06', NULL, NULL),
(176, '117540OLAWALE', '87', 'SEGUN OLAOGUN', 'cccc', NULL, '0', '1621442063', '2021-05-19 16:34:26', NULL, NULL),
(177, '117540OLAWALE', '87', 'SEGUN OLAOGUN', 'llkkkllk', NULL, '0', '1621461318', '2021-05-19 21:55:24', NULL, NULL),
(178, '117540OLAWALE', '87', 'SEGUN OLAOGUN', 'kk', NULL, '0', '1621461465', '2021-05-19 21:58:04', NULL, NULL),
(179, '117540OLAWALE', '87', 'SEGUN OLAOGUN', 'jhhj', NULL, '0', '1621465753', '2021-05-19 23:09:20', NULL, NULL),
(180, '117540OLAWALE', '87', 'SEGUN OLAOGUN', 'jjj', NULL, '0', '1621465904', '2021-05-19 23:11:54', NULL, NULL),
(181, '117540OLAWALE', '87', 'SEGUN OLAOGUN', '', NULL, '0', '1621465981', '2021-05-19 23:13:05', NULL, NULL),
(182, '117540OLAWALE', '87', 'SEGUN OLAOGUN', 'Jesus', NULL, '0', '1621465981', '2021-05-19 23:13:25', NULL, NULL),
(183, '117540OLAWALE', '87', 'SEGUN OLAOGUN', 'sanitation', NULL, '0', '1621466049', '2021-05-19 23:17:50', NULL, NULL),
(184, '117540OLAWALE', '87', 'SEGUN OLAOGUN', 'sanitation', NULL, '0', '1621466049', '2021-05-19 23:17:52', NULL, NULL),
(185, '117540OLAWALE', '87', 'SEGUN OLAOGUN', 'sanitation', NULL, '0', '1621466049', '2021-05-19 23:18:10', NULL, NULL),
(186, '117540OLAWALE', '87', 'SEGUN OLAOGUN', 'JUMOKE', NULL, '0', '1621466314', '2021-05-19 23:18:38', NULL, NULL),
(187, '117540OLAWALE', '87', 'SEGUN OLAOGUN', 'JUMOKE', NULL, '0', '1621466321', '2021-05-19 23:19:14', NULL, NULL),
(188, '117540OLAWALE', '87', 'SEGUN OLAOGUN', 'enjoyinh', NULL, '0', '1621466394', '2021-05-19 23:20:03', NULL, NULL),
(189, '117540OLAWALE', '87', 'SEGUN OLAOGUN', 'enjoyinh', NULL, '0', '1621466394', '2021-05-19 23:20:08', NULL, NULL),
(190, '117540OLAWALE', '87', 'SEGUN OLAOGUN', 'ghhhhg', NULL, '0', '1621467059', '2021-05-19 23:31:16', NULL, NULL),
(191, '117540OLAWALE', '87', 'SEGUN OLAOGUN', 'ghhhhg', NULL, '0', '1621467059', '2021-05-19 23:31:17', NULL, NULL),
(192, '117540OLAWALE', '87', 'SEGUN OLAOGUN', 'SOJ', NULL, '0', '1621503035', '2021-05-20 09:30:48', NULL, NULL),
(193, '117540OLAWALE', '87', 'SEGUN OLAOGUN', 'SOJ', NULL, '0', '1621503035', '2021-05-20 09:31:52', NULL, NULL),
(194, '117540OLAWALE', '87', 'SEGUN OLAOGUN', 'hdhdhd', NULL, '0', '1621503119', '2021-05-20 09:32:05', NULL, NULL),
(195, '117540OLAWALE', '87', 'SEGUN OLAOGUN', 'JUMOKE', NULL, '0', '1621503232', '2021-05-20 09:33:52', NULL, NULL),
(196, '117540OLAWALE', '87', 'SEGUN OLAOGUN', 'gowon was arrested', NULL, '0', '1621503237', '2021-05-20 09:34:05', NULL, NULL),
(197, '117540OLAWALE', '87', 'SEGUN OLAOGUN', 'oladele', NULL, '0', '1621503253', '2021-05-20 09:34:13', NULL, NULL),
(198, '117540OLAWALE', '87', 'SEGUN OLAOGUN', 'tunji', NULL, '0', '1621503261', '2021-05-20 09:34:21', NULL, NULL),
(199, '117540OLAWALE', '87', 'SEGUN OLAOGUN', 'why is he a crook', NULL, '0', '1621503315', '2021-05-20 09:35:24', NULL, NULL),
(200, '117540OLAWALE', '87', 'SEGUN OLAOGUN', 'I don t know', NULL, '0', '1621503324', '2021-05-20 09:35:32', NULL, NULL),
(201, '117540OLAWALE', '87', 'SEGUN OLAOGUN', 'You don t know   ', NULL, '0', '1621503332', '2021-05-20 09:35:44', NULL, NULL),
(202, '117540OLAWALE', '87', 'SEGUN OLAOGUN', 'I think you should definitely know', NULL, '0', '1621503359', '2021-05-20 09:36:11', NULL, NULL),
(203, '117540OLAWALE', '86', 'SEGUN OLAOGUN', 'so  what is the score', NULL, '0', '1621503371', '2021-05-20 09:36:25', NULL, NULL),
(204, '117540OLAWALE', '86', 'SEGUN OLAOGUN', 'I dont the answer to the question mate....', NULL, '0', '1621503385', '2021-05-20 09:36:40', NULL, NULL),
(205, '117540OLAWALE', '85', 'SEGUN OLAOGUN', 'I think you should definitely know', NULL, '0', '1621503400', '2021-05-20 09:36:49', NULL, NULL),
(206, '117540OLAWALE', '85', 'SEGUN OLAOGUN', 'How should I know', NULL, '0', '1621503409', '2021-05-20 09:37:02', NULL, NULL),
(207, '117540OLAWALE', '88', 'SEGUN OLAOGUN', 'welldone Jummy', NULL, '0', '1621504132', '2021-05-20 09:49:06', NULL, NULL),
(208, '117540OLAWALE', '87', 'SEGUN OLAOGUN', 'amazing', NULL, '0', '1621723093', '2021-05-22 22:38:14', NULL, NULL),
(209, '117540OLAWALE', '87', 'SEGUN OLAOGUN', 'is he really a crook', NULL, '0', '1621723106', '2021-05-22 22:38:37', NULL, NULL),
(210, '117540OLAWALE', '86', 'SEGUN OLAOGUN', 'Let us check why it is like that', NULL, '0', '1621723117', '2021-05-22 22:38:59', NULL, NULL),
(211, '117540OLAWALE', '88', 'SEGUN OLAOGUN', 'Is that correct', NULL, '0', '1621809831', '2021-05-23 22:43:51', NULL, NULL),
(212, '117540OLAWALE', '88', 'SEGUN OLAOGUN', 'super good', NULL, '0', '1621809847', '2021-05-23 22:44:07', NULL, NULL),
(213, '117540OLAWALE', '88', 'SEGUN OLAOGUN', 'reallt', NULL, '0', '1621809859', '2021-05-23 22:44:19', NULL, NULL),
(214, '117540OLAWALE', '88', 'SEGUN OLAOGUN', 'the tennis player', NULL, '0', '1621853865', '2021-05-24 10:57:45', NULL, NULL),
(215, '117540OLAWALE', '88', 'SEGUN OLAOGUN', 'ambitious', NULL, '0', '1621853877', '2021-05-24 10:57:57', NULL, NULL),
(216, '117540OLAWALE', '90', 'SEGUN OLAOGUN', 'this place looks nice', NULL, '0', '1621863170', '2021-05-24 13:32:50', NULL, NULL),
(217, '117540OLAWALE', '90', 'SEGUN OLAOGUN', '  eerer', NULL, '0', '1621874643', '2021-05-24 16:45:13', NULL, NULL),
(218, '117540OLAWALE', '90', 'SEGUN OLAOGUN', 'anytime', NULL, '0', '1622685141', '2021-06-03 01:52:34', NULL, NULL),
(219, '117540OLAWALE', '89', 'SEGUN OLAOGUN', 'it s shocking', NULL, '0', '1622685169', '2021-06-03 01:52:49', NULL, NULL),
(220, '117540OLAWALE', '90', 'SEGUN OLAOGUN', 'really', NULL, '0', '1622685177', '2021-06-03 01:52:57', NULL, NULL),
(221, '117540OLAWALE', '89', 'SEGUN OLAOGUN', 'is it ', NULL, '0', '1622685177', '2021-06-03 01:53:14', NULL, NULL),
(222, '117540OLAWALE', '89', 'SEGUN OLAOGUN', 'I don t think so', NULL, '0', '1622685205', '2021-06-03 01:53:25', NULL, NULL),
(223, '117540OLAWALE', '89', 'SEGUN OLAOGUN', 'why dont you think', NULL, '0', '1622685212', '2021-06-03 01:53:41', NULL, NULL),
(224, '117540OLAWALE', '89', 'SEGUN OLAOGUN', 'I guess I dont believe', NULL, '0', '1622685232', '2021-06-03 01:53:52', NULL, NULL),
(225, '117540OLAWALE', '90', 'SEGUN OLAOGUN', 'loo', NULL, '0', '1622716163', '2021-06-03 10:34:14', NULL, NULL),
(226, '117540OLAWALE', '90', 'SEGUN OLAOGUN', 'Account is active - The latest statement is based on the plan end date of 1st Jan 2021. ', NULL, '0', '1622716454', '2021-06-03 10:34:28', NULL, NULL),
(227, '117540OLAWALE', '90', 'SEGUN OLAOGUN', 'ss', NULL, '0', '1622716831', '2021-06-03 10:40:36', NULL, NULL),
(228, '117540OLAWALE', '90', 'SEGUN OLAOGUN', 'sss', NULL, '0', '1622716837', '2021-06-03 10:40:42', NULL, NULL),
(229, '117540OLAWALE', '90', 'SEGUN OLAOGUN', 'ss', NULL, '0', '1622716842', '2021-06-03 10:41:01', NULL, NULL),
(230, '117540OLAWALE', '90', 'SEGUN OLAOGUN', 'xcx', NULL, '0', '1622717419', '2021-06-03 10:50:24', NULL, NULL),
(231, '117540OLAWALE', '90', 'SEGUN OLAOGUN', 'sdss', NULL, '0', '1622717427', '2021-06-03 10:50:31', NULL, NULL),
(232, '117540OLAWALE', '90', 'SEGUN OLAOGUN', 'sd', NULL, '0', '1622717451', '2021-06-03 10:50:51', NULL, NULL),
(233, '117540OLAWALE', '89', 'SEGUN OLAOGUN', 'sdsd', NULL, '0', '1622717451', '2021-06-03 10:50:56', NULL, NULL),
(234, '117540OLAWALE', '90', 'SEGUN OLAOGUN', 'gh', NULL, '0', '1622717516', '2021-06-03 10:52:00', NULL, NULL),
(235, '117540OLAWALE', '90', 'SEGUN OLAOGUN', 'jjhjhhhhhjj__', NULL, '0', '1622717701', '2021-06-03 10:55:09', NULL, NULL),
(236, '117540OLAWALE', '90', 'SEGUN OLAOGUN', 'sdsdsds', NULL, '0', '1622718121', '2021-06-03 11:02:08', NULL, NULL),
(237, '117540OLAWALE', '90', 'SEGUN OLAOGUN', 'Ethnic sanitation', NULL, '0', '1622718412', '2021-06-03 11:07:19', NULL, NULL),
(238, '117540OLAWALE', '90', 'SEGUN OLAOGUN', 'oladele', NULL, '0', '1622719019', '2021-06-03 11:17:06', NULL, NULL),
(239, '117540OLAWALE', '89', 'SEGUN OLAOGUN', 'chukwu', NULL, '0', '1622719026', '2021-06-03 11:17:14', NULL, NULL),
(240, '117540OLAWALE', '88', 'SEGUN OLAOGUN', 'christian', NULL, '0', '1622719034', '2021-06-03 11:17:23', NULL, NULL),
(241, '117540OLAWALE', '88', 'SEGUN OLAOGUN', 'engagement', NULL, '0', '1622719043', '2021-06-03 11:17:30', NULL, NULL),
(242, '117540OLAWALE', '88', 'SEGUN OLAOGUN', 'example', NULL, '0', '1622719050', '2021-06-03 11:17:37', NULL, NULL),
(243, '117540OLAWALE', '88', 'SEGUN OLAOGUN', 'sanitation', NULL, '0', '1622719057', '2021-06-03 11:17:43', NULL, NULL),
(244, '117540OLAWALE', '90', 'SEGUN OLAOGUN', 'possible way out', NULL, '0', '1622719063', '2021-06-03 11:17:52', NULL, NULL),
(245, '117540OLAWALE', '90', 'SEGUN OLAOGUN', 'eps', NULL, '0', '1622719072', '2021-06-03 11:19:58', NULL, NULL),
(246, '117540OLAWALE', '90', 'SEGUN OLAOGUN', 'reported', NULL, '0', '1622725374', '2021-06-03 13:03:59', NULL, NULL),
(247, '117540OLAWALE', '89', 'SEGUN OLAOGUN', 'asda', NULL, '0', '1622725439', '2021-06-03 13:04:08', NULL, NULL),
(248, '117540OLAWALE', '91', 'SEGUN OLAOGUN', 'when did you get here  ', NULL, '0', '1622727036', '2021-06-03 13:30:52', NULL, NULL),
(249, '397755OLUSOLA', '91', 'FEMI OLAOGUN', 'Am not sure', NULL, '0', '1622730494', '2021-06-03 14:30:03', NULL, NULL),
(250, '397755OLUSOLA', '91', 'FEMI OLAOGUN', 'are you sure', NULL, '0', '1622732470', '2021-06-03 15:01:10', NULL, NULL),
(251, '397755OLUSOLA', '91', 'FEMI OLAOGUN', 'am not so sure', NULL, '0', '1622732470', '2021-06-03 15:01:20', NULL, NULL),
(252, '117540OLAWALE', '91', 'SEGUN OLAOGUN', 'are you sure of the day ', NULL, '0', '1623067397', '2021-06-07 12:03:41', NULL, NULL),
(253, '117540OLAWALE', '90', 'SEGUN OLAOGUN', 'Interesting', NULL, '0', '1623067421', '2021-06-07 12:03:55', NULL, NULL),
(254, '117540OLAWALE', '282', 'SEGUN OLAOGUN', 'intersting', NULL, '0', '1623271197', '2021-06-09 20:40:42', NULL, NULL),
(255, '397755OLUSOLA', '282', 'FEMI OLAOGUN', 'are you sure', NULL, '0', '1623271247', '2021-06-09 20:40:54', NULL, NULL),
(256, '397755OLUSOLA', '282', 'FEMI OLAOGUN', 'engage', NULL, '0', '1623271369', '2021-06-09 20:42:49', NULL, NULL),
(257, '117540OLAWALE', '346', 'SEGUN OLAOGUN', 'JUMOKE', NULL, '0', '1623679222', '2021-06-14 14:02:12', NULL, NULL),
(258, '117540OLAWALE', '335', 'SEGUN OLAOGUN', 'JUMOKE', NULL, '0', '1623758070', '2021-06-15 11:56:40', NULL, NULL),
(259, '117540OLAWALE', '348', 'SEGUN OLAOGUN', 'Account is active - The latest statement is based on the plan end date of 1st Jan 2021. ', NULL, '0', '1623758206', '2021-06-15 11:56:46', NULL, NULL),
(260, '117540OLAWALE', '347', 'SEGUN OLAOGUN', 'Account is active - The latest statement is based on the plan end date of 1st Jan 2021. ', NULL, '0', '1623758206', '2021-06-15 11:56:52', NULL, NULL),
(261, '117540OLAWALE', '472', 'SEGUN OLAOGUN', 'JUMOKE', NULL, '0', '1624488787', '2021-06-23 22:54:17', NULL, NULL),
(262, '117540OLAWALE', '480', 'SEGUN OLAOGUN', 'nice', NULL, '0', '1624565053875', '2021-06-24 20:04:13', NULL, NULL),
(263, '432292OLAWALE', '505', 'OLAWALE OLAOGUN', 'what are you checking', NULL, '0', '1624632504064', '2021-06-25 14:48:24', NULL, NULL),
(264, '432292OLAWALE', '505', 'OLAWALE OLAOGUN', '', NULL, '0', '1624634278027', '2021-06-25 15:17:58', NULL, NULL),
(265, '432292OLAWALE', '505', 'OLAWALE OLAOGUN', 'hjjhjh', NULL, '0', '1624641621502', '2021-06-25 17:20:21', NULL, NULL),
(266, '432292OLAWALE', '1000', 'OLAWALE OLAOGUN', 'jhjhjh', NULL, '0', '1624875411678', '2021-06-28 10:16:51', NULL, NULL),
(267, '432292OLAWALE', '507', 'OLAWALE OLAOGUN', 'it is a return', NULL, '0', '1624892236138', '2021-06-28 14:57:16', NULL, NULL),
(268, '432292OLAWALE', '507', 'OLAWALE OLAOGUN', 'what are you returning', NULL, '0', '1624892253593', '2021-06-28 14:57:33', NULL, NULL),
(269, '432292OLAWALE', '507', 'OLAWALE OLAOGUN', 'dffddfd', NULL, '0', '1624893449199', '2021-06-28 15:17:29', NULL, NULL),
(270, '432292OLAWALE', '504', 'OLAWALE OLAOGUN', '', NULL, '0', '1624917763480', '2021-06-28 22:02:43', NULL, NULL),
(271, '432292OLAWALE', '507', 'OLAWALE OLAOGUN', 'rdfdf', NULL, '0', '1624918466114', '2021-06-28 22:14:26', NULL, NULL),
(272, '432292OLAWALE', '507', 'OLAWALE OLAOGUN', 'popopopo', NULL, '0', '1624922800910', '2021-06-28 23:26:40', NULL, NULL),
(273, '432292OLAWALE', '500', 'OLAWALE OLAOGUN', 'interestin', NULL, '0', '1624922868377', '2021-06-28 23:27:48', NULL, NULL),
(274, '432292OLAWALE', '498', 'OLAWALE OLAOGUN', 'kkkk', NULL, '0', '1624923320485', '2021-06-28 23:35:20', NULL, NULL),
(275, '432292OLAWALE', '498', 'OLAWALE OLAOGUN', 'kjjjkjkjk', NULL, '0', '1624923348580', '2021-06-28 23:35:48', NULL, NULL),
(276, '397755OLUSOLA', '507', 'FEMI OLAOGUN', 'sanitation', NULL, '0', '1625148651002', '2021-07-01 14:10:51', NULL, NULL),
(277, '397755OLUSOLA', '507', 'FEMI OLAOGUN', 'interesting', NULL, '0', '1625148664075', '2021-07-01 14:11:04', NULL, NULL),
(278, '432292OLAWALE', '507', 'OLAWALE OLAOGUN', 'is it that a good things to check', NULL, '0', '1625148850835', '2021-07-01 14:14:10', NULL, NULL),
(279, '432292OLAWALE', '508', 'OLAWALE OLAOGUN', 'hjhjhj', NULL, '0', '1625149147353', '2021-07-01 14:19:07', NULL, NULL),
(280, '432292OLAWALE', '508', 'OLAWALE OLAOGUN', 'hjjhhjhjhj', NULL, '0', '1625149160549', '2021-07-01 14:19:20', NULL, NULL),
(281, '432292OLAWALE', '507', 'OLAWALE OLAOGUN', 'nkk', NULL, '0', '1625477791931', '2021-07-05 09:36:31', NULL, NULL),
(282, '432292OLAWALE', '510', 'OLAWALE OLAOGUN', 'jhjhhj', NULL, '0', '1625481759432', '2021-07-05 10:42:39', NULL, NULL),
(283, '432292OLAWALE', '510', 'OLAWALE OLAOGUN', 'sanitation', NULL, '0', '1625481774978', '2021-07-05 10:42:54', NULL, NULL),
(284, '432292OLAWALE', '507', 'OLAWALE OLAOGUN', 'ayodeji', NULL, '0', '1625481855747', '2021-07-05 10:44:15', NULL, NULL),
(285, '432292OLAWALE', '507', 'OLAWALE OLAOGUN', 'are you sure ', NULL, '0', '1625481924919', '2021-07-05 10:45:24', NULL, NULL),
(286, '432292OLAWALE', '507', 'OLAWALE OLAOGUN', 'dont worry about it', NULL, '0', '1625482169831', '2021-07-05 10:49:29', NULL, NULL),
(287, '432292OLAWALE', '507', 'OLAWALE OLAOGUN', 'dont worry about it', NULL, '0', '1625484390674', '2021-07-05 11:26:30', NULL, NULL),
(288, '432292OLAWALE', '507', 'OLAWALE OLAOGUN', 'commander', NULL, '0', '1625484700822', '2021-07-05 11:31:40', NULL, NULL),
(289, '432292OLAWALE', '511', 'OLAWALE OLAOGUN', 'SANIS', NULL, '0', '1625485752421', '2021-07-05 11:49:12', NULL, NULL),
(290, '432292OLAWALE', '511', 'OLAWALE OLAOGUN', 'I dont undersa', NULL, '0', '1625487064007', '2021-07-05 12:11:04', NULL, NULL),
(291, '432292OLAWALE', '511', 'OLAWALE OLAOGUN', 'dont worry about it', NULL, '0', '1625487097870', '2021-07-05 12:11:37', NULL, NULL),
(292, '432292OLAWALE', '511', 'OLAWALE OLAOGUN', 'get alone', NULL, '0', '1625487141960', '2021-07-05 12:12:21', NULL, NULL),
(293, '432292OLAWALE', '511', 'OLAWALE OLAOGUN', 'blessing', NULL, '0', '1625487723860', '2021-07-05 12:22:03', NULL, NULL),
(294, '432292OLAWALE', '507', 'OLAWALE OLAOGUN', 'beautiful in his time', NULL, '0', '1625487990780', '2021-07-05 12:26:30', NULL, NULL),
(295, '432292OLAWALE', '507', 'OLAWALE OLAOGUN', 'dgddgdfd', NULL, '0', '1625489272045', '2021-07-05 12:47:52', NULL, NULL),
(296, '432292OLAWALE', '507', 'OLAWALE OLAOGUN', 'dgddgdfd', NULL, '0', '1625489308984', '2021-07-05 12:48:28', NULL, NULL),
(297, '432292OLAWALE', '513', 'OLAWALE OLAOGUN', 'extremmes', NULL, '0', '1625490517316', '2021-07-05 13:08:37', NULL, NULL),
(298, '432292OLAWALE', '513', 'OLAWALE OLAOGUN', 'jskjsjksjks', NULL, '0', '1625490614826', '2021-07-05 13:10:14', NULL, NULL),
(299, '432292OLAWALE', '513', 'OLAWALE OLAOGUN', 'gdgd', NULL, '0', '1625490648310', '2021-07-05 13:10:48', NULL, NULL),
(300, '432292OLAWALE', '513', 'OLAWALE OLAOGUN', 'gentle', NULL, '0', '1625490677517', '2021-07-05 13:11:17', NULL, NULL),
(301, '432292OLAWALE', '514', 'OLAWALE OLAOGUN', 'i dont think so ', NULL, '0', '1625490701799', '2021-07-05 13:11:41', NULL, NULL),
(302, '432292OLAWALE', '514', 'OLAWALE OLAOGUN', 'sfss', NULL, '0', '1625490769049', '2021-07-05 13:12:49', NULL, NULL),
(303, '432292OLAWALE', '516', 'OLAWALE OLAOGUN', 'police man', NULL, '0', '1625491223492', '2021-07-05 13:20:23', NULL, NULL),
(304, '432292OLAWALE', '516', 'OLAWALE OLAOGUN', 'police woman then', NULL, '0', '1625491256291', '2021-07-05 13:20:56', NULL, NULL),
(305, '117540OLAWALE', '516', 'SEGUN OLAOGUN', 'Account is active - The latest statement is based on the plan end date of 1st Jan 2021. ', NULL, '0', '1625491345280', '2021-07-05 13:22:25', NULL, NULL),
(306, '117540OLAWALE', '516', 'SEGUN OLAOGUN', 'I think  it worked', NULL, '0', '1625491445492', '2021-07-05 13:24:05', NULL, NULL),
(307, '117540OLAWALE', '516', 'SEGUN OLAOGUN', 'gowon was arrested', NULL, '0', '1625491742345', '2021-07-05 13:29:02', NULL, NULL),
(308, '432292OLAWALE', '516', 'OLAWALE OLAOGUN', 'hjhjhj', NULL, '0', '1625491882458', '2021-07-05 13:31:22', NULL, NULL),
(309, '117540OLAWALE', '521', 'SEGUN OLAOGUN', 'sanitation of the universe', NULL, '0', '1625492664421', '2021-07-05 13:44:24', NULL, NULL),
(310, '432292OLAWALE', '521', 'OLAWALE OLAOGUN', 'enjoyment', NULL, '0', '1625492811287', '2021-07-05 13:46:51', NULL, NULL),
(311, '432292OLAWALE', '521', 'OLAWALE OLAOGUN', 'dere', NULL, '0', '1625492953908', '2021-07-05 13:49:13', NULL, NULL),
(312, '432292OLAWALE', '522', 'OLAWALE OLAOGUN', 'ddreeree', NULL, '0', '1625493001100', '2021-07-05 13:50:01', NULL, NULL),
(313, '432292OLAWALE', '522', 'OLAWALE OLAOGUN', 'DFDFDDD', NULL, '0', '1625493127823', '2021-07-05 13:52:07', NULL, NULL),
(314, '432292OLAWALE', '523', 'OLAWALE OLAOGUN', 'JOGGLING EVERYTHING TOGETHER', NULL, '0', '1625493175676', '2021-07-05 13:52:55', NULL, NULL),
(315, '432292OLAWALE', '525', 'OLAWALE OLAOGUN', 'hjjh', NULL, '0', '1625494080715', '2021-07-05 14:08:00', NULL, NULL),
(316, '432292OLAWALE', '526', 'OLAWALE OLAOGUN', 'kjjkjkkj', NULL, '0', '1625494131036', '2021-07-05 14:08:51', NULL, NULL),
(317, '432292OLAWALE', '527', 'OLAWALE OLAOGUN', 'interesting', NULL, '0', '1625494213814', '2021-07-05 14:10:13', NULL, NULL),
(318, '432292OLAWALE', '528', 'OLAWALE OLAOGUN', 'are you really', NULL, '0', '1625494316347', '2021-07-05 14:11:56', NULL, NULL),
(319, '432292OLAWALE', '534', 'OLAWALE OLAOGUN', 'capticating ', NULL, '0', '1625511144725', '2021-07-05 18:52:24', NULL, NULL),
(320, '432292OLAWALE', '534', 'OLAWALE OLAOGUN', 'is it really ', NULL, '0', '1625511240545', '2021-07-05 18:54:00', NULL, NULL),
(321, '432292OLAWALE', '534', 'OLAWALE OLAOGUN', 'am good', NULL, '0', '1625512485945', '2021-07-05 19:14:45', NULL, NULL),
(322, '432292OLAWALE', '534', 'OLAWALE OLAOGUN', 'interesting', NULL, '0', '1625515487455', '2021-07-05 20:04:47', NULL, NULL),
(323, '432292OLAWALE', '534', 'OLAWALE OLAOGUN', 'get on with it', NULL, '0', '1625515522325', '2021-07-05 20:05:22', NULL, NULL),
(324, '432292OLAWALE', '534', 'OLAWALE OLAOGUN', 'ssdsds', NULL, '0', '1625516760560', '2021-07-05 20:26:00', NULL, NULL),
(325, '432292OLAWALE', '534', 'OLAWALE OLAOGUN', 'aje', NULL, '0', '1625516776925', '2021-07-05 20:26:16', NULL, NULL),
(326, '432292OLAWALE', '534', 'OLAWALE OLAOGUN', 'ati gbo', NULL, '0', '1625516903948', '2021-07-05 20:28:23', NULL, NULL),
(327, '432292OLAWALE', '534', 'OLAWALE OLAOGUN', 'fopsisis', NULL, '0', '1625516938336', '2021-07-05 20:28:58', NULL, NULL),
(328, '432292OLAWALE', '534', 'OLAWALE OLAOGUN', 'dede', NULL, '0', '1625517106282', '2021-07-05 20:31:46', NULL, NULL),
(329, '432292OLAWALE', '534', 'OLAWALE OLAOGUN', 'sfsfsfff', NULL, '0', '1625517145891', '2021-07-05 20:32:25', NULL, NULL),
(330, '432292OLAWALE', '534', 'OLAWALE OLAOGUN', 'another terst', NULL, '0', '1625517174397', '2021-07-05 20:32:54', NULL, NULL),
(331, '432292OLAWALE', '534', 'OLAWALE OLAOGUN', 'the body', NULL, '0', '1625519068953', '2021-07-05 21:04:28', NULL, NULL),
(332, '432292OLAWALE', '534', 'OLAWALE OLAOGUN', 'realise', NULL, '0', '1625519086722', '2021-07-05 21:04:46', NULL, NULL),
(333, '432292OLAWALE', '534', 'OLAWALE OLAOGUN', 'DFDFDDD', NULL, '0', '1625519274662', '2021-07-05 21:07:54', NULL, NULL),
(334, '432292OLAWALE', '534', 'OLAWALE OLAOGUN', 'fdf', NULL, '0', '1625519281361', '2021-07-05 21:08:01', NULL, NULL),
(335, '432292OLAWALE', '534', 'OLAWALE OLAOGUN', 'fdfgfg', NULL, '0', '1625519286648', '2021-07-05 21:08:06', NULL, NULL),
(336, '432292OLAWALE', '534', 'OLAWALE OLAOGUN', 'dfdggd', NULL, '0', '1625519410993', '2021-07-05 21:10:10', NULL, NULL),
(337, '432292OLAWALE', '534', 'OLAWALE OLAOGUN', 'dfdggdgfgf', NULL, '0', '1625519415384', '2021-07-05 21:10:15', NULL, NULL),
(338, '432292OLAWALE', '534', 'OLAWALE OLAOGUN', 'cfdc', NULL, '0', '1625519423320', '2021-07-05 21:10:23', NULL, NULL),
(339, '432292OLAWALE', '534', 'OLAWALE OLAOGUN', 'opollspsps', NULL, '0', '1625519490399', '2021-07-05 21:11:30', NULL, NULL),
(340, '432292OLAWALE', '534', 'OLAWALE OLAOGUN', 'anything is good', NULL, '0', '1625519519245', '2021-07-05 21:11:59', NULL, NULL),
(341, '432292OLAWALE', '534', 'OLAWALE OLAOGUN', 'JOGGLING EVERYTHING TOGETHER', NULL, '0', '1625519542089', '2021-07-05 21:12:22', NULL, NULL),
(342, '432292OLAWALE', '534', 'OLAWALE OLAOGUN', 'ARE YOU SYRE', NULL, '0', '1625519737349', '2021-07-05 21:15:37', NULL, NULL),
(343, '432292OLAWALE', '534', 'OLAWALE OLAOGUN', 'enjoyment at this', NULL, '0', '1625520161672', '2021-07-05 21:22:41', NULL, NULL),
(344, '432292OLAWALE', '533', 'OLAWALE OLAOGUN', 'did it happen', NULL, '0', '1625520420817', '2021-07-05 21:27:00', NULL, NULL),
(345, '432292OLAWALE', '533', 'OLAWALE OLAOGUN', 'Yes  it did', NULL, '0', '1625520438573', '2021-07-05 21:27:18', NULL, NULL),
(346, '432292OLAWALE', '533', 'OLAWALE OLAOGUN', 'dont worry about it', NULL, '0', '1625520504482', '2021-07-05 21:28:24', NULL, NULL),
(347, '432292OLAWALE', '533', 'OLAWALE OLAOGUN', 'exactly', NULL, '0', '1625520584046', '2021-07-05 21:29:44', NULL, NULL),
(348, '432292OLAWALE', '533', 'OLAWALE OLAOGUN', 'president', NULL, '0', '1625520645016', '2021-07-05 21:30:45', NULL, NULL),
(349, '432292OLAWALE', '533', 'OLAWALE OLAOGUN', 'escape', NULL, '0', '1625520673790', '2021-07-05 21:31:13', NULL, NULL),
(350, '432292OLAWALE', '534', 'OLAWALE OLAOGUN', 'polip ', NULL, '0', '1625520906665', '2021-07-05 21:35:06', NULL, NULL),
(351, '117540OLAWALE', '534', 'SEGUN OLAOGUN', 'Account is active - The latest statement is based on the plan end date of 1st Jan 2021. ', NULL, '0', '1625521047451', '2021-07-05 21:37:27', NULL, NULL),
(352, '432292OLAWALE', '534', 'OLAWALE OLAOGUN', 'wales', NULL, '0', '1625521229284', '2021-07-05 21:40:29', NULL, NULL),
(353, '432292OLAWALE', '534', 'OLAWALE OLAOGUN', 'detailed energy', NULL, '0', '1625521319705', '2021-07-05 21:41:59', NULL, NULL),
(354, '432292OLAWALE', '534', 'OLAWALE OLAOGUN', 'do you think so', NULL, '0', '1625521333756', '2021-07-05 21:42:13', NULL, NULL),
(355, '432292OLAWALE', '534', 'OLAWALE OLAOGUN', 'do you think for the energy and sanitation', NULL, '0', '1625521347634', '2021-07-05 21:42:27', NULL, NULL),
(356, '432292OLAWALE', '534', 'OLAWALE OLAOGUN', 'execution of the man', NULL, '0', '1625521363884', '2021-07-05 21:42:43', NULL, NULL),
(357, '432292OLAWALE', '533', 'OLAWALE OLAOGUN', '200 people are investigating', NULL, '0', '1625521402334', '2021-07-05 21:43:22', NULL, NULL),
(358, '432292OLAWALE', '534', 'OLAWALE OLAOGUN', 'enjoy', NULL, '0', '1625523096175', '2021-07-05 22:11:36', NULL, NULL),
(359, '432292OLAWALE', '534', 'OLAWALE OLAOGUN', 'enjoy', NULL, '0', '1625523109041', '2021-07-05 22:11:49', NULL, NULL),
(360, '432292OLAWALE', '534', 'OLAWALE OLAOGUN', 'interesting', NULL, '0', '1625523186475', '2021-07-05 22:13:06', NULL, NULL),
(361, '432292OLAWALE', '535', 'OLAWALE OLAOGUN', 'jump at it', NULL, '0', '1625523740405', '2021-07-05 22:22:20', NULL, NULL),
(362, '432292OLAWALE', '535', 'OLAWALE OLAOGUN', 'AGA', NULL, '0', '1625524613264', '2021-07-05 22:36:53', NULL, NULL),
(363, '432292OLAWALE', '535', 'OLAWALE OLAOGUN', 'AGAIN NEVER', NULL, '0', '1625524625569', '2021-07-05 22:37:05', NULL, NULL),
(364, '117540OLAWALE', '535', 'SEGUN OLAOGUN', 'I think  it worked', NULL, '0', '1625524870734', '2021-07-05 22:41:10', NULL, NULL),
(365, '432292OLAWALE', '535', 'OLAWALE OLAOGUN', 'hgjvkhb', NULL, '0', '1625525773140', '2021-07-05 22:56:13', NULL, NULL),
(366, '432292OLAWALE', '535', 'OLAWALE OLAOGUN', 'inter', NULL, '0', '1625525857691', '2021-07-05 22:57:37', NULL, NULL),
(367, '432292OLAWALE', '535', 'OLAWALE OLAOGUN', 'police woman then', NULL, '0', '1625525915576', '2021-07-05 22:58:35', NULL, NULL),
(368, '432292OLAWALE', '535', 'OLAWALE OLAOGUN', 'brutal', NULL, '0', '1625526313495', '2021-07-05 23:05:13', NULL, NULL),
(369, '432292OLAWALE', '535', 'OLAWALE OLAOGUN', 'thank you', NULL, '0', '1625526368238', '2021-07-05 23:06:08', NULL, NULL),
(370, '432292OLAWALE', '535', 'OLAWALE OLAOGUN', 'espe', NULL, '0', '1625526478958', '2021-07-05 23:07:58', NULL, NULL),
(371, '432292OLAWALE', '535', 'OLAWALE OLAOGUN', 'ijdfjfdkffkldldld', NULL, '0', '1625526998137', '2021-07-05 23:16:38', NULL, NULL),
(372, '432292OLAWALE', '535', 'OLAWALE OLAOGUN', 'intetenyion', NULL, '0', '1625527131456', '2021-07-05 23:18:51', NULL, NULL),
(373, '432292OLAWALE', '535', 'OLAWALE OLAOGUN', 'interesting game', NULL, '0', '1625527420819', '2021-07-05 23:23:40', NULL, NULL),
(374, '432292OLAWALE', '535', 'OLAWALE OLAOGUN', 'are you going to do it', NULL, '0', '1625527956695', '2021-07-05 23:32:36', NULL, NULL),
(375, '432292OLAWALE', '535', 'OLAWALE OLAOGUN', 'are you sure about that ', NULL, '0', '1625528176991', '2021-07-05 23:36:16', NULL, NULL),
(376, '432292OLAWALE', '536', 'OLAWALE OLAOGUN', 'Are you sure about it ', NULL, '0', '1625567498037', '2021-07-06 10:31:38', NULL, NULL),
(377, '432292OLAWALE', '538', 'OLAWALE OLAOGUN', 'Enjoyment', NULL, '0', '1625596969030', '2021-07-06 18:42:49', NULL, NULL),
(378, '432292OLAWALE', '538', 'OLAWALE OLAOGUN', 'Enjoyment and sanitation', NULL, '0', '1625596979089', '2021-07-06 18:42:59', NULL, NULL),
(379, '432292OLAWALE', '538', 'OLAWALE OLAOGUN', 'Enjoyment and sanitation and Enjoyment', NULL, '0', '1625596997124', '2021-07-06 18:43:17', NULL, NULL),
(380, '432292OLAWALE', '541', 'OLAWALE OLAOGUN', 'so sure', NULL, '0', '1625651648705', '2021-07-07 09:54:08', NULL, NULL),
(381, '432292OLAWALE', '551', 'OLAWALE OLAOGUN', 'have you any friends ', NULL, '0', '1625666114590', '2021-07-07 13:55:14', NULL, NULL),
(382, '432292OLAWALE', '553', 'OLAWALE OLAOGUN', 'YOU FAILED TO THE IMPORTANT THINGS', NULL, '0', '1625666642187', '2021-07-07 14:04:02', NULL, NULL),
(383, '432292OLAWALE', '553', 'OLAWALE OLAOGUN', 'VISUALISE POWERS AND ACHIEVEMENT', NULL, '0', '1625666679173', '2021-07-07 14:04:39', NULL, NULL),
(384, '432292OLAWALE', '577', 'OLAWALE OLAOGUN', 'we are excited', NULL, '0', '1625704369440', '2021-07-08 00:32:49', NULL, NULL),
(385, '432292OLAWALE', '577', 'OLAWALE OLAOGUN', 'Yes  I agree', NULL, '0', '1625705169179', '2021-07-08 00:46:09', NULL, NULL),
(386, '432292OLAWALE', '578', 'OLAWALE OLAOGUN', 'increasing', NULL, '0', '1625705292622', '2021-07-08 00:48:12', NULL, NULL),
(387, '432292OLAWALE', '578', 'OLAWALE OLAOGUN', 'are you sure', NULL, '0', '1625705333740', '2021-07-08 00:48:53', NULL, NULL),
(388, '432292OLAWALE', '578', 'OLAWALE OLAOGUN', 'really', NULL, '0', '1625705404359', '2021-07-08 00:50:04', NULL, NULL),
(389, '432292OLAWALE', '578', 'OLAWALE OLAOGUN', 'really', NULL, '0', '1625705412253', '2021-07-08 00:50:12', NULL, NULL),
(390, '432292OLAWALE', '578', 'OLAWALE OLAOGUN', 'dont mention', NULL, '0', '1625705508274', '2021-07-08 00:51:48', NULL, NULL),
(391, '432292OLAWALE', '578', 'OLAWALE OLAOGUN', 'beautiful in his time', NULL, '0', '1625705590795', '2021-07-08 00:53:10', NULL, NULL),
(392, '432292OLAWALE', '578', 'OLAWALE OLAOGUN', 'detailed energy', NULL, '0', '1625705645481', '2021-07-08 00:54:05', NULL, NULL),
(393, '432292OLAWALE', '578', 'OLAWALE OLAOGUN', 'jhj', NULL, '0', '1625705674648', '2021-07-08 00:54:34', NULL, NULL),
(394, '432292OLAWALE', '578', 'OLAWALE OLAOGUN', 'jkjkk', NULL, '0', '1625705728863', '2021-07-08 00:55:28', NULL, NULL),
(395, '432292OLAWALE', '578', 'OLAWALE OLAOGUN', 'hh', NULL, '0', '1625706001763', '2021-07-08 01:00:01', NULL, NULL),
(396, '432292OLAWALE', '578', 'OLAWALE OLAOGUN', 'bbb', NULL, '0', '1625706040965', '2021-07-08 01:00:40', NULL, NULL);
INSERT INTO `comment` (`comment_no`, `id`, `post_no`, `fullName`, `comment`, `profileImg`, `picture`, `post_time`, `date_created`, `date_updated`, `date_deleted`) VALUES
(397, '432292OLAWALE', '583', 'OLAWALE OLAOGUN', 'jjjjh', NULL, '0', '1625706583185', '2021-07-08 01:09:43', NULL, NULL),
(398, '432292OLAWALE', '582', 'OLAWALE OLAOGUN', 'jhjjhjh', NULL, '0', '1625706596711', '2021-07-08 01:09:56', NULL, NULL),
(399, '432292OLAWALE', '585', 'OLAWALE OLAOGUN', 'aojfjdijfdnkdjd', NULL, '0', '1625707042211', '2021-07-08 01:17:22', NULL, NULL),
(400, '432292OLAWALE', '587', 'OLAWALE OLAOGUN', 'sds', NULL, '0', '1625710742979', '2021-07-08 02:19:02', NULL, NULL),
(401, '117540OLAWALE', '636', 'SEGUN OLAOGUN', 'baba', NULL, '0', '1628200551448', '2021-08-05 21:55:51', NULL, NULL),
(402, '117540OLAWALE', '634', 'SEGUN OLAOGUN', '   ', NULL, '0', '1628204698680', '2021-08-05 23:04:58', NULL, NULL),
(403, '117540OLAWALE', '633', 'SEGUN OLAOGUN', 'welldone Jummy', NULL, '0', '1628204706850', '2021-08-05 23:05:06', NULL, NULL),
(404, '117540OLAWALE', '633', 'SEGUN OLAOGUN', 'welldone Jummy2', NULL, '0', '1628204715075', '2021-08-05 23:05:15', NULL, NULL),
(405, '117540OLAWALE', '638', 'SEGUN OLAOGUN', 'Account is active - The latest statement is based on the plan end date of 1st Jan 2021. ', NULL, '0', '1629849628759', '2021-08-25 00:00:28', NULL, NULL),
(406, '117540OLAWALE', '637', 'SEGUN OLAOGUN', 'IP changed', NULL, '0', '1629849635378', '2021-08-25 00:00:35', NULL, NULL),
(407, '117540OLAWALE', '628', 'SEGUN OLAOGUN', 'Account is active - The latest statement is based on the plan end date of 1st Jan 2021. ', NULL, '0', '1629849640435', '2021-08-25 00:00:40', NULL, NULL),
(408, '929019DAYO', '1000', 'Dayo', 'Your comment will show here', '/avatar/avatarF.png', '0', NULL, '2021-09-21 22:39:56', NULL, NULL),
(409, '870016OLAWALE', '638', 'ADESOJI OLAOGUN', 'bbnb', NULL, '0', '1632268970770', '2021-09-22 00:02:50', NULL, NULL),
(410, '870016OLAWALE', '637', 'ADESOJI OLAOGUN', 'polop', NULL, '0', '1632268980546', '2021-09-22 00:03:00', NULL, NULL),
(411, '117540OLAWALE', '638', 'SEGUN OLAOGUN', 'Good  that you have your birthday ready', NULL, '0', '1632348321038', '2021-09-22 22:05:21', NULL, NULL),
(412, '870016OLAWALE', '638', 'ADESOJI OLAOGUN', 'sanitation will work well', NULL, '0', '1635461701902', '2021-10-28 22:55:01', NULL, NULL),
(413, '870016OLAWALE', '638', 'ADESOJI OLAOGUN', 'No story with it', NULL, '0', '1635461723490', '2021-10-28 22:55:23', NULL, NULL),
(414, '870016OLAWALE', '637', 'ADESOJI OLAOGUN', 'the money is here to go', NULL, '0', '1635461736149', '2021-10-28 22:55:36', NULL, NULL),
(415, '870016OLAWALE', '637', 'ADESOJI OLAOGUN', 'I may need to talk to you one on one', NULL, '0', '1635461756570', '2021-10-28 22:55:56', NULL, NULL),
(416, '870016OLAWALE', '640', 'ADESOJI OLAOGUN', 'are you sleeping ', NULL, '0', '1635461860527', '2021-10-28 22:57:40', NULL, NULL),
(417, '870016OLAWALE', '640', 'ADESOJI OLAOGUN', 'Yes  James. it is plagarism', NULL, '0', '1635461875532', '2021-10-28 22:57:55', NULL, NULL),
(418, '870016OLAWALE', '640', 'ADESOJI OLAOGUN', 'JUMOKE', NULL, '0', '1635472794724', '2021-10-29 01:59:54', NULL, NULL),
(419, '117540OLAWALE', '642', 'SEGUN OLAOGUN', 'amapiano', NULL, NULL, '1639849378717', '2021-12-18 17:42:58', NULL, NULL),
(420, '117540OLAWALE', '642', 'SEGUN OLAOGUN', 'enjoy it', NULL, NULL, '1639849702526', '2021-12-18 17:48:22', NULL, NULL),
(421, '117540OLAWALE', '642', 'SEGUN OLAOGUN', 'enjoy the Amapiano song', NULL, NULL, '1639849771479', '2021-12-18 17:49:31', NULL, NULL),
(422, '117540OLAWALE', '642', 'SEGUN OLAOGUN', 'enjoy the Amapiano song part 2', NULL, NULL, '1639850390214', '2021-12-18 17:59:50', NULL, NULL),
(423, '397755OLUSOLA', '642', 'FEMI OLAOGUN', 'I still don t know you', NULL, NULL, '1639850474961', '2021-12-18 18:01:14', NULL, NULL),
(424, '117540OLAWALE', '642', 'SEGUN OLAOGUN', 'I know what you are saying', NULL, NULL, '1639850684171', '2021-12-18 18:04:44', NULL, NULL),
(425, '117540OLAWALE', '642', 'SEGUN OLAOGUN', 'Ignore the bastard', NULL, NULL, '1639851281802', '2021-12-18 18:14:41', NULL, NULL),
(426, '117540OLAWALE', '643', 'SEGUN OLAOGUN', 'ENJOT', NULL, NULL, '1641661296100', '2022-01-08 17:01:36', NULL, NULL),
(427, '117540OLAWALE', '643', 'SEGUN OLAOGUN', 'ENJOT2', NULL, NULL, '1641661301946', '2022-01-08 17:01:41', NULL, NULL),
(428, '117540OLAWALE', '644', 'SEGUN OLAOGUN', 'JUMOKE', NULL, NULL, '1641930187829', '2022-01-11 19:43:07', NULL, NULL),
(429, '117540OLAWALE', '644', 'SEGUN OLAOGUN', 'JUMOKE', NULL, NULL, '1641930192240', '2022-01-11 19:43:12', NULL, NULL),
(430, '117540OLAWALE', '644', 'SEGUN OLAOGUN', 'JUMOKE222', NULL, NULL, '1641930203872', '2022-01-11 19:43:23', NULL, NULL),
(431, '117540OLAWALE', '644', 'SEGUN OLAOGUN', 'it worked', NULL, NULL, '1642114732278', '2022-01-13 22:58:52', NULL, NULL),
(432, '117540OLAWALE', '644', 'SEGUN OLAOGUN', 'Let\'s do it', NULL, NULL, '1642249294484', '2022-01-15 12:21:34', NULL, NULL),
(433, '117540OLAWALE', '644', 'SEGUN OLAOGUN', 'I think  it worked', NULL, NULL, '1642549260489', '2022-01-18 23:41:00', NULL, NULL),
(434, '117540OLAWALE', '644', 'SEGUN OLAOGUN', 'I think  it worked2', NULL, NULL, '1642549291306', '2022-01-18 23:41:31', NULL, NULL),
(435, '117540OLAWALE', '644', 'SEGUN OLAOGUN', 'is it really', NULL, NULL, '1642550895740', '2022-01-19 00:08:15', NULL, NULL),
(436, '117540OLAWALE', '644', 'SEGUN OLAOGUN', 'sanitation', NULL, NULL, '1642551007806', '2022-01-19 00:10:07', NULL, NULL),
(437, '397755OLUSOLA', '644', 'FEMI OLAOGUN', 'Are u thinking', NULL, NULL, '1642551116874', '2022-01-19 00:11:56', NULL, NULL),
(438, '117540OLAWALE', '644', 'SEGUN OLAOGUN', 'IF YOU COME OUT', NULL, NULL, '1642633530158', '2022-01-19 23:05:30', NULL, NULL),
(439, '117540OLAWALE', '644', 'SEGUN OLAOGUN', '', NULL, NULL, '1642636944018', '2022-01-20 00:02:24', NULL, NULL),
(440, '117540OLAWALE', '645', 'SEGUN OLAOGUN', 'Ore is a wondeful boy', NULL, NULL, '1642637225181', '2022-01-20 00:07:05', NULL, NULL),
(441, '397755OLUSOLA', '647', 'FEMI OLAOGUN', 'Are you very sure ', NULL, NULL, '1643912006357', '2022-02-03 18:13:26', NULL, NULL),
(442, '397755OLUSOLA', '647', 'FEMI OLAOGUN', '', NULL, NULL, '1643912137733', '2022-02-03 18:15:37', NULL, NULL),
(443, '117540OLAWALE', '647', 'SEGUN OLAOGUN', 'JUMOKE', NULL, NULL, '1643968395809', '2022-02-04 09:53:15', NULL, NULL),
(444, '117540OLAWALE', '647', 'SEGUN OLAOGUN', '', NULL, NULL, '1643968417004', '2022-02-04 09:53:37', NULL, NULL),
(445, '117540OLAWALE', '647', 'SEGUN OLAOGUN', 'checking', NULL, NULL, '1643968439252', '2022-02-04 09:53:59', NULL, NULL),
(446, '117540OLAWALE', '647', 'SEGUN OLAOGUN', 'JUMOKE', NULL, NULL, '1643968529304', '2022-02-04 09:55:29', NULL, NULL),
(447, '117540OLAWALE', '647', 'SEGUN OLAOGUN', 'DF', NULL, NULL, '1643968592685', '2022-02-04 09:56:32', NULL, NULL),
(448, '117540OLAWALE', '647', 'SEGUN OLAOGUN', '', NULL, NULL, '1643974022311', '2022-02-04 11:27:02', NULL, NULL),
(449, '117540OLAWALE', '647', 'SEGUN OLAOGUN', '', NULL, NULL, '1643974083643', '2022-02-04 11:28:03', NULL, NULL),
(450, '117540OLAWALE', '703', 'SEGUN OLAOGUN', 'JUMOKE', NULL, NULL, '1644165942452', '2022-02-06 16:45:42', NULL, NULL),
(451, '117540OLAWALE', '703', 'SEGUN OLAOGUN', '', NULL, NULL, '1644165948021', '2022-02-06 16:45:48', NULL, NULL),
(452, '117540OLAWALE', '704', 'SEGUN OLAOGUN', 'JUMOKE', NULL, NULL, '1644168698066', '2022-02-06 17:31:38', NULL, NULL),
(453, '117540OLAWALE', '704', 'SEGUN OLAOGUN', 'JUMOKE', NULL, NULL, '1644168710560', '2022-02-06 17:31:50', NULL, NULL),
(454, '117540OLAWALE', '704', 'SEGUN OLAOGUN', '', NULL, NULL, '1644168732399', '2022-02-06 17:32:12', NULL, NULL),
(455, '117540OLAWALE', '704', 'SEGUN OLAOGUN', 'JUMOKE', NULL, NULL, '1644168801693', '2022-02-06 17:33:21', NULL, NULL),
(456, '117540OLAWALE', '704', 'SEGUN OLAOGUN', '', NULL, NULL, '1644168824448', '2022-02-06 17:33:44', NULL, NULL),
(457, '117540OLAWALE', '704', 'SEGUN OLAOGUN', 'como', NULL, NULL, '1644168848561', '2022-02-06 17:34:08', NULL, NULL),
(458, '117540OLAWALE', '706', 'SEGUN OLAOGUN', 'awesome', NULL, NULL, '1652170369404', '2022-05-10 08:12:49', NULL, NULL),
(459, '117540OLAWALE', '705', 'SEGUN OLAOGUN', 'we love it', NULL, NULL, '1655642160059', '2022-06-19 12:36:00', NULL, NULL),
(460, '975782ADETUNJI', '1000', 'adetunji', 'Your comment will show here', '/avatar/avatarF.png', NULL, NULL, '2022-07-21 00:12:34', NULL, NULL),
(461, '936779SHABA', '1000', 'Shaba', 'Your comment will show here', '/avatar/avatarF.png', NULL, NULL, '2022-07-21 00:20:44', NULL, NULL),
(462, '954243OLAOLUWA', '1000', 'Olaoluwa', 'Your comment will show here', '/avatar/avatarF.png', NULL, NULL, '2022-07-21 08:06:51', NULL, NULL),
(463, '117540OLAWALE', '709', 'SEGUN OLAOGUN', 'bjjjjjk', NULL, NULL, '1658452637388', '2022-07-22 01:17:17', NULL, NULL),
(464, '117540OLAWALE', '704', 'SEGUN OLAOGUN', 'oladele is coming well', NULL, NULL, '1662655273986', '2022-09-08 16:41:14', NULL, NULL),
(465, '912189MOMODU', '1000', 'MOMODU', 'Your comment will show here', '/avatar/avatarF.png', NULL, NULL, '2023-02-13 17:14:09', NULL, NULL),
(466, '987764OLAWALE', '1000', 'OLAWALE', 'Your comment will show here', '/avatar/avatarF.png', NULL, NULL, '2023-02-13 17:36:11', NULL, NULL),
(467, '913882OLAWALE', '1000', 'Olawale', 'Your comment will show here', '/avatar/avatarF.png', NULL, NULL, '2023-02-13 17:44:09', NULL, NULL),
(468, '940728BUSSY', '1000', 'Bussy', 'Your comment will show here', '/avatar/avatarF.png', NULL, NULL, '2023-02-14 13:21:11', NULL, NULL),
(469, '942737GBEDUO', '1000', 'Gbeduo', 'Your comment will show here', '/avatar/avatarF.png', NULL, NULL, '2023-02-14 19:36:51', NULL, NULL),
(470, '971778GENERA', '1000', 'Genera', 'Your comment will show here', '/avatar/avatarF.png', NULL, NULL, '2023-02-15 00:41:26', NULL, NULL),
(471, '937619LAFANE', '1000', 'Lafane', 'Your comment will show here', '/avatar/avatarF.png', NULL, NULL, '2023-02-15 00:47:04', NULL, NULL),
(472, '117540OLAWALE', '714', 'SEGUN OLAOGUN', 'focus', NULL, NULL, '1677107351299', '2023-02-22 23:09:11', NULL, NULL),
(473, '937619LAFANE', '722', 'Lafane OLAOGUN', 'I\'m sure about that', NULL, NULL, '1677595958868', '2023-02-28 14:52:38', NULL, NULL),
(474, '117540OLAWALE', '709', 'SEGUN OLAOGUN', 'Just seeing this', NULL, NULL, '1680310021478', '2023-04-01 00:47:01', NULL, NULL),
(475, '117540OLAWALE', '714', 'SEGUN OLAOGUN', 'Are you sure', NULL, NULL, '1680310036660', '2023-04-01 00:47:16', NULL, NULL),
(476, '937619LAFANE', '709', 'Lafane OLAOGUN', 'evidence has shown', NULL, NULL, '1680704607470', '2023-04-05 14:23:27', NULL, NULL),
(477, '937619LAFANE', '709', 'Lafane OLAOGUN', 'really', NULL, NULL, '1680704621100', '2023-04-05 14:23:41', NULL, NULL),
(478, '964649OLAWALE', '1000', 'Olawale', 'Your comment will show here', 'avatarF.png', NULL, NULL, '2023-07-07 21:43:20', NULL, NULL),
(479, '915784TESTING', '1000', 'Testing', 'Your comment will show here', 'avatarF.png', NULL, NULL, '2023-07-11 22:25:01', NULL, NULL),
(480, '974628TESTING', '1000', 'Testing', 'Your comment will show here', 'avatarF.png', NULL, NULL, '2023-07-12 17:59:47', NULL, NULL),
(481, '941501TOBI', '1000', 'tobi', 'Your comment will show here', 'avatarF.png', NULL, NULL, '2023-07-30 20:01:19', NULL, NULL),
(482, '995544TESTING', '1000', 'Testing', 'Your comment will show here', 'avatarF.png', NULL, NULL, '2023-08-01 01:50:38', NULL, NULL),
(483, '962673TOBI', '1000', 'tobi', 'Your comment will show here', 'avatarF.png', NULL, NULL, '2023-08-01 13:29:58', NULL, NULL),
(484, '957154TESTING', '1000', 'Testing', 'Your comment will show here', 'avatarF.png', NULL, NULL, '2023-08-01 21:29:11', NULL, NULL),
(485, '918455TESTING', '1000', 'Testing', 'Your comment will show here', 'avatarF.png', NULL, NULL, '2023-08-01 21:35:30', NULL, NULL),
(486, '994428TESTING', '1000', 'Testing', 'Your comment will show here', 'avatarF.png', NULL, NULL, '2023-08-01 22:06:14', NULL, NULL),
(487, '999881TESTING', '1000', 'Testing', 'Your comment will show here', 'avatarF.png', NULL, NULL, '2023-08-01 22:11:49', NULL, NULL),
(488, '985172TESTING', '1000', 'Testing', 'Your comment will show here', 'avatarF.png', NULL, NULL, '2023-08-01 22:13:27', NULL, NULL),
(489, '948473TESTING', '1000', 'Testing', 'Your comment will show here', 'avatarF.png', NULL, NULL, '2023-08-01 22:14:17', NULL, NULL),
(490, '117540OLAWALE', NULL, 'SEGUN OLAOGUN', '  oppp', NULL, NULL, '1691625766805', '2023-08-10 00:02:46', NULL, NULL),
(491, '117540OLAWALE', NULL, 'SEGUN OLAOGUN', '  oppp', NULL, NULL, '1691625776390', '2023-08-10 00:02:56', NULL, NULL),
(492, '117540OLAWALE', NULL, 'SEGUN OLAOGUN', '  ghjhj', NULL, NULL, '1691625787014', '2023-08-10 00:03:07', NULL, NULL),
(493, '117540OLAWALE', '738', 'SEGUN OLAOGUN', 'hrrhr', NULL, NULL, '1692314325289', '2023-08-17 23:18:45', NULL, NULL),
(494, '117540OLAWALE', '738', 'SEGUN OLAOGUN', 'hrrhr', NULL, NULL, '1692314332274', '2023-08-17 23:18:52', NULL, NULL),
(495, '117540OLAWALE', '737', 'SEGUN OLAOGUN', 'it is working  right', NULL, NULL, '1692314344156', '2023-08-17 23:19:04', NULL, NULL),
(496, '921152LEBANON', '1000', 'LEBANON', 'Your comment will show here', 'avatarF.png', NULL, NULL, '2023-08-23 23:57:31', NULL, NULL),
(497, '967957TESTING', '1000', 'Testing', 'Your comment will show here', 'avatarF.png', NULL, NULL, '2023-08-25 09:29:52', NULL, NULL),
(498, '992501TESTING', '1000', 'TESTING', 'Your comment will show here', 'avatarF.png', NULL, NULL, '2023-08-26 17:00:20', NULL, NULL),
(499, '918627OLAWALE', '1000', 'Olawale', 'Your comment will show here', 'avatarF.png', NULL, NULL, '2023-08-26 18:06:55', NULL, NULL),
(500, '950455OLAWALE', '1000', 'Olawale', 'Your comment will show here', 'avatarF.png', NULL, NULL, '2023-08-26 18:08:47', NULL, NULL),
(501, '913895OLAWALE', '1000', 'Olawale', 'Your comment will show here', 'avatarF.png', NULL, NULL, '2023-08-26 18:10:09', NULL, NULL),
(502, '974368OLAWALE', '1000', 'Olawale', 'Your comment will show here', 'avatarF.png', NULL, NULL, '2023-08-27 10:48:02', NULL, NULL),
(503, '935796TESTING', '1000', 'Testing', 'Your comment will show here', 'avatarF.png', NULL, NULL, '2023-08-27 10:51:50', NULL, NULL),
(504, '948454TESTING', '1000', 'Testing', 'Your comment will show here', 'avatarF.png', NULL, NULL, '2023-08-27 10:57:20', NULL, NULL),
(505, '926764TESTING', '1000', 'Testing', 'Your comment will show here', 'avatarF.png', NULL, NULL, '2023-08-27 11:36:09', NULL, NULL),
(506, '973719TESTING', '1000', 'Testing', 'Your comment will show here', 'avatarF.png', NULL, NULL, '2023-08-27 11:37:00', NULL, NULL),
(507, '981243TESTING', '1000', 'TESTING', 'Your comment will show here', 'avatarF.png', NULL, NULL, '2023-08-27 11:38:47', NULL, NULL),
(508, '930410TESTING', '1000', 'TESTING', 'Your comment will show here', 'avatarF.png', NULL, NULL, '2023-08-27 11:45:24', NULL, NULL),
(509, '991388TESTING', '1000', 'TESTING', 'Your comment will show here', 'avatarF.png', NULL, NULL, '2023-08-27 11:47:38', NULL, NULL),
(510, '949886TESTING', '1000', 'TESTING', 'Your comment will show here', 'avatarF.png', NULL, NULL, '2023-08-27 11:52:56', NULL, NULL),
(511, '972748TESTING', '1000', 'TESTING', 'Your comment will show here', 'avatarF.png', NULL, NULL, '2023-08-27 12:01:19', NULL, NULL),
(512, '117540OLAWALE', '754', 'SEGUN OLAOGUN', 'we loved it', NULL, NULL, '1693232427275', '2023-08-28 14:20:27', NULL, NULL),
(513, '117540OLAWALE', '754', 'SEGUN OLAOGUN', 'are you sure?', NULL, NULL, '1693232551426', '2023-08-28 14:22:31', NULL, NULL),
(514, '117540OLAWALE', '754', 'SEGUN OLAOGUN', 'we love it very well', NULL, NULL, '1693235057174', '2023-08-28 15:04:17', NULL, NULL),
(515, '117540OLAWALE', '754', 'SEGUN OLAOGUN', 'we love it very well', NULL, NULL, '1693235198537', '2023-08-28 15:06:38', NULL, NULL),
(516, '117540OLAWALE', '754', 'SEGUN OLAOGUN', 'weddings', NULL, NULL, '1693235292833', '2023-08-28 15:08:12', NULL, NULL),
(517, '117540OLAWALE', '754', 'SEGUN OLAOGUN', 'weddings for sure', NULL, NULL, '1693236022804', '2023-08-28 15:20:22', NULL, NULL),
(518, '117540OLAWALE', '754', 'SEGUN OLAOGUN', 'weddings for sure - I dont think so', NULL, NULL, '1693236064066', '2023-08-28 15:21:04', NULL, NULL),
(519, '117540OLAWALE', '754', 'SEGUN OLAOGUN', 'ereree', NULL, NULL, '1693344818368', '2023-08-29 21:33:38', NULL, NULL),
(520, '937619LAFANE', '754', 'Lafane OLAOGUN', 'plesbisite', NULL, NULL, '1694555991919', '2023-09-12 21:59:51', NULL, NULL),
(521, '937619LAFANE', NULL, 'Lafane OLAOGUN', 'hjjhhj', NULL, NULL, '1695993377265', '2023-09-29 13:16:17', NULL, NULL),
(522, '937619LAFANE', '754', 'Lafane OLAOGUN', 'what do you mean?', NULL, NULL, '1696252745799', '2023-10-02 13:19:05', NULL, NULL),
(523, '117540OLAWALE', '754', 'SEGUN OLAOGUN', 'Does it work?', NULL, NULL, '1697239777566', '2023-10-13 23:29:37', NULL, NULL),
(524, '117540OLAWALE', '758', 'SEGUN OLAOGUN', 'hey, whats up', NULL, NULL, '1697240685406', '2023-10-13 23:44:45', NULL, NULL),
(525, '117540OLAWALE', '758', 'SEGUN OLAOGUN', 'why did you ask?', NULL, NULL, '1697240708543', '2023-10-13 23:45:08', NULL, NULL),
(526, '117540OLAWALE', '758', 'SEGUN OLAOGUN', 'Hamas attacked Isreal', NULL, NULL, '1697284571579', '2023-10-14 11:56:11', NULL, NULL),
(527, '117540OLAWALE', '758', 'SEGUN OLAOGUN', 'Hamas attacked Isreal', NULL, NULL, '1697284588094', '2023-10-14 11:56:28', NULL, NULL),
(528, '117540OLAWALE', '758', 'SEGUN OLAOGUN', 'when and how did it happen?', NULL, NULL, '1697284650182', '2023-10-14 11:57:30', NULL, NULL),
(529, '117540OLAWALE', '758', 'SEGUN OLAOGUN', 'and Isreal is acting like an bruised spoilt giant', NULL, NULL, '1697284689372', '2023-10-14 11:58:09', NULL, NULL),
(530, '117540OLAWALE', '759', 'SEGUN OLAOGUN', 'does it work', NULL, NULL, '1697284960597', '2023-10-14 12:02:40', NULL, NULL),
(531, '117540OLAWALE', '759', 'SEGUN OLAOGUN', 'bjjjjjk', NULL, NULL, '1697285290781', '2023-10-14 12:08:10', NULL, NULL),
(532, '117540OLAWALE', '759', 'SEGUN OLAOGUN', 'soilder injured', NULL, NULL, '1697285400001', '2023-10-14 12:10:00', NULL, NULL),
(533, '117540OLAWALE', '759', 'SEGUN OLAOGUN', 'evacuate people', NULL, NULL, '1697285736453', '2023-10-14 12:15:36', NULL, NULL),
(534, '117540OLAWALE', '759', 'SEGUN OLAOGUN', 'name', NULL, NULL, '1697286219739', '2023-10-14 12:23:39', NULL, NULL),
(535, '117540OLAWALE', '759', 'SEGUN OLAOGUN', 'focus', NULL, NULL, '1697286283471', '2023-10-14 12:24:43', NULL, NULL),
(536, '117540OLAWALE', '759', 'SEGUN OLAOGUN', 'I&#039;m sure about that', NULL, NULL, '1697289780725', '2023-10-14 13:23:00', NULL, NULL),
(537, '117540OLAWALE', '759', 'SEGUN OLAOGUN', 'oladele is coming well', NULL, NULL, '1697289847762', '2023-10-14 13:24:07', NULL, NULL),
(538, '117540OLAWALE', '759', 'SEGUN OLAOGUN', 'focus', NULL, NULL, '1697289953616', '2023-10-14 13:25:53', NULL, NULL),
(539, '117540OLAWALE', '759', 'SEGUN OLAOGUN', 'Just seeing this', NULL, NULL, '1697290037820', '2023-10-14 13:27:17', NULL, NULL),
(540, '117540OLAWALE', '759', 'SEGUN OLAOGUN', 'Just seeing this', NULL, NULL, '1697290051171', '2023-10-14 13:27:31', NULL, NULL),
(541, '117540OLAWALE', '759', 'SEGUN OLAOGUN', 'campaign for aid budget to be stopped', NULL, NULL, '1697291735349', '2023-10-14 13:55:35', NULL, NULL),
(542, '117540OLAWALE', '759', 'SEGUN OLAOGUN', 'hello', NULL, NULL, '1697313398241', '2023-10-14 19:56:38', NULL, NULL),
(543, '937619LAFANE', '760', 'Lafane OLAOGUN', 'Da sho da', NULL, NULL, '1697656602197', '2023-10-18 19:16:42', NULL, NULL),
(544, '117540OLAWALE', '760', 'SEGUN OLAOGUN', 'What does that mean?', NULL, NULL, '1697716680714', '2023-10-19 11:58:00', NULL, NULL),
(545, '937619LAFANE', '761', 'Lafane OLAOGUN', 'SSDSD', NULL, NULL, '1698328803058', '2023-10-26 14:00:03', NULL, NULL),
(546, '937619LAFANE', '762', 'Lafane OLAOGUN', 'that is a very good point Honestly', NULL, NULL, '1698929927430', '2023-11-02 12:58:47', NULL, NULL),
(547, '870016OLAWALE', '772', 'ADESOJI OLAOGUN', 'headed to the briddge', NULL, NULL, '1712168493407', '2024-04-03 18:21:33', NULL, NULL),
(548, '870016OLAWALE', '771', 'ADESOJI OLAOGUN', 'we are safe', NULL, NULL, '1712168513045', '2024-04-03 18:21:53', NULL, NULL),
(549, '870016OLAWALE', '774', 'ADESOJI OLAOGUN', 'I&#039;m sure about that', NULL, NULL, '1712190964488', '2024-04-04 00:36:04', NULL, NULL),
(550, '870016OLAWALE', '774', 'ADESOJI OLAOGUN', 'I&#039;m sure about that2', NULL, NULL, '1712190970681', '2024-04-04 00:36:10', NULL, NULL),
(551, '870016OLAWALE', '807', 'ADESOJI OLAOGUN', 'testing', NULL, NULL, '1712281493565', '2024-04-05 01:44:53', NULL, NULL),
(552, '432292OLAWALE', '809', 'OLAWALE OLAOGUN', 'Olutobs loves it so much', NULL, NULL, '1712308378111', '2024-04-05 09:12:58', NULL, NULL),
(553, '432292OLAWALE', '809', 'OLAWALE OLAOGUN', 'think about it for now', NULL, NULL, '1712310089094', '2024-04-05 09:41:29', NULL, NULL),
(554, '432292OLAWALE', '814', 'OLAWALE OLAOGUN', 'I&#039;m sure about that', NULL, NULL, '1712999897828', '2024-04-13 09:18:17', NULL, NULL),
(555, '397755OLUSOLA', '815', 'FEMI OLAOGUN', 'it is not posting twice for now', NULL, NULL, '1713431183992', '2024-04-18 09:06:23', NULL, NULL),
(556, '117540OLAWALE', '820', 'SEGUN OLAOGUN', 'INTERESTING', NULL, NULL, '1718362440157', '2024-06-14 10:54:00', NULL, NULL),
(557, '432292OLAWALE', '821', 'OLAWALE OLAOGUN', 'Dont be nervous', NULL, NULL, '1719944469633', '2024-07-02 18:21:09', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `contact`
--

CREATE TABLE `contact` (
  `no` int NOT NULL,
  `id` varchar(255) NOT NULL,
  `email` varchar(50) NOT NULL,
  `country` varchar(40) NOT NULL,
  `mobile` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `contact`
--

INSERT INTO `contact` (`no`, `id`, `email`, `country`, `mobile`, `created_at`, `updated_at`, `deleted_at`) VALUES
(3, '870016OLAWALE', 'topsy@gmail.com', 'UK', '447809650816', '2021-02-15 23:42:31', '2023-04-18 09:56:19', '2023-04-18 09:56:19'),
(4, '397755OLUSOLA', 'waleolaogunrac@gmail.com', 'UK', '078096508165', '2021-02-15 23:49:10', '2022-07-21 15:32:18', '2022-07-21 15:32:18'),
(6, '929019DAYO', 'segunswindon@gmail.com', 'UK', '447805262504', '2021-09-21 22:39:56', NULL, NULL),
(7, '975782ADETUNJI', 'retailwally@gmail.com', 'UK', '447809650817', '2022-07-21 00:12:34', NULL, NULL),
(8, '936779SHABA', 'bikeoguns@gmail.com', 'UK', '447805262504', '2022-07-21 00:20:44', NULL, NULL),
(9, '954243OLAOLUWA', 'eniolaoguns@gmail.com', 'UK', '447743465148', '2022-07-21 08:06:51', NULL, NULL),
(10, '912189MOMODU', 'retailwally@gmail.com', 'UK', '07809650816', '2023-02-13 17:14:09', NULL, NULL),
(11, '987764OLAWALE', 'retailwally@gmail.com', 'UK', '07809650816', '2023-02-13 17:36:11', NULL, NULL),
(12, '913882OLAWALE', 'retailwally@gmail.com', 'UK', '07809650816', '2023-02-13 17:44:09', NULL, NULL),
(14, '940728BUSSY', 'retailwally@gmail.com', 'UK', '447809650816', '2023-02-14 13:21:11', NULL, NULL),
(15, '942737GBEDUO', 'retailwally@gmail.com', 'UK', '07809650816', '2023-02-14 19:36:51', NULL, NULL),
(16, '971778GENERA', 'retailwally@gmail.com', 'UK', '07809650816', '2023-02-15 00:41:26', NULL, NULL),
(17, '937619LAFANE', 'retailwally@gmail.com', 'UK', '07809651816', '2023-02-15 00:47:04', '2023-10-18 15:10:13', '2023-10-18 15:10:13'),
(18, '964649OLAWALE', 'investwally@gmail.com', 'UK', '447809650816', '2023-07-07 21:43:20', NULL, NULL),
(19, '915784TESTING', 'investwally@gmail.com', 'Nigeria', '2347036416070', '2023-07-11 22:25:01', NULL, NULL),
(20, '974628TESTING', 'investwally@gmail.com', 'UK', '447809650816', '2023-07-12 17:59:46', NULL, NULL),
(21, '941501TOBI', 'oolutobs@gmail.comx', 'UK', '447845161628', '2023-07-30 20:01:19', '2023-08-01 13:27:43', '2023-08-01 13:27:43'),
(22, '995544TESTING', 'loaneasyfinance16@gmail.com', 'UK', '447809650816', '2023-08-01 01:50:38', NULL, NULL),
(23, '962673TOBI', 'oolutobs@gmail.com', 'United Kingdom', '447845161628', '2023-08-01 13:29:58', NULL, NULL),
(24, '957154TESTING', 'Testing@olaogun.com', 'United Kingdom', '447809650816', '2023-08-01 21:29:11', NULL, NULL),
(25, '918455TESTING', 'Testing@olaog.com', 'United Kingdom', '447809650716', '2023-08-01 21:35:30', NULL, NULL),
(26, '994428TESTING', 'Testing23@gmail.com', 'United Kingdom', '447809650816', '2023-08-01 22:06:14', NULL, NULL),
(27, '999881TESTING', 'Testing2963@gmail.com', 'United Kingdom', '447809650816', '2023-08-01 22:11:49', NULL, NULL),
(28, '985172TESTING', 'Testing2f963@gmail.com', 'United Kingdom', '447809650816', '2023-08-01 22:13:27', NULL, NULL),
(29, '948473TESTING', 'Testing2vf963@gmail.com', 'United Kingdom', '447809650816', '2023-08-01 22:14:17', NULL, NULL),
(32, '992501TESTING', 'bikeogunsxx@gmail.com', 'United Kingdom', '447805262504', '2023-08-26 17:00:20', '2023-08-26 17:10:59', '2023-08-26 17:10:59'),
(33, '918627OLAWALE', 'waleolaogunracdea@gmail.com', 'United Kingdom', '447809650816', '2023-08-26 18:06:54', '2023-08-26 18:08:30', '2023-08-26 18:08:30'),
(34, '950455OLAWALE', 'waleolaogunracdelata@gmail.com', 'United Kingdom', '447809650816', '2023-08-26 18:08:47', NULL, NULL),
(35, '913895OLAWALE', 'waleolaogunracelata@gmail.com', 'United Kingdom', '447809650816', '2023-08-26 18:10:09', NULL, NULL),
(36, '974368OLAWALE', 'waleolffff@gmail.com', 'United Kingdom', '447809650816', '2023-08-27 10:48:02', NULL, NULL),
(37, '935796TESTING', 'wjhwjhwrb@gmail.com', 'United Kingdom', '447809650898', '2023-08-27 10:51:50', NULL, NULL),
(38, '948454TESTING', 'Testing90873737@gmail.com', 'United Kingdom', '447809650816', '2023-08-27 10:57:20', NULL, NULL),
(46, '972748TESTING', 'Testingwewe@gmail.com', 'United Kingdom', '447890650816', '2023-08-27 12:01:19', NULL, NULL),
(47, '432292OLAWALE', 'modernman00@yahoo.com', 'UK', '01793321653', '2021-02-15 23:39:16', '2022-02-06 15:16:48', '2022-02-06 15:16:48'),
(54, '117540OLAWALE', 'woguns@ymail.com', 'United Kingdom', '07805262504', '2023-10-18 11:03:15', '2023-10-19 11:58:24', '2023-10-19 11:58:24');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `no` int NOT NULL,
  `id` varchar(255) NOT NULL,
  `eventName` text NOT NULL,
  `eventDate` date DEFAULT NULL,
  `eventType` text,
  `eventGroup` text,
  `eventDescription` text,
  `eventFrequency` text,
  `eventCode` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`no`, `id`, `eventName`, `eventDate`, `eventType`, `eventGroup`, `eventDescription`, `eventFrequency`, `eventCode`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '117540OLAWALE', 'oSINBAJI', '2022-01-14', 'Wedding', 'Canada', 'We are having a party for Obafemi Today', 'One-off', '', '2022-01-13 22:23:26', NULL, NULL),
(5, '117540OLAWALE', 'are you sure', '2022-01-14', 'House Warming', 'UK', 'sds', 'Annually', '', '2022-01-13 22:59:10', NULL, NULL),
(6, '117540OLAWALE', 'checking it well', '2022-01-14', 'Anniversary', 'Canada', 'dudududu', 'Monthly', '', '2022-01-13 23:00:18', NULL, NULL),
(7, '117540OLAWALE', 'Number 4 event', '2022-01-14', 'House Warming', 'UK', 'To celebrate Jummy s new job', 'Annually', '', '2022-01-13 23:01:48', NULL, NULL),
(8, '117540OLAWALE', 'Number 5 event', '2022-01-14', 'House Warming', 'UK', 'To celebrate Jummy s new job', 'Annually', '', '2022-01-13 23:02:33', NULL, NULL),
(9, '117540OLAWALE', 'event 6 checking', '2022-01-20', 'Anniversary', 'UK', 'We are having a party for Obafemi Today', 'Annually', '', '2022-01-13 23:05:45', NULL, NULL),
(10, '117540OLAWALE', 'Attention to detail  right ', '2022-01-15', 'Barbecue', 'UK', 'We are having a party for Obafemi Today', 'Annually', '', '2022-01-15 11:34:13', NULL, NULL),
(11, '117540OLAWALE', 'Jumoke s birthday', '2022-01-15', 'Barbecue', 'Canada', 'sds', 'Annually', '', '2022-01-15 11:41:14', NULL, NULL),
(12, '117540OLAWALE', 'Jumoke s birthday', '2022-01-22', 'Wedding', 'UK', 'dudududu', 'Monthly', '', '2022-01-15 11:42:32', NULL, NULL),
(13, '117540OLAWALE', 'Jumoke s birthday', '2022-01-16', 'Barbecue', 'Canada', 'sds', 'Annually', '', '2022-01-15 11:44:34', NULL, NULL),
(14, '117540OLAWALE', 'Jumoke s birthday', '2022-01-16', 'Holidays', 'UK', 'sds', 'Annually', '', '2022-01-15 11:46:15', NULL, NULL),
(15, '117540OLAWALE', 'Jumoke\'s birthday', '2022-01-16', 'Barbecue', 'Canada', 'dudududu', 'Annually', '', '2022-01-15 11:51:02', NULL, NULL),
(16, '117540OLAWALE', 'Explosion now', '2022-01-15', 'Anniversary', 'UK', 'We are having a party for Obafemi Today', 'Annually', '', '2022-01-15 12:11:48', NULL, NULL),
(17, '397755OLUSOLA', 'Femba Birthday', '2022-03-03', 'Birthday', 'UK', 'Let\'s celebrate Femba\'s birthday', 'Annually', '', '2022-02-03 18:12:50', NULL, NULL),
(18, '117540OLAWALE', 'Testing', '2022-05-11', 'Anniversary', 'Canada', 'To celebrate Jummy\'s new job', 'Annually', '', '2022-05-10 11:21:35', NULL, NULL),
(19, '117540OLAWALE', 'Olutobi Birthday', '2022-06-22', 'Birthday', 'Global', 'Olutobs will be 13 ', 'Annually', '', '2022-06-19 12:02:56', NULL, NULL),
(20, '117540OLAWALE', 'Olutobi Birthday', '2022-07-01', 'Birthday', 'UK', 'To celebrate Jummy\'s new job', 'Annually', '', '2022-06-28 23:02:38', NULL, NULL),
(21, '117540OLAWALE', 'Jumoke\'s birthday', '2022-07-06', 'Wedding', 'UK', 'To celebrate Jummy\'s new job', 'Annually', '', '2022-06-28 23:47:22', NULL, NULL),
(22, '117540OLAWALE', 'Jumoke\'s birthday', '2022-07-06', 'Barbecue', 'Canada', 'To celebrate Jummy\'s new job', 'Annually', '', '2022-06-29 23:12:44', NULL, NULL),
(23, '117540OLAWALE', 'Jumoke\'s birthday', '2022-07-07', 'Anniversary', 'Canada', 'ddfdfdd', 'Annually', '', '2022-06-29 23:13:16', NULL, NULL),
(24, '117540OLAWALE', 'Olutobi Birthday', '2022-07-07', 'House Warming', 'UK', 'ddfdfdd', 'Annually', '', '2022-06-29 23:14:44', NULL, NULL),
(25, '117540OLAWALE', 'Olawale Olaogun', '1979-07-15', 'Birthday', 'UK', 'Celebrate the modernman\'s day', 'Annually', '', '2022-07-07 23:18:46', NULL, NULL),
(26, '117540OLAWALE', 'Abiola Birthday', '2024-07-26', 'Birthday', 'UK', 'Celebrate Abiola on his day', 'Annually', '', '2022-09-03 18:11:28', NULL, NULL),
(27, '937619LAFANE', 'Nike Birthday', '2023-08-02', 'Birthday', 'UK', 'Celebrate Nike', 'Annually', '', '2023-02-28 14:52:02', NULL, NULL),
(28, '937619LAFANE', 'Temitayo Birthday', '2023-07-27', 'Birthday', 'UK', 'To celebrate Temitayo', 'Annually', '', '2023-05-04 23:14:54', NULL, NULL),
(29, '117540OLAWALE', 'Jumoke\'s birthday', '2023-08-06', 'Birthday', 'UK', 'Celebrate the modernman\'s day', 'One-off', '', '2023-07-30 23:24:33', NULL, NULL),
(30, '994428TESTING', 'Testing Birthday', '2024-06-01', 'Birthday', 'Global', 'Testing is adding another year', 'Annually', '', '2023-08-01 22:06:14', NULL, NULL),
(31, '999881TESTING', 'Testing Birthday', '2024-06-01', 'Birthday', 'Global', 'Testing is adding another year', 'Annually', '', '2023-08-01 22:11:49', NULL, NULL),
(32, '985172TESTING', 'Testing Birthday', '2024-06-01', 'Birthday', 'Global', 'Testing is adding another year', 'Annually', '', '2023-08-01 22:13:27', NULL, NULL),
(33, '948473TESTING', 'Testing Birthday', '2024-06-01', 'Birthday', 'Global', 'Testing is adding another year', 'Annually', '', '2023-08-01 22:14:17', NULL, NULL),
(34, '921152LEBANON', 'LEBANON Birthday', '2024-03-01', 'Birthday', 'Global', 'LEBANON is adding another year', 'Annually', '', '2023-08-23 23:57:31', NULL, NULL),
(35, '967957TESTING', 'Testing Birthday', '2024-03-01', 'Birthday', 'Global', 'Testing is adding another year', 'Annually', '', '2023-08-25 09:29:52', NULL, NULL),
(36, '992501TESTING', 'TESTING Birthday', '2024-03-01', 'Birthday', 'Global', 'TESTING is adding another year', 'Annually', '', '2023-08-26 17:00:20', NULL, NULL),
(37, '918627OLAWALE', 'Olawale Birthday', '2024-03-01', 'Birthday', 'Global', 'Olawale is adding another year', 'Annually', '', '2023-08-26 18:06:55', NULL, NULL),
(38, '950455OLAWALE', 'Olawale Birthday', '2024-03-01', 'Birthday', 'Global', 'Olawale is adding another year', 'Annually', '', '2023-08-26 18:08:47', NULL, NULL),
(39, '913895OLAWALE', 'Olawale Birthday', '2024-03-01', 'Birthday', 'Global', 'Olawale is adding another year', 'Annually', '', '2023-08-26 18:10:09', NULL, NULL),
(40, '974368OLAWALE', 'Olawale Birthday', '2024-05-01', 'Birthday', 'Global', 'Olawale is adding another year', 'Annually', '', '2023-08-27 10:48:02', NULL, NULL),
(41, '935796TESTING', 'Testing Birthday', '2024-05-01', 'Birthday', 'Global', 'Testing is adding another year', 'Annually', '', '2023-08-27 10:51:50', NULL, NULL),
(42, '948454TESTING', 'Testing Birthday', '2024-03-01', 'Birthday', 'Global', 'Testing is adding another year', 'Annually', '', '2023-08-27 10:57:20', NULL, NULL),
(43, '926764TESTING', 'Testing Birthday', '2024-03-01', 'Birthday', 'Global', 'Testing is adding another year', 'Annually', '', '2023-08-27 11:36:09', NULL, NULL),
(44, '973719TESTING', 'Testing Birthday', '2024-03-01', 'Birthday', 'Global', 'Testing is adding another year', 'Annually', '', '2023-08-27 11:37:00', NULL, NULL),
(45, '981243TESTING', 'TESTING Birthday', '2023-04-01', 'Birthday', 'Global', 'TESTING is adding another year', 'Annually', '', '2023-08-27 11:38:47', NULL, NULL),
(46, '930410TESTING', 'TESTING Birthday', '2023-04-01', 'Birthday', 'Global', 'TESTING is adding another year', 'Annually', '', '2023-08-27 11:45:24', NULL, NULL),
(47, '991388TESTING', 'TESTING Birthday', '2023-04-01', 'Birthday', 'Global', 'TESTING is adding another year', 'Annually', '', '2023-08-27 11:47:38', NULL, NULL),
(48, '949886TESTING', 'TESTING Birthday', '2023-04-01', 'Birthday', 'Global', 'TESTING is adding another year', 'Annually', '', '2023-08-27 11:52:56', NULL, NULL),
(49, '972748TESTING', 'TESTING Birthday', '2023-04-01', 'Birthday', 'Global', 'TESTING is adding another year', 'Annually', '', '2023-08-27 12:01:19', NULL, NULL),
(50, '117540OLAWALE', 'Jumoke&#039;s birthday', '2023-10-12', 'Anniversary', 'UK', 'Celebrate Nike', 'Annually', NULL, '2023-10-12 21:04:53', NULL, NULL),
(51, '117540OLAWALE', 'The treatment', '2023-10-13', 'Wedding', 'UK', 'Celebrate Abiola on his day', 'One-off', NULL, '2023-10-12 21:16:22', NULL, NULL),
(52, '117540OLAWALE', 'Palestine day', '2023-10-12', 'Anniversary', 'UK', 'Celebrate Nike', 'One-off', NULL, '2023-10-12 21:17:24', NULL, NULL),
(53, '117540OLAWALE', 'Jumoke&#039;s birthday', '2023-10-15', 'House Warming', 'UK', 'Celebrate Nike', 'Annually', NULL, '2023-10-14 00:04:12', NULL, NULL),
(54, '937619LAFANE', 'Jumoke&#039;s birthday', '2023-10-19', 'Birthday', 'Europe', 'Celebrate Nike', 'One-off', NULL, '2023-10-18 19:30:25', NULL, NULL),
(55, '937619LAFANE', 'Jumoke&#039;s birthday', '2023-10-19', 'Education', 'Canada', 'Celebrate Nike', 'Monthly', NULL, '2023-10-18 19:32:17', NULL, NULL),
(56, '937619LAFANE', 'Jumoke&#039;s birthday', '2023-10-19', 'Education', 'Europe', 'Celebrate Nike', 'Annually', NULL, '2023-10-18 19:33:53', NULL, NULL),
(57, '937619LAFANE', 'Jumoke&#039;s birthday', '2023-10-19', 'Anniversary', 'UK', 'Celebrate Nike', 'One-off', NULL, '2023-10-18 19:38:04', NULL, NULL),
(58, '937619LAFANE', 'Jumoke&#039;s birthday', '2023-10-25', 'Remembrance', 'Canada', 'Celebrate Nike', 'Monthly', NULL, '2023-10-18 19:39:04', NULL, NULL),
(59, '870016OLAWALE', 'Tope birthday', '2023-10-20', 'Wedding', 'UK', 'Celebrate Nike', 'One-off', NULL, '2023-10-19 20:19:07', NULL, NULL),
(60, '870016OLAWALE', 'F1 Tomorrow', '2023-10-20', 'Remembrance', 'Canada', 'Celebrate Nike', 'One-off', NULL, '2023-10-19 20:20:28', NULL, NULL),
(61, '870016OLAWALE', 'F1 Tomorrow', '2023-10-20', 'Remembrance', 'Canada', 'Celebrate Nike', 'One-off', NULL, '2023-10-19 20:36:15', NULL, NULL),
(62, '870016OLAWALE', 'F1 Tomorrow', '2023-10-27', 'Remembrance', 'Canada', 'Celebrate Nike', 'One-off', NULL, '2023-10-19 20:40:41', NULL, NULL),
(63, '870016OLAWALE', 'F1 Tomorrow', '2023-10-22', 'Remembrance', 'Canada', 'Celebrate Nike', 'One-off', NULL, '2023-10-19 20:49:45', NULL, NULL),
(64, '870016OLAWALE', 'F1 Tomorrow', '2023-10-22', 'Remembrance', 'Canada', 'Celebrate Nike', 'One-off', NULL, '2023-10-19 20:52:43', NULL, NULL),
(65, '870016OLAWALE', 'F1 Tomorrow', '2023-10-22', 'Remembrance', 'Canada', 'Celebrate Nike', 'One-off', NULL, '2023-10-19 21:04:37', NULL, NULL),
(66, '870016OLAWALE', 'F1 Tomorrow', '2023-10-22', 'Remembrance', 'Canada', 'Celebrate Nike', 'One-off', NULL, '2023-10-19 21:05:13', NULL, NULL),
(67, '870016OLAWALE', 'Temitayo Birthday', '2023-10-17', 'Education', 'Global', 'Celebrate Nike', 'One-off', NULL, '2023-10-19 21:08:47', NULL, NULL),
(68, '870016OLAWALE', 'Temitayo Birthday', '2023-10-17', 'Education', 'Global', 'Celebrate Nike', 'One-off', NULL, '2023-10-19 21:18:42', NULL, NULL),
(69, '870016OLAWALE', 'Temitayo Birthday', '2023-10-17', 'Education', 'Global', 'Celebrate Nike', 'One-off', NULL, '2023-10-19 21:19:12', NULL, NULL),
(70, '870016OLAWALE', 'Jumoke&#039;s birthday', '2023-10-23', 'Birthday', 'Canada', 'Celebrate Nike', 'Annually', NULL, '2023-10-19 21:22:03', NULL, NULL),
(71, '870016OLAWALE', 'Jumoke&#039;s birthday', '2023-10-23', 'Birthday', 'Canada', 'Celebrate Nike', 'Annually', NULL, '2023-10-19 21:28:13', NULL, NULL),
(72, '870016OLAWALE', 'Jumoke&#039;s birthday', '2023-10-22', 'Naming', 'UK', 'Celebrate Nike', 'Annually', NULL, '2023-10-19 21:32:17', NULL, NULL),
(73, '870016OLAWALE', 'Jumoke&#039;s birthday', '2024-10-26', 'Naming', 'UK', 'Celebrate Nike', 'Annually', NULL, '2023-10-19 21:34:39', NULL, NULL),
(74, '870016OLAWALE', 'Jumoke&#039;s birthday', '2023-12-23', 'Remembrance', 'Canada', 'Celebrate the modernman&#039;s day', 'One-off', NULL, '2023-10-19 22:47:11', NULL, NULL),
(75, '870016OLAWALE', 'Jumoke&#039;s birthday', '2023-12-23', 'Remembrance', 'Canada', 'Celebrate the modernman&#039;s day', 'One-off', NULL, '2023-10-19 22:47:23', NULL, NULL),
(76, '870016OLAWALE', 'Jumoke&#039;s birthday', '2023-10-21', 'Anniversary', 'Canada', 'Celebrate the modernman&#039;s day', 'Annually', NULL, '2023-10-19 22:51:03', NULL, NULL),
(77, '870016OLAWALE', 'Abiola Birthday', '2023-10-25', 'House Warming', 'Global', 'Celebrate Nike', 'One-off', NULL, '2023-10-19 22:52:57', NULL, NULL),
(78, '870016OLAWALE', 'Launch family network', '2023-11-01', 'House Warming', 'Global', 'Celebrate Nike', 'Annually', NULL, '2023-10-19 23:03:34', NULL, NULL),
(79, '870016OLAWALE', 'Launch family network', '2023-11-01', 'House Warming', 'Global', 'Launch family network', 'Annually', NULL, '2023-10-19 23:05:41', NULL, NULL),
(80, '937619LAFANE', 'Tope&#039;s birthday', '2023-10-20', 'Birthday', 'Global', 'Celebrate Nike', 'One-off', NULL, '2023-10-20 15:55:15', NULL, NULL),
(81, '937619LAFANE', 'Launch family network', '2023-10-20', 'House Warming', 'UK', 'Launch family network', 'One-off', NULL, '2023-10-20 15:56:42', NULL, NULL),
(82, '937619LAFANE', 'Launch family network', '2023-10-20', 'House Warming', 'UK', 'Launch family network', 'One-off', NULL, '2023-10-20 15:57:43', NULL, NULL),
(83, '937619LAFANE', 'Launch family network', '2023-10-20', 'House Warming', 'UK', 'Launch family network', 'One-off', NULL, '2023-10-20 16:01:09', NULL, NULL),
(84, '937619LAFANE', 'Jumoke&#039;s birthday', '2023-10-21', 'Wedding', 'UK', 'Celebrate Nike', 'Annually', NULL, '2023-10-20 16:59:36', NULL, NULL),
(85, '937619LAFANE', 'Jumoke&#039;s birthday', '2023-10-21', 'Anniversary', 'UK', 'Celebrate Nike', 'One-off', NULL, '2023-10-20 17:06:21', NULL, NULL),
(86, '937619LAFANE', 'Jumoke&#039;s birthday', '2023-10-21', 'Anniversary', 'UK', 'Celebrate Nike', 'One-off', NULL, '2023-10-20 17:07:47', NULL, NULL),
(87, '937619LAFANE', 'Jumoke&#039;s birthday', '2023-10-21', 'Anniversary', 'UK', 'Celebrate Nike', 'One-off', NULL, '2023-10-20 17:20:35', NULL, NULL),
(88, '937619LAFANE', 'Jumoke&#039;s birthday', '2023-10-19', 'Anniversary', 'UK', 'Celebrate Nike', 'One-off', NULL, '2023-10-20 17:25:07', NULL, NULL),
(89, '937619LAFANE', 'Jumoke&#039;s birthday', '2023-10-20', 'House Warming', 'UK', 'Let&#039;s celebrate Jumoke on her this beautiful day', 'Annually', NULL, '2023-10-20 18:35:55', NULL, NULL),
(90, '937619LAFANE', 'Jumoke&#039;s birthday', '2023-10-20', 'House Warming', 'UK', 'Let&#039;s celebrate Jumoke on her this beautiful day', 'Annually', NULL, '2023-10-20 18:37:46', NULL, NULL),
(91, '937619LAFANE', 'Jumoke&#039;s birthday', '2023-10-20', 'House Warming', 'UK', 'Let&#039;s celebrate Jumoke on her this beautiful day', 'Annually', NULL, '2023-10-20 18:39:18', NULL, NULL),
(92, '937619LAFANE', 'Jumoke&#039;s birthday', '2023-10-20', 'House Warming', 'UK', 'Let&#039;s celebrate Jumoke on her this beautiful day', 'Annually', NULL, '2023-10-20 18:40:45', NULL, NULL),
(93, '937619LAFANE', 'Jumoke&#039;s birthday', '2023-10-20', 'House Warming', 'UK', 'Let&#039;s celebrate Jumoke on her this beautiful day', 'Annually', NULL, '2023-10-20 18:44:05', NULL, NULL),
(94, '937619LAFANE', 'wale wale', '2023-10-20', 'Anniversary', 'Global', 'wedding anniversary', 'One-off', NULL, '2023-10-20 19:20:04', NULL, NULL),
(95, '937619LAFANE', 'Jumoke&#039;s birthday', '2023-10-20', 'Anniversary', 'Global', 'Celebrate Nike', 'Annually', NULL, '2023-10-20 20:32:17', NULL, NULL),
(96, '937619LAFANE', 'Jumoke&#039;s birthday', '2023-10-19', 'Anniversary', 'UK', 'Celebrate the modernman&#039;s day', 'One-off', NULL, '2023-10-20 20:33:09', NULL, NULL),
(97, '937619LAFANE', 'Jumoke&#039;s birthday', '2023-10-19', 'Anniversary', 'UK', 'Celebrate the modernman&#039;s day', 'One-off', NULL, '2023-10-20 20:35:20', NULL, NULL),
(98, '937619LAFANE', 'Jumoke&#039;s birthday', '2023-10-19', 'Anniversary', 'UK', 'Celebrate the modernman&#039;s day', 'One-off', NULL, '2023-10-20 20:36:17', NULL, NULL),
(99, '937619LAFANE', 'Jumoke&#039;s birthday', '2023-10-19', 'Anniversary', 'UK', 'Celebrate the modernman&#039;s day', 'One-off', NULL, '2023-10-20 20:36:45', NULL, NULL),
(100, '937619LAFANE', 'Jumoke&#039;s birthday', '2023-10-19', 'Anniversary', 'UK', 'Celebrate the modernman&#039;s day', 'One-off', NULL, '2023-10-20 20:37:20', NULL, NULL),
(101, '937619LAFANE', 'It is a party', '2023-10-22', 'House Warming', 'UK', 'Celebrate Nike', 'Annually', NULL, '2023-10-22 02:29:23', NULL, NULL),
(102, '937619LAFANE', 'It is a party', '2023-10-22', 'House Warming', 'UK', 'Celebrate Nike', 'Annually', NULL, '2023-10-22 02:29:42', NULL, NULL),
(103, '937619LAFANE', 'It is a party', '2023-10-22', 'House Warming', 'UK', 'Celebrate Nike', 'Annually', NULL, '2023-10-22 02:31:45', NULL, NULL),
(104, '937619LAFANE', 'Lets&#039; party', '2023-10-22', 'House Warming', 'UK', 'Celebrate Nike&#039;s new house', 'One-off', NULL, '2023-10-22 02:34:08', NULL, NULL),
(105, '937619LAFANE', 'Jumoke&#039;s birthday', '2023-10-29', 'Wedding', 'Global', 'Celebrate Abiola on his day', 'One-off', NULL, '2023-10-22 02:50:59', NULL, NULL),
(106, '937619LAFANE', 'Jumoke&#039;s birthday', '2023-10-22', 'House Warming', 'UK', 'Celebrate the modernman&#039;s day', 'One-off', NULL, '2023-10-22 02:55:19', NULL, NULL),
(107, '937619LAFANE', 'Jumoke&#039;s birthday', '2023-10-22', 'House Warming', 'UK', 'Celebrate the modernman&#039;s day', 'One-off', NULL, '2023-10-22 03:00:51', NULL, NULL),
(108, '937619LAFANE', 'Jumoke&#039;s birthday', '2023-10-22', 'House Warming', 'UK', 'Celebrate Nike', 'One-off', NULL, '2023-10-22 03:05:25', NULL, NULL),
(109, '937619LAFANE', 'Jumoke&#039;s birthday', '2023-10-13', 'Barbecue', 'Canada', 'Celebrate Nike', 'One-off', NULL, '2023-10-22 03:08:18', NULL, NULL),
(110, '937619LAFANE', 'Jumoke 2nd birthday', '2023-10-13', 'Barbecue', 'Canada', 'Celebrate Nike', 'One-off', NULL, '2023-10-22 03:08:53', NULL, NULL),
(111, '937619LAFANE', 'Jumoke 3rd birthday', '2023-10-22', 'Barbecue', 'Canada', 'Celebrate Nike', 'One-off', NULL, '2023-10-22 03:09:13', NULL, NULL),
(112, '937619LAFANE', 'Abiola Birthday', '2023-10-22', 'Anniversary', 'UK', 'Celebrate Nike', 'One-off', NULL, '2023-10-22 03:13:05', NULL, NULL),
(113, '870016OLAWALE', 'Osa graduation', '2023-10-24', 'Birthday', 'UK', 'we did it', 'One-off', NULL, '2023-10-24 00:19:54', NULL, NULL),
(114, '117540OLAWALE', 'Jumoke&#039;s birthday', '2023-10-24', 'Wedding', 'UK', 'Celebrate Nike', 'One-off', NULL, '2023-10-24 01:01:43', NULL, NULL),
(115, '117540OLAWALE', 'Launch family network', '2023-10-24', 'House Warming', 'UK', 'dfd', 'One-off', NULL, '2023-10-24 01:19:22', NULL, NULL),
(116, '870016OLAWALE', 'Jumoke&#039;s birthday', '2025-03-30', 'Wedding', 'UK', 'Celebrate Nike', 'Annually', NULL, '2024-03-29 08:49:29', NULL, NULL),
(117, '870016OLAWALE', 'EASRTER TRIP TO LONDON', '2024-03-31', 'Others', 'UK', 'We are going to London Hurray', 'One-off', NULL, '2024-03-30 16:33:54', NULL, NULL),
(118, '432292OLAWALE', 'Jumoke&#039;s birthday', '2024-04-06', 'Birthday', 'UK', 'Celebrate Abiola on his day', 'Annually', NULL, '2024-04-05 09:14:32', NULL, NULL),
(119, '397755OLUSOLA', 'The treatment', '2024-04-19', 'Wedding', 'UK', 'Celebrate Nike', 'Annually', NULL, '2024-04-18 09:08:11', NULL, NULL),
(120, '397755OLUSOLA', 'Grandma birthday', '2024-05-31', 'Birthday', 'Global', 'Celebrate Nike', 'Annually', NULL, '2024-05-31 12:56:36', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `familyFriend`
--

CREATE TABLE `familyFriend` (
  `no` int NOT NULL,
  `id` varchar(50) NOT NULL,
  `friend_id` varchar(50) NOT NULL,
  `famFriend_code` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NOT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `images`
--

CREATE TABLE `images` (
  `no` int NOT NULL,
  `id` varchar(30) NOT NULL,
  `img` text,
  `where_from` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `images`
--

INSERT INTO `images` (`no`, `id`, `img`, `where_from`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '117540OLAWALE', 'WhatsApp Image 2021-01-24 at 14.01.45 (9).jpeg', '', '2021-02-17 20:17:54', NULL, NULL),
(2, '117540OLAWALE', 'Jibs.jpeg', '', '2021-02-17 22:19:33', NULL, NULL),
(7, '397755OLUSOLA', 'oladeleNFayo.jpeg', 'profile', '2021-04-13 00:31:34', NULL, NULL),
(9, '870016OLAWALE', 'Jibs.jpeg', '', '2021-02-17 22:19:33', NULL, NULL),
(10, '117540OLAWALE', 'dede.jpeg', 'profile', '2021-05-17 13:40:13', NULL, NULL),
(11, '870016OLAWALE', 'WhatsApp Image 2021 09 26 at 10.36.40  1 .jpeg', 'profile', '2021-10-28 23:00:32', NULL, NULL),
(12, '870016OLAWALE', 'WhatsApp Image 2021 09 26 at 10.36.39.jpeg', 'profile', '2021-10-28 23:01:12', NULL, NULL),
(13, '870016OLAWALE', 'WhatsApp Image 2021 08 14 at 22.50.40  24 .jpeg', 'profile', '2021-10-29 00:25:19', NULL, NULL),
(14, '870016OLAWALE', 'femba.jpeg', 'profile', '2021-10-29 01:09:59', NULL, NULL),
(15, '117540OLAWALE', 'modernman.jpeg', 'profile', '2022-01-08 18:09:46', NULL, NULL),
(16, '117540OLAWALE', 'modernman.jpeg', 'profile', '2022-01-08 18:10:00', NULL, NULL),
(17, '117540OLAWALE', 'family.jpeg', 'profile', '2022-01-08 18:10:24', NULL, NULL),
(18, '117540OLAWALE', 'modernman.jpeg', 'profile', '2022-01-08 18:10:32', NULL, NULL),
(19, '117540OLAWALE', 'IMG_3640.jpeg', 'profile', '2022-07-07 23:20:36', NULL, NULL),
(20, '117540OLAWALE', 'SojProfile.jpeg', 'profile', '2022-07-07 23:21:27', NULL, NULL),
(21, '117540OLAWALE', 'olutobs_13th.jpeg', 'profile', '2022-07-22 01:16:43', NULL, NULL),
(22, '937619LAFANE', 'ShoTakingSelfy.jpeg', 'profile', '2023-03-02 18:29:01', NULL, NULL),
(23, '117540OLAWALE', 'cough.png', 'profile', '2023-10-12 01:59:48', NULL, NULL),
(24, '117540OLAWALE', 'olutobs_13th.jpeg', 'profile', '2023-10-12 02:00:07', NULL, NULL),
(25, '432292OLAWALE', 'cough.png', 'profile', '2024-04-05 09:56:37', NULL, NULL),
(26, '432292OLAWALE', 'olutobs13th.jpeg', 'profile', '2024-04-05 11:14:27', NULL, NULL),
(27, '432292OLAWALE', 'cough.png', 'profile', '2024-04-05 11:17:05', NULL, NULL),
(28, '432292OLAWALE', 'olutobs13th.jpeg', 'profile', '2024-04-05 11:17:18', NULL, NULL),
(29, '432292OLAWALE', 'olutobs13th.jpeg', 'profile', '2024-04-05 11:20:51', NULL, NULL),
(30, '432292OLAWALE', 'cough.png', 'profile', '2024-04-05 11:23:15', NULL, NULL),
(31, '432292OLAWALE', 'covid19-icon.png', 'profile', '2024-04-05 11:23:33', NULL, NULL),
(32, '432292OLAWALE', 'HandSanitiser.png', 'profile', '2024-04-05 11:23:43', NULL, NULL),
(33, '432292OLAWALE', 'modernman.jpeg', 'profile', '2024-04-05 11:23:58', NULL, NULL),
(34, '432292OLAWALE', 'family.jpeg', 'profile', '2024-04-05 11:24:08', NULL, NULL),
(35, '432292OLAWALE', 'modernman.jpeg', 'profile', '2024-04-05 11:24:17', NULL, NULL),
(36, '432292OLAWALE', 'WhatsApp Image 2021-04-17 at 17.20.52 1.jpeg', 'profile', '2024-04-05 11:24:26', NULL, NULL),
(37, '432292OLAWALE', 'olutobs13th.jpeg', 'profile', '2024-04-05 11:24:54', NULL, NULL),
(38, '432292OLAWALE', 'cough.png', 'profile', '2024-04-05 11:44:37', NULL, NULL),
(39, '432292OLAWALE', 'olutobs13th.jpeg', 'profile', '2024-04-05 11:59:44', NULL, NULL),
(40, '432292OLAWALE', 'cough.png', 'profile', '2024-04-05 12:45:45', NULL, NULL),
(41, '432292OLAWALE', 'olutobs13th.jpeg', 'profile', '2024-04-05 12:48:55', NULL, NULL),
(42, '432292OLAWALE', 'cough.png', 'profile', '2024-04-05 12:52:04', NULL, NULL),
(43, '432292OLAWALE', 'covid19-icon.png', 'profile', '2024-04-05 12:54:12', NULL, NULL),
(44, '432292OLAWALE', NULL, 'profile', '2024-04-05 13:01:18', NULL, NULL),
(45, '432292OLAWALE', NULL, 'profile', '2024-04-05 13:02:12', NULL, NULL),
(46, '432292OLAWALE', NULL, 'profile', '2024-04-05 13:02:28', NULL, NULL),
(47, '432292OLAWALE', NULL, 'profile', '2024-04-05 13:02:51', NULL, NULL),
(48, '432292OLAWALE', NULL, 'profile', '2024-04-05 13:04:46', NULL, NULL),
(49, '432292OLAWALE', NULL, 'profile', '2024-04-05 13:07:27', NULL, NULL),
(50, '432292OLAWALE', NULL, 'profile', '2024-04-05 13:08:31', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `kids`
--

CREATE TABLE `kids` (
  `no` int NOT NULL,
  `id` varchar(255) NOT NULL,
  `kid_name` text,
  `kid_email` text,
  `kid_linked` text,
  `kid_code` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `kids`
--

INSERT INTO `kids` (`no`, `id`, `kid_name`, `kid_email`, `kid_linked`, `kid_code`, `created_at`, `updated_at`, `deleted_at`) VALUES
(3, '870016OLAWALE', 'Olutobi Olaogun', 'oolutobs@gmail.com', NULL, '', '2021-02-15 23:42:31', NULL, NULL),
(4, '870016OLAWALE', 'Jumoke Olaogun', 'jummyoguns03@yahoo.com', NULL, '', '2021-02-15 23:42:31', NULL, NULL),
(5, '397755OLUSOLA', 'IRE OLAOGUN', 'ire@yahoo.com', NULL, '', '2021-02-15 23:49:10', NULL, NULL),
(6, '397755OLUSOLA', 'ORE OLAOGUN', 'ore@yahoo.com', NULL, '', '2021-02-15 23:49:10', NULL, NULL),
(7, '397755OLUSOLA', 'ABIOLA OLAOGUN', 'abiola@gmail.com', NULL, '', '2021-02-15 23:49:10', NULL, NULL),
(11, '929019DAYO', 'AYOMIDE OLAOGUN', 'Please  provide email address', NULL, '', '2021-09-21 22:39:56', NULL, NULL),
(12, '929019DAYO', 'TEMITAYO OLAOGUN', 'Please  provide email address', NULL, '', '2021-09-21 22:39:56', NULL, NULL),
(14, '936779SHABA', 'Olutobi Olaogun', 'oolutobs@gmail.com', NULL, '', '2022-07-21 00:20:44', NULL, NULL),
(15, '936779SHABA', 'Oladele Olaogun', 'sportcarexpert@gmail.com', NULL, '', '2022-07-21 00:20:44', NULL, NULL),
(16, '940728BUSSY', 'Bike ', 'testing@gmail.com', NULL, '', '2023-02-14 13:21:11', NULL, NULL),
(17, '940728BUSSY', 'james', 'testing@gmail.com', NULL, '', '2023-02-14 13:21:11', NULL, NULL),
(18, '942737GBEDUO', 'laf', 'testing@gmail.com', NULL, '', '2023-02-14 19:36:51', NULL, NULL),
(19, '942737GBEDUO', 'tayo', 'testing@gmail.com', NULL, '', '2023-02-14 19:36:51', NULL, NULL),
(20, '964649OLAWALE', 'Eniola Olaogun', 'investwally@gmail.com', NULL, '', '2023-07-07 21:43:20', NULL, NULL),
(21, '999881TESTING', 'BAEU OLAOGUN', 'Please  provide email address', 'With Spouse', '', '2023-08-01 22:11:49', '2024-06-20 09:52:53', '2024-06-20 09:52:53'),
(22, '985172TESTING', 'Testing', 'Please  provide email address', 'With Spouse', '', '2023-08-01 22:13:27', NULL, NULL),
(23, '948473TESTING', 'Testing', 'Please  provide email address', 'With Spouse', '', '2023-08-01 22:14:17', NULL, NULL),
(26, '992501TESTING', 'Olawale Olaogun', 'waleolaogunrac@gmail.com', 'With Spouse', '', '2023-08-26 17:00:20', NULL, NULL),
(27, '918627OLAWALE', 'segun olao', 'Please, provide email address', 'Not With Spouse', '', '2023-08-26 18:06:55', NULL, NULL),
(28, '950455OLAWALE', 'segun olao', 'Please, provide email address', 'Not With Spouse', '', '2023-08-26 18:08:47', NULL, NULL),
(29, '913895OLAWALE', 'segun olao', 'Please, provide email address', 'Not With Spouse', '', '2023-08-26 18:10:09', NULL, NULL),
(30, '937619LAFANE', 'jame edward', 'oplp@yahoo.com', 'Not With Spouse', NULL, '2023-10-18 16:20:00', NULL, NULL),
(31, '937619LAFANE', 'jame edwardty', 'oplgghghgp@yahoo.com', 'Not With Spouse', NULL, '2023-10-18 16:21:35', NULL, NULL),
(32, '937619LAFANE', 'jame edwardty', 'oplgghghgp@yahoo.com', 'Not With Spouse', NULL, '2023-10-18 16:22:33', NULL, NULL),
(33, '937619LAFANE', 'London Olaogun', 'lon@lol.com', 'With Spouse', NULL, '2023-10-18 19:14:48', NULL, NULL),
(34, '432292OLAWALE', 'OLUTOBS OLAOGUN', 'oolutobs@gmail.com', 'Not With Spouse', NULL, '2024-03-03 01:49:25', NULL, NULL),
(35, '432292OLAWALE', 'OLADELE OLAOGUN', 'oladele2412@gmail.com', 'With Spouse', NULL, '2024-03-03 01:49:25', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `notification`
--

CREATE TABLE `notification` (
  `no` int NOT NULL,
  `sender_id` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `receiver_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `sender_name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `receiver` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `notification_name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `notification_date` text,
  `notification_type` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `notification_content` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `notification_status` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `notification`
--

INSERT INTO `notification` (`no`, `sender_id`, `receiver_id`, `sender_name`, `receiver`, `notification_name`, `notification_date`, `notification_type`, `notification_content`, `notification_status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(55, '117540OLAWALE', '117540OLAWALE', 'SEGUN OLAOGUN', 'Olawale OLAOGUN', 'Friend Request from SEGUN OLAOGUN', '2023-10-24', 'Friend Request', 'SEGUN OLAOGUN sent Olawale OLAOGUN a family request', NULL, '2023-10-24 00:59:07', NULL, NULL),
(57, '117540OLAWALE', 'MODERNMAN', 'SEGUN OLAOGUN', '', 'Launch family network', '2023-10-24', 'House Warming', 'dfd', NULL, '2023-10-24 01:19:22', NULL, NULL),
(58, '117540OLAWALE', '397755OLUSOLA', '\n        SEGUN \n        OLAOGUN', '\n        FEMI \n        OLAOGUN', 'Friend Request from \n        SEGUN \n        OLAOGUN', '2023-10-24', 'Friend Request', '\n        SEGUN \n        OLAOGUN sent \n        FEMI \n        OLAOGUN a family request', NULL, '2023-10-24 01:44:43', NULL, NULL),
(59, '117540OLAWALE', '937619LAFANE', '\n        SEGUN \n        OLAOGUN', '\n        Lafane \n        OLAOGUN', 'Friend Request from \n        SEGUN \n        OLAOGUN', '2023-10-24', 'Friend Request', '\n        SEGUN \n        OLAOGUN sent \n        Lafane \n        OLAOGUN a family request', NULL, '2023-10-24 13:32:55', NULL, NULL),
(60, '870016OLAWALE', 'MODERNMAN', 'ADESOJI OLAOGUN', NULL, 'Jumoke&#039;s birthday', '2024-03-30', 'Wedding', 'Celebrate Nike', NULL, '2024-03-29 08:49:29', NULL, NULL),
(61, '870016OLAWALE', 'MODERNMAN', 'ADESOJI OLAOGUN', NULL, 'EASRTER TRIP TO LONDON', '2024-03-31', 'Others', 'We are going to London Hurray', NULL, '2024-03-30 16:33:54', NULL, NULL),
(62, '117540OLAWALE', '974368OLAWALE', '\n        SEGUN \n        OLAOGUN', '\n        Olawale \n        OLAOGUN', 'Friend Request from \n        SEGUN \n        OLAOGUN', '2024-04-05', 'Friend Request', '\n        SEGUN \n        OLAOGUN sent \n        Olawale \n        OLAOGUN a family request', NULL, '2024-04-05 09:11:18', NULL, NULL),
(63, '117540OLAWALE', '987764OLAWALE', '\n        SEGUN \n        OLAOGUN', '\n        OLAWALE \n        OLAOGUN', 'Friend Request from \n        SEGUN \n        OLAOGUN', '2024-04-05', 'Friend Request', '\n        SEGUN \n        OLAOGUN sent \n        OLAWALE \n        OLAOGUN a family request', NULL, '2024-04-05 09:11:21', NULL, NULL),
(64, '432292OLAWALE', 'MODERNMAN', 'OLAWALE OLAOGUN', NULL, 'Jumoke&#039;s birthday', '2024-04-06', 'Birthday', 'Celebrate Abiola on his day', NULL, '2024-04-05 09:14:32', NULL, NULL),
(65, '397755OLUSOLA', '117540OLAWALE', '\n        FEMI \n        OLAOGUN', '\n        SEGUN \n        OLAOGUN', 'Friend Request from \n        FEMI \n        OLAOGUN', '2024-04-18', 'Friend Request', '\n        FEMI \n        OLAOGUN sent \n        SEGUN \n        OLAOGUN a family request', NULL, '2024-04-18 08:54:06', NULL, NULL),
(66, '397755OLUSOLA', 'SHO', 'FEMI OLAOGUN', NULL, 'The treatment', '2024-04-19', 'Wedding', 'Celebrate Nike', NULL, '2024-04-18 09:08:11', NULL, NULL),
(67, '397755OLUSOLA', '870016OLAWALE', '\n        FEMI \n        OLAOGUN', '\n        ADESOJI \n        OLAOGUN', 'Friend Request from \n        FEMI \n        OLAOGUN', '2024-05-31', 'Friend Request', '\n        FEMI \n        OLAOGUN sent \n        ADESOJI \n        OLAOGUN a family request', NULL, '2024-05-31 12:52:30', NULL, NULL),
(68, '397755OLUSOLA', 'SHO', 'FEMI OLAOGUN', NULL, 'Grandma birthday', '2024-05-31', 'Birthday', 'Celebrate Nike', NULL, '2024-05-31 12:56:36', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `otherFamily`
--

CREATE TABLE `otherFamily` (
  `no` int NOT NULL,
  `id` varchar(255) NOT NULL,
  `otherFamCode` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `spouseName` text,
  `spouseMobile` text,
  `spouseEmail` varchar(50) DEFAULT NULL,
  `spouseMaidenName` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `fatherName` text,
  `fatherMobile` text,
  `fatherEmail` varchar(50) DEFAULT NULL,
  `motherName` text,
  `motherMobile` text,
  `motherEmail` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `otherFamily`
--

INSERT INTO `otherFamily` (`no`, `id`, `otherFamCode`, `spouseName`, `spouseMobile`, `spouseEmail`, `spouseMaidenName`, `fatherName`, `fatherMobile`, `fatherEmail`, `motherName`, `motherMobile`, `motherEmail`, `created_at`, `updated_at`, `deleted_at`) VALUES
(2, '117540OLAWALE', '', NULL, '11', '', 'MAYUNGBE', 'OLUYOMI OLAOGUN', '2348036416078', '', 'IYABO OLAOGUN', '01793321653', '', '2023-08-01 01:53:43', '2021-05-05 20:32:15', '2021-05-05 20:32:15'),
(3, '870016OLAWALE', '', 'Yogun', '447809650816', 'waledevtest@gmail.com', 'akinduro', 'OLUYOMI OLAOGUN', '2348036416081', '', 'IYABO OLAOGUN', '01793321653', '', '2023-08-01 01:53:43', NULL, NULL),
(4, '397755OLUSOLA', '', 'TOYIN OLAOGUN', '01793321653', '', 'MAYUNGBE', 'OLUYOMI OLAOGUN', '2348036416076', '', 'IYABO OLAOGUN', '01793321653', '', '2023-08-01 01:53:43', NULL, NULL),
(6, '929019DAYO', '', NULL, '11', '', 'MAYUNGBE', 'OLUYOMI OLAOGUN', '2348036416078', '', 'IYABO OLAOGUN', '01793321653', '', '2023-08-01 01:53:43', '2021-05-05 20:32:15', '2021-05-05 20:32:15'),
(8, '975782ADETUNJI', '', 'Engine', '2348078096508', 'retailwally@gmail.com', 'Etingbo', 'Yomi', 'Not Provided', 'waleolaogunrac@gmail.com', 'ISEYIN', 'Not Provided', 'Not Provided', '2023-08-01 01:53:43', NULL, NULL),
(9, '936779SHABA', '', 'Jibike Olaogun', '07805262504', 'bikeoguns@gmail.com', 'Mayungbe', 'Yomi Olaogun', '2348036416079', 'Not Provided', 'Iyabo Olaogun', 'Not Provided', 'Not Provided', '2023-08-01 01:53:43', NULL, NULL),
(10, '954243OLAOLUWA', '', 'Not Provided', 'Not Provided', 'Not Provided', 'Gbelo', 'Shola Olaogun', '447466568065', 'solaolaogun2013@gmail.com', 'Toyin Olaogun', 'Not Provided', 'Not Provided', '2023-08-01 01:53:43', NULL, NULL),
(11, '912189MOMODU', 'Laf123', 'TOYIN', '07805262504', 'bikeoguns@gmail.com', 'mayungbe', 'yomi olaogun', 'Not Provided', 'Not Provided', 'IYABO OLAOGUN', 'Not Provided', 'Not Provided', '2023-08-01 01:53:43', NULL, NULL),
(12, '987764OLAWALE', '', 'Not Provided', 'Not Provided', 'Not Provided', 'ebi', 'IYABO OLAOGUN', '07809650816', 'waleolaogunrac@gmail.com', 'IYABO OLAOGUN', 'Not Provided', 'Not Provided', '2023-08-01 01:53:43', NULL, NULL),
(13, '913882OLAWALE', '', 'Not Provided', 'Not Provided', 'Not Provided', 'kio', 'IYABO', '07809650816', 'waleolaogunrac@gmail.com', 'IYABO OLAOGUN', '07809650816', 'waleolaogunrac@gmail.com', '2023-08-01 01:53:43', NULL, NULL),
(14, '940728BUSSY', '', 'Not Provided', 'Not Provided', 'Not Provided', NULL, 'OLUYOMI OLAOGUN', '447809650816', 'TESTING@GMAIL.COM', 'IYABO OLAOGUN', '447809650814', 'TESTING@GMAIL.COM', '2023-08-01 01:53:43', NULL, NULL),
(15, '942737GBEDUO', '', 'toyin', '447809650816', 'testing@gmail.com', NULL, 'olaogun yom', 'Not Provided', 'Not Provided', 'olaogun iya', 'Not Provided', 'Not Provided', '2023-08-01 01:53:43', NULL, NULL),
(16, '971778GENERA', '', 'Not Provided', 'Not Provided', 'Not Provided', NULL, 'wale', 'Not Provided', 'Not Provided', 'Fati', 'Not Provided', 'Not Provided', '2023-08-01 01:53:43', NULL, NULL),
(17, '937619LAFANE', '', 'Not Provided', 'Not Provided', 'Not Provided', NULL, 'SHOLA', 'Not Provided', 'Not Provided', 'ISEYIN', 'Not Provided', 'Not Provided', '2023-08-01 01:53:43', NULL, NULL),
(18, '964649OLAWALE', '', 'Ajibike Olaogun', '07805262504', 'bikeoguns@gmail.com', NULL, 'OLUYOMI OLAOGUN', 'Not Provided', 'Not Provided', 'IYABO OLAOGUN', 'Not Provided', 'Not Provided', '2023-08-01 01:53:43', NULL, NULL),
(19, '915784TESTING', '', 'Not Provided', 'Not Provided', 'Not Provided', NULL, 'DadTesting Olaogun', 'Not Provided', 'Not Provided', 'Testing Olaogun', 'Not Provided', 'Not Provided', '2023-08-01 01:53:43', NULL, NULL),
(20, '974628TESTING', '', 'Not Provided', 'Not Provided', 'Not Provided', NULL, 'DadTesting Olaogun', 'Not Provided', 'Not Provided', 'Testing Olaogun', 'Not Provided', 'Not Provided', '2023-08-01 01:53:43', NULL, NULL),
(21, '941501TOBI', '', 'Not Provided', 'Not Provided', 'Not Provided', NULL, 'olawale olaogun', '07809650816', 'waleolaogunrac@gmail.com', 'ajibike olaogun', '07805262504', 'bikeoguns@yahoo.com', '2023-08-01 01:53:43', NULL, NULL),
(22, '995544TESTING', '', 'Testing Olaogun', '447809650816', 'bikeoguns@gmail.com', NULL, 'OLUYOMI OLAOGUN', 'Not Provided', 'Not Provided', 'IYABO OLAOGUN', 'Not Provided', 'Not Provided', '2023-08-01 01:53:43', NULL, NULL),
(23, '962673TOBI', '', 'Not Provided', 'Not Provided', 'Not Provided', NULL, 'olawale olaogun', 'Not Provided', 'Not Provided', 'ajibike olaogun', 'Not Provided', 'Not Provided', '2023-08-01 13:29:58', NULL, NULL),
(24, '957154TESTING', 'laf123', 'Testing', '447809650816', 'Testing@olaogun.com', NULL, 'Testing', 'Not Provided', 'Not Provided', 'Testing', 'Not Provided', 'Not Provided', '2023-08-01 21:29:11', NULL, NULL),
(25, '918455TESTING', '', 'Testing', '2348036416079', 'Testing@olaogunx.com', NULL, 'Testing', 'Not Provided', 'Not Provided', 'Testing', 'Not Provided', 'Not Provided', '2023-08-01 21:35:30', NULL, NULL),
(26, '994428TESTING', '', 'Testing', '07809650816', 'Testing@olaogue.com', NULL, 'Testing', 'Not Provided', 'Not Provided', 'Testing', 'Not Provided', 'Not Provided', '2023-08-01 22:06:14', NULL, NULL),
(27, '999881TESTING', 'Laf123', 'JENNY OGUNS', '07809650816', 'Testing@olaogue.com', NULL, 'ABIOLA OGUNS', 'Not Provided', 'Not Provided', 'TESTING OGUNS', 'Not Provided', 'Not Provided', '2023-08-01 22:11:49', NULL, NULL),
(28, '985172TESTING', '', 'Testing', '07809650816', 'Testing@olaogue.com', NULL, 'Testing', 'Not Provided', 'Not Provided', 'Testing', 'Not Provided', 'Not Provided', '2023-08-01 22:13:27', NULL, NULL),
(29, '948473TESTING', '', 'Testing', '07809650816', 'Testing@olaogue.com', NULL, 'Testing', 'Not Provided', 'Not Provided', 'Testing', 'Not Provided', 'Not Provided', '2023-08-01 22:14:17', NULL, NULL),
(30, '992501TESTING', NULL, 'JIBS', '07805262504', 'bikeoguns@gmail.com', 'AKINDURO', 'testing', 'Not Provided', 'Not Provided', 'testing', 'Not Provided', 'Not Provided', '2023-08-26 17:00:20', NULL, NULL),
(31, '918627OLAWALE', NULL, 'das', '07809650816', 'waleolaogunrac@gmail.com', 'dad', 'Testing', 'Not Provided', 'Not Provided', 'Testing', 'Not Provided', 'Not Provided', '2023-08-26 18:06:54', NULL, NULL),
(32, '950455OLAWALE', NULL, 'das', '07809650816', 'waleolaogunrac@gmail.com', 'dad', 'Testing', 'Not Provided', 'Not Provided', 'Testing', 'Not Provided', 'Not Provided', '2023-08-26 18:08:47', NULL, NULL),
(33, '913895OLAWALE', NULL, 'das', '07809650816', 'waleolaogunrac@gmail.com', 'dad', 'Testing', 'Not Provided', 'Not Provided', 'Testing', 'Not Provided', 'Not Provided', '2023-08-26 18:10:09', NULL, NULL),
(34, '974368OLAWALE', NULL, 'Not Provided', 'Not Provided', 'Not Provided', 'Not Provided', 'testing', 'Not Provided', 'Not Provided', 'testing', 'Not Provided', 'Not Provided', '2023-08-27 10:48:02', NULL, NULL),
(35, '935796TESTING', NULL, 'Not Provided', 'Not Provided', 'Not Provided', 'Not Provided', 'test', 'Not Provided', 'Not Provided', 'test', 'Not Provided', 'Not Provided', '2023-08-27 10:51:50', NULL, NULL),
(36, '948454TESTING', NULL, 'Not Provided', 'Not Provided', 'Not Provided', 'Not Provided', 'Testing', 'Not Provided', 'Not Provided', 'Testing', 'Not Provided', 'Not Provided', '2023-08-27 10:57:20', NULL, NULL),
(43, '972748TESTING', NULL, 'Not Provided', 'Not Provided', 'Not Provided', 'Not Provided', 'Testing', 'Not Provided', 'Not Provided', 'Testing', 'Not Provided', 'Not Provided', '2023-08-27 12:01:19', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `personal`
--

CREATE TABLE `personal` (
  `no` int NOT NULL,
  `id` varchar(255) NOT NULL,
  `firstName` text NOT NULL,
  `lastName` text NOT NULL,
  `famCode` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `familyCode2` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `day` int NOT NULL,
  `month` text NOT NULL,
  `year` int NOT NULL,
  `kids` int NOT NULL,
  `gender` text,
  `siblings` int NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `personal`
--

INSERT INTO `personal` (`no`, `id`, `firstName`, `lastName`, `famCode`, `familyCode2`, `day`, `month`, `year`, `kids`, `gender`, `siblings`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '432292OLAWALE', 'OLAWALE', 'OLAOGUN', 'MODERNMAN', '', 22, 'Jul', 1979, 3, 'Male', 3, '2021-02-15 23:21:43', '2023-07-22 02:23:37', '2023-07-22 02:23:37'),
(2, '117540OLAWALE', 'SEGUN', 'OLAOGUN', 'MODERNMAN', '', 29, 'Jul', 1979, 3, 'Male', 3, '2021-02-15 23:39:16', '2023-10-03 23:19:43', '2023-10-03 23:19:43'),
(3, '870016OLAWALE', 'ADESOJI', 'OLAOGUN', 'MODERNMAN', '', 23, 'Jul', 1979, 3, 'Male', 3, '2021-02-15 23:42:31', '2023-07-22 02:24:20', '2023-07-22 02:24:20'),
(4, '397755OLUSOLA', 'FEMI', 'OLAOGUN', 'SHO', '', 14, 'AUGUST', 1979, 3, 'Male', 3, '2021-02-15 23:49:10', '2021-02-17 20:48:29', '2021-02-17 20:48:29'),
(6, '929019DAYO', 'Dayo', 'OLAOGUN', 'Dayo', '', 5, 'Mar', 1997, 2, 'Male', 1, '2021-09-21 22:39:56', NULL, NULL),
(8, '975782ADETUNJI', 'adetunji', 'OLAOGUN', 'adetunji', '', 1, 'Jan', 1930, 0, 'Female', 0, '2022-07-21 00:12:34', NULL, NULL),
(9, '936779SHABA', 'Shaba', 'OLAOGUN', 'Shabatintin', '', 6, 'Jul', 1936, 2, 'Male', 2, '2022-07-21 00:20:44', NULL, NULL),
(10, '954243OLAOLUWA', 'Olaoluwa', 'OLAOGUN', 'Olaoluwa', '', 2, 'Apr', 2018, 0, 'Male', 2, '2022-07-21 08:06:51', NULL, NULL),
(11, '912189MOMODU', 'MOMODU', 'OLAOGUN', 'MOMODU', '', 7, 'May', 1944, 0, 'Female', 1, '2023-02-13 17:14:09', NULL, NULL),
(12, '987764OLAWALE', 'OLAWALE', 'OLAOGUN', 'Laf123', '', 15, 'Aug', 1938, 0, 'Male', 0, '2023-02-13 17:36:11', '2023-09-15 12:12:56', '2023-09-15 12:12:56'),
(13, '913882OLAWALE', 'Olawale', 'Olaogun', 'Olawale', '', 12, 'Nov', 1939, 0, 'Male', 0, '2023-02-13 17:44:09', NULL, NULL),
(15, '940728BUSSY', 'Bussy', 'OLAOGUN', 'Laf123', '', 9, 'Apr', 1953, 2, 'Female', 2, '2023-02-14 13:21:11', '2023-09-15 12:13:09', '2023-09-15 12:13:09'),
(16, '942737GBEDUO', 'Gbeduo', 'OLAOGUN', 'Gbeduo', '', 5, 'May', 1934, 2, 'Male', 1, '2023-02-14 19:36:51', NULL, NULL),
(17, '971778GENERA', 'Genera', 'OLAOGUN', 'Genera', '', 3, 'Mar', 1933, 0, 'Female', 0, '2023-02-15 00:41:26', NULL, NULL),
(18, '937619LAFANE', 'Lafane', 'OLAOGUN', 'Laf123', 'laf234', 14, 'Jan', 2000, 0, 'Female', 0, '2023-02-15 00:47:04', '2023-08-30 13:37:35', '2023-08-30 13:37:35'),
(19, '964649OLAWALE', 'Olawale', 'OLAOGUN', 'Modernman', '', 15, 'Jan', 1979, 1, 'Male', 1, '2023-07-07 21:43:20', NULL, NULL),
(20, '915784TESTING', 'Barton', 'OLAOGUN', 'Laf123', '', 6, 'Apr', 1974, 0, 'Male', 0, '2023-07-11 22:25:01', '2023-09-15 13:52:55', '2023-09-15 13:52:55'),
(21, '974628TESTING', 'Adegoke', 'OLAOGUN', 'Testing', '', 1, 'Jan', 2000, 0, 'Female', 0, '2023-07-12 17:59:46', '2023-09-15 13:53:06', '2023-09-15 13:53:06'),
(22, '941501TOBI', 'tobi', 'OLAOGUN', 'tobi', '', 1, 'Jul', 2009, 0, 'Female', 2, '2023-07-30 20:01:19', NULL, NULL),
(23, '995544TESTING', 'Testing', 'OLAOGUN', 'Testing', '', 6, 'Apr', 1944, 1, 'Female', 1, '2023-08-01 01:50:38', NULL, NULL),
(24, '962673TOBI', 'tobi', 'OLAOGUN', 'MODERNMAN', '', 1, 'Jul', 2009, 1, 'Female', 1, '2023-08-01 13:29:58', '2023-10-03 23:19:18', '2023-10-03 23:19:18'),
(25, '957154TESTING', 'Test', 'OLAOGUN', 'Testing', '', 4, 'Apr', 1933, 1, 'Female', 2, '2023-08-01 21:29:11', '2023-09-15 13:53:10', '2023-09-15 13:53:10'),
(26, '918455TESTING', 'Oladele', 'OLAOGUN', 'MODERNMAN', '', 7, 'Sep', 1938, 1, 'Male', 1, '2023-08-01 21:35:30', '2023-10-03 23:19:27', '2023-10-03 23:19:27'),
(27, '994428TESTING', 'John', 'OLAOGUN', 'Laf123', '', 9, 'Jun', 1936, 1, 'Female', 2, '2023-08-01 22:06:14', '2023-09-15 13:53:25', '2023-09-15 13:53:25'),
(28, '999881TESTING', 'Abby', 'OLAOGUN', 'MODERNMAN', '', 9, 'Jun', 1936, 1, 'Female', 2, '2023-08-01 22:11:49', '2023-10-03 23:19:34', '2023-10-03 23:19:34'),
(29, '985172TESTING', 'Istanbul', 'OLAOGUN', 'Testing', '', 9, 'Jun', 1936, 1, 'Female', 2, '2023-08-01 22:13:27', '2023-09-15 13:53:45', '2023-09-15 13:53:45'),
(30, '948473TESTING', 'Lewis', 'OLAOGUN', 'Testing', '', 9, 'Jun', 1936, 1, 'Female', 2, '2023-08-01 22:14:17', '2023-09-15 13:53:55', '2023-09-15 13:53:55'),
(31, '992501TESTING', 'Japan', 'OLAOGUN', 'OLAO116932', NULL, 3, 'Mar', 1932, 1, 'Female', 1, '2023-08-26 17:00:20', '2023-09-15 13:54:12', '2023-09-15 13:54:12'),
(32, '918627OLAWALE', 'Olawale', 'OLAOGUN', 'OLAO134911', NULL, 2, 'Mar', 1962, 1, 'Female', 1, '2023-08-26 18:06:54', NULL, NULL),
(33, '950455OLAWALE', 'Swindon', 'OLAOGUN', 'OLAO134911', NULL, 2, 'Mar', 1962, 1, 'Female', 1, '2023-08-26 18:08:47', '2023-09-15 13:54:05', '2023-09-15 13:54:05'),
(34, '913895OLAWALE', 'Olawale', 'OLAOGUN', 'OLAO134911', NULL, 2, 'Mar', 1962, 1, 'Female', 1, '2023-08-26 18:10:09', NULL, NULL),
(35, '974368OLAWALE', 'Olawale', 'OLAOGUN', '23244', NULL, 3, 'May', 1936, 0, 'Male', 0, '2023-08-27 10:48:02', NULL, NULL),
(36, '935796TESTING', 'London', 'OLAOGUN', '23244', NULL, 8, 'May', 1936, 0, 'Female', 0, '2023-08-27 10:51:50', '2023-09-15 13:54:20', '2023-09-15 13:54:20'),
(37, '948454TESTING', 'Testing', 'OLAOGUN', '2323', NULL, 2, 'Mar', 1932, 0, 'Female', 0, '2023-08-27 10:57:20', NULL, NULL),
(45, '972748TESTING', 'TESTING', 'OLAOGUN', 'we23', NULL, 2, 'Apr', 1932, 0, 'Male', 0, '2023-08-27 12:01:19', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `pma__bookmark`
--

CREATE TABLE `pma__bookmark` (
  `id` int UNSIGNED NOT NULL,
  `dbase` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL DEFAULT '',
  `user` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL DEFAULT '',
  `label` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '',
  `query` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin COMMENT='Bookmarks';

-- --------------------------------------------------------

--
-- Table structure for table `pma__central_columns`
--

CREATE TABLE `pma__central_columns` (
  `db_name` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL,
  `col_name` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL,
  `col_type` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL,
  `col_length` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin,
  `col_collation` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL,
  `col_isNull` tinyint(1) NOT NULL,
  `col_extra` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT '',
  `col_default` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin COMMENT='Central list of columns';

-- --------------------------------------------------------

--
-- Table structure for table `pma__column_info`
--

CREATE TABLE `pma__column_info` (
  `id` int UNSIGNED NOT NULL,
  `db_name` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL DEFAULT '',
  `table_name` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL DEFAULT '',
  `column_name` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL DEFAULT '',
  `comment` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '',
  `mimetype` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '',
  `transformation` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL DEFAULT '',
  `transformation_options` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL DEFAULT '',
  `input_transformation` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL DEFAULT '',
  `input_transformation_options` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin COMMENT='Column information for phpMyAdmin';

-- --------------------------------------------------------

--
-- Table structure for table `pma__designer_settings`
--

CREATE TABLE `pma__designer_settings` (
  `username` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL,
  `settings_data` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin COMMENT='Settings related to Designer';

-- --------------------------------------------------------

--
-- Table structure for table `pma__export_templates`
--

CREATE TABLE `pma__export_templates` (
  `id` int UNSIGNED NOT NULL,
  `username` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL,
  `export_type` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL,
  `template_name` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL,
  `template_data` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin COMMENT='Saved export templates';

-- --------------------------------------------------------

--
-- Table structure for table `pma__favorite`
--

CREATE TABLE `pma__favorite` (
  `username` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL,
  `tables` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin COMMENT='Favorite tables';

-- --------------------------------------------------------

--
-- Table structure for table `pma__history`
--

CREATE TABLE `pma__history` (
  `id` bigint UNSIGNED NOT NULL,
  `username` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL DEFAULT '',
  `db` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL DEFAULT '',
  `table` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL DEFAULT '',
  `timevalue` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `sqlquery` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin COMMENT='SQL history for phpMyAdmin';

-- --------------------------------------------------------

--
-- Table structure for table `pma__navigationhiding`
--

CREATE TABLE `pma__navigationhiding` (
  `username` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL,
  `item_name` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL,
  `item_type` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL,
  `db_name` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL,
  `table_name` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin COMMENT='Hidden items of navigation tree';

-- --------------------------------------------------------

--
-- Table structure for table `pma__pdf_pages`
--

CREATE TABLE `pma__pdf_pages` (
  `db_name` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL DEFAULT '',
  `page_nr` int UNSIGNED NOT NULL,
  `page_descr` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin COMMENT='PDF relation pages for phpMyAdmin';

-- --------------------------------------------------------

--
-- Table structure for table `pma__recent`
--

CREATE TABLE `pma__recent` (
  `username` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL,
  `tables` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin COMMENT='Recently accessed tables';

--
-- Dumping data for table `pma__recent`
--

INSERT INTO `pma__recent` (`username`, `tables`) VALUES
('root', '[{\"db\":\"family\",\"table\":\"pushNotification\"},{\"db\":\"family\",\"table\":\"personal\"},{\"db\":\"family\",\"table\":\"post\"},{\"db\":\"family\",\"table\":\"notification\"},{\"db\":\"family\",\"table\":\"familyFriend\"},{\"db\":\"family\",\"table\":\"kids\"},{\"db\":\"family\",\"table\":\"otherFamily\"},{\"db\":\"family\",\"table\":\"account\"},{\"db\":\"family\",\"table\":\"contact\"},{\"db\":\"family\",\"table\":\"requestMgt\"}]');

-- --------------------------------------------------------

--
-- Table structure for table `pma__relation`
--

CREATE TABLE `pma__relation` (
  `master_db` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL DEFAULT '',
  `master_table` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL DEFAULT '',
  `master_field` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL DEFAULT '',
  `foreign_db` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL DEFAULT '',
  `foreign_table` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL DEFAULT '',
  `foreign_field` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin COMMENT='Relation table';

-- --------------------------------------------------------

--
-- Table structure for table `pma__savedsearches`
--

CREATE TABLE `pma__savedsearches` (
  `id` int UNSIGNED NOT NULL,
  `username` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL DEFAULT '',
  `db_name` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL DEFAULT '',
  `search_name` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL DEFAULT '',
  `search_data` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin COMMENT='Saved searches';

-- --------------------------------------------------------

--
-- Table structure for table `pma__table_coords`
--

CREATE TABLE `pma__table_coords` (
  `db_name` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL DEFAULT '',
  `table_name` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL DEFAULT '',
  `pdf_page_number` int NOT NULL DEFAULT '0',
  `x` float UNSIGNED NOT NULL DEFAULT '0',
  `y` float UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin COMMENT='Table coordinates for phpMyAdmin PDF output';

-- --------------------------------------------------------

--
-- Table structure for table `pma__table_info`
--

CREATE TABLE `pma__table_info` (
  `db_name` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL DEFAULT '',
  `table_name` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL DEFAULT '',
  `display_field` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin COMMENT='Table information for phpMyAdmin';

--
-- Dumping data for table `pma__table_info`
--

INSERT INTO `pma__table_info` (`db_name`, `table_name`, `display_field`) VALUES
('family', 'events', 'id'),
('family', 'notification', 'notification_content'),
('family', 'pushNotification', 'auth_key TEXT');

-- --------------------------------------------------------

--
-- Table structure for table `pma__table_uiprefs`
--

CREATE TABLE `pma__table_uiprefs` (
  `username` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL,
  `db_name` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL,
  `table_name` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL,
  `prefs` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL,
  `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin COMMENT='Tables'' UI preferences';

--
-- Dumping data for table `pma__table_uiprefs`
--

INSERT INTO `pma__table_uiprefs` (`username`, `db_name`, `table_name`, `prefs`, `last_update`) VALUES
('root', 'family', 'account', '{\"sorted_col\":\"`account`.`created_at` DESC\"}', '2023-08-26 18:08:03'),
('root', 'family', 'comment', '{\"sorted_col\":\"`comment`.`date_created`  ASC\"}', '2022-01-18 23:30:24'),
('root', 'family', 'contact', '{\"sorted_col\":\"`contact`.`id` ASC\"}', '2023-09-18 17:10:49'),
('root', 'family', 'events', '{\"sorted_col\":\"`events`.`created_at` DESC\"}', '2023-10-12 21:05:31'),
('root', 'family', 'kids', '{\"sorted_col\":\"`kids`.`created_at` DESC\"}', '2023-08-30 11:43:37'),
('root', 'family', 'otherFamily', '{\"sorted_col\":\"`otherFamily`.`id` ASC\"}', '2023-09-18 17:11:33'),
('root', 'family', 'personal', '{\"CREATE_TIME\":\"2023-08-26 15:18:24\",\"col_order\":[0,1,2,3,5,6,7,4,8,9,10,11,12,13,14],\"col_visib\":[1,1,1,1,1,1,1,1,1,1,1,1,1,1,1]}', '2023-08-30 13:41:47'),
('root', 'family', 'post', '{\"sorted_col\":\"`post`.`date_created` DESC\"}', '2023-08-09 23:47:13'),
('root', 'family', 'siblings', '{\"sorted_col\":\"`siblings`.`created_at` DESC\"}', '2023-08-26 18:15:05'),
('root', 'family', 'work', '{\"sorted_col\":\"`work`.`created_at` DESC\"}', '2023-08-27 12:01:56');

-- --------------------------------------------------------

--
-- Table structure for table `pma__tracking`
--

CREATE TABLE `pma__tracking` (
  `db_name` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL,
  `table_name` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL,
  `version` int UNSIGNED NOT NULL,
  `date_created` datetime NOT NULL,
  `date_updated` datetime NOT NULL,
  `schema_snapshot` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL,
  `schema_sql` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin,
  `data_sql` longtext CHARACTER SET utf8mb3 COLLATE utf8mb3_bin,
  `tracking` set('UPDATE','REPLACE','INSERT','DELETE','TRUNCATE','CREATE DATABASE','ALTER DATABASE','DROP DATABASE','CREATE TABLE','ALTER TABLE','RENAME TABLE','DROP TABLE','CREATE INDEX','DROP INDEX','CREATE VIEW','ALTER VIEW','DROP VIEW') CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `tracking_active` int UNSIGNED NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin COMMENT='Database changes tracking for phpMyAdmin';

-- --------------------------------------------------------

--
-- Table structure for table `pma__userconfig`
--

CREATE TABLE `pma__userconfig` (
  `username` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL,
  `timevalue` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `config_data` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin COMMENT='User preferences storage for phpMyAdmin';

--
-- Dumping data for table `pma__userconfig`
--

INSERT INTO `pma__userconfig` (`username`, `timevalue`, `config_data`) VALUES
('root', '2024-09-16 15:37:43', '{\"Console\\/Mode\":\"collapse\",\"NavigationWidth\":221}');

-- --------------------------------------------------------

--
-- Table structure for table `pma__usergroups`
--

CREATE TABLE `pma__usergroups` (
  `usergroup` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL,
  `tab` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL,
  `allowed` enum('Y','N') CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin COMMENT='User groups with configured menu items';

-- --------------------------------------------------------

--
-- Table structure for table `pma__users`
--

CREATE TABLE `pma__users` (
  `username` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL,
  `usergroup` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin COMMENT='Users and their assignments to user groups';

-- --------------------------------------------------------

--
-- Table structure for table `post`
--

CREATE TABLE `post` (
  `post_no` int NOT NULL,
  `id` varchar(255) DEFAULT NULL,
  `fullName` text,
  `postMessage` varchar(255) DEFAULT NULL,
  `post_likes` int DEFAULT '0',
  `profileImg` text,
  `post_img0` text,
  `post_img1` text,
  `post_img2` text,
  `post_img3` text,
  `post_img4` text,
  `post_img5` text,
  `post_time` varchar(50) DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_updated` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `date_deleted` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `post`
--

INSERT INTO `post` (`post_no`, `id`, `fullName`, `postMessage`, `post_likes`, `profileImg`, `post_img0`, `post_img1`, `post_img2`, `post_img3`, `post_img4`, `post_img5`, `post_time`, `date_created`, `date_updated`, `date_deleted`) VALUES
(2, '117540OLAWALE', 'OLAWALE', 'Hey, welcome to your page', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2021-02-15 23:39:16', NULL, NULL),
(3, '870016OLAWALE', 'OLAWALE', 'Hey, welcome to your page', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2021-02-15 23:42:31', '2021-04-12 14:09:01', '2021-04-12 14:09:01'),
(4, '397755OLUSOLA', 'OLUSOLA', 'Hey, welcome to your page', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2021-02-15 23:49:10', '2021-04-12 14:08:58', '2021-04-12 14:08:58'),
(6, '117540OLAWALE', 'OLAWALE OLAOGUN', 'Am doing great', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1613593024', '2021-02-17 20:17:16', '2021-04-12 14:08:50', '2021-04-12 14:08:50'),
(7, '117540OLAWALE', 'SEGUN OLAOGUN', 'Interesting job', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1613594915', '2021-02-17 20:49:21', '2021-04-12 14:09:11', '2021-04-12 14:09:11'),
(8, '117540OLAWALE', 'SEGUN OLAOGUN', 'when are we paying our next tuition fees ', 0, NULL, 'SAM_0795.JPG', NULL, NULL, NULL, NULL, NULL, '1613595653', '2021-02-17 21:01:33', NULL, NULL),
(9, '870016OLAWALE', 'ADESOJI OLAOGUN', 'Checking', 0, NULL, NULL, 'WhatsApp Image 2021-01-24 at 14.01.44 (1).jpeg', NULL, NULL, NULL, NULL, '1613596845', '2021-02-17 21:22:21', '2021-04-12 14:09:07', '2021-04-12 14:09:07'),
(10, '870016OLAWALE', 'ADESOJI OLAOGUN', 'this is my pics', 0, NULL, 'shoSojWal4.jpeg', 'ShoSojWal3.jpeg', 'shoSojWal2.jpeg', NULL, NULL, NULL, '1613597963', '2021-02-17 21:39:46', NULL, NULL),
(11, '117540OLAWALE', 'SEGUN OLAOGUN', 'Barquet is watching for the arrow to point in the right place', 0, NULL, 'FembaNSoj.jpeg', 'shoSojWal1.jpeg', NULL, NULL, NULL, NULL, '1613687717', '2021-02-18 23:00:32', NULL, NULL),
(12, '117540OLAWALE', 'SEGUN OLAOGUN', 'let it go ', 0, NULL, 'Jibs.jpeg', 'FembaNSoj.jpeg', 'shoSojWal1.jpeg', NULL, NULL, NULL, '1613692179', '2021-02-19 00:01:24', NULL, NULL),
(13, '117540OLAWALE', 'SEGUN OLAOGUN', 'Am glad the upload is a success', 0, NULL, 'allthefamily2.jpeg', 'allthefamily.jpeg', 'thebrother2.jpeg', NULL, NULL, NULL, '1613692884', '2021-02-19 00:06:06', NULL, NULL),
(14, '117540OLAWALE', 'SEGUN OLAOGUN', 'still testing', 0, NULL, 'sojandfemba2.jpeg', 'FembaShoSojWale.jpeg', NULL, NULL, NULL, NULL, '1613693166', '2021-02-19 00:06:54', NULL, NULL),
(15, '117540OLAWALE', 'SEGUN OLAOGUN', 'Post here', 0, NULL, 'family1.jpeg', 'family2.jpeg', NULL, NULL, NULL, NULL, '1613693375', '2021-02-19 00:29:57', NULL, NULL),
(16, '117540OLAWALE', 'SEGUN OLAOGUN', 'it is heavy', 0, NULL, 'family3.jpeg', 'family4.jpeg', NULL, NULL, NULL, NULL, '1613694598', '2021-02-19 00:31:01', NULL, NULL),
(17, '117540OLAWALE', 'SEGUN OLAOGUN', 'Post it here now', 0, NULL, 'ShoSoj1.jpeg', 'ShoSoj2.jpeg', NULL, NULL, NULL, NULL, '1613694661', '2021-02-19 01:05:29', NULL, NULL),
(18, '117540OLAWALE', 'SEGUN OLAOGUN', 'Post here', 0, NULL, 'ShoSoj2.jpeg', 'ShoSoj3.jpeg', NULL, NULL, NULL, NULL, '1613696809', '2021-02-19 01:07:43', NULL, NULL),
(19, '117540OLAWALE', 'SEGUN OLAOGUN', 'Post here', 0, NULL, 'ShoSoj2.jpeg', 'ShoSoj3.jpeg', NULL, NULL, NULL, NULL, '1613696863', '2021-02-19 01:10:23', NULL, NULL),
(20, '117540OLAWALE', 'SEGUN OLAOGUN', 'Post here', 0, NULL, 'family4.jpeg', 'ShoSoj1.jpeg', 'ShoSoj2.jpeg', NULL, NULL, NULL, '1613697202', '2021-02-19 01:13:35', NULL, NULL),
(21, '117540OLAWALE', 'SEGUN OLAOGUN', 'Working up the chair', 0, NULL, 'shoSoj4.jpeg', 'shoSoj5.jpeg', NULL, NULL, NULL, NULL, '1613697215', '2021-02-19 01:15:31', NULL, NULL),
(22, '117540OLAWALE', 'SEGUN OLAOGUN', 'Sho and Soj arrived today', 0, NULL, 'ShoSoj6.jpeg', 'ShoSoj7.jpeg', 'ShoSoj8.jpeg', 'ShoSoj9.jpeg', 'ShoSoj10.jpeg', NULL, '1613697332', '2021-02-19 01:17:16', NULL, NULL),
(23, '117540OLAWALE', 'SEGUN OLAOGUN', 'It is a beautiful day', 0, NULL, 'ShoSoj12.jpeg', 'ShoSoj13.jpeg', 'ShoSoj14.jpeg', NULL, NULL, NULL, '1613730090', '2021-02-19 10:22:56', NULL, NULL),
(24, '117540OLAWALE', 'SEGUN OLAOGUN', 'Post herevjukvukv', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1616784643', '2021-03-26 18:52:44', NULL, NULL),
(25, '117540OLAWALE', 'SEGUN OLAOGUN', 'Post here', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1616785173', '2021-03-26 19:00:18', NULL, NULL),
(31, '870016OLAWALE', 'ADESOJI OLAOGUN', 'Am Adesoji ', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1618274403', '2021-04-13 00:40:26', NULL, NULL),
(32, '870016OLAWALE', 'ADESOJI OLAOGUN', 'Wow  it is working', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1618315590', '2021-04-13 12:06:56', NULL, NULL),
(33, '117540OLAWALE', 'SEGUN OLAOGUN', 'OLUTOBI IS WORKING HARD', 0, NULL, 'tobs.jpeg', NULL, NULL, NULL, NULL, NULL, '1618316419', '2021-04-13 12:21:24', NULL, NULL),
(84, '117540OLAWALE', 'SEGUN OLAOGUN', 'Post here', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1621350107', '2021-05-18 15:08:31', NULL, NULL),
(85, '117540OLAWALE', 'SEGUN OLAOGUN', 'Post here', 13, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1621350107', '2021-05-18 15:08:54', '2021-05-20 09:37:07', '2021-05-20 09:37:07'),
(86, '117540OLAWALE', 'SEGUN OLAOGUN', 'Hey Soj is learning how to use Mac', 72, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1621350534', '2021-05-18 15:09:15', '2021-05-18 19:15:26', '2021-05-18 19:15:26'),
(87, '117540OLAWALE', 'SEGUN OLAOGUN', 'Baba Ijesha is a crook', 42, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1621350555', '2021-05-18 15:33:38', '2021-05-24 17:54:34', '2021-05-24 17:54:34'),
(88, '117540OLAWALE', 'SEGUN OLAOGUN', 'My daughter olajumoke finishes school today', 11, NULL, '20140902_063945.jpg', NULL, NULL, NULL, NULL, NULL, '1621503422', '2021-05-20 09:48:24', '2021-05-24 11:57:24', '2021-05-24 11:57:24'),
(89, '117540OLAWALE', 'SEGUN OLAOGUN', 'It is going to be fine', 1, NULL, 'images.jpeg', NULL, NULL, NULL, NULL, NULL, '1621858247', '2021-05-24 12:11:10', '2021-05-24 12:11:13', '2021-05-24 12:11:13'),
(90, '117540OLAWALE', 'SEGUN OLAOGUN', 'Am testing multiple pics', 8, NULL, 'imageresizer.jpeg', 'Former_Courage_Brewery,_Bristol_-_geograph.org.uk_-_182194.jpg', NULL, NULL, NULL, NULL, '1621863127', '2021-05-24 13:32:36', '2021-06-03 13:14:11', '2021-06-03 13:14:11'),
(326, '117540OLAWALE', 'SEGUN OLAOGUN', 'Post here', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1623282405', '2021-06-09 23:48:38', NULL, NULL),
(327, '117540OLAWALE', 'SEGUN OLAOGUN', 'Post here now', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1623282532', '2021-06-09 23:49:02', NULL, NULL),
(328, '117540OLAWALE', 'SEGUN OLAOGUN', 'Post here now', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1623282532', '2021-06-09 23:49:03', NULL, NULL),
(329, '117540OLAWALE', 'SEGUN OLAOGUN', 'Post herejjj', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1623282549', '2021-06-09 23:49:25', NULL, NULL),
(330, '117540OLAWALE', 'SEGUN OLAOGUN', 'love it', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1623283125', '2021-06-09 23:59:13', NULL, NULL),
(331, '117540OLAWALE', 'SEGUN OLAOGUN', 'Post here', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1623284080', '2021-06-10 00:15:40', NULL, NULL),
(332, '117540OLAWALE', 'SEGUN OLAOGUN', 'I think its working somwhow', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1623284080', '2021-06-10 00:15:53', '2021-06-14 23:32:00', '2021-06-14 23:32:00'),
(333, '117540OLAWALE', 'SEGUN OLAOGUN', 'You think so....', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1623284167', '2021-06-10 00:16:25', NULL, NULL),
(334, '117540OLAWALE', 'SEGUN OLAOGUN', 'Am not sure', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1623284167', '2021-06-10 00:16:36', NULL, NULL),
(335, '397755OLUSOLA', 'FEMI OLAOGUN', 'Well  it is working as expected', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1623284166', '2021-06-10 00:16:53', NULL, NULL),
(336, '397755OLUSOLA', 'FEMI OLAOGUN', 'But is true', 11, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1623284275', '2021-06-10 00:18:05', '2021-06-10 00:20:25', '2021-06-10 00:20:25'),
(337, '117540OLAWALE', 'SEGUN OLAOGUN', 'I dont think so', 14, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1623284286', '2021-06-10 00:18:18', '2021-06-10 00:20:13', '2021-06-10 00:20:13'),
(338, '397755OLUSOLA', 'FEMI OLAOGUN', 'Exactly', 0, NULL, 'WhatsApp Image 2021-04-17 at 17.20.52.jpeg', NULL, NULL, NULL, NULL, NULL, '1623284285', '2021-06-10 00:21:24', NULL, NULL),
(339, '117540OLAWALE', 'SEGUN OLAOGUN', 'Post here', 0, NULL, 'ShoTakingSelfy.jpeg', NULL, NULL, NULL, NULL, NULL, '1623284525', '2021-06-10 00:22:05', NULL, NULL),
(340, '117540OLAWALE', 'SEGUN OLAOGUN', 'is it working', 0, NULL, 'ShoSojWal3.jpeg', NULL, NULL, NULL, NULL, NULL, '1623284559', '2021-06-10 00:22:39', NULL, NULL),
(341, '117540OLAWALE', 'SEGUN OLAOGUN', 'Post here', 0, NULL, 'WhatsApp Image 2021-01-24 at 12.00.04 (10).jpeg', NULL, NULL, NULL, NULL, NULL, '1623284559', '2021-06-10 00:23:29', NULL, NULL),
(342, '117540OLAWALE', 'SEGUN OLAOGUN', 'Post here', 0, NULL, 'PASSPORT.jpeg', NULL, NULL, NULL, NULL, NULL, '1623284637', '2021-06-10 00:23:57', NULL, NULL),
(343, '397755OLUSOLA', 'FEMI OLAOGUN', 'No be me ooooo', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1623371794', '2021-06-11 00:38:23', NULL, NULL),
(344, '117540OLAWALE', 'SEGUN OLAOGUN', 'Post here', 0, NULL, 'ShoSojWal3.jpeg', 'PASSPORT.jpeg', NULL, NULL, NULL, NULL, '1623431565', '2021-06-11 17:12:51', '2021-06-12 13:19:27', '2021-06-12 13:19:27'),
(345, '117540OLAWALE', 'SEGUN OLAOGUN', 'Is it real', 0, NULL, 'PASSPORT.jpeg', 'ShoSojWal3.jpeg', NULL, NULL, NULL, NULL, '1623431667', '2021-06-11 17:14:49', '2021-06-12 13:19:18', '2021-06-12 13:19:18'),
(346, '117540OLAWALE', 'SEGUN OLAOGUN', 'Nothing can change this', 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1623668815', '2021-06-14 12:23:13', '2021-06-14 14:04:11', '2021-06-14 14:04:11'),
(347, '117540OLAWALE', 'SEGUN OLAOGUN', 'Post here again and changed now', 6, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1623679447', '2021-06-14 14:04:29', '2021-06-14 15:56:48', '2021-06-14 15:56:48'),
(350, '117540OLAWALE', 'SEGUN OLAOGUN', 'We love it', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1623764071', '2021-06-15 13:34:31', NULL, NULL),
(351, '117540OLAWALE', 'SEGUN OLAOGUN', 'Not applied', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1623764159', '2021-06-15 13:36:23', NULL, NULL),
(353, '117540OLAWALE', 'SEGUN OLAOGUN', 'God is great ', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1623766326', '2021-06-15 14:12:29', NULL, NULL),
(354, '397755OLUSOLA', 'FEMI OLAOGUN', 'Are you sure', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1623766414', '2021-06-15 14:13:50', NULL, NULL),
(355, '117540OLAWALE', 'SEGUN OLAOGUN', 'Post', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1623766462', '2021-06-15 14:14:40', NULL, NULL),
(356, '117540OLAWALE', 'SEGUN OLAOGUN', 'Interesting', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1623766567', '2021-06-15 14:16:23', NULL, NULL),
(358, '397755OLUSOLA', 'FEMI OLAOGUN', 'exciting over here and it is sunny', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1623767277', '2021-06-15 14:28:18', NULL, NULL),
(359, '117540OLAWALE', 'SEGUN OLAOGUN', 'I agree with everything you have said', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1623767244', '2021-06-15 14:28:46', NULL, NULL),
(360, '397755OLUSOLA', 'FEMI OLAOGUN', 'Enjoying it to the fullest', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1623767298', '2021-06-15 15:41:56', NULL, NULL),
(368, '117540OLAWALE', 'SEGUN OLAOGUN', 'WHAT AN AMATEUAR', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1623852633', '2021-06-16 14:16:15', NULL, NULL),
(369, '117540OLAWALE', 'SEGUN OLAOGUN', 'Post here for the grestet time and advantage', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1623853759', '2021-06-16 14:29:33', NULL, NULL),
(370, '117540OLAWALE', 'SEGUN OLAOGUN', 'Am enjoying the sun', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1623854613', '2021-06-16 14:43:50', NULL, NULL),
(371, '117540OLAWALE', 'SEGUN OLAOGUN', 'Are you sure', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1623854633', '2021-06-16 14:44:15', NULL, NULL),
(372, '117540OLAWALE', 'SEGUN OLAOGUN', 'Am definitely sure', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1623854685', '2021-06-16 14:45:01', NULL, NULL),
(373, '117540OLAWALE', 'SEGUN OLAOGUN', 'Post here -  You look beautoful', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1623854925', '2021-06-16 14:49:00', NULL, NULL),
(374, '117540OLAWALE', 'SEGUN OLAOGUN', 'Post here - AJIBIKE', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1623855278', '2021-06-16 14:54:49', NULL, NULL),
(375, '117540OLAWALE', 'SEGUN OLAOGUN', 'Post here', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1623855309', '2021-06-16 14:55:51', NULL, NULL),
(376, '117540OLAWALE', 'SEGUN OLAOGUN', 'Post here- AND CHECK IT OUT', 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1623857629', '2021-06-16 15:34:11', '2021-06-16 15:35:18', '2021-06-16 15:35:18'),
(377, '117540OLAWALE', 'SEGUN OLAOGUN', 'Post here - interesting', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1623858249', '2021-06-16 15:44:19', NULL, NULL),
(378, '117540OLAWALE', 'SEGUN OLAOGUN', 'Post here - you are doing well', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1623858433', '2021-06-16 15:47:32', NULL, NULL),
(481, '117540OLAWALE', 'SEGUN OLAOGUN', 'Post here', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1624565061959', '2021-06-24 20:04:21', NULL, NULL),
(482, '432292OLAWALE', 'OLAWALE OLAOGUN', 'Post here - engagement', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1624626530428', '2021-06-25 13:08:50', NULL, NULL),
(483, '432292OLAWALE', 'OLAWALE OLAOGUN', 'Post here', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1624626601883', '2021-06-25 13:10:01', NULL, NULL),
(484, '432292OLAWALE', 'OLAWALE OLAOGUN', 'Post here', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1624626664314', '2021-06-25 13:11:04', NULL, NULL),
(485, '432292OLAWALE', 'OLAWALE OLAOGUN', 'Post here', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1624627322744', '2021-06-25 13:22:02', NULL, NULL),
(486, '432292OLAWALE', 'OLAWALE OLAOGUN', 'Post here', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1624627629060', '2021-06-25 13:27:09', NULL, NULL),
(487, '432292OLAWALE', 'OLAWALE OLAOGUN', 'Post here', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1624627645710', '2021-06-25 13:27:25', NULL, NULL),
(488, '432292OLAWALE', 'OLAWALE OLAOGUN', 'Post here', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1624627722685', '2021-06-25 13:28:42', NULL, NULL),
(489, '432292OLAWALE', 'OLAWALE OLAOGUN', 'Post here-  sanitation', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1624628450801', '2021-06-25 13:40:50', NULL, NULL),
(490, '432292OLAWALE', 'OLAWALE OLAOGUN', 'Post here', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1624628513176', '2021-06-25 13:41:53', NULL, NULL),
(491, '432292OLAWALE', 'OLAWALE OLAOGUN', 'Post here', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1624628566727', '2021-06-25 13:42:46', NULL, NULL),
(492, '432292OLAWALE', 'OLAWALE OLAOGUN', 'Post here', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1624628694660', '2021-06-25 13:44:54', NULL, NULL),
(493, '432292OLAWALE', 'OLAWALE OLAOGUN', 'Post here', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1624628721727', '2021-06-25 13:45:21', NULL, NULL),
(494, '432292OLAWALE', 'OLAWALE OLAOGUN', 'Post here', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1624628845320', '2021-06-25 13:47:25', NULL, NULL),
(495, '432292OLAWALE', 'OLAWALE OLAOGUN', 'Post here', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1624628895119', '2021-06-25 13:48:15', NULL, NULL),
(496, '432292OLAWALE', 'OLAWALE OLAOGUN', 'Post here - not sure', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1624628906654', '2021-06-25 13:48:26', NULL, NULL),
(497, '432292OLAWALE', 'OLAWALE OLAOGUN', 'Post heream ', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1624628945466', '2021-06-25 13:49:05', NULL, NULL),
(498, '432292OLAWALE', 'OLAWALE OLAOGUN', 'Post heream - are you leading', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1624628977209', '2021-06-25 13:49:37', NULL, NULL),
(499, '432292OLAWALE', 'OLAWALE OLAOGUN', 'Post here', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1624629305830', '2021-06-25 13:55:05', NULL, NULL),
(500, '432292OLAWALE', 'OLAWALE OLAOGUN', 'Post here-hjjhhjbjbh', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1624629368284', '2021-06-25 13:56:08', NULL, NULL),
(501, '432292OLAWALE', 'OLAWALE OLAOGUN', 'Post here', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1624629422759', '2021-06-25 13:57:02', NULL, NULL),
(502, '432292OLAWALE', 'OLAWALE OLAOGUN', 'Post here', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1624629785705', '2021-06-25 14:03:05', NULL, NULL),
(503, '432292OLAWALE', 'OLAWALE OLAOGUN', 'Post here - how are you', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1624629802014', '2021-06-25 14:03:22', NULL, NULL),
(504, '432292OLAWALE', 'OLAWALE OLAOGUN', 'Post here - afre yo', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1624630098427', '2021-06-25 14:08:18', '2021-06-25 14:29:45', '2021-06-25 14:29:45'),
(505, '432292OLAWALE', 'OLAWALE OLAOGUN', 'just checking', 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1624631228035', '2021-06-25 14:27:08', '2021-06-28 23:53:38', '2021-06-28 23:53:38'),
(506, '432292OLAWALE', 'OLAWALE OLAOGUN', 'Hello everyone  Thanks for the update', 9, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1624885463001', '2021-06-28 13:04:23', '2021-06-28 23:53:31', '2021-06-28 23:53:31'),
(507, '432292OLAWALE', 'OLAWALE OLAOGUN', 'is thar ', 11, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1624885766345', '2021-06-28 13:09:26', '2021-07-05 10:42:27', '2021-07-05 10:42:27'),
(508, '432292OLAWALE', 'OLAWALE OLAOGUN', 'enkoyment', 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625149130779', '2021-07-01 14:18:50', '2021-07-05 10:42:25', '2021-07-05 10:42:25'),
(509, '432292OLAWALE', 'OLAWALE OLAOGUN', 'Police has done well', 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625149763128', '2021-07-01 14:29:23', '2021-07-05 10:42:31', '2021-07-05 10:42:31'),
(510, '432292OLAWALE', 'OLAWALE OLAOGUN', 'Am thinking of doing the same thing', 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625439433572', '2021-07-04 22:57:13', '2021-07-05 10:42:20', '2021-07-05 10:42:20'),
(511, '432292OLAWALE', 'OLAWALE OLAOGUN', 'renegotiate it for us', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625485094861', '2021-07-05 11:38:14', NULL, NULL),
(512, '432292OLAWALE', 'OLAWALE OLAOGUN', '', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625489903796', '2021-07-05 12:58:23', NULL, NULL),
(513, '432292OLAWALE', 'OLAWALE OLAOGUN', '', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625490086613', '2021-07-05 13:01:26', NULL, NULL),
(514, '432292OLAWALE', 'OLAWALE OLAOGUN', 'sos', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625490684671', '2021-07-05 13:11:24', NULL, NULL),
(515, '432292OLAWALE', 'OLAWALE OLAOGUN', 'jjn', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625490916884', '2021-07-05 13:15:16', NULL, NULL),
(516, '432292OLAWALE', 'OLAWALE OLAOGUN', 'enjoyment', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625491072546', '2021-07-05 13:17:52', NULL, NULL),
(517, '432292OLAWALE', 'OLAWALE OLAOGUN', 'start', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625492247733', '2021-07-05 13:37:27', NULL, NULL),
(518, '432292OLAWALE', 'OLAWALE OLAOGUN', 'jjkk', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625492336571', '2021-07-05 13:38:56', NULL, NULL),
(519, '432292OLAWALE', 'OLAWALE OLAOGUN', 'seewew', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625492376469', '2021-07-05 13:39:36', NULL, NULL),
(520, '432292OLAWALE', 'OLAWALE OLAOGUN', 'ddrdfds', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625492421999', '2021-07-05 13:40:22', NULL, NULL),
(521, '432292OLAWALE', 'OLAWALE OLAOGUN', 'am tag', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625492463386', '2021-07-05 13:41:03', NULL, NULL),
(522, '432292OLAWALE', 'OLAWALE OLAOGUN', 'erere', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625492973143', '2021-07-05 13:49:33', NULL, NULL),
(523, '432292OLAWALE', 'OLAWALE OLAOGUN', 'SANITA', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625493158900', '2021-07-05 13:52:38', NULL, NULL),
(524, '432292OLAWALE', 'OLAWALE OLAOGUN', 'AEEQ', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625493323367', '2021-07-05 13:55:23', NULL, NULL),
(525, '432292OLAWALE', 'OLAWALE OLAOGUN', 'AEEQ2', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625493343956', '2021-07-05 13:55:43', NULL, NULL),
(526, '432292OLAWALE', 'OLAWALE OLAOGUN', 'bjjbbnb', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625494123219', '2021-07-05 14:08:43', NULL, NULL),
(527, '432292OLAWALE', 'OLAWALE OLAOGUN', 'bbnbbnnbnb', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625494177842', '2021-07-05 14:09:37', NULL, NULL),
(528, '432292OLAWALE', 'OLAWALE OLAOGUN', 'checking for growth', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625494252880', '2021-07-05 14:10:52', NULL, NULL),
(529, '432292OLAWALE', 'OLAWALE OLAOGUN', 'shocking', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625494359387', '2021-07-05 14:12:39', NULL, NULL),
(530, '432292OLAWALE', 'OLAWALE OLAOGUN', 'shocking - One hundred and fifty thousand', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625510763759', '2021-07-05 18:46:03', NULL, NULL),
(531, '432292OLAWALE', 'OLAWALE OLAOGUN', 'Enjoyment at the very best', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625510808679', '2021-07-05 18:46:48', NULL, NULL),
(532, '432292OLAWALE', 'OLAWALE OLAOGUN', 'In the century ahead and aside of the way', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625510861732', '2021-07-05 18:47:41', NULL, NULL),
(533, '432292OLAWALE', 'OLAWALE OLAOGUN', 'Police at the very best of the time', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625510966448', '2021-07-05 18:49:26', NULL, NULL),
(534, '432292OLAWALE', 'OLAWALE OLAOGUN', 'In the interest of time and sanitation of yours', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625511133687', '2021-07-05 18:52:13', NULL, NULL),
(535, '432292OLAWALE', 'OLAWALE OLAOGUN', 'are u normal ', 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625523243748', '2021-07-05 22:14:03', '2021-07-05 23:36:27', '2021-07-05 23:36:27'),
(536, '432292OLAWALE', 'OLAWALE OLAOGUN', 'NA REAL EDO GIRL', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625567478528', '2021-07-06 10:31:18', '2021-07-06 10:31:59', '2021-07-06 10:31:59'),
(537, '432292OLAWALE', 'OLAWALE OLAOGUN', 'Login taken', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625569153000', '2021-07-06 10:59:13', NULL, NULL),
(538, '432292OLAWALE', 'OLAWALE OLAOGUN', 'Interesting', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625570314423', '2021-07-06 11:18:34', NULL, NULL),
(539, '432292OLAWALE', 'OLAWALE OLAOGUN', 'did you say so', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625646697902', '2021-07-07 08:31:37', NULL, NULL),
(540, '432292OLAWALE', 'OLAWALE OLAOGUN', 'Are you sure ', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625646761372', '2021-07-07 08:32:41', NULL, NULL),
(541, '432292OLAWALE', 'OLAWALE OLAOGUN', 'Are you sure ', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625646769844', '2021-07-07 08:32:49', NULL, NULL),
(542, '432292OLAWALE', 'OLAWALE OLAOGUN', 'I dont think so', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625646786131', '2021-07-07 08:33:06', NULL, NULL),
(543, '432292OLAWALE', 'OLAWALE OLAOGUN', 'Budda', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625665031280', '2021-07-07 13:37:11', NULL, NULL),
(544, '432292OLAWALE', 'OLAWALE OLAOGUN', 'Budda', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625665703637', '2021-07-07 13:48:23', NULL, NULL),
(545, '432292OLAWALE', 'OLAWALE OLAOGUN', 'budd2', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625665712528', '2021-07-07 13:48:32', NULL, NULL),
(546, '432292OLAWALE', 'OLAWALE OLAOGUN', 'budd3', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625665744366', '2021-07-07 13:49:04', NULL, NULL),
(547, '432292OLAWALE', 'OLAWALE OLAOGUN', 'change', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625665909132', '2021-07-07 13:51:49', NULL, NULL),
(548, '432292OLAWALE', 'OLAWALE OLAOGUN', 'do it again', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625665950113', '2021-07-07 13:52:30', '2021-07-07 13:52:57', '2021-07-07 13:52:57'),
(549, '432292OLAWALE', 'OLAWALE OLAOGUN', 'Is your wife', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625666075814', '2021-07-07 13:54:35', NULL, NULL),
(550, '432292OLAWALE', 'OLAWALE OLAOGUN', 'Is your wife going to the carnival', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625666088828', '2021-07-07 13:54:48', NULL, NULL),
(551, '432292OLAWALE', 'OLAWALE OLAOGUN', 'They are all wonderful', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625666101997', '2021-07-07 13:55:01', NULL, NULL),
(552, '432292OLAWALE', 'OLAWALE OLAOGUN', 'I thought everything has been swept away  ', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625666438639', '2021-07-07 14:00:38', NULL, NULL),
(553, '432292OLAWALE', 'OLAWALE OLAOGUN', 'DONT EMPHASISE WHAT YOU DONT HAVE', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625666631373', '2021-07-07 14:03:51', NULL, NULL),
(554, '432292OLAWALE', 'OLAWALE OLAOGUN', 'are you okay james', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625669200028', '2021-07-07 14:46:40', NULL, NULL),
(555, '432292OLAWALE', 'OLAWALE OLAOGUN', 'enjoyment as it happens', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625669504129', '2021-07-07 14:51:44', NULL, NULL),
(556, '432292OLAWALE', 'OLAWALE OLAOGUN', 'enjoyment as it happens2', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625669565799', '2021-07-07 14:52:45', NULL, NULL),
(557, '432292OLAWALE', 'OLAWALE OLAOGUN', 'she became sad', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625669710167', '2021-07-07 14:55:10', NULL, NULL),
(558, '432292OLAWALE', 'OLAWALE OLAOGUN', 'they dont upset me anymore', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625669763620', '2021-07-07 14:56:03', NULL, NULL),
(559, '432292OLAWALE', 'OLAWALE OLAOGUN', 'silent', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625673002842', '2021-07-07 15:50:02', NULL, NULL),
(560, '432292OLAWALE', 'OLAWALE OLAOGUN', 'are you sure', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625675795256', '2021-07-07 16:36:35', NULL, NULL),
(561, '432292OLAWALE', 'OLAWALE OLAOGUN', 'do you know', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625675960431', '2021-07-07 16:39:20', NULL, NULL),
(562, '432292OLAWALE', 'OLAWALE OLAOGUN', 'I no know you', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625676053255', '2021-07-07 16:40:53', NULL, NULL),
(563, '432292OLAWALE', 'OLAWALE OLAOGUN', 'hahaha', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625676216015', '2021-07-07 16:43:36', NULL, NULL),
(564, '432292OLAWALE', 'OLAWALE OLAOGUN', 'then it worked', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625676278802', '2021-07-07 16:44:38', NULL, NULL),
(565, '432292OLAWALE', 'OLAWALE OLAOGUN', 'are you sure', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625676663284', '2021-07-07 16:51:03', NULL, NULL),
(566, '432292OLAWALE', 'OLAWALE OLAOGUN', 'Option', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625687347174', '2021-07-07 19:49:07', NULL, NULL),
(567, '432292OLAWALE', 'OLAWALE OLAOGUN', 'innet', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625687365620', '2021-07-07 19:49:25', NULL, NULL),
(568, '432292OLAWALE', 'OLAWALE OLAOGUN', 'hhhjhhjj', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625688165973', '2021-07-07 20:02:45', NULL, NULL),
(569, '432292OLAWALE', 'OLAWALE OLAOGUN', 'police', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625688382974', '2021-07-07 20:06:22', NULL, NULL),
(570, '432292OLAWALE', 'OLAWALE OLAOGUN', 'jkbjl.bhbh', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625691546876', '2021-07-07 20:59:06', NULL, NULL),
(571, '432292OLAWALE', 'OLAWALE OLAOGUN', 'toxic', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625703591847', '2021-07-08 00:19:51', NULL, NULL),
(572, '432292OLAWALE', 'OLAWALE OLAOGUN', 'everything is fine', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625703682337', '2021-07-08 00:21:22', NULL, NULL),
(573, '432292OLAWALE', 'OLAWALE OLAOGUN', 'explode with rage with emotion', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625703820421', '2021-07-08 00:23:40', NULL, NULL),
(574, '432292OLAWALE', 'OLAWALE OLAOGUN', 'energy', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625703865858', '2021-07-08 00:24:25', NULL, NULL),
(575, '432292OLAWALE', 'OLAWALE OLAOGUN', 'we are still', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625704170576', '2021-07-08 00:29:30', NULL, NULL),
(576, '117540OLAWALE', 'SEGUN OLAOGUN', 'we are still', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625704191954', '2021-07-08 00:29:51', NULL, NULL),
(577, '432292OLAWALE', 'OLAWALE OLAOGUN', 'God is good', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625704359854', '2021-07-08 00:32:39', NULL, NULL),
(578, '432292OLAWALE', 'OLAWALE OLAOGUN', 'God is always good', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625705156760', '2021-07-08 00:45:56', NULL, NULL),
(579, '432292OLAWALE', 'OLAWALE OLAOGUN', 'did it happen', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625706102592', '2021-07-08 01:01:42', NULL, NULL),
(580, '432292OLAWALE', 'OLAWALE OLAOGUN', 'intere', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625706475606', '2021-07-08 01:07:55', NULL, NULL),
(581, '117540OLAWALE', 'SEGUN OLAOGUN', 'dfdfd', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625706512403', '2021-07-08 01:08:32', NULL, NULL),
(582, '117540OLAWALE', 'SEGUN OLAOGUN', 'cvcvcbbc', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625706529475', '2021-07-08 01:08:49', NULL, NULL),
(583, '432292OLAWALE', 'OLAWALE OLAOGUN', 'cvcvc', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625706543095', '2021-07-08 01:09:03', NULL, NULL),
(584, '432292OLAWALE', 'OLAWALE OLAOGUN', 'nn', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625706673722', '2021-07-08 01:11:13', NULL, NULL),
(585, '432292OLAWALE', 'OLAWALE OLAOGUN', 'DANGEROUS', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625706917694', '2021-07-08 01:15:17', NULL, NULL),
(586, '432292OLAWALE', 'OLAWALE OLAOGUN', 'shsjsis', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625710627546', '2021-07-08 02:17:07', NULL, NULL),
(587, '117540OLAWALE', 'SEGUN OLAOGUN', 'susr', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625710649336', '2021-07-08 02:17:29', NULL, NULL),
(588, '432292OLAWALE', 'OLAWALE OLAOGUN', 'fear', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625757118124', '2021-07-08 15:11:58', NULL, NULL),
(589, '432292OLAWALE', 'OLAWALE OLAOGUN', 'adeolu', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625766638291', '2021-07-08 17:50:38', NULL, NULL),
(590, '432292OLAWALE', 'OLAWALE OLAOGUN', 'CHECKING', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625767137476', '2021-07-08 17:58:57', NULL, NULL),
(591, '117540OLAWALE', 'SEGUN OLAOGUN', 'ARE THEY UP', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625767280009', '2021-07-08 18:01:20', '2021-07-08 22:24:53', '2021-07-08 22:24:53'),
(592, '432292OLAWALE', 'OLAWALE OLAOGUN', 'LOOKING UP', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625767409569', '2021-07-08 18:03:29', NULL, NULL),
(593, '117540OLAWALE', 'SEGUN OLAOGUN', 'sure', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625767549243', '2021-07-08 18:05:49', NULL, NULL),
(594, '432292OLAWALE', 'OLAWALE OLAOGUN', 'they look up', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625783252818', '2021-07-08 22:27:32', NULL, NULL),
(595, '117540OLAWALE', 'SEGUN OLAOGUN', 'they never know', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625783324922', '2021-07-08 22:28:44', NULL, NULL),
(596, '432292OLAWALE', 'OLAWALE OLAOGUN', 'they look up - are you sure', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625783364039', '2021-07-08 22:29:24', NULL, NULL),
(597, '117540OLAWALE', 'SEGUN OLAOGUN', 'they never know - Am so so sure', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625783389709', '2021-07-08 22:29:49', NULL, NULL),
(598, '432292OLAWALE', 'OLAWALE OLAOGUN', 'Alesa - micheal jackson', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625783477530', '2021-07-08 22:31:17', NULL, NULL),
(599, '117540OLAWALE', 'SEGUN OLAOGUN', 'It is very interesting', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625783508502', '2021-07-08 22:31:48', NULL, NULL),
(600, '432292OLAWALE', 'OLAWALE OLAOGUN', 'checking for success', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625785177224', '2021-07-08 22:59:37', NULL, NULL),
(601, '432292OLAWALE', 'OLAWALE OLAOGUN', 'lets see the result', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625785261285', '2021-07-08 23:01:01', NULL, NULL),
(602, '432292OLAWALE', 'OLAWALE OLAOGUN', 'generating report', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625785438353', '2021-07-08 23:03:58', NULL, NULL),
(603, '432292OLAWALE', 'OLAWALE OLAOGUN', 'jjj', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625787127681', '2021-07-08 23:32:07', NULL, NULL),
(604, '432292OLAWALE', 'OLAWALE OLAOGUN', 'jjj', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625787275570', '2021-07-08 23:34:35', NULL, NULL),
(605, '432292OLAWALE', 'OLAWALE OLAOGUN', 'kjkkj', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625787289911', '2021-07-08 23:34:49', NULL, NULL),
(606, '432292OLAWALE', 'OLAWALE OLAOGUN', 'kkkl', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625787346212', '2021-07-08 23:35:46', NULL, NULL),
(607, '432292OLAWALE', 'OLAWALE OLAOGUN', 'sdsds', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625787433634', '2021-07-08 23:37:13', NULL, NULL),
(608, '432292OLAWALE', 'OLAWALE OLAOGUN', 'feerrww', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625787445060', '2021-07-08 23:37:25', NULL, NULL),
(609, '432292OLAWALE', 'OLAWALE OLAOGUN', 'feerrww', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625787480205', '2021-07-08 23:38:00', NULL, NULL),
(610, '432292OLAWALE', 'OLAWALE OLAOGUN', 'frtrt', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625787508796', '2021-07-08 23:38:28', NULL, NULL),
(611, '432292OLAWALE', 'OLAWALE OLAOGUN', 'am giving evrything', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625787577436', '2021-07-08 23:39:37', NULL, NULL),
(612, '432292OLAWALE', 'OLAWALE OLAOGUN', 'nnm', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625787636883', '2021-07-08 23:40:36', NULL, NULL),
(613, '432292OLAWALE', 'OLAWALE OLAOGUN', 'nnn', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625787735449', '2021-07-08 23:42:15', NULL, NULL),
(614, '432292OLAWALE', 'OLAWALE OLAOGUN', 'kk', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625787850508', '2021-07-08 23:44:10', NULL, NULL),
(615, '432292OLAWALE', 'OLAWALE OLAOGUN', 'poloplpp', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625788312387', '2021-07-08 23:51:52', NULL, NULL),
(616, '432292OLAWALE', 'OLAWALE OLAOGUN', 'polpol', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625788404758', '2021-07-08 23:53:24', NULL, NULL),
(617, '432292OLAWALE', 'OLAWALE OLAOGUN', 'sani', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1625788438381', '2021-07-08 23:53:58', NULL, NULL),
(618, '117540OLAWALE', 'SEGUN OLAOGUN', 'REALLY GOOD', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1626003696190', '2021-07-11 11:41:36', NULL, NULL),
(619, '117540OLAWALE', 'SEGUN OLAOGUN', 'do', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1626010773798', '2021-07-11 13:39:33', NULL, NULL),
(620, '432292OLAWALE', 'OLAWALE OLAOGUN', 'do2', 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1626010815016', '2021-07-11 13:40:15', '2021-07-11 15:04:06', '2021-07-11 15:04:06'),
(621, '117540OLAWALE', 'SEGUN OLAOGUN', 'intere', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1626016401829', '2021-07-11 15:13:21', NULL, NULL),
(622, '432292OLAWALE', 'OLAWALE OLAOGUN', 'Free interest period', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1626347931379', '2021-07-15 11:18:51', NULL, NULL),
(623, '432292OLAWALE', 'OLAWALE OLAOGUN', 'Free interest period in the universe and the instillation', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1626358048360', '2021-07-15 14:07:28', NULL, NULL),
(624, '432292OLAWALE', 'OLAWALE OLAOGUN', 'It is my birthday today', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1626359569448', '2021-07-15 14:32:49', NULL, NULL),
(625, '432292OLAWALE', 'OLAWALE OLAOGUN', 'Anything should work', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1626364688440', '2021-07-15 15:58:08', NULL, NULL),
(626, '432292OLAWALE', 'OLAWALE OLAOGUN', 'anything should work', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1626364726944', '2021-07-15 15:58:46', NULL, NULL),
(627, '432292OLAWALE', 'OLAWALE OLAOGUN', 'But are you sure', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1626364748117', '2021-07-15 15:59:08', NULL, NULL),
(628, '432292OLAWALE', 'OLAWALE OLAOGUN', 'INTERESTING', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1626364934968', '2021-07-15 16:02:14', NULL, NULL),
(629, '432292OLAWALE', 'OLAWALE OLAOGUN', 'INTERESTING22', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1626364952647', '2021-07-15 16:02:32', NULL, NULL),
(630, '117540OLAWALE', 'SEGUN OLAOGUN', 'ARE YOU SURE ', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1626365056059', '2021-07-15 16:04:16', NULL, NULL),
(631, '117540OLAWALE', 'SEGUN OLAOGUN', 'Then  did it work out', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1626366562411', '2021-07-15 16:29:22', NULL, NULL),
(632, '117540OLAWALE', 'SEGUN OLAOGUN', 'one weekend in a month', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1626366675624', '2021-07-15 16:31:15', NULL, NULL),
(633, '117540OLAWALE', 'SEGUN OLAOGUN', 'one weekend in a month2', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1626366688391', '2021-07-15 16:31:28', NULL, NULL),
(634, '432292OLAWALE', 'OLAWALE OLAOGUN', 'are they thinking straight', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1626367199260', '2021-07-15 16:39:59', NULL, NULL),
(635, '117540OLAWALE', 'SEGUN OLAOGUN', 'jhhjjhjh', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1628199764639', '2021-08-05 21:42:44', '2021-08-05 23:04:44', '2021-08-05 23:04:44'),
(636, '117540OLAWALE', 'SEGUN OLAOGUN', 'Interesting days ahead', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1628200544312', '2021-08-05 21:55:44', NULL, NULL),
(637, '432292OLAWALE', 'OLAWALE OLAOGUN', 'It is interesting and good', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1628546355177', '2021-08-09 21:59:15', '2021-09-22 00:02:37', '2021-09-22 00:02:37'),
(638, '870016OLAWALE', 'ADESOJI OLAOGUN', 'Yes  I agree with you', 6, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1628546374273', '2021-08-09 21:59:34', '2021-10-29 01:26:22', '2021-10-29 01:26:22'),
(639, '929019DAYO', 'Dayo', 'Hey, welcome to your page', 0, '/avatar/avatarF.png', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2021-09-21 22:39:56', NULL, NULL),
(640, '870016OLAWALE', 'ADESOJI OLAOGUN', 'I just want you to pass so that things can work well', 16, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1635461850463', '2021-10-28 22:57:30', '2021-10-29 02:18:55', '2021-10-29 02:18:55'),
(642, '117540OLAWALE', 'SEGUN OLAOGUN', 'No Gist Connect', 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1639842156917', '2021-12-18 15:42:36', '2021-12-18 17:38:02', '2021-12-18 17:38:02'),
(643, '117540OLAWALE', 'SEGUN OLAOGUN', 'do you think it will work ', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1641498700323', '2022-01-06 19:51:40', NULL, NULL),
(644, '117540OLAWALE', 'SEGUN OLAOGUN', 'Hey everyone', 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1641927065607', '2022-01-11 18:51:05', '2022-01-19 00:13:13', '2022-01-19 00:13:13'),
(645, '117540OLAWALE', 'SEGUN OLAOGUN', 'What a wonderful day ', 1, NULL, 'WhatsApp Image 2021-10-03 at 15.11.59.jpeg', NULL, NULL, NULL, NULL, NULL, '1642637207815', '2022-01-20 00:06:47', '2022-01-20 00:06:53', '2022-01-20 00:06:53'),
(646, '117540OLAWALE', 'SEGUN OLAOGUN', 'I\'m enjoying the moment', 0, NULL, 'WhatsApp Image 2021-07-03 at 17.46.54.jpeg', NULL, NULL, NULL, NULL, NULL, '1642637568345', '2022-01-20 00:12:48', NULL, NULL),
(647, '397755OLUSOLA', 'FEMI OLAOGUN', 'We are doing just fine', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1643911995792', '2022-02-03 18:13:15', NULL, NULL),
(648, '117540OLAWALE', 'SEGUN OLAOGUN', '', 0, NULL, 'femba.jpeg', NULL, NULL, NULL, NULL, NULL, '1643974955980', '2022-02-04 11:42:35', NULL, NULL),
(649, '117540OLAWALE', 'SEGUN OLAOGUN', 'I think it looks good', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1643975815227', '2022-02-04 11:56:55', NULL, NULL),
(650, '117540OLAWALE', 'SEGUN OLAOGUN', 'Interesting', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1643975935772', '2022-02-04 11:58:55', NULL, NULL),
(651, '117540OLAWALE', 'SEGUN OLAOGUN', 'Getij', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1643979645022', '2022-02-04 13:00:45', NULL, NULL),
(652, '117540OLAWALE', 'SEGUN OLAOGUN', 'sdwdsds', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1643979693939', '2022-02-04 13:01:33', NULL, NULL),
(653, '117540OLAWALE', 'SEGUN OLAOGUN', 'jhhjh', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1643979806582', '2022-02-04 13:03:26', NULL, NULL),
(654, '117540OLAWALE', 'SEGUN OLAOGUN', 'Uncalled for', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1643999605273', '2022-02-04 18:33:25', NULL, NULL),
(655, '117540OLAWALE', 'SEGUN OLAOGUN', 'andrew ', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1644004996006', '2022-02-04 20:03:16', NULL, NULL),
(656, '117540OLAWALE', 'SEGUN OLAOGUN', 'enjoy', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1644005105559', '2022-02-04 20:05:05', NULL, NULL),
(657, '117540OLAWALE', 'SEGUN OLAOGUN', 'still testing it', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1644005163302', '2022-02-04 20:06:03', NULL, NULL),
(658, '117540OLAWALE', 'SEGUN OLAOGUN', 'family', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1644006721454', '2022-02-04 20:32:01', NULL, NULL),
(659, '117540OLAWALE', 'SEGUN OLAOGUN', 'We are ready to win', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1644007163143', '2022-02-04 20:39:23', NULL, NULL),
(660, '117540OLAWALE', 'SEGUN OLAOGUN', 'I am a winner', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1644007245407', '2022-02-04 20:40:45', NULL, NULL),
(661, '117540OLAWALE', 'SEGUN OLAOGUN', 'I am a winner', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1644007319007', '2022-02-04 20:41:59', NULL, NULL),
(662, '117540OLAWALE', 'SEGUN OLAOGUN', 'Are you sure ', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1644007360139', '2022-02-04 20:42:40', NULL, NULL),
(663, '117540OLAWALE', 'SEGUN OLAOGUN', 'Surprise ', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1644007526139', '2022-02-04 20:45:26', NULL, NULL),
(664, '117540OLAWALE', 'SEGUN OLAOGUN', 'John', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1644007640109', '2022-02-04 20:47:20', NULL, NULL),
(665, '117540OLAWALE', 'SEGUN OLAOGUN', 'Interesting', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1644007938889', '2022-02-04 20:52:18', NULL, NULL),
(666, '117540OLAWALE', 'SEGUN OLAOGUN', 'I\'m so excited', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1644155200774', '2022-02-06 13:46:40', NULL, NULL),
(667, '117540OLAWALE', 'SEGUN OLAOGUN', 'Adetunji knows me', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1644157883104', '2022-02-06 14:31:23', NULL, NULL),
(668, '117540OLAWALE', 'SEGUN OLAOGUN', 'Adetunji knows me2', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1644157989327', '2022-02-06 14:33:09', NULL, NULL),
(669, '117540OLAWALE', 'SEGUN OLAOGUN', 'Adetunji knows me3', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1644158034208', '2022-02-06 14:33:54', NULL, NULL),
(670, '117540OLAWALE', 'SEGUN OLAOGUN', 'right now', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1644158572091', '2022-02-06 14:42:52', NULL, NULL),
(671, '117540OLAWALE', 'SEGUN OLAOGUN', 'we are looking', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1644158761735', '2022-02-06 14:46:01', NULL, NULL),
(672, '117540OLAWALE', 'SEGUN OLAOGUN', 'looking ', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1644158791040', '2022-02-06 14:46:31', NULL, NULL),
(673, '117540OLAWALE', 'SEGUN OLAOGUN', 'second', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1644158837175', '2022-02-06 14:47:17', NULL, NULL),
(674, '117540OLAWALE', 'SEGUN OLAOGUN', 'looking at', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1644159186889', '2022-02-06 14:53:06', NULL, NULL),
(675, '117540OLAWALE', 'SEGUN OLAOGUN', 'owo ', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1644159278392', '2022-02-06 14:54:38', NULL, NULL),
(676, '117540OLAWALE', 'SEGUN OLAOGUN', 'chii', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1644159496257', '2022-02-06 14:58:16', NULL, NULL),
(677, '117540OLAWALE', 'SEGUN OLAOGUN', 'thinking', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1644159719289', '2022-02-06 15:01:59', NULL, NULL),
(678, '117540OLAWALE', 'SEGUN OLAOGUN', 'enjoiu', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1644159766171', '2022-02-06 15:02:46', NULL, NULL),
(679, '117540OLAWALE', 'SEGUN OLAOGUN', 'Intersting', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1644159808119', '2022-02-06 15:03:28', NULL, NULL),
(680, '117540OLAWALE', 'SEGUN OLAOGUN', 'boo', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1644159947632', '2022-02-06 15:05:47', NULL, NULL),
(681, '117540OLAWALE', 'SEGUN OLAOGUN', 'boo', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1644160089555', '2022-02-06 15:08:09', NULL, NULL),
(682, '117540OLAWALE', 'SEGUN OLAOGUN', 'boo', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1644160172341', '2022-02-06 15:09:32', NULL, NULL),
(683, '117540OLAWALE', 'SEGUN OLAOGUN', 'boopopop', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1644160207959', '2022-02-06 15:10:07', NULL, NULL),
(684, '117540OLAWALE', 'SEGUN OLAOGUN', 'show', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1644160262555', '2022-02-06 15:11:02', NULL, NULL),
(685, '117540OLAWALE', 'SEGUN OLAOGUN', 'show', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1644160298477', '2022-02-06 15:11:38', NULL, NULL),
(686, '117540OLAWALE', 'SEGUN OLAOGUN', 'show', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1644160402538', '2022-02-06 15:13:22', NULL, NULL),
(687, '117540OLAWALE', 'SEGUN OLAOGUN', 'Taribo', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1644160800144', '2022-02-06 15:20:00', NULL, NULL),
(688, '117540OLAWALE', 'SEGUN OLAOGUN', 'posit', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1644160960358', '2022-02-06 15:22:40', NULL, NULL),
(689, '117540OLAWALE', 'SEGUN OLAOGUN', 'enjo', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1644161147590', '2022-02-06 15:25:47', NULL, NULL),
(690, '117540OLAWALE', 'SEGUN OLAOGUN', 'dfd', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1644161268484', '2022-02-06 15:27:48', NULL, NULL),
(691, '117540OLAWALE', 'SEGUN OLAOGUN', 'erer', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1644161335723', '2022-02-06 15:28:55', NULL, NULL),
(692, '117540OLAWALE', 'SEGUN OLAOGUN', 'sds', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1644161366519', '2022-02-06 15:29:26', NULL, NULL),
(693, '117540OLAWALE', 'SEGUN OLAOGUN', 'polp', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1644161504719', '2022-02-06 15:31:44', NULL, NULL),
(694, '117540OLAWALE', 'SEGUN OLAOGUN', 'Int', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1644161814610', '2022-02-06 15:36:54', NULL, NULL),
(695, '117540OLAWALE', 'SEGUN OLAOGUN', 'inherti', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1644162006141', '2022-02-06 15:40:06', NULL, NULL),
(696, '117540OLAWALE', 'SEGUN OLAOGUN', 'I dont know', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1644162973536', '2022-02-06 15:56:13', NULL, NULL),
(697, '117540OLAWALE', 'SEGUN OLAOGUN', 'i kno', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1644163130559', '2022-02-06 15:58:50', NULL, NULL),
(698, '117540OLAWALE', 'SEGUN OLAOGUN', 'test', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1644163213914', '2022-02-06 16:00:13', NULL, NULL),
(699, '117540OLAWALE', 'SEGUN OLAOGUN', 'hello hello', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1644163946157', '2022-02-06 16:12:26', NULL, NULL),
(700, '117540OLAWALE', 'SEGUN OLAOGUN', 'Interesting', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1644164276141', '2022-02-06 16:17:56', NULL, NULL),
(701, '117540OLAWALE', 'SEGUN OLAOGUN', 'Exactly', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1644164843283', '2022-02-06 16:27:23', NULL, NULL),
(702, '117540OLAWALE', 'SEGUN OLAOGUN', 'Let\'s make', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1644165010780', '2022-02-06 16:30:10', NULL, NULL),
(703, '117540OLAWALE', 'SEGUN OLAOGUN', 'jjhkjk', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1644165890742', '2022-02-06 16:44:50', NULL, NULL),
(704, '117540OLAWALE', 'SEGUN OLAOGUN', 'hone', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1644166223102', '2022-02-06 16:50:23', NULL, NULL),
(705, '117540OLAWALE', 'SEGUN OLAOGUN', 'Testing images', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1644170783414', '2022-02-06 18:06:23', '2022-06-28 23:18:24', '2022-06-28 23:18:24'),
(706, '117540OLAWALE', 'SEGUN OLAOGUN', 'Testing images 2', 2, NULL, 'SAM_1164.JPG', NULL, NULL, NULL, NULL, NULL, '1644170815346', '2022-02-06 18:06:55', '2022-05-10 08:12:53', '2022-05-10 08:12:53'),
(707, '117540OLAWALE', 'SEGUN OLAOGUN', 'the bar is very high', 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1652187588429', '2022-05-10 12:59:48', '2022-06-28 23:18:18', '2022-06-28 23:18:18'),
(708, '117540OLAWALE', 'SEGUN OLAOGUN', 'Hello Hello', 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1657016617154', '2022-07-05 10:23:37', '2022-07-07 23:17:20', '2022-07-07 23:17:20'),
(709, '117540OLAWALE', 'SEGUN OLAOGUN', 'Are you sure you want to see it ', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1657235976304', '2022-07-07 23:19:36', NULL, NULL),
(710, '975782ADETUNJI', 'adetunji', 'Hey, welcome to your page', 0, '/avatar/avatarF.png', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2022-07-21 00:12:34', NULL, NULL),
(711, '936779SHABA', 'Shaba', 'Hey, welcome to your page', 0, '/avatar/avatarF.png', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2022-07-21 00:20:44', NULL, NULL),
(712, '954243OLAOLUWA', 'Olaoluwa', 'Hey, welcome to your page', 0, '/avatar/avatarF.png', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2022-07-21 08:06:51', NULL, NULL),
(713, '117540OLAWALE', 'SEGUN OLAOGUN', 'hiYA', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1662228424626', '2022-09-03 18:07:04', '2022-09-03 18:07:08', '2022-09-03 18:07:08');
INSERT INTO `post` (`post_no`, `id`, `fullName`, `postMessage`, `post_likes`, `profileImg`, `post_img0`, `post_img1`, `post_img2`, `post_img3`, `post_img4`, `post_img5`, `post_time`, `date_created`, `date_updated`, `date_deleted`) VALUES
(714, '117540OLAWALE', 'SEGUN OLAOGUN', 'Never again', 3, NULL, 'Screenshot 2022-08-30 at 17.17.56.png', NULL, NULL, NULL, NULL, NULL, '1662229225480', '2022-09-03 18:20:25', '2023-02-22 23:09:05', '2023-02-22 23:09:05'),
(715, '912189MOMODU', 'MOMODU', 'Hey, welcome to your page', 0, '/avatar/avatarF.png', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2023-02-13 17:14:09', NULL, NULL),
(716, '987764OLAWALE', 'OLAWALE', 'Hey, welcome to your page', 0, '/avatar/avatarF.png', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2023-02-13 17:36:11', NULL, NULL),
(717, '913882OLAWALE', 'Olawale', 'Hey, welcome to your page', 0, '/avatar/avatarF.png', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2023-02-13 17:44:09', NULL, NULL),
(718, '940728BUSSY', 'Bussy', 'Hey, welcome to your page', 0, '/avatar/avatarF.png', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2023-02-14 13:21:11', NULL, NULL),
(719, '942737GBEDUO', 'Gbeduo', 'Hey, welcome to your page', 0, '/avatar/avatarF.png', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2023-02-14 19:36:51', NULL, NULL),
(720, '971778GENERA', 'Genera', 'Hey, welcome to your page', 0, '/avatar/avatarF.png', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2023-02-15 00:41:26', NULL, NULL),
(721, '937619LAFANE', 'Lafane', 'Hey, welcome to your page', 2, '/avatar/avatarF.png', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2023-02-15 00:47:04', '2023-02-22 23:09:18', '2023-02-22 23:09:18'),
(722, '937619LAFANE', 'Lafane OLAOGUN', 'Someone makes me feel bad', 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1677595949640', '2023-02-28 14:52:29', '2023-03-28 18:32:20', '2023-03-28 18:32:20'),
(723, '937619LAFANE', 'Lafane OLAOGUN', 'It looks like a beautiful day', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1681731883925', '2023-04-17 11:44:43', NULL, NULL),
(724, '937619LAFANE', 'Lafane OLAOGUN', 'almost finished. This project took about 4 years', 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1683243288916', '2023-05-04 23:34:48', '2023-07-21 19:14:16', '2023-07-21 19:14:16'),
(725, '964649OLAWALE', 'Olawale', 'Hey, welcome to your page', 0, 'avatarF.png', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2023-07-07 21:43:20', NULL, NULL),
(726, '915784TESTING', 'Testing', 'Hey, welcome to your page', 0, 'avatarF.png', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2023-07-11 22:25:01', NULL, NULL),
(727, '974628TESTING', 'Testing', 'Hey, welcome to your page', 0, 'avatarF.png', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2023-07-12 17:59:46', NULL, NULL),
(728, '941501TOBI', 'tobi', 'Hey, welcome to your page', 0, 'avatarF.png', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2023-07-30 20:01:19', NULL, NULL),
(729, '995544TESTING', 'Testing', 'Hey, welcome to your page', 0, 'avatarF.png', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2023-08-01 01:50:38', NULL, NULL),
(730, '962673TOBI', 'tobi', 'Hey, welcome to your page', 0, 'avatarF.png', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2023-08-01 13:29:58', NULL, NULL),
(731, '957154TESTING', 'Testing', 'Hey, welcome to your page', 0, 'avatarF.png', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2023-08-01 21:29:11', NULL, NULL),
(732, '918455TESTING', 'Testing', 'Hey, welcome to your page', 0, 'avatarF.png', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2023-08-01 21:35:30', NULL, NULL),
(733, '994428TESTING', 'Testing', 'Hey, welcome to your page', 0, 'avatarF.png', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2023-08-01 22:06:14', NULL, NULL),
(734, '999881TESTING', 'Testing', 'Hey, welcome to your page', 0, 'avatarF.png', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2023-08-01 22:11:49', NULL, NULL),
(735, '985172TESTING', 'Testing', 'Hey, welcome to your page', 0, 'avatarF.png', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2023-08-01 22:13:27', NULL, NULL),
(736, '948473TESTING', 'Testing', 'Hey, welcome to your page', 1, 'avatarF.png', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2023-08-01 22:14:17', '2023-08-09 23:40:02', '2023-08-09 23:40:02'),
(737, '117540OLAWALE', 'SEGUN OLAOGUN', 'enjoying the projects', 0, NULL, 'oladeleNFayo.jpeg', 'opp.jpeg', 'shoSojWal1.jpeg', 'shoSojWal2.jpeg', NULL, NULL, '1690944615849', '2023-08-02 02:50:15', NULL, NULL),
(738, '117540OLAWALE', 'SEGUN OLAOGUN', 'We are having a great time in Casares Spain', 0, NULL, 'HandSanitiser.png', 'cough.png', 'covid19-icon.png', NULL, NULL, NULL, '1692306886289', '2023-08-17 21:14:46', NULL, NULL),
(741, '992501TESTING', 'TESTING', 'Hey, welcome to your page', 0, 'avatarF.png', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2023-08-26 17:00:20', NULL, NULL),
(742, '918627OLAWALE', 'Olawale', 'Hey, welcome to your page', 0, 'avatarF.png', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2023-08-26 18:06:54', NULL, NULL),
(743, '950455OLAWALE', 'Olawale', 'Hey, welcome to your page', 0, 'avatarF.png', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2023-08-26 18:08:47', NULL, NULL),
(744, '913895OLAWALE', 'Olawale', 'Hey, welcome to your page', 0, 'avatarF.png', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2023-08-26 18:10:09', NULL, NULL),
(745, '974368OLAWALE', 'Olawale', 'Hey, welcome to your page', 0, 'avatarF.png', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2023-08-27 10:48:02', NULL, NULL),
(746, '935796TESTING', 'Testing', 'Hey, welcome to your page', 0, 'avatarF.png', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2023-08-27 10:51:50', NULL, NULL),
(747, '948454TESTING', 'Testing', 'Hey, welcome to your page', 1, 'avatarF.png', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2023-08-27 10:57:20', '2023-09-12 21:59:43', '2023-09-12 21:59:43'),
(754, '972748TESTING', 'TESTING', 'Hey, welcome to your page', 6, 'avatarF.png', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2023-08-27 12:01:19', '2023-10-14 00:48:32', '2023-10-14 00:48:32'),
(755, '937619LAFANE', 'Lafane OLAOGUN', 'it is going according to plan', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1696252725102', '2023-10-02 13:18:45', NULL, NULL),
(756, '870016OLAWALE', 'ADESOJI OLAOGUN', 'Checking if it works automatically', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1697071473222', '2023-10-12 00:44:33', NULL, NULL),
(757, '870016OLAWALE', 'ADESOJI OLAOGUN', 'it should be alright for everyone', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1697071579393', '2023-10-12 00:46:19', '2023-10-12 12:44:53', '2023-10-12 12:44:53'),
(758, '117540OLAWALE', 'SEGUN OLAOGUN', 'My beautifil jIbs', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1697140631900', '2023-10-12 19:57:11', NULL, NULL),
(759, '117540OLAWALE', 'SEGUN OLAOGUN', 'Isreal should be careful about not committing war crime', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1697284718211', '2023-10-14 11:58:38', '2023-10-14 11:58:52', '2023-10-14 11:58:52'),
(760, '117540OLAWALE', 'SEGUN OLAOGUN', 'whats going on', 10, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1697313407099', '2023-10-14 19:56:47', '2024-04-04 00:34:52', '2024-04-04 00:34:52'),
(761, '870016OLAWALE', 'ADESOJI OLAOGUN', 'WE LOVE IT', 10, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1698106725448', '2023-10-24 00:18:45', '2024-04-04 00:34:48', '2024-04-04 00:34:48'),
(762, '937619LAFANE', 'Lafane OLAOGUN', 'I&#039;m here again after a while. I&#039;m going to take it easy and slow so I dont run myself down', 17, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1698929910508', '2023-11-02 12:58:30', '2024-04-04 00:34:44', '2024-04-04 00:34:44'),
(763, '937619LAFANE', 'Lafane OLAOGUN', 'back again', 15, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1701085604171', '2023-11-27 11:46:44', '2024-04-04 00:34:39', '2024-04-04 00:34:39'),
(764, '870016OLAWALE', 'ADESOJI OLAOGUN', 'We are here. Didnt sleep well again', 18, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1702972587531', '2023-12-19 07:56:27', '2024-04-04 00:34:35', '2024-04-04 00:34:35'),
(765, '870016OLAWALE', 'ADESOJI OLAOGUN', 'Buddy, we are back again', 25, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1707207068449', '2024-02-06 08:11:08', '2024-04-04 00:34:31', '2024-04-04 00:34:31'),
(766, '870016OLAWALE', 'ADESOJI OLAOGUN', 'I&#039;m back here for the show.', 8, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1709121549479', '2024-02-28 11:59:09', '2024-03-03 01:58:42', '2024-03-03 01:58:42'),
(767, '432292OLAWALE', 'OLAWALE OLAOGUN', 'this is a gonna be a success', 18, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1709307603243', '2024-03-01 15:40:03', '2024-03-03 01:58:31', '2024-03-03 01:58:31'),
(768, '432292OLAWALE', 'OLAWALE OLAOGUN', '', 25, NULL, 'cough.png', NULL, NULL, NULL, NULL, NULL, '1709429085371', '2024-03-03 01:24:45', '2024-03-03 01:58:17', '2024-03-03 01:58:17'),
(769, '432292OLAWALE', 'OLAWALE OLAOGUN', 'Monty&#039;s birthday', 31, NULL, 'olutobs_13th.jpeg', NULL, NULL, NULL, NULL, NULL, '1709429133428', '2024-03-03 01:25:33', '2024-03-03 01:58:06', '2024-03-03 01:58:06'),
(770, '432292OLAWALE', 'OLAWALE OLAOGUN', 'Are we ready. The like button acts funny', 14, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1709431158053', '2024-03-03 01:59:18', '2024-04-03 18:20:15', '2024-04-03 18:20:15'),
(771, '432292OLAWALE', 'OLAWALE OLAOGUN', 'Major focus now is the PWA', 13, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1709468060598', '2024-03-03 12:14:20', '2024-04-03 18:20:09', '2024-04-03 18:20:09'),
(772, '432292OLAWALE', 'OLAWALE OLAOGUN', 'We love you isnt', 58, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1709469278456', '2024-03-03 12:34:38', '2024-04-04 00:26:49', '2024-04-04 00:26:49'),
(773, '870016OLAWALE', 'ADESOJI OLAOGUN', 'I need to work on the like button again.', 33, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1712187208084', '2024-04-03 23:33:28', '2024-04-04 00:34:15', '2024-04-04 00:34:15'),
(774, '870016OLAWALE', 'ADESOJI OLAOGUN', 'checking everything is okay?', 8, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1712190921543', '2024-04-04 00:35:21', '2024-04-18 08:59:07', '2024-04-18 08:59:07'),
(775, '870016OLAWALE', 'ADESOJI OLAOGUN', 'incredible', 1, NULL, '750x1334-screenshot.png', '1280x800-screenshot.png', NULL, NULL, NULL, NULL, '1712268314788', '2024-04-04 22:05:14', '2024-04-18 08:59:02', '2024-04-18 08:59:02'),
(776, '870016OLAWALE', 'ADESOJI OLAOGUN', 'post it to work', 0, NULL, 'olutobs_13th.jpeg', NULL, NULL, NULL, NULL, NULL, '1712268381569', '2024-04-04 22:06:21', NULL, NULL),
(777, '870016OLAWALE', 'ADESOJI OLAOGUN', 'post it to work', 0, NULL, 'olutobs_13th.jpeg', NULL, NULL, NULL, NULL, NULL, '1712268429264', '2024-04-04 22:07:09', NULL, NULL),
(778, '870016OLAWALE', 'ADESOJI OLAOGUN', 'going for another test', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1712268461690', '2024-04-04 22:07:41', NULL, NULL),
(779, '870016OLAWALE', 'ADESOJI OLAOGUN', 'Going for another test.', 6, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1712269103833', '2024-04-04 22:18:23', '2024-04-04 22:29:13', '2024-04-04 22:29:13'),
(780, '870016OLAWALE', 'ADESOJI OLAOGUN', 'we are doing everything', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1712275275381', '2024-04-05 00:01:15', NULL, NULL),
(781, '870016OLAWALE', 'ADESOJI OLAOGUN', 'we are doing everything', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1712275304083', '2024-04-05 00:01:44', NULL, NULL),
(782, '870016OLAWALE', 'ADESOJI OLAOGUN', 'finally', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1712275991424', '2024-04-05 00:13:11', NULL, NULL),
(783, '870016OLAWALE', 'ADESOJI OLAOGUN', 'finally', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1712276009447', '2024-04-05 00:13:29', NULL, NULL),
(784, '870016OLAWALE', 'ADESOJI OLAOGUN', 'the model', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1712276260879', '2024-04-05 00:17:40', NULL, NULL),
(785, '870016OLAWALE', 'ADESOJI OLAOGUN', 'still testing', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1712276331081', '2024-04-05 00:18:51', NULL, NULL),
(786, '870016OLAWALE', 'ADESOJI OLAOGUN', 'Donald Trump is the president', 5, NULL, 'HandSanitiser.png', 'mask.png', 'olutobs_13th.jpeg', NULL, NULL, NULL, '1712277387452', '2024-04-05 00:36:27', '2024-04-05 00:39:24', '2024-04-05 00:39:24'),
(787, '870016OLAWALE', 'ADESOJI OLAOGUN', 'I finally fixed it the upload button looks better', 0, NULL, 'HandSanitiser.png', 'mask.png', 'olutobs_13th.jpeg', NULL, NULL, NULL, '1712277671047', '2024-04-05 00:41:11', NULL, NULL),
(788, '870016OLAWALE', 'ADESOJI OLAOGUN', 'code is working when it should not be', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1712278217343', '2024-04-05 00:50:17', NULL, NULL),
(789, '870016OLAWALE', 'ADESOJI OLAOGUN', 'Now, I&#039;m testing it', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1712278283818', '2024-04-05 00:51:23', NULL, NULL),
(790, '870016OLAWALE', 'ADESOJI OLAOGUN', 'still testing', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1712278369012', '2024-04-05 00:52:49', NULL, NULL),
(791, '870016OLAWALE', 'ADESOJI OLAOGUN', 'still working onn it', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1712278434132', '2024-04-05 00:53:54', NULL, NULL),
(792, '870016OLAWALE', 'ADESOJI OLAOGUN', 'ignore', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1712279058198', '2024-04-05 01:04:18', NULL, NULL),
(793, '870016OLAWALE', 'ADESOJI OLAOGUN', 'configuratu', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1712279088688', '2024-04-05 01:04:48', NULL, NULL),
(794, '870016OLAWALE', 'ADESOJI OLAOGUN', 'change', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1712279152928', '2024-04-05 01:05:52', NULL, NULL),
(795, '870016OLAWALE', 'ADESOJI OLAOGUN', 'dont tell', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1712279205263', '2024-04-05 01:06:45', NULL, NULL),
(796, '870016OLAWALE', 'ADESOJI OLAOGUN', 'gane', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1712279267605', '2024-04-05 01:07:47', NULL, NULL),
(797, '870016OLAWALE', 'ADESOJI OLAOGUN', 'Testing ageing', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1712279337733', '2024-04-05 01:08:57', NULL, NULL),
(798, '870016OLAWALE', 'ADESOJI OLAOGUN', 'wanting', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1712279430782', '2024-04-05 01:10:30', NULL, NULL),
(799, '870016OLAWALE', 'ADESOJI OLAOGUN', 'australia', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1712279546886', '2024-04-05 01:12:26', NULL, NULL),
(800, '870016OLAWALE', 'ADESOJI OLAOGUN', 'healthy', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1712279619215', '2024-04-05 01:13:39', NULL, NULL),
(801, '870016OLAWALE', 'ADESOJI OLAOGUN', 'sciencing', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1712279782607', '2024-04-05 01:16:22', NULL, NULL),
(802, '870016OLAWALE', 'ADESOJI OLAOGUN', 'copy', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1712280083560', '2024-04-05 01:21:23', NULL, NULL),
(803, '870016OLAWALE', 'ADESOJI OLAOGUN', 'sayinh', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1712280129762', '2024-04-05 01:22:09', NULL, NULL),
(804, '870016OLAWALE', 'ADESOJI OLAOGUN', 'lion', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1712280213802', '2024-04-05 01:23:33', NULL, NULL),
(805, '870016OLAWALE', 'ADESOJI OLAOGUN', 'Incredible diet for longevity', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1712281283317', '2024-04-05 01:41:23', NULL, NULL),
(806, '870016OLAWALE', 'ADESOJI OLAOGUN', 'Remember the covid time', 0, NULL, 'covid19-icon.png', NULL, NULL, NULL, NULL, NULL, '1712281319468', '2024-04-05 01:41:59', NULL, NULL),
(807, '870016OLAWALE', 'ADESOJI OLAOGUN', 'is it working', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1712281354894', '2024-04-05 01:42:34', NULL, NULL),
(808, '870016OLAWALE', 'ADESOJI OLAOGUN', 'Final test', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1712281697328', '2024-04-05 01:48:17', NULL, NULL),
(809, '117540OLAWALE', 'SEGUN OLAOGUN', 'Showing Olutobs how the network works', 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1712308253242', '2024-04-05 09:10:53', '2024-04-05 09:12:37', '2024-04-05 09:12:37'),
(810, '432292OLAWALE', 'OLAWALE OLAOGUN', '', 13, NULL, 'modernman.jpeg', NULL, NULL, NULL, NULL, NULL, '1712317953064', '2024-04-05 11:52:33', '2024-04-05 11:52:54', '2024-04-05 11:52:54'),
(811, '432292OLAWALE', 'OLAWALE OLAOGUN', '', 0, NULL, 'family.jpeg', NULL, NULL, NULL, NULL, NULL, '1712318101455', '2024-04-05 11:55:01', NULL, NULL),
(812, '432292OLAWALE', 'OLAWALE OLAOGUN', 'WHY IS POST APPEARING TWICE', 0, NULL, 'WhatsApp Image 2021-04-17 at 17.20.52 (1).jpeg', NULL, NULL, NULL, NULL, NULL, '1712318263947', '2024-04-05 11:57:43', NULL, NULL),
(813, '432292OLAWALE', 'OLAWALE OLAOGUN', 'IS IT WORKING WELL?', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1712997564184', '2024-04-13 08:39:24', NULL, NULL),
(814, '432292OLAWALE', 'OLAWALE OLAOGUN', '', 0, NULL, 'family.jpeg', NULL, NULL, NULL, NULL, NULL, '1712997703832', '2024-04-13 08:41:43', NULL, NULL),
(815, '397755OLUSOLA', 'FEMI OLAOGUN', 'check if it is posting twice', 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1713431154586', '2024-04-18 09:05:54', '2024-04-18 09:06:00', '2024-04-18 09:06:00'),
(816, '117540OLAWALE', 'SEGUN OLAOGUN', 'I think it works okay', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1713431240249', '2024-04-18 09:07:20', NULL, NULL),
(817, '117540OLAWALE', 'SEGUN OLAOGUN', 'check if it is still doubling the post', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1713431362268', '2024-04-18 09:09:22', NULL, NULL),
(818, '397755OLUSOLA', 'FEMI OLAOGUN', 'picture already posted are not allowed. we need to check', 0, NULL, 'olutobs_13th.jpeg', 'mask.png', NULL, NULL, NULL, NULL, '1713431690923', '2024-04-18 09:14:50', NULL, NULL),
(819, '397755OLUSOLA', 'FEMI OLAOGUN', 'i&#039;M BACK AGAIN. i NEED TO FINSH THIS PROJECT', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1716331503990', '2024-05-21 22:45:03', '2024-05-31 13:00:12', '2024-05-31 13:00:12'),
(820, '117540OLAWALE', 'SEGUN OLAOGUN', 'I&#039;m back for the final review before launch  - target is to launch before my birthday', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1718359202913', '2024-06-14 10:00:02', NULL, NULL),
(821, '432292OLAWALE', 'OLAWALE OLAOGUN', 'I&#039;m looking at the project again. Nervous about the implementation as it needs to go live before my birthday', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1719944456800', '2024-07-02 18:20:56', NULL, NULL),
(822, '432292OLAWALE', 'OLAWALE OLAOGUN', 'it still didnt go live before my birthday. gosh!!!! I will use August to go for it. This project is going live this year', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1722693178917', '2024-08-03 13:52:58', NULL, NULL),
(823, '937619LAFANE', 'Lafane OLAOGUN', 'Login is definitely a problem', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1724082848705', '2024-08-19 15:54:08', NULL, NULL),
(824, '937619LAFANE', 'Lafane OLAOGUN', 'Lots of issues now fixed. mysql, routing problems, etc', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1725548928696', '2024-09-05 15:08:48', NULL, NULL),
(825, '432292OLAWALE', 'OLAWALE OLAOGUN', 'I need to check why it is not updating real time', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1725549052406', '2024-09-05 15:10:52', NULL, NULL),
(826, '432292OLAWALE', 'OLAWALE OLAOGUN', 'still working on the PWA. Have completed the backend push notification. still have to build the pushtable', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1725914061893', '2024-09-09 20:34:21', NULL, NULL),
(827, '937619LAFANE', 'Lafane OLAOGUN', 'something seems wrong', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1726167413826', '2024-09-12 18:56:53', NULL, NULL),
(828, '937619LAFANE', 'Lafane OLAOGUN', 'What is wrong', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1726167466346', '2024-09-12 18:57:46', NULL, NULL),
(829, '432292OLAWALE', 'OLAWALE OLAOGUN', 'have you checked the reason', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1726167497686', '2024-09-12 18:58:17', NULL, NULL),
(830, '432292OLAWALE', 'OLAWALE OLAOGUN', 'if this works, then something si right', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1726167550360', '2024-09-12 18:59:10', NULL, NULL),
(831, '937619LAFANE', 'Lafane OLAOGUN', 'Mr Latin', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1726168160357', '2024-09-12 19:09:20', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `profilePics`
--

CREATE TABLE `profilePics` (
  `no` int NOT NULL,
  `id` varchar(100) NOT NULL,
  `img` text,
  `buttonHTML` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT 'Add family',
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_updated` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `date_deleted` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `profilePics`
--

INSERT INTO `profilePics` (`no`, `id`, `img`, `buttonHTML`, `date_created`, `date_updated`, `date_deleted`) VALUES
(3, '117540OLAWALE', 'olutobs_13th.jpeg', 'Add friend', '2021-02-15 23:42:31', '2023-10-12 02:00:07', '2023-10-12 02:00:07'),
(4, '870016OLAWALE', 'femba.jpeg', 'Request sent', '2021-02-15 23:49:09', '2023-08-30 00:35:27', '2023-08-30 00:35:27'),
(5, '397755OLUSOLA', 'walesoj.jpeg', 'Request Sent', '2021-02-15 23:51:02', '2023-08-30 00:35:54', '2023-08-30 00:35:54'),
(6, '937619LAFANE', 'ShoTakingSelfy.jpeg', '', '2021-02-15 23:51:02', '2023-03-02 18:29:01', '2023-03-02 18:29:01'),
(7, '994428TESTING', 'avatarM.png', '', '2023-08-01 22:06:14', '2023-08-27 16:13:47', '2023-08-27 16:13:47'),
(8, '999881TESTING', 'avatarF.png', '', '2023-08-01 22:11:49', NULL, NULL),
(9, '985172TESTING', 'avatarM.png', '', '2023-08-01 22:13:27', '2023-08-27 16:13:41', '2023-08-27 16:13:41'),
(10, '948473TESTING', 'avatarF.png', '', '2023-08-01 22:14:17', NULL, NULL),
(13, '992501TESTING', 'avatarM.png', '', '2023-08-26 17:00:20', '2023-08-27 16:13:53', '2023-08-27 16:13:53'),
(14, '918627OLAWALE', 'avatarF.png', '', '2023-08-26 18:06:55', NULL, NULL),
(15, '950455OLAWALE', 'avatarF.png', '', '2023-08-26 18:08:47', NULL, NULL),
(16, '913895OLAWALE', 'avatarF.png', '', '2023-08-26 18:10:09', NULL, NULL),
(17, '974368OLAWALE', 'avatarF.png', '', '2023-08-27 10:48:02', NULL, NULL),
(18, '935796TESTING', 'avatarF.png', '', '2023-08-27 10:51:50', NULL, NULL),
(19, '948454TESTING', 'avatarF.png', '', '2023-08-27 10:57:20', NULL, NULL),
(26, '972748TESTING', 'avatarF.png', '', '2023-08-27 12:01:19', NULL, NULL),
(27, '432292OLAWALE', 'covid19-icon.png', 'Add friend', '2021-02-15 23:42:31', '2024-04-05 12:54:13', '2024-04-05 12:54:13'),
(28, '929019DAYO', 'ShoTakingSelfy.jpeg', '', '2021-02-15 23:51:02', '2023-03-02 18:29:01', '2023-03-02 18:29:01'),
(29, '975782ADETUNJI', 'ShoTakingSelfy.jpeg', '', '2021-02-15 23:51:02', '2023-03-02 18:29:01', '2023-03-02 18:29:01'),
(30, '936779SHABA', 'avatarF.png', '', '2023-08-26 18:10:09', NULL, NULL),
(31, '954243OLAOLUWA', 'avatarF.png', '', '2023-08-27 10:48:02', NULL, NULL),
(32, '912189MOMODU', 'avatarF.png', '', '2023-08-27 10:51:50', NULL, NULL),
(33, '987764OLAWALE', 'avatarF.png', '', '2023-08-27 10:57:20', NULL, NULL),
(34, '913895OLAWALE', 'avatarF.png', '', '2023-08-27 12:01:19', NULL, NULL),
(35, '432292OLAWALE', 'covid19-icon.png', 'Add friend', '2021-02-15 23:42:31', '2024-04-05 12:54:13', '2024-04-05 12:54:13'),
(36, '929019DAYO', 'ShoTakingSelfy.jpeg', '', '2021-02-15 23:51:02', '2023-03-02 18:29:01', '2023-03-02 18:29:01'),
(37, '975782ADETUNJI', 'ShoTakingSelfy.jpeg', '', '2021-02-15 23:51:02', '2023-03-02 18:29:01', '2023-03-02 18:29:01');

-- --------------------------------------------------------

--
-- Table structure for table `pushNotification`
--

CREATE TABLE `pushNotification` (
  `no` int NOT NULL,
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `endpoint` varchar(255) NOT NULL,
  `p256dh_key` text NOT NULL,
  `auth_key TEXT` text NOT NULL,
  `created_at` timestamp NOT NULL,
  `deleted_at` timestamp NOT NULL ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `requestMgt`
--

CREATE TABLE `requestMgt` (
  `id` int NOT NULL,
  `approver_id` varchar(100) NOT NULL,
  `requester_id` varchar(100) NOT NULL,
  `status` varchar(20) NOT NULL,
  `requesterCode` text,
  `approverCode` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `requestMgt`
--

INSERT INTO `requestMgt` (`id`, `approver_id`, `requester_id`, `status`, `requesterCode`, `approverCode`, `created_at`, `updated_at`) VALUES
(40, '935796TESTING', '937619LAFANE', 'Request sent', NULL, NULL, '2023-10-23 20:02:14', '2023-10-23 20:02:14'),
(41, '950455OLAWALE', '937619LAFANE', 'Request sent', NULL, NULL, '2023-10-23 20:02:41', '2023-10-23 20:02:41'),
(42, '972748TESTING', '937619LAFANE', 'Request sent', NULL, NULL, '2023-10-23 20:03:36', '2023-10-23 20:03:36'),
(43, '918627OLAWALE', '117540OLAWALE', 'Request sent', NULL, NULL, '2023-10-24 00:51:31', '2023-10-24 00:51:31'),
(44, '948473TESTING', '117540OLAWALE', 'Request sent', NULL, NULL, '2023-10-24 00:56:25', '2023-10-24 00:56:25'),
(45, '913895OLAWALE', '117540OLAWALE', 'Request sent', NULL, NULL, '2023-10-24 00:59:06', '2023-10-24 00:59:06'),
(46, '397755OLUSOLA', '117540OLAWALE', 'Approved', 'MODERNMAN', NULL, '2023-10-24 01:44:42', '2024-05-31 12:57:14'),
(47, '937619LAFANE', '117540OLAWALE', 'Request sent', NULL, NULL, '2023-10-24 13:32:53', '2023-10-24 13:32:53'),
(48, '974368OLAWALE', '117540OLAWALE', 'Request sent', NULL, NULL, '2024-04-05 09:11:16', '2024-04-05 09:11:16'),
(49, '987764OLAWALE', '117540OLAWALE', 'Request sent', NULL, NULL, '2024-04-05 09:11:20', '2024-04-05 09:11:20'),
(50, '117540OLAWALE', '397755OLUSOLA', 'Approved', 'SHO', NULL, '2024-04-18 08:54:05', '2024-04-18 08:56:03'),
(51, '870016OLAWALE', '397755OLUSOLA', 'Approved', 'SHO', NULL, '2024-05-31 12:52:29', '2024-05-31 12:53:43');

-- --------------------------------------------------------

--
-- Table structure for table `siblings`
--

CREATE TABLE `siblings` (
  `no` int NOT NULL,
  `id` varchar(255) NOT NULL,
  `famCode` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `sibling_name` text,
  `sibling_email` text,
  `sibling_linked` text,
  `sibling_code` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `siblings`
--

INSERT INTO `siblings` (`no`, `id`, `famCode`, `sibling_name`, `sibling_email`, `sibling_linked`, `sibling_code`, `created_at`, `updated_at`, `deleted_at`) VALUES
(3, '870016OLAWALE', '10', 'TOPE OLAOGUN', 'topsy@gmail.com', NULL, '', '2021-02-15 23:42:31', '2023-08-30 11:41:43', '2023-08-30 11:41:43'),
(4, '870016OLAWALE', '10', 'OLUSOLA OLAOGUN', 'solaolaogun2013@gmail.com', NULL, '', '2021-02-15 23:42:31', '2023-08-30 11:41:48', '2023-08-30 11:41:48'),
(5, '397755OLUSOLA', '11', 'TOPE OLAOGUN', 'topsy@gmail.com', NULL, '', '2021-02-15 23:49:10', '2023-08-30 11:41:52', '2023-08-30 11:41:52'),
(6, '397755OLUSOLA', '11', 'FEMI OLAOGUN', 'femmyoguns@gmail.com', NULL, '', '2021-02-15 23:49:10', '2023-08-30 11:41:55', '2023-08-30 11:41:55'),
(7, '397755OLUSOLA', '11', 'OLAWALE OLAOGUN', 'modernman00@yahoo.com', NULL, '', '2021-02-15 23:49:10', '2023-08-30 11:41:58', '2023-08-30 11:41:58'),
(11, '929019DAYO', '', 'RASHIDAT OLAOGUN', 'Please  provide email address', NULL, '', '2021-09-21 22:39:56', NULL, NULL),
(12, '936779SHABA', '', 'shola Olaogun', 'shooguns@gmail com', NULL, '', '2022-07-21 00:20:44', NULL, NULL),
(13, '936779SHABA', '', 'Femi Olaogun', 'femmyoguns@gmail.com', NULL, '', '2022-07-21 00:20:44', NULL, NULL),
(14, '954243OLAOLUWA', '', 'Ire Olaogun', 'Please  provide email address', NULL, '', '2022-07-21 08:06:51', NULL, NULL),
(15, '954243OLAOLUWA', '', 'Oreoluwa Olaogun', 'Please  provide email address', NULL, '', '2022-07-21 08:06:51', NULL, NULL),
(16, '912189MOMODU', '', 'dupe olaogun', 'waleolaogunrac@gmail.com', NULL, '', '2023-02-13 17:14:09', NULL, NULL),
(17, '940728BUSSY', '', 'Shola', 'testing@gmail.com', NULL, '', '2023-02-14 13:21:11', NULL, NULL),
(18, '940728BUSSY', '', 'Lafane', 'testing@gmail.com', NULL, '', '2023-02-14 13:21:11', NULL, NULL),
(19, '942737GBEDUO', '', 'tayo', 'testing@gmail.com', NULL, '', '2023-02-14 19:36:51', NULL, NULL),
(20, '964649OLAWALE', '', 'Shola Olaogun', 'waleolaogunrac@gmail.com', NULL, '', '2023-07-07 21:43:20', NULL, NULL),
(21, '948473TESTING', '', 'Testing2', 'Please  provide email address', 'Same Mother', '', '2023-08-01 22:14:17', NULL, NULL),
(22, '948473TESTING', '', 'Testing', 'Please  provide email address', 'Same Father', '', '2023-08-01 22:14:17', NULL, NULL),
(23, '918627OLAWALE', NULL, 'wale', 'bikeoguns@gmail.com', 'Same Mother &amp; Father', '', '2023-08-26 18:06:55', NULL, NULL),
(24, '950455OLAWALE', NULL, 'wale', 'bikeoguns@gmail.com', 'Same Mother &amp; Father', '', '2023-08-26 18:08:47', NULL, NULL),
(25, '913895OLAWALE', NULL, 'wale', 'bikeoguns@gmail.com', 'Same Mother &amp; Father', '', '2023-08-26 18:10:09', NULL, NULL),
(26, '937619LAFANE', NULL, 'samu olpo', 'jojo@opod.com', 'Same_Father', NULL, '2023-10-18 16:22:33', NULL, NULL),
(27, '937619LAFANE', NULL, 'AJIBIKE OLAOGUN', 'jojo@ag.com', 'Same_Mother_Father', NULL, '2023-10-18 16:22:33', NULL, NULL),
(28, '937619LAFANE', NULL, 'Atinuke Olaogun', 'ati@gmai.com', 'Same_Mother_Father', NULL, '2023-10-18 18:57:05', NULL, NULL),
(29, '937619LAFANE', NULL, 'Femba Olaogun', 'femba@femba.com', 'Same_Mother_Father', NULL, '2023-10-18 19:14:48', NULL, NULL),
(30, '432292OLAWALE', NULL, 'TOPE OLAOGUN', 'TP@BCP.COM', 'Same_Mother_Father', NULL, '2024-03-03 01:49:25', NULL, NULL),
(31, '432292OLAWALE', NULL, 'OLUFEMI OLAOGUN', 'AL@BCP.COM', 'Same_Mother_Father', NULL, '2024-03-03 01:49:25', NULL, NULL),
(32, '432292OLAWALE', NULL, 'OLUSOLA OLAOGUN', 'OL@BCP.COM', 'Same_Mother_Father', NULL, '2024-03-03 01:49:25', NULL, NULL),
(33, '432292OLAWALE', NULL, 'Adeyanju Olaogun', 'waledevtest@gmail.com', 'Same_Mother_Father', NULL, '2024-04-05 02:20:25', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `uploadPics`
--

CREATE TABLE `uploadPics` (
  `no` int NOT NULL,
  `id` varchar(25) NOT NULL,
  `uploadPics` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `work`
--

CREATE TABLE `work` (
  `no` int NOT NULL,
  `id` varchar(255) NOT NULL,
  `employmentStatus` text NOT NULL,
  `occupation` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `work`
--

INSERT INTO `work` (`no`, `id`, `employmentStatus`, `occupation`, `created_at`, `updated_at`, `deleted_at`) VALUES
(2, '117540OLAWALE', 'Full time employment', 'ACCOUNTANT', '2021-02-15 23:39:16', NULL, NULL),
(3, '870016OLAWALE', 'Full time employment', 'ACCOUNTANT', '2021-02-15 23:42:31', NULL, NULL),
(4, '397755OLUSOLA', 'Student', 'SUPPLY CHAIN PROFESSIONAL', '2021-02-15 23:49:10', NULL, NULL),
(6, '929019DAYO', 'Self-employed', 'carer', '2021-09-21 22:39:56', NULL, NULL),
(8, '975782ADETUNJI', 'Full-time-employment', 'carer', '2022-07-21 00:12:34', NULL, NULL),
(9, '936779SHABA', 'Full-time-employment', 'business man', '2022-07-21 00:20:44', NULL, NULL),
(10, '954243OLAOLUWA', 'Student', 'None', '2022-07-21 08:06:51', NULL, NULL),
(11, '912189MOMODU', 'Full-time-employment', 'Accountant', '2023-02-13 17:14:09', NULL, NULL),
(12, '987764OLAWALE', 'Full-time-employment', 'tech', '2023-02-13 17:36:11', NULL, NULL),
(13, '913882OLAWALE', 'Self-employed', 'sdsd', '2023-02-13 17:44:09', NULL, NULL),
(15, '940728BUSSY', 'Full-time-employment', 'Accountant', '2023-02-14 13:21:11', NULL, NULL),
(16, '942737GBEDUO', 'Unemployed', 'tech', '2023-02-14 19:36:51', NULL, NULL),
(17, '971778GENERA', 'Unemployed', 'tech', '2023-02-15 00:41:26', NULL, NULL),
(18, '937619LAFANE', 'Full-time-employment', 'TECH', '2023-02-15 00:47:04', NULL, NULL),
(19, '964649OLAWALE', 'Self-employed', 'Accountant', '2023-07-07 21:43:20', NULL, NULL),
(20, '915784TESTING', 'Unemployed', 'Account', '2023-07-11 22:25:01', NULL, NULL),
(21, '974628TESTING', 'Unemployed', 'Housewife', '2023-07-12 17:59:46', NULL, NULL),
(22, '941501TOBI', 'Unemployed', 'no job', '2023-07-30 20:01:19', NULL, NULL),
(23, '995544TESTING', 'Full-time-employment', 'Accountant', '2023-08-01 01:50:38', NULL, NULL),
(24, '962673TOBI', 'Student', 'Student', '2023-08-01 13:29:58', NULL, NULL),
(25, '957154TESTING', 'Full-time-employment', 'student', '2023-08-01 21:29:11', NULL, NULL),
(26, '918455TESTING', 'Student', 'Student', '2023-08-01 21:35:30', NULL, NULL),
(27, '994428TESTING', 'Student', 'Student', '2023-08-01 22:06:14', NULL, NULL),
(28, '999881TESTING', 'Student', 'Student', '2023-08-01 22:11:49', NULL, NULL),
(29, '985172TESTING', 'Student', 'Student', '2023-08-01 22:13:27', NULL, NULL),
(30, '948473TESTING', 'Student', 'Student', '2023-08-01 22:14:17', NULL, NULL),
(31, '921152LEBANON', 'Full-time-employment', 'PROCUREMENT ', '2023-08-23 23:57:31', NULL, NULL),
(32, '967957TESTING', 'Full-time-employment', 'Housewife', '2023-08-25 09:29:52', NULL, NULL),
(33, '992501TESTING', 'Student', 'Student', '2023-08-26 17:00:20', NULL, NULL),
(34, '918627OLAWALE', 'Student', 'Student', '2023-08-26 18:06:54', NULL, NULL),
(35, '950455OLAWALE', 'Student', 'Student', '2023-08-26 18:08:47', NULL, NULL),
(36, '913895OLAWALE', 'Student', 'Student', '2023-08-26 18:10:09', NULL, NULL),
(37, '974368OLAWALE', 'Student', 'Student', '2023-08-27 10:48:02', NULL, NULL),
(38, '935796TESTING', 'Student', 'Student', '2023-08-27 10:51:50', NULL, NULL),
(39, '948454TESTING', 'Student', 'Student', '2023-08-27 10:57:20', NULL, NULL),
(40, '926764TESTING', 'Student', 'Student', '2023-08-27 11:36:09', NULL, NULL),
(41, '973719TESTING', 'Student', 'Student', '2023-08-27 11:37:00', NULL, NULL),
(42, '981243TESTING', 'Student', 'Student', '2023-08-27 11:38:47', NULL, NULL),
(43, '930410TESTING', 'Student', 'Student', '2023-08-27 11:45:24', NULL, NULL),
(44, '991388TESTING', 'Student', 'Student', '2023-08-27 11:47:38', NULL, NULL),
(45, '949886TESTING', 'Student', 'Student', '2023-08-27 11:52:56', NULL, NULL),
(46, '906317TESTING', 'Student', 'Student', '2023-08-27 11:59:59', NULL, NULL),
(47, '972748TESTING', 'Student', 'Student', '2023-08-27 12:01:19', NULL, NULL);

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
-- Indexes for table `familyFriend`
--
ALTER TABLE `familyFriend`
  ADD PRIMARY KEY (`no`);

--
-- Indexes for table `images`
--
ALTER TABLE `images`
  ADD PRIMARY KEY (`no`),
  ADD KEY `id` (`id`);

--
-- Indexes for table `kids`
--
ALTER TABLE `kids`
  ADD PRIMARY KEY (`no`),
  ADD KEY `id` (`id`);

--
-- Indexes for table `notification`
--
ALTER TABLE `notification`
  ADD PRIMARY KEY (`no`),
  ADD KEY `sender_id` (`sender_id`);

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
-- Indexes for table `profilePics`
--
ALTER TABLE `profilePics`
  ADD PRIMARY KEY (`no`),
  ADD KEY `id` (`id`);

--
-- Indexes for table `pushNotification`
--
ALTER TABLE `pushNotification`
  ADD PRIMARY KEY (`no`);

--
-- Indexes for table `requestMgt`
--
ALTER TABLE `requestMgt`
  ADD PRIMARY KEY (`id`);

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
  MODIFY `no` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `comment`
--
ALTER TABLE `comment`
  MODIFY `comment_no` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=558;

--
-- AUTO_INCREMENT for table `contact`
--
ALTER TABLE `contact`
  MODIFY `no` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `no` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=121;

--
-- AUTO_INCREMENT for table `familyFriend`
--
ALTER TABLE `familyFriend`
  MODIFY `no` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `images`
--
ALTER TABLE `images`
  MODIFY `no` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `kids`
--
ALTER TABLE `kids`
  MODIFY `no` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `notification`
--
ALTER TABLE `notification`
  MODIFY `no` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- AUTO_INCREMENT for table `otherFamily`
--
ALTER TABLE `otherFamily`
  MODIFY `no` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `personal`
--
ALTER TABLE `personal`
  MODIFY `no` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `pma__bookmark`
--
ALTER TABLE `pma__bookmark`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pma__column_info`
--
ALTER TABLE `pma__column_info`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pma__export_templates`
--
ALTER TABLE `pma__export_templates`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pma__history`
--
ALTER TABLE `pma__history`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pma__pdf_pages`
--
ALTER TABLE `pma__pdf_pages`
  MODIFY `page_nr` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pma__savedsearches`
--
ALTER TABLE `pma__savedsearches`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `post`
--
ALTER TABLE `post`
  MODIFY `post_no` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=832;

--
-- AUTO_INCREMENT for table `profilePics`
--
ALTER TABLE `profilePics`
  MODIFY `no` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `pushNotification`
--
ALTER TABLE `pushNotification`
  MODIFY `no` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `requestMgt`
--
ALTER TABLE `requestMgt`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `siblings`
--
ALTER TABLE `siblings`
  MODIFY `no` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `uploadPics`
--
ALTER TABLE `uploadPics`
  MODIFY `no` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `work`
--
ALTER TABLE `work`
  MODIFY `no` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

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
-- Constraints for table `kids`
--
ALTER TABLE `kids`
  ADD CONSTRAINT `kids_ibfk_1` FOREIGN KEY (`id`) REFERENCES `personal` (`id`);

--
-- Constraints for table `notification`
--
ALTER TABLE `notification`
  ADD CONSTRAINT `notification_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `personal` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

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
-- Constraints for table `profilePics`
--
ALTER TABLE `profilePics`
  ADD CONSTRAINT `profilePics_ibfk_1` FOREIGN KEY (`id`) REFERENCES `personal` (`id`);

--
-- Constraints for table `siblings`
--
ALTER TABLE `siblings`
  ADD CONSTRAINT `siblings_ibfk_1` FOREIGN KEY (`id`) REFERENCES `personal` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
