-- phpMyAdmin SQL Dump
-- version 5.1.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 24, 2025 at 03:07 PM
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
  `location` varchar(255) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `attendance_status` enum('present','absent') DEFAULT NULL
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
  `status` enum('valid','invalid') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `comitee_role`
--

CREATE TABLE `comitee_role` (
  `cr_id` int(11) NOT NULL,
  `cr_desc` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `comitee_role`
--

INSERT INTO `comitee_role` (`cr_id`, `cr_desc`) VALUES
(1, 'Main committee'),
(2, 'committee\r\n');

-- --------------------------------------------------------

--
-- Table structure for table `event`
--

CREATE TABLE `event` (
  `event_id` int(11) NOT NULL,
  `qrcode_id` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `event_start_date` date DEFAULT NULL,
  `event_status` enum('Upcoming','postponed','cancelled') DEFAULT 'Upcoming',
  `approval_letter` text DEFAULT NULL,
  `geolocation` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `event`
--

INSERT INTO `event` (`event_id`, `qrcode_id`, `title`, `description`, `location`, `event_start_date`, `event_status`, `approval_letter`, `geolocation`) VALUES
(22, NULL, '4444', '4\r\n\r\n', '4\\', '2025-05-17', '', 'uploads/1747483267_نتيجه الفصل الدراسي الثالث.pdf', '444');

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

-- --------------------------------------------------------

--
-- Table structure for table `membership`
--

CREATE TABLE `membership` (
  `membership_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `approved_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `merit_application`
--

CREATE TABLE `merit_application` (
  `Merit_id` int(11) NOT NULL,
  `event_id` int(11) DEFAULT NULL,
  `event_level` varchar(100) DEFAULT NULL,
  `points_main_committee` int(11) DEFAULT NULL,
  `points_committee` int(11) DEFAULT NULL,
  `points_participant` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `merit_application`
--

INSERT INTO `merit_application` (`Merit_id`, `event_id`, `event_level`, `points_main_committee`, `points_committee`, `points_participant`) VALUES
(5, 22, 'International', 100, 70, 50);

-- --------------------------------------------------------

--
-- Table structure for table `qrcode`
--

CREATE TABLE `qrcode` (
  `qrcode_id` int(11) NOT NULL,
  `code` varchar(100) DEFAULT NULL,
  `code_status` enum('active','inactive') DEFAULT NULL,
  `qr_image` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `qrcode`
--

INSERT INTO `qrcode` (`qrcode_id`, `code`, `code_status`, `qr_image`) VALUES
(1, 'http://localhost/project%20prototypes/Html_files/student_attendance.php?event_id=18', 'active', '../qr_images/event_18.png'),
(2, 'http://localhost/project%20prototypes/Html_files/student_attendance.php?event_id=18', 'active', '../qr_images/event_18.svg'),
(3, 'http://localhost/project%20prototypes/Html_files/student_attendance.php?event_id=18', 'active', '../qr_images/event_18.svg'),
(4, 'http://localhost/project%20prototypes/Html_files/student_attendance.php?event_id=18', 'active', '../qr_images/event_18.svg'),
(5, 'http://localhost/project%20prototypes/Html_files/student_attendance.php?event_id=18', 'active', '../qr_images/event_18.svg'),
(6, 'http://localhost/project%20prototypes/Html_files/student_attendance.php?event_id=18', 'active', '../qr_images/event_18.svg'),
(7, 'http://localhost/project%20prototypes/Html_files/student_attendance.php?event_id=18', 'active', '../qr_images/event_18.svg'),
(8, 'http://localhost/project%20prototypes/Html_files/student_attendance.php?event_id=19', 'active', '../qr_images/event_19.svg'),
(9, 'http://localhost/project%20prototypes/Html_files/student_attendance.php?event_id=18', 'active', '../qr_images/event_18.svg'),
(10, 'http://localhost/project%20prototypes/Html_files/student_attendance.php?event_id=20', 'active', '../qr_images/event_20.svg'),
(11, 'http://localhost/project%20prototypes/Html_files/student_attendance.php?event_id=18', 'active', '../qr_images/event_18.svg'),
(12, 'http://10.65.84.166/project%20prototypes/Html_files/student_attendance.php?event_id=18', 'active', '../qr_images/event_18.svg'),
(13, 'http://10.65.84.166/Project%20prototypes/Html_files/student_attendance.php?event_id=20', 'active', '../qr_images/event_20.svg'),
(14, 'http://10.65.84.166/Project%20prototypes/Html_files/student_attendance.php?event_id=22', 'active', '../qr_images/event_22.svg'),
(15, 'http://10.65.84.166/Project%20prototypes/Html_files/student_attendance.php?event_id=22', 'active', '../qr_images/event_22.svg');

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `user_id` int(11) NOT NULL,
  `position` varchar(50) DEFAULT NULL,
  `staff_id_card` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

CREATE TABLE `student` (
  `user_id` int(11) NOT NULL,
  `major` varchar(20) DEFAULT NULL,
  `student_matric_id` varchar(10) DEFAULT NULL,
  `student_matric_card` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `student_merit`
--

CREATE TABLE `student_merit` (
  `student_merit_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `Merit_id` int(11) DEFAULT NULL,
  `role` varchar(100) DEFAULT NULL,
  `points_awarded` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `user_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('student','staff','admin') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `name`, `email`, `password`, `role`) VALUES
(1, 'aziz', 'ax0557115346@gmail.com', '1111', 'student'),
(2, 'aziz', 'ss', 'ss', 'student'),
(6, 'sam', 'ax@gmail', '123', 'staff');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`attendance_id`),
  ADD KEY `event_id` (`event_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `attendance_slot`
--
ALTER TABLE `attendance_slot`
  ADD PRIMARY KEY (`attendance_slot_id`),
  ADD KEY `attendance_id` (`attendance_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `qrcode_id` (`qrcode_id`);

--
-- Indexes for table `comitee_role`
--
ALTER TABLE `comitee_role`
  ADD PRIMARY KEY (`cr_id`);

--
-- Indexes for table `event`
--
ALTER TABLE `event`
  ADD PRIMARY KEY (`event_id`),
  ADD KEY `qrcode_id` (`qrcode_id`);

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
  ADD PRIMARY KEY (`Merit_id`),
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
-- Indexes for table `student_merit`
--
ALTER TABLE `student_merit`
  ADD PRIMARY KEY (`student_merit_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `Merit_id` (`Merit_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

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
-- AUTO_INCREMENT for table `comitee_role`
--
ALTER TABLE `comitee_role`
  MODIFY `cr_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `event`
--
ALTER TABLE `event`
  MODIFY `event_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `eventcommittee`
--
ALTER TABLE `eventcommittee`
  MODIFY `committee_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `membership`
--
ALTER TABLE `membership`
  MODIFY `membership_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `merit_application`
--
ALTER TABLE `merit_application`
  MODIFY `Merit_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `qrcode`
--
ALTER TABLE `qrcode`
  MODIFY `qrcode_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `student_merit`
--
ALTER TABLE `student_merit`
  MODIFY `student_merit_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `event` (`event_id`),
  ADD CONSTRAINT `attendance_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);

--
-- Constraints for table `attendance_slot`
--
ALTER TABLE `attendance_slot`
  ADD CONSTRAINT `attendance_slot_ibfk_1` FOREIGN KEY (`attendance_id`) REFERENCES `attendance` (`attendance_id`),
  ADD CONSTRAINT `attendance_slot_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`),
  ADD CONSTRAINT `attendance_slot_ibfk_3` FOREIGN KEY (`qrcode_id`) REFERENCES `qrcode` (`qrcode_id`);

--
-- Constraints for table `event`
--
ALTER TABLE `event`
  ADD CONSTRAINT `event_ibfk_1` FOREIGN KEY (`qrcode_id`) REFERENCES `qrcode` (`qrcode_id`);

--
-- Constraints for table `eventcommittee`
--
ALTER TABLE `eventcommittee`
  ADD CONSTRAINT `eventcommittee_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `event` (`event_id`),
  ADD CONSTRAINT `eventcommittee_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`),
  ADD CONSTRAINT `eventcommittee_ibfk_3` FOREIGN KEY (`cr_id`) REFERENCES `comitee_role` (`cr_id`);

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
  ADD CONSTRAINT `merit_application_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `event` (`event_id`);

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
-- Constraints for table `student_merit`
--
ALTER TABLE `student_merit`
  ADD CONSTRAINT `student_merit_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`),
  ADD CONSTRAINT `student_merit_ibfk_2` FOREIGN KEY (`Merit_id`) REFERENCES `merit_application` (`Merit_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
