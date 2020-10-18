-- phpMyAdmin SQL Dump
-- version 5.0.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Mar 16, 2020 at 07:59 AM
-- Server version: 10.4.11-MariaDB
-- PHP Version: 7.4.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dgx1_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `affiliation`
--

CREATE TABLE `affiliation` (
  `affiliation_ID` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `affiliation`
--

INSERT INTO `affiliation` (`affiliation_ID`, `name`) VALUES
(1, 'Student'),
(2, 'Faculty'),
(3, 'Research');

-- --------------------------------------------------------

--
-- Table structure for table `containers`
--

CREATE TABLE `containers` (
  `container_ID` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `isActive` tinyint(1) NOT NULL DEFAULT 0,
  `description` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `containers`
--

INSERT INTO `containers` (`container_ID`, `name`, `isActive`, `description`) VALUES
(1, 'Container 1', 1, 'Container 1 Description'),
(2, 'Container 2', 1, 'Container 2 Description');

-- --------------------------------------------------------

--
-- Table structure for table `emails`
--

CREATE TABLE `emails` (
  `email_ID` int(11) NOT NULL,
  `task_ID` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `status` varchar(255) NOT NULL,
  `time_sent` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `resources`
--

CREATE TABLE `resources` (
  `resource_ID` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `value` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `resources`
--

INSERT INTO `resources` (`resource_ID`, `type`, `value`) VALUES
(1, 'GPU', 0),
(2, 'GPU', 1),
(3, 'GPU', 2),
(4, 'GPU', 3),
(5, 'GPU', 4),
(6, 'GPU', 5),
(7, 'GPU', 6),
(8, 'GPU', 7);

-- --------------------------------------------------------

--
-- Table structure for table `status`
--

CREATE TABLE `status` (
  `status_ID` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `status`
--

INSERT INTO `status` (`status_ID`, `name`) VALUES
(1, 'under review'),
(2, 'approved'),
(3, 'rejected'),
(4, 'in progress (running)'),
(5, 'canceled'),
(6, 'completed');

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `task_ID` int(11) NOT NULL,
  `user_ID` int(11) NOT NULL,
  `container_ID` int(11) DEFAULT NULL,
  `status_ID` int(11) NOT NULL DEFAULT 1,
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `requested_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `requested_from` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `request_duration` int(11) NOT NULL COMMENT 'Hours',
  `num_resources_requested` int(11) NOT NULL,
  `num_resources_approved` int(11) DEFAULT NULL,
  `canceled_at` timestamp NULL DEFAULT NULL,
  `approved_from` timestamp NULL DEFAULT NULL,
  `approved_duration` int(11) DEFAULT NULL,
  `command_run` varchar(255) DEFAULT NULL,
  `comments` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tasks`
--

INSERT INTO `tasks` (`task_ID`, `user_ID`, `container_ID`, `status_ID`, `approved_by`, `approved_at`, `requested_at`, `requested_from`, `request_duration`, `num_resources_requested`, `num_resources_approved`, `canceled_at`, `approved_from`, `approved_duration`, `command_run`, `comments`) VALUES
(1, 2, 2, 3, 2, '2020-03-08 11:42:45', '2020-01-21 07:06:15', '2020-01-25 00:00:00', 3, 2, 2, NULL, NULL, NULL, 'docker run --gpus device=0,1,2,3,4,5,6,7 -it --rm -d --name Task_1 -p 6000:6006 -p 8000:8888 -p 20000:22 --mount type=bind,source=/home//,target=/tf/projects -w /tf/research Container 2 bash', ''),
(141, 2, 1, 2, 2, '2020-03-04 08:28:30', '2020-03-03 11:01:34', '2020-03-03 11:00:00', 12, 8, NULL, NULL, '2020-03-02 11:00:00', 12, 'docker run --gpus device=0,1,2,3,4,5,6,7 -it --rm -d --name Task_141 -p 6000:6006 -p 8000:8888 -p 20000:22 --mount type=bind,source=/home//,target=/tf/projects -w /tf/research Container 1 bash', ''),
(142, 2, 1, 3, 2, '2020-03-03 11:05:42', '2020-03-03 11:01:52', '2020-03-03 23:00:00', 20, 4, NULL, NULL, NULL, 20, 'docker run --gpus device=0,1,2,3 -it --rm -d --name Task_142 -p 6000:6006 -p 8000:8888 -p 20000:22 --mount type=bind,source=/home//,target=/tf/projects -w /tf/research Container 1 bash', ''),
(143, 2, 1, 3, 2, '2020-03-08 11:55:41', '2020-03-03 11:02:12', '2020-03-03 23:00:00', 13, 3, NULL, NULL, NULL, 13, 'docker run --gpus device=4,5,6 -it --rm -d --name Task_143 -p 6004:6006 -p 8004:8888 -p 20004:22 --mount type=bind,source=/home//,target=/tf/projects -w /tf/research Container 1 bash', ''),
(144, 2, 1, 2, 2, '2020-03-10 08:21:30', '2020-03-03 11:03:22', '2020-03-04 23:00:00', 13, 6, 6, NULL, '2020-03-04 23:00:00', 13, '0', ''),
(145, 2, 1, 2, 2, '2020-03-10 08:10:43', '2020-03-03 11:43:00', '2020-04-01 20:00:00', 13, 4, 5, NULL, '2020-04-01 20:00:00', 12, '0', ''),
(146, 2, 1, 2, 2, '2020-03-10 08:10:18', '2020-03-03 12:30:12', '2020-04-01 00:00:00', 12, 7, 7, NULL, '2020-04-01 00:00:00', 12, '0', ''),
(147, 38, 2, 2, 2, '2020-03-10 08:11:30', '2020-03-03 12:40:11', '2020-03-04 06:00:00', 13, 4, 4, NULL, '2020-03-04 06:00:00', 13, '0', ''),
(148, 2, 1, 3, 2, '2020-03-10 11:54:25', '2020-03-08 11:57:56', '2020-03-08 11:00:00', 2, 1, 1, NULL, NULL, 15, '0', ''),
(149, 2, 2, 3, 2, '2020-03-10 11:54:09', '2020-03-08 11:58:32', '2020-03-08 11:00:00', 24, 7, 7, NULL, NULL, 24, '0', ''),
(150, 2, 2, 1, NULL, NULL, '2020-03-11 06:44:39', '2020-03-11 11:00:00', 7, 5, NULL, NULL, NULL, NULL, NULL, ''),
(151, 2, 1, 1, NULL, NULL, '2020-03-11 06:45:05', '2020-03-11 18:00:00', 6, 5, NULL, NULL, NULL, NULL, NULL, ''),
(152, 38, 1, 1, NULL, NULL, '2020-03-11 08:18:54', '2020-03-24 21:00:00', 5, 5, NULL, NULL, NULL, NULL, NULL, '');

-- --------------------------------------------------------

--
-- Table structure for table `tasks_delete`
--

CREATE TABLE `tasks_delete` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `start_date` date NOT NULL,
  `numDays` int(11) NOT NULL,
  `numGPUs` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tasks_delete`
--

INSERT INTO `tasks_delete` (`id`, `name`, `start_date`, `numDays`, `numGPUs`) VALUES
(1, 'Tania', '2020-01-16', 3, 2),
(2, 'Khaled', '2020-01-18', 2, 4),
(107, 'Fady', '2020-01-14', 3, 1);

-- --------------------------------------------------------

--
-- Table structure for table `task_resources`
--

CREATE TABLE `task_resources` (
  `task_ID` int(11) NOT NULL,
  `resource_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `task_resources`
--

INSERT INTO `task_resources` (`task_ID`, `resource_ID`) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 4),
(1, 5),
(1, 6),
(1, 7),
(1, 8),
(141, 1),
(141, 2),
(141, 3),
(141, 4),
(141, 5),
(141, 6),
(141, 7),
(141, 8),
(142, 1),
(142, 2),
(142, 3),
(142, 4),
(143, 5),
(143, 6),
(143, 7),
(144, 1),
(144, 2),
(144, 3),
(144, 4),
(144, 5),
(144, 6),
(145, 1),
(145, 2),
(145, 3),
(145, 4),
(146, 1),
(146, 2),
(146, 3),
(146, 4),
(146, 5),
(146, 6),
(146, 7),
(147, 1),
(147, 2),
(147, 3),
(147, 4),
(148, 1),
(149, 2),
(149, 3),
(149, 4),
(149, 5),
(149, 6),
(149, 7),
(149, 8),
(150, 1),
(150, 2),
(150, 3),
(150, 4),
(150, 5),
(151, 1),
(151, 2),
(151, 3),
(151, 4),
(151, 5),
(152, 1),
(152, 2),
(152, 3),
(152, 4),
(152, 5);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_ID` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `affiliation_ID` int(11) NOT NULL,
  `is_admin` tinyint(1) NOT NULL,
  `password` varchar(255) NOT NULL,
  `server_account` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_ID`, `name`, `email`, `affiliation_ID`, `is_admin`, `password`, `server_account`) VALUES
(2, 'Khaled', 'Khaled.waleed.app@gmail.com', 2, 1, '6269a28fc2c053bb09b5c3419fc78f0f', 'khaled'),
(3, '123', '123@123.com', 1, 0, '202cb962ac59075b964b07152d234b70', NULL),
(4, 'Tania', '201890064@uaeu.ac.ae', 3, 1, '19637a4a43e3978a377a14e7c158ac51', NULL),
(30, 'Test D', 'duscdofus@gmail.com', 1, 0, '202cb962ac59075b964b07152d234b70', NULL),
(31, 'Munkhjargal Gochoo', 'mgochoo@uaeu.ac.ae', 1, 0, '25d55ad283aa400af464c76d713c07ad', NULL),
(32, 'Sumayya', 'sumayya.khalid@uaeu.ac.ae', 1, 0, 'd01393436e02c4c5078bd5d4a9808182', NULL),
(33, 'hazem', 'habutaha94@gmail.com', 1, 0, '39449eaedba2eb4ec7685b6ad5166586', NULL),
(34, 'Muhammad', 'ameenebrahim@gmail.com', 1, 0, 'a7693098c801709198fa950523e7a4d9', NULL),
(35, 'Luqman Ali', '201990024@uaeu.ac.ae', 1, 1, 'e723a5a7acf8e4e7128146e8a8c7529d', NULL),
(38, 'Crudes', 'crudes123@gmail.com', 1, 0, '202cb962ac59075b964b07152d234b70', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `affiliation`
--
ALTER TABLE `affiliation`
  ADD PRIMARY KEY (`affiliation_ID`);

--
-- Indexes for table `containers`
--
ALTER TABLE `containers`
  ADD PRIMARY KEY (`container_ID`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `emails`
--
ALTER TABLE `emails`
  ADD PRIMARY KEY (`email_ID`),
  ADD KEY `FK_emails_tasks_task_ID` (`task_ID`);

--
-- Indexes for table `resources`
--
ALTER TABLE `resources`
  ADD PRIMARY KEY (`resource_ID`);

--
-- Indexes for table `status`
--
ALTER TABLE `status`
  ADD PRIMARY KEY (`status_ID`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`task_ID`),
  ADD KEY `FK_tasks_users_user_ID` (`user_ID`),
  ADD KEY `FK_tasks_users_approved_by` (`approved_by`),
  ADD KEY `FK_tasks_containers_container_ID` (`container_ID`),
  ADD KEY `FK_tasks_status_status_ID` (`status_ID`);

--
-- Indexes for table `tasks_delete`
--
ALTER TABLE `tasks_delete`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `task_resources`
--
ALTER TABLE `task_resources`
  ADD PRIMARY KEY (`task_ID`,`resource_ID`),
  ADD KEY `FK_task_resources_resources_resource_ID` (`resource_ID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_ID`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `FK_users_affiliation_user_ID` (`affiliation_ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `affiliation`
--
ALTER TABLE `affiliation`
  MODIFY `affiliation_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `containers`
--
ALTER TABLE `containers`
  MODIFY `container_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `emails`
--
ALTER TABLE `emails`
  MODIFY `email_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `resources`
--
ALTER TABLE `resources`
  MODIFY `resource_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `status`
--
ALTER TABLE `status`
  MODIFY `status_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `task_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=153;

--
-- AUTO_INCREMENT for table `tasks_delete`
--
ALTER TABLE `tasks_delete`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=109;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `emails`
--
ALTER TABLE `emails`
  ADD CONSTRAINT `FK_emails_tasks_task_ID` FOREIGN KEY (`task_ID`) REFERENCES `tasks` (`task_ID`);

--
-- Constraints for table `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `FK_tasks_containers_container_ID` FOREIGN KEY (`container_ID`) REFERENCES `containers` (`container_ID`),
  ADD CONSTRAINT `FK_tasks_status_status_ID` FOREIGN KEY (`status_ID`) REFERENCES `status` (`status_id`),
  ADD CONSTRAINT `FK_tasks_users_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `users` (`user_ID`),
  ADD CONSTRAINT `FK_tasks_users_user_ID` FOREIGN KEY (`user_ID`) REFERENCES `users` (`user_ID`);

--
-- Constraints for table `task_resources`
--
ALTER TABLE `task_resources`
  ADD CONSTRAINT `FK_task_resources_resources_resource_ID` FOREIGN KEY (`resource_ID`) REFERENCES `resources` (`resource_ID`),
  ADD CONSTRAINT `FK_task_resources_tasks_task_ID` FOREIGN KEY (`task_ID`) REFERENCES `tasks` (`task_ID`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `FK_users_affiliation_user_ID` FOREIGN KEY (`affiliation_ID`) REFERENCES `affiliation` (`affiliation_ID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;


ALTER TABLE `tasks`
CHANGE `command_run` `command_run` longtext DEFAULT NULL;
            
CREATE TABLE `server_accounts` (
  `account_ID` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL,
  PRIMARY KEY (`account_ID`),
  UNIQUE KEY `name` (`name`)
);

INSERT INTO `server_accounts` 
    (`name`) 
VALUES 
    ('tetiana'), 
    ('khaled'), 
    ('luqman');

SET FOREIGN_KEY_CHECKS=0;
ALTER TABLE `users`
CHANGE `server_account` `server_account_ID` int(11) DEFAULT NULL,
ADD CONSTRAINT `FK_users_server_account_ID` FOREIGN KEY (`server_account_ID`) REFERENCES `server_accounts`(`account_ID`);
SET FOREIGN_KEY_CHECKS=1;


ALTER TABLE `tasks`
CHANGE `command_run` `command_run` longtext DEFAULT NULL;
            
CREATE TABLE `server_accounts` (
  `account_ID` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL,
  PRIMARY KEY (`account_ID`),
  UNIQUE KEY `name` (`name`)
);

INSERT INTO `server_accounts` 
    (`name`) 
VALUES 
    ('tetiana'), 
    ('khaled'), 
    ('luqman');

SET FOREIGN_KEY_CHECKS=0;
ALTER TABLE `users`
CHANGE `server_account` `server_account_ID` int(11) DEFAULT NULL,
ADD CONSTRAINT `FK_users_server_account_ID` FOREIGN KEY (`server_account_ID`) REFERENCES `server_accounts`(`account_ID`);
SET FOREIGN_KEY_CHECKS=1;


ALTER TABLE `tasks` ADD `canceled_by` int(11) DEFAULT NULL AFTER `canceled_at`;

