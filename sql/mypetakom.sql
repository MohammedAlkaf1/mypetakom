-- phpMyAdmin SQL Dump
-- version 5.1.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 28, 2025 at 04:24 PM
-- Server version: 10.4.24-MariaDB
-- PHP Version: 8.1.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mypetakom`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `attendance_id` int(11) NOT NULL,
  `event_id` int(11) DEFAULT NULL,
  `check_in_time` datetime DEFAULT NULL,
  `location` varchar(200) DEFAULT NULL,
  `attendance_status` enum('Active','Deactive') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `attendance_slot`
--

CREATE TABLE `attendance_slot` (
  `attendance_slot_id` int(11) NOT NULL,
  `attendance_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `qrcode_id` int(11) DEFAULT NULL,
  `status` enum('present','absent') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `committee_role`
--

CREATE TABLE `committee_role` (
  `cr_id` int(11) NOT NULL,
  `cr_desc` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `committee_role`
--

INSERT INTO `committee_role` (`cr_id`, `cr_desc`) VALUES
(1, 'committee'),
(2, 'main committee');

-- --------------------------------------------------------

--
-- Table structure for table `event`
--

CREATE TABLE `event` (
  `event_id` int(11) NOT NULL,
  `qrcode_id` int(11) DEFAULT NULL,
  `title` varchar(200) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `location` varchar(200) DEFAULT NULL,
  `event_start_date` date DEFAULT NULL,
  `event_status` enum('Upcoming','Postponed','Cancelled') DEFAULT NULL,
  `approval_letter` text DEFAULT NULL,
  `geolocation` int(100) DEFAULT NULL,
  `added_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `event`
--

INSERT INTO `event` (`event_id`, `qrcode_id`, `title`, `description`, `location`, `event_start_date`, `event_status`, `approval_letter`, `geolocation`, `added_by`) VALUES
(1, NULL, 'vfvv', 'fv', 'gg', '2025-05-28', 'Upcoming', 'uploads/1748370594_AQEL-M-38.pdf', 555, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `eventcommittee`
--

CREATE TABLE `eventcommittee` (
  `committee_id` int(11) NOT NULL,
  `event_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `cr_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `eventcommittee`
--

INSERT INTO `eventcommittee` (`committee_id`, `event_id`, `user_id`, `cr_id`) VALUES
(1, 1, 1, 2),
(3, 1, 3, 2);

-- --------------------------------------------------------

--
-- Table structure for table `membership`
--

CREATE TABLE `membership` (
  `membership_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `status` enum('approved','pending','not_approved') NOT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `student_matric_card` int(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `merit_application`
--

CREATE TABLE `merit_application` (
  `merit_id` int(11) NOT NULL,
  `event_id` int(11) DEFAULT NULL,
  `event_level` enum('International','National','State','District','UMPSA') NOT NULL,
  `points_main_committee` int(11) DEFAULT NULL,
  `points_committee` int(11) DEFAULT NULL,
  `points_participant` int(11) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `applied_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `merit_claims`
--

CREATE TABLE `merit_claims` (
  `claim_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `event_id` int(11) DEFAULT NULL,
  `role_claimed` enum('Main Committee','Committee','Participant') NOT NULL,
  `justification` text DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `official_letter_path` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `qrcode`
--

CREATE TABLE `qrcode` (
  `qrcode_id` int(11) NOT NULL,
  `code_url` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `user_id` int(11) NOT NULL,
  `position` enum('Advisor','Admin') NOT NULL,
  `staff_id_card` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

CREATE TABLE `student` (
  `user_id` int(11) NOT NULL,
  `major` varchar(20) DEFAULT NULL,
  `student_matric_id` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `student`
--

INSERT INTO `student` (`user_id`, `major`, `student_matric_id`) VALUES
(1, 'com', 'cb22'),
(2, 'computer', 'cb222'),
(3, 'computer', 'cb33');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `user_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('student','staff') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `name`, `email`, `password`, `role`) VALUES
(1, 'aziz', 'aaaaaaaaaaa@gmail.com', '111', 'student'),
(2, 'yash', 'aaaa', '111', 'student'),
(3, 'mohammed', 'aa444', '111', 'student');

-- --------------------------------------------------------

--
-- Table structure for table `view_awarded_merits`
--

CREATE TABLE `view_awarded_merits` (
  `student_merit_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `merit_id` int(11) DEFAULT NULL,
  `role` varchar(50) DEFAULT NULL,
  `points_awarded` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`attendance_id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `attendance_slot`
--
ALTER TABLE `attendance_slot`
  ADD PRIMARY KEY (`attendance_slot_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `qrcode_id` (`qrcode_id`),
  ADD KEY `attendance_id` (`attendance_id`);

--
-- Indexes for table `committee_role`
--
ALTER TABLE `committee_role`
  ADD PRIMARY KEY (`cr_id`);

--
-- Indexes for table `event`
--
ALTER TABLE `event`
  ADD PRIMARY KEY (`event_id`),
  ADD KEY `qrcode_id` (`qrcode_id`),
  ADD KEY `added_by` (`added_by`);

--
-- Indexes for table `eventcommittee`
--
ALTER TABLE `eventcommittee`
  ADD PRIMARY KEY (`committee_id`),
  ADD KEY `event_id` (`event_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `cr_id` (`cr_id`);

--
-- Indexes for table `membership`
--
ALTER TABLE `membership`
  ADD PRIMARY KEY (`membership_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `approved_by` (`approved_by`);

--
-- Indexes for table `merit_application`
--
ALTER TABLE `merit_application`
  ADD PRIMARY KEY (`merit_id`),
  ADD UNIQUE KEY `unique_event_merit` (`event_id`),
  ADD KEY `applied_by` (`applied_by`);

--
-- Indexes for table `merit_claims`
--
ALTER TABLE `merit_claims`
  ADD PRIMARY KEY (`claim_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `qrcode`
--
ALTER TABLE `qrcode`
  ADD PRIMARY KEY (`qrcode_id`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `student`
--
ALTER TABLE `student`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `view_awarded_merits`
--
ALTER TABLE `view_awarded_merits`
  ADD PRIMARY KEY (`student_merit_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `merit_id` (`merit_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `attendance_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `attendance_slot`
--
ALTER TABLE `attendance_slot`
  MODIFY `attendance_slot_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `committee_role`
--
ALTER TABLE `committee_role`
  MODIFY `cr_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `event`
--
ALTER TABLE `event`
  MODIFY `event_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `eventcommittee`
--
ALTER TABLE `eventcommittee`
  MODIFY `committee_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `membership`
--
ALTER TABLE `membership`
  MODIFY `membership_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `merit_application`
--
ALTER TABLE `merit_application`
  MODIFY `merit_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `merit_claims`
--
ALTER TABLE `merit_claims`
  MODIFY `claim_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `qrcode`
--
ALTER TABLE `qrcode`
  MODIFY `qrcode_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `view_awarded_merits`
--
ALTER TABLE `view_awarded_merits`
  MODIFY `student_merit_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `event` (`event_id`);

--
-- Constraints for table `attendance_slot`
--
ALTER TABLE `attendance_slot`
  ADD CONSTRAINT `attendance_slot_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`),
  ADD CONSTRAINT `attendance_slot_ibfk_2` FOREIGN KEY (`qrcode_id`) REFERENCES `qrcode` (`qrcode_id`),
  ADD CONSTRAINT `attendance_slot_ibfk_3` FOREIGN KEY (`attendance_id`) REFERENCES `attendance` (`attendance_id`);

--
-- Constraints for table `event`
--
ALTER TABLE `event`
  ADD CONSTRAINT `event_ibfk_1` FOREIGN KEY (`qrcode_id`) REFERENCES `qrcode` (`qrcode_id`),
  ADD CONSTRAINT `event_ibfk_2` FOREIGN KEY (`added_by`) REFERENCES `user` (`user_id`);

--
-- Constraints for table `eventcommittee`
--
ALTER TABLE `eventcommittee`
  ADD CONSTRAINT `eventcommittee_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `event` (`event_id`),
  ADD CONSTRAINT `eventcommittee_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`),
  ADD CONSTRAINT `eventcommittee_ibfk_3` FOREIGN KEY (`cr_id`) REFERENCES `committee_role` (`cr_id`);

--
-- Constraints for table `membership`
--
ALTER TABLE `membership`
  ADD CONSTRAINT `membership_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`),
  ADD CONSTRAINT `membership_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `user` (`user_id`);

--
-- Constraints for table `merit_application`
--
ALTER TABLE `merit_application`
  ADD CONSTRAINT `merit_application_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `event` (`event_id`),
  ADD CONSTRAINT `merit_application_ibfk_2` FOREIGN KEY (`applied_by`) REFERENCES `user` (`user_id`);

--
-- Constraints for table `merit_claims`
--
ALTER TABLE `merit_claims`
  ADD CONSTRAINT `merit_claims_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`),
  ADD CONSTRAINT `merit_claims_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `event` (`event_id`);

--
-- Constraints for table `staff`
--
ALTER TABLE `staff`
  ADD CONSTRAINT `staff_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);

--
-- Constraints for table `student`
--
ALTER TABLE `student`
  ADD CONSTRAINT `student_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);

--
-- Constraints for table `view_awarded_merits`
--
ALTER TABLE `view_awarded_merits`
  ADD CONSTRAINT `view_awarded_merits_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`),
  ADD CONSTRAINT `view_awarded_merits_ibfk_2` FOREIGN KEY (`merit_id`) REFERENCES `merit_application` (`merit_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
