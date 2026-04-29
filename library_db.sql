-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 29, 2026 at 05:26 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `library_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`, `name`) VALUES
(2, 'admin', '$2y$10$vAVp5i.kI3V2iDxZRDQ2P.HoskdieVKsS4T88I33cgqlI9xG2CV0O', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `authors`
--

CREATE TABLE `authors` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `authors`
--

INSERT INTO `authors` (`id`, `name`) VALUES
(1, 'F. Scott Fitzgerald'),
(2, 'James Clear'),
(3, 'PHILIP AGUSTIN');

-- --------------------------------------------------------

--
-- Table structure for table `books`
--

CREATE TABLE `books` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `author_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `isbn` varchar(20) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `quality` varchar(50) DEFAULT NULL,
  `date_published` date DEFAULT NULL,
  `date_registered` date DEFAULT NULL,
  `published_year` year(4) DEFAULT NULL,
  `total_copies` int(11) DEFAULT 1,
  `available_copies` int(11) DEFAULT 1,
  `quantity` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `books`
--

INSERT INTO `books` (`id`, `title`, `author_id`, `category_id`, `isbn`, `description`, `quality`, `date_published`, `date_registered`, `published_year`, `total_copies`, `available_copies`, `quantity`) VALUES
(1, 'The Great Gatsby', 1, 1, '978-0-141-03435-8', 'A classic novel about wealth, love, and the American Dream in the 1920s.', 'Good', '1925-04-10', '2025-11-30', NULL, 1, 1, 140),
(6, 'THE QUINY POOO', 2, 3, '978-0-141-03435-9', 'BEAUTY IN WHITE', 'Good', '2026-01-05', '2026-01-08', NULL, 1, 1, 18),
(7, 'THE RIDER', 3, 1, '978-0-525-47546-7', 'MOTORSTAR', 'New', '2025-04-01', '2025-10-10', NULL, 1, 1, 100);

-- --------------------------------------------------------

--
-- Table structure for table `borrow_requests`
--

CREATE TABLE `borrow_requests` (
  `id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `request_date` datetime DEFAULT current_timestamp(),
  `status` enum('pending','approved','rejected') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`) VALUES
(1, 'Classic Literature', 'A classic novel about wealth, love, and the American Dream in the 1920.'),
(3, 'Mystery / Thriller', ''),
(4, 'Romance', '');

-- --------------------------------------------------------

--
-- Table structure for table `issued_books`
--

CREATE TABLE `issued_books` (
  `id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `issue_date` datetime DEFAULT current_timestamp(),
  `due_date` datetime NOT NULL,
  `return_date` datetime DEFAULT NULL,
  `status` enum('issued','returned','overdue') DEFAULT 'issued',
  `penalty` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `issued_books`
--

INSERT INTO `issued_books` (`id`, `book_id`, `student_id`, `issue_date`, `due_date`, `return_date`, `status`, `penalty`) VALUES
(20, 1, 8, '2026-01-08 00:00:00', '0000-00-00 00:00:00', '2026-01-08 14:50:29', 'issued', 7400210.00),
(21, 6, 8, '2026-01-08 00:00:00', '0000-00-00 00:00:00', '2026-01-08 14:56:05', 'issued', 7400210.00),
(22, 1, 8, '2026-01-08 00:00:00', '0000-00-00 00:00:00', '2026-01-08 14:59:54', 'issued', 3700105.00),
(23, 6, 8, '2026-01-08 00:00:00', '0000-00-00 00:00:00', '2026-01-08 15:05:59', 'issued', 0.00),
(24, 7, 8, '2026-01-08 15:11:34', '2026-01-08 00:00:00', '2026-01-08 15:11:58', 'issued', 0.00),
(25, 7, 8, '2026-01-08 15:12:41', '2026-01-08 00:00:00', '2026-01-08 15:12:47', 'issued', 0.00),
(26, 7, 8, '2026-01-08 15:13:19', '2026-01-09 00:00:00', '2026-01-08 15:13:28', 'issued', 0.00),
(27, 1, 8, '2026-01-08 15:18:44', '2026-01-08 00:00:00', '2026-01-08 15:18:52', 'issued', 0.00),
(28, 7, 8, '2026-01-08 15:20:20', '2026-01-09 00:00:00', '2026-01-08 15:20:39', 'issued', 0.00),
(30, 1, 10, '2026-01-08 00:00:00', '0000-00-00 00:00:00', '2026-01-08 15:53:39', 'issued', 0.00),
(31, 6, 10, '2026-01-08 00:00:00', '0000-00-00 00:00:00', '2026-01-08 23:56:14', 'issued', 0.00),
(32, 7, 8, '2026-01-08 15:52:47', '2026-01-08 00:00:00', '2026-01-08 15:55:57', 'issued', 0.00),
(33, 1, 10, '2026-01-08 00:00:00', '0000-00-00 00:00:00', '2026-01-08 23:57:50', 'issued', 0.00),
(34, 1, 10, '2026-01-08 00:00:00', '0000-00-00 00:00:00', '2026-01-08 23:59:04', 'issued', 0.00),
(35, 7, 10, '2026-01-08 00:00:00', '0000-00-00 00:00:00', '2026-01-09 00:00:06', 'issued', 0.00),
(36, 7, 11, '2026-01-09 00:08:10', '2026-01-06 00:00:00', '2026-01-09 00:08:26', 'issued', 30.00),
(37, 7, 11, '2026-01-09 00:00:00', '0000-00-00 00:00:00', '2026-01-09 00:12:28', 'issued', 0.00),
(38, 1, 11, '2026-01-09 00:21:20', '2026-01-01 00:00:00', '2026-01-09 00:26:34', 'issued', 80.00),
(39, 6, 11, '2026-01-09 00:21:41', '2026-01-03 00:00:00', '2026-01-09 00:26:23', 'issued', 60.00),
(40, 6, 11, '2026-01-09 00:27:43', '2026-01-01 00:00:00', '2026-01-09 00:36:38', 'issued', 80.00),
(41, 7, 11, '2026-01-09 00:31:28', '2026-01-07 00:00:00', '2026-01-09 00:33:27', 'issued', 20.00),
(42, 7, 11, '2026-01-09 00:00:00', '0000-00-00 00:00:00', '2026-01-09 00:36:11', 'issued', 0.00),
(43, 1, 11, '2026-01-09 00:00:00', '0000-00-00 00:00:00', '2026-01-09 00:36:24', 'issued', 0.00),
(44, 6, 11, '2026-01-09 00:00:00', '0000-00-00 00:00:00', '2026-01-09 00:37:56', 'issued', 0.00),
(45, 6, 11, '2026-01-09 00:00:00', '0000-00-00 00:00:00', '2026-01-09 00:38:23', 'issued', 0.00),
(46, 6, 11, '2026-01-09 00:00:00', '0000-00-00 00:00:00', '2026-01-09 01:21:28', 'issued', 0.00),
(47, 1, 8, '2026-01-09 00:39:43', '2026-01-07 00:00:00', '2026-01-09 01:36:28', 'issued', 20.00),
(48, 6, 8, '2026-01-09 00:40:05', '2026-01-07 00:00:00', NULL, 'issued', 0.00),
(49, 1, 11, '2026-01-09 00:00:00', '0000-00-00 00:00:00', '2026-01-09 01:24:00', 'issued', 0.00),
(50, 1, 11, '2026-01-09 00:00:00', '0000-00-00 00:00:00', '2026-01-09 01:36:36', 'issued', 0.00),
(51, 6, 11, '2026-01-09 00:00:00', '0000-00-00 00:00:00', NULL, 'issued', 0.00),
(52, 1, 11, '2026-01-09 04:31:18', '2026-01-06 00:00:00', '2026-01-09 04:31:37', 'issued', 30.00);

-- --------------------------------------------------------

--
-- Table structure for table `password_changes`
--

CREATE TABLE `password_changes` (
  `id` int(11) NOT NULL,
  `user_type` enum('admin','student') NOT NULL,
  `user_id` int(11) NOT NULL,
  `changed_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `student_id` varchar(50) NOT NULL,
  `name` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `address` text DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `gender` varchar(10) DEFAULT NULL,
  `course` varchar(100) DEFAULT NULL,
  `civil_status` varchar(20) DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `student_id`, `name`, `password`, `created_at`, `address`, `age`, `gender`, `course`, `civil_status`, `username`, `email`) VALUES
(8, '22-34234', 'Jeain Alalong', '$2y$10$LAk7mJ6zkHOGx8tkGoc9yOkSRMtVZu6NyGNvHUc43FbzjAUOHrUDy', '2026-01-09 01:02:51', 'MAMPANG. Z,C', 19, 'Male', 'AUTOMOTIVE TECHNOLOGY', 'Single', 'jeain04', 'alalong04@gmail.com'),
(10, '22-34235', 'GAIL SINDA CALUSCUSIN', '$2y$10$fd3DWReWT9jKBZcgiChWp.VpcCIpAPavAGv5YPR3435GGb.5GpF..', '2026-01-09 02:44:27', 'mampang. ZC', 23, 'Female', 'BS- INFORMATION TECHNOLOGY', 'Single', 'Gail16', 'caluscusin@gmail.com'),
(11, '11234', 'bess weak', '$2y$10$meRL2HluyckdQAZQvdQw0.x2k/DQrwMB0xTXiLZx882yMeU3xNyCq', '2026-01-09 11:01:50', 'mampang', 35, 'Male', 'ELECTRICAL TECHNOLOGY', 'Married', 'boss123', 'weak@gmail.com');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `authors`
--
ALTER TABLE `authors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `isbn` (`isbn`),
  ADD KEY `author_id` (`author_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `borrow_requests`
--
ALTER TABLE `borrow_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `book_id` (`book_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `issued_books`
--
ALTER TABLE `issued_books`
  ADD PRIMARY KEY (`id`),
  ADD KEY `book_id` (`book_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `password_changes`
--
ALTER TABLE `password_changes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_id` (`student_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `authors`
--
ALTER TABLE `authors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `books`
--
ALTER TABLE `books`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `borrow_requests`
--
ALTER TABLE `borrow_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `issued_books`
--
ALTER TABLE `issued_books`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `password_changes`
--
ALTER TABLE `password_changes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `books`
--
ALTER TABLE `books`
  ADD CONSTRAINT `books_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `authors` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `books_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `borrow_requests`
--
ALTER TABLE `borrow_requests`
  ADD CONSTRAINT `borrow_requests_ibfk_1` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `borrow_requests_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `issued_books`
--
ALTER TABLE `issued_books`
  ADD CONSTRAINT `issued_books_ibfk_1` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `issued_books_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
