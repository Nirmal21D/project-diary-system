-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 31, 2025 at 11:04 PM
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
-- Database: `micro`
--

-- --------------------------------------------------------

--
-- Table structure for table `diary_entries`
--

CREATE TABLE `diary_entries` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `project_group_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text DEFAULT NULL,
  `reviewed` tinyint(1) DEFAULT 0,
  `feedback` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `entry_date` datetime NOT NULL,
  `hours_spent` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `diary_feedback`
--

CREATE TABLE `diary_feedback` (
  `id` int(11) NOT NULL,
  `diary_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `comment` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `institutional_info`
--

CREATE TABLE `institutional_info` (
  `id` int(11) NOT NULL,
  `info_key` varchar(50) NOT NULL,
  `info_value` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `institutional_info`
--

INSERT INTO `institutional_info` (`id`, `info_key`, `info_value`, `updated_at`, `updated_by`) VALUES
(1, 'vision', 'qwertyuiop', '2025-03-31 15:30:24', 1),
(2, 'mission', 'wertyujhgbffdsgf', '2025-03-31 15:30:24', 1),
(3, 'core_values', 'wewfdsfdsfs', '2025-03-31 15:30:24', 1),
(4, 'guidelines', 'asdfsfdsfdsfsd', '2025-03-31 15:30:24', 1),
(5, 'about_us', 'dfsdfdsfdsfsdf', '2025-03-31 15:30:24', 1),
(6, 'contact_info', 'dfdsfsdfsdfsd', '2025-03-31 15:30:24', 1);

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` varchar(50) DEFAULT 'pending',
  `teacher_id` int(11) NOT NULL,
  `teacher_info` text DEFAULT NULL,
  `student_ids` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`id`, `name`, `description`, `status`, `teacher_id`, `teacher_info`, `student_ids`, `created_at`, `updated_at`) VALUES
(1, 'Nirmal Darekar', 'dsadasdsadasdasda', 'active', 4, '{\"id\":4,\"name\":\"Jane Smith\",\"email\":\"jane.smith@example.com\"}', '[\"3\",\"2\"]', '2025-03-31 16:56:16', '2025-03-31 16:56:16'),
(2, 'Nirmal Darekar', 'asdsadasdasdas', 'completed', 4, '{\"id\":4,\"name\":\"Jane Smith\",\"email\":\"jane.smith@example.com\"}', '[\"3\",\"2\"]', '2025-03-31 16:56:53', '2025-03-31 17:47:15'),
(3, 'Nirmal Drekar', 'wdwsdsadsad', 'active', 4, '{\"id\":4,\"name\":\"Jane Smith\",\"email\":\"jane.smith@example.com\"}', '[\"2\"]', '2025-03-31 20:44:22', '2025-03-31 20:44:22'),
(4, 'abc', 'asdsadasdasda', 'active', 4, '{\"id\":4,\"name\":\"Jane Smith\",\"email\":\"jane.smith@example.com\"}', '[\"3\",\"2\"]', '2025-03-31 20:45:09', '2025-03-31 20:45:09');

-- --------------------------------------------------------

--
-- Table structure for table `project_diary`
--

CREATE TABLE `project_diary` (
  `id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `status` enum('draft','submitted','reviewed','approved','rejected') DEFAULT 'draft',
  `submission_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `project_groups`
--

CREATE TABLE `project_groups` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('active','completed','archived') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `project_groups`
--

INSERT INTO `project_groups` (`id`, `name`, `teacher_id`, `description`, `start_date`, `end_date`, `status`, `created_at`, `updated_at`) VALUES
(10, 'Nirmal Darekar', 4, 'sadsadasdas', '2025-03-31', '2025-05-01', 'active', '2025-03-31 16:37:03', '2025-03-31 16:37:03');

-- --------------------------------------------------------

--
-- Table structure for table `project_group_members`
--

CREATE TABLE `project_group_members` (
  `id` int(11) NOT NULL,
  `project_group_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `project_group_members`
--

INSERT INTO `project_group_members` (`id`, `project_group_id`, `user_id`, `joined_at`) VALUES
(8, 10, 2, '2025-03-31 16:37:03');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','teacher','student') NOT NULL,
  `department` varchar(100) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT 'default.jpg',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `department`, `profile_image`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NULL, 'default.jpg', '2025-03-31 14:12:31', '2025-03-31 14:12:31'),
(2, 'Nirmal Darekar', 'nirmaldarekar90@gmail.com', '$2y$10$FDbqccWnLtzjU0joOUMGDuiIeqgYk/iRHFLZxUR8V/0l7CaYJF9le', 'student', NULL, 'default.jpg', '2025-03-31 14:55:55', '2025-03-31 14:55:55'),
(3, 'John Doe', 'john.doe@example.com', '$2y$10$dFqDytwz/GOn6Mp/ixXWuO4DsbFWNskeisGlnGLkkqAQ2Yh4.DiQa', 'student', NULL, 'default.jpg', '2025-03-31 15:21:59', '2025-03-31 15:21:59'),
(4, 'Jane Smith', 'jane.smith@example.com', '$2y$10$BLLeOwglZ/8UPK2yDmaMy.3HqmX.9k2.84lkVx.CPeL/P2w81EMJm', 'teacher', NULL, 'default.jpg', '2025-03-31 15:21:59', '2025-03-31 15:21:59');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `diary_entries`
--
ALTER TABLE `diary_entries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `project_group_id` (`project_group_id`);

--
-- Indexes for table `diary_feedback`
--
ALTER TABLE `diary_feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `teacher_id` (`teacher_id`),
  ADD KEY `idx_diary` (`diary_id`);

--
-- Indexes for table `institutional_info`
--
ALTER TABLE `institutional_info`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `info_key` (`info_key`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `project_diary`
--
ALTER TABLE `project_diary`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_group_status` (`group_id`,`status`),
  ADD KEY `idx_user` (`user_id`);

--
-- Indexes for table `project_groups`
--
ALTER TABLE `project_groups`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_teacher` (`teacher_id`);

--
-- Indexes for table `project_group_members`
--
ALTER TABLE `project_group_members`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `project_group_id` (`project_group_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_role` (`role`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `diary_entries`
--
ALTER TABLE `diary_entries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `diary_feedback`
--
ALTER TABLE `diary_feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `institutional_info`
--
ALTER TABLE `institutional_info`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `project_diary`
--
ALTER TABLE `project_diary`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `project_groups`
--
ALTER TABLE `project_groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `project_group_members`
--
ALTER TABLE `project_group_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `diary_entries`
--
ALTER TABLE `diary_entries`
  ADD CONSTRAINT `diary_entries_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `diary_entries_ibfk_2` FOREIGN KEY (`project_group_id`) REFERENCES `project_groups` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `diary_feedback`
--
ALTER TABLE `diary_feedback`
  ADD CONSTRAINT `diary_feedback_ibfk_1` FOREIGN KEY (`diary_id`) REFERENCES `project_diary` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `diary_feedback_ibfk_2` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `institutional_info`
--
ALTER TABLE `institutional_info`
  ADD CONSTRAINT `institutional_info_ibfk_1` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `project_diary`
--
ALTER TABLE `project_diary`
  ADD CONSTRAINT `project_diary_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `project_groups` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `project_diary_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `project_groups`
--
ALTER TABLE `project_groups`
  ADD CONSTRAINT `project_groups_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `project_group_members`
--
ALTER TABLE `project_group_members`
  ADD CONSTRAINT `project_group_members_ibfk_1` FOREIGN KEY (`project_group_id`) REFERENCES `project_groups` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `project_group_members_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
