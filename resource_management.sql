-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 18, 2025 at 03:00 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `resource_management`
--

-- --------------------------------------------------------

--
-- Table structure for table `resource_requests`
--

CREATE TABLE `resource_requests` (
  `id_requests` int(11) NOT NULL,
  `project_name` varchar(255) NOT NULL,
  `resource_type` varchar(100) NOT NULL,
  `num_resources` int(11) NOT NULL,
  `skills` text NOT NULL,
  `start_date` date NOT NULL,
  `duration` varchar(50) NOT NULL,
  `priority` enum('low','medium','high','critical') NOT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `resource_requests`
--

INSERT INTO `resource_requests` (`id_requests`, `project_name`, `resource_type`, `num_resources`, `skills`, `start_date`, `duration`, `priority`, `notes`, `status`, `created_at`) VALUES
(1, 'WEDREDESIGN', 'designer', 3, 'HMTL', '2025-10-17', '6months', 'high', '', 'pending', '2025-10-17 15:32:27');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('project_manager','hr_admin','employee') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reset_token` varchar(255) DEFAULT NULL,
  `token_expiry` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`, `reset_token`, `token_expiry`) VALUES
(1, 'John Manager', 'manager@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'project_manager', '2025-09-08 13:34:26', 'bc6a9605683e5585d7d885d4a2b0ead925cb31bc1a573e377d906773ae7f5698', '2025-10-15 16:13:36'),
(2, 'Sarah HR', 'hr@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'hr_admin', '2025-09-08 13:34:26', NULL, NULL),
(3, 'Alice Developer', 'alice@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employee', '2025-09-08 13:34:26', NULL, NULL),
(4, 'Bob Analyst', 'bob@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employee', '2025-09-08 13:34:26', NULL, NULL),
(5, 'Charlie Designer', 'charlie@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employee', '2025-09-08 13:34:26', NULL, NULL),
(6, 'Lynn Czyla', 'lynnzylameczdo@gmail.com', 'password', 'employee', '0000-00-00 00:00:00', '0db560553c1fc7d5529f5070fd1de4b170a3fe5c2c2408ae5fe22f32a01fd0bc', '2025-10-15 16:13:51');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `resource_requests`
--
ALTER TABLE `resource_requests`
  ADD PRIMARY KEY (`id_requests`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `resource_requests`
--
ALTER TABLE `resource_requests`
  MODIFY `id_requests` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
