-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 09, 2026 at 01:55 PM
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
-- Database: `mini_linkedin`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_auth`
--

CREATE TABLE `admin_auth` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_auth`
--

INSERT INTO `admin_auth` (`id`, `username`, `password`) VALUES
(1, 'admin', 'admin123');

-- --------------------------------------------------------

--
-- Table structure for table `applications`
--

CREATE TABLE `applications` (
  `id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `resume_path` varchar(255) NOT NULL,
  `status` enum('Pending','Reviewed','Round 1','Round 2','Hired','Rejected') DEFAULT 'Pending',
  `applied_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `applications`
--

INSERT INTO `applications` (`id`, `job_id`, `user_id`, `resume_path`, `status`, `applied_at`) VALUES
(1, 2, 4, '1775491266_24SDSCE01129_SAD_EX9.pdf', 'Hired', '2026-04-06 16:01:06'),
(2, 5, 4, '1775561449_24SDSCE01129_SAD_EX9.pdf', 'Pending', '2026-04-07 11:30:49'),
(3, 5, 6, '1775620844_24SDSCE01131       Mendapara vishwa manishbhai.pdf', 'Round 1', '2026-04-08 04:00:44'),
(4, 1, 4, '1775626857_24SDSCE01129_SAD_EX9.pdf', 'Pending', '2026-04-08 05:40:57');

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comment_text` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `company_credentials`
--

CREATE TABLE `company_credentials` (
  `id` int(11) NOT NULL,
  `company_name` varchar(150) NOT NULL,
  `about_us` text DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `logo_path` varchar(255) DEFAULT NULL,
  `location` varchar(150) DEFAULT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_verified` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `company_credentials`
--

INSERT INTO `company_credentials` (`id`, `company_name`, `about_us`, `website`, `logo_path`, `location`, `email`, `password`, `created_at`, `is_verified`) VALUES
(1, 'OneTochService', 'About TechWorld\r\nWelcome to TechWorld, where we believe that the best way to predict the future is to create it. Born in a hostel room amidst late-night coding sessions and endless cups of tea, TechWorld is more than just a coding company—it is a creative studio dedicated to turning complex ideas into seamless digital experiences.\r\n\r\nOur Vision\r\nWe don’t just write code; we build connections. At TechWorld, we understand the hustle and grit required to take a project from a blank screen to a functional reality. Whether it is a web platform, a mobile application, or an innovative IoT solution, our goal is to empower creators and students with the tools they need to thrive in a digital age.\r\n\r\nWhat We Do\r\nWeb & Mobile Development: Crafting high-performance platforms, from professional networking feeds to service-based mobile apps.\r\n\r\nUI/UX Design: Using tools like Figma to ensure every interface is as beautiful as it is functional.\r\n\r\nInnovative Solutions: Bridging the gap between hardware and software through IoT and creative engineering.\r\n\r\nWhy TechWorld?\r\nFounded by a developer who understands that technology is a journey of constant growth, TechWorld is built on the values of transparency, innovation, and community. We aren\'t just building software; we are building the future, one bug fix and one creative layout at a time.', 'https://techworld-it.com/', 'logo_1_1775621486.png', 'Rajkot', 'hetvikhunt13@gmail.com', '$2y$10$/L1K6qN12WtNOQodBeUaFuvdZBx7wV9WGsInPLr2IReA5Rf6Xi/lO', '2026-04-07 06:48:36', 1),
(2, 'LogicLayer', NULL, NULL, NULL, NULL, 'hr@gmail.com', '$2y$10$QNXu/oa.IMb4E58Oy9kQPOCziyb4rxG.DUEVJu4.qPPFqzcJdoQqq', '2026-04-08 04:47:46', 0);

-- --------------------------------------------------------

--
-- Table structure for table `connections`
--

CREATE TABLE `connections` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `status` enum('pending','accepted') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `connections`
--

INSERT INTO `connections` (`id`, `sender_id`, `receiver_id`, `status`, `created_at`) VALUES
(6, 2, 1, 'pending', '2026-04-06 15:51:05'),
(7, 2, 3, 'pending', '2026-04-06 15:51:07'),
(10, 4, 5, 'pending', '2026-04-06 15:52:18'),
(11, 2, 4, 'accepted', '2026-04-06 15:52:58'),
(12, 6, 1, 'pending', '2026-04-08 04:00:18'),
(13, 4, 3, 'pending', '2026-04-08 04:39:40'),
(14, 7, 1, 'pending', '2026-04-08 04:45:21'),
(15, 7, 3, 'pending', '2026-04-08 04:45:25'),
(16, 7, 4, 'accepted', '2026-04-08 04:45:28'),
(17, 8, 1, 'pending', '2026-04-08 05:38:32');

-- --------------------------------------------------------

--
-- Table structure for table `interviews`
--

CREATE TABLE `interviews` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `application_id` int(11) NOT NULL,
  `interview_date` date NOT NULL,
  `interview_time` time NOT NULL,
  `duration` int(11) DEFAULT 30,
  `timezone` varchar(50) DEFAULT 'IST',
  `interviewer_name` varchar(100) DEFAULT NULL,
  `interview_round` varchar(50) NOT NULL,
  `interview_type` varchar(100) DEFAULT 'General',
  `meeting_link` varchar(255) NOT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('Scheduled','Completed','Cancelled') DEFAULT 'Scheduled',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` int(11) NOT NULL,
  `company_id` int(11) DEFAULT NULL,
  `company_name` varchar(150) NOT NULL,
  `title` varchar(150) NOT NULL,
  `job_type` enum('Internship','Full-time','Part-time') NOT NULL,
  `location` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jobs`
--

INSERT INTO `jobs` (`id`, `company_id`, `company_name`, `title`, `job_type`, `location`, `description`, `created_at`) VALUES
(1, 1, 'TechCorp Solutions', 'Junior Web Developer', 'Full-time', 'Ahmedabad, Gujarat', 'Looking for a fresh graduate with skills in PHP, HTML, CSS, and basic JavaScript to join our growing development team.', '2026-04-06 16:00:35'),
(2, 1, 'Global IT Innovators', 'Android App Dev Intern', 'Internship', 'Remote', '3-month internship for computer science students. Must know Java or Kotlin and have experience with Android Studio.', '2026-04-06 16:00:35'),
(3, 1, 'DataSync Systems', 'Database Administrator', 'Full-time', 'Rajkot, Gujarat', 'Manage and optimize MySQL databases for our client portals. Great opportunity for recent IT graduates.', '2026-04-06 16:00:35'),
(5, 1, 'TechWorld', 'UI/UX', 'Full-time', 'Rajkot', 'We are looking for a user-centric UI/UX Designer to join our team. You will be responsible for delivering the best online user experience, which makes your role extremely important for customer satisfaction and brand loyalty. You will design graphic user interface elements, like menus, tabs, and widgets, while ensuring that all our products are visually appealing and easy to navigate.', '2026-04-07 07:20:25'),
(6, 1, 'TechWorld', 'content writing', 'Part-time', 'Remote', 'We are looking for a content writer for part time job who is good at writing know the skills and atleast 6 months of experience.', '2026-04-08 04:10:23');

-- --------------------------------------------------------

--
-- Table structure for table `login_credentials`
--

CREATE TABLE `login_credentials` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `account_status` enum('active','blocked') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `login_credentials`
--

INSERT INTO `login_credentials` (`id`, `email`, `password`, `created_at`, `account_status`) VALUES
(1, 'hkhunt708@gmail.com', '$2y$10$yZLCL.aesDidBfAXoQOWW.va5j0VhPIHQDuqMT/F4VAP1Qxv/eTyi', '2026-04-02 04:10:00', 'active'),
(2, 'vmendapara432@rku.ac.in', '$2y$10$WqSSlhRzhe6jXynKv8Q/1uD8i.Jrp2n333g1VlcKBDI4Z986eoXt6', '2026-04-03 07:13:58', 'active'),
(3, 'hetvikhunt13@gmail.com', '$2y$10$5prSVxn54bKN3JNcp.a.QuZ06QUIpwVtCPIv1jOw0S/xSSkO5P1A6', '2026-04-03 07:50:39', 'active'),
(4, 'hkhunt708@rku.ac.in', '$2y$10$eLYl9YdBdWQAsPn.G.Zd2eRDF5GCwDjrnBnXlby7DywGvI9hluTu2', '2026-04-06 12:11:21', 'active'),
(5, 'vmendapara431@rku.ac.in', '$2y$10$EC.WhKkKCyCQdtAOVQFXSuSG4fa0xQOt05aO356pf/2G7radKFbka', '2026-04-06 13:08:24', 'blocked'),
(6, 'a@gmail.com', '$2y$10$hhxR9Gm2TLvVAdb748q6PeWi.IKBj1SOSVKFONAoDzybA1AwMUkRe', '2026-04-08 03:58:59', 'active'),
(7, 'mgajera566@rku.ac.in', '$2y$10$Q9hFsY10H.JoZZORWNow5.99qoXdgWnrsu9Wo46T4mk02Jd./WgtC', '2026-04-08 04:40:33', 'active'),
(8, 'manshi@gmail.com', '$2y$10$DEuevDViK6L1gD9cXk0mSe4n2x8GczaqA/I1h/75VVACfrsfuEfiS', '2026-04-08 05:36:38', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `sender_type` enum('company','student') NOT NULL,
  `message_text` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `sender_type`, `message_text`, `is_read`, `created_at`) VALUES
(1, 4, 2, 'company', 'hiii', 0, '2026-04-06 16:14:55'),
(2, 4, 2, 'company', 'Hey! Check out this post I found on the dashboard: Post #6', 0, '2026-04-06 16:17:22'),
(3, 1, 4, 'company', 'hello we love to hire you', 0, '2026-04-07 07:49:03'),
(4, 4, 2, 'company', 'hello !!!', 0, '2026-04-08 05:00:52'),
(5, 4, 2, '', 'hi', 0, '2026-04-08 05:05:59'),
(6, 4, 2, '', 'i saw your post', 0, '2026-04-08 05:18:31');

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`id`, `user_id`, `content`, `image_path`, `created_at`) VALUES
(5, 4, 'Being a student isn\'t just about passing exams; it\'s about the late-night tea, the debugging marathons, and the constant urge to create something meaningful. Whether it\'s my \'One Touch Service\' app or my IoT projects, I’m putting my heart into every pixel and every logic. It’s a grind, but it’s a beautiful one. Thank you to everyone who has been part of my journey so far. Onwards and upwards! ✨', '', '2026-04-06 13:06:58'),
(6, 2, '', '1775481044_Screenshot 2026-04-06 184029.png', '2026-04-06 13:10:44'),
(8, 7, 'The best way to predict the future is to write it. ✍️✨\r\n\r\nMy hostel room has officially become my creative studio. Between late-night drafts and endless tea, I’ve realized that being a writer is about more than just words—it’s about the hustle, the grit, and the power to bring ideas to life from nothing.\r\n\r\nEvery sentence polished and every story told is a step toward the creator I want to be. We aren’t just students; we are voices in training.\r\n\r\nIf you’re working on a draft today—keep going. The world needs your story. Let’s grow together! 🚀\r\n\r\n#ContentWriter #CreativeHustle #Storytelling #GrowthMindset #StudentVoices', '', '2026-04-08 04:44:31');

-- --------------------------------------------------------

--
-- Table structure for table `post_interactions`
--

CREATE TABLE `post_interactions` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `interaction_type` enum('like','comment','share') NOT NULL,
  `content` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `post_interactions`
--

INSERT INTO `post_interactions` (`id`, `post_id`, `user_id`, `interaction_type`, `content`, `created_at`) VALUES
(1, 5, 4, 'like', NULL, '2026-04-06 13:07:00'),
(2, 5, 2, 'like', NULL, '2026-04-06 13:08:51'),
(3, 5, 2, 'comment', 'GOOD', '2026-04-06 13:08:57'),
(4, 6, 4, 'like', NULL, '2026-04-06 13:15:35'),
(5, 5, 6, 'like', NULL, '2026-04-08 04:00:05'),
(6, 6, 6, 'like', NULL, '2026-04-08 04:00:09'),
(8, 5, 7, 'like', NULL, '2026-04-08 04:40:54');

-- --------------------------------------------------------

--
-- Table structure for table `post_likes`
--

CREATE TABLE `post_likes` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `registration_details`
--

CREATE TABLE `registration_details` (
  `id` int(11) NOT NULL,
  `login_id` int(11) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `university` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `registration_details`
--

INSERT INTO `registration_details` (`id`, `login_id`, `fullname`, `university`) VALUES
(1, 1, 'Khunt Hetvi', 'Rk University'),
(2, 2, 'Vishwa Mendapara', 'Rk University'),
(3, 3, 'Hetvi Patel', 'Rk University'),
(4, 4, 'Hetvi Patel', 'Rk University'),
(5, 5, 'Mendapara Vishwa', 'Rk University'),
(6, 6, 'Krupal Patel', 'Atmiya University'),
(7, 7, 'Manshi Gajera', 'Silver Oak'),
(8, 8, 'manshi gajera', 'Rk University');

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL,
  `maintenance_mode` tinyint(1) DEFAULT 0,
  `broadcast_message` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `maintenance_mode`, `broadcast_message`, `updated_at`) VALUES
(1, 0, 'Welcome to Mini LinkedIn!', '2026-04-07 12:33:11');

-- --------------------------------------------------------

--
-- Table structure for table `user_profiles`
--

CREATE TABLE `user_profiles` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `skills` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_profiles`
--

INSERT INTO `user_profiles` (`id`, `user_id`, `profile_image`, `bio`, `location`, `skills`) VALUES
(1, 4, '1775493143_app logo android.png', '', 'Rajkot', 'HTML,CSS,FIGMA,PHP,JAVA,PYTHON'),
(2, 8, '1775626699_Screenshot 2026-04-06 175916.png', '', '', '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_auth`
--
ALTER TABLE `admin_auth`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `applications`
--
ALTER TABLE `applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `job_id` (`job_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_id` (`post_id`);

--
-- Indexes for table `company_credentials`
--
ALTER TABLE `company_credentials`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `connections`
--
ALTER TABLE `connections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Indexes for table `interviews`
--
ALTER TABLE `interviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `application_id` (`application_id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `login_credentials`
--
ALTER TABLE `login_credentials`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `post_interactions`
--
ALTER TABLE `post_interactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_id` (`post_id`);

--
-- Indexes for table `post_likes`
--
ALTER TABLE `post_likes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_id` (`post_id`);

--
-- Indexes for table `registration_details`
--
ALTER TABLE `registration_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `login_id` (`login_id`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_auth`
--
ALTER TABLE `admin_auth`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `applications`
--
ALTER TABLE `applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `company_credentials`
--
ALTER TABLE `company_credentials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `connections`
--
ALTER TABLE `connections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `interviews`
--
ALTER TABLE `interviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `login_credentials`
--
ALTER TABLE `login_credentials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `post_interactions`
--
ALTER TABLE `post_interactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `post_likes`
--
ALTER TABLE `post_likes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `registration_details`
--
ALTER TABLE `registration_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `user_profiles`
--
ALTER TABLE `user_profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `applications`
--
ALTER TABLE `applications`
  ADD CONSTRAINT `applications_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `applications_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `login_credentials` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `connections`
--
ALTER TABLE `connections`
  ADD CONSTRAINT `connections_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `login_credentials` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `connections_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `login_credentials` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `interviews`
--
ALTER TABLE `interviews`
  ADD CONSTRAINT `interviews_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `login_credentials` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `login_credentials` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `login_credentials` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `post_interactions`
--
ALTER TABLE `post_interactions`
  ADD CONSTRAINT `post_interactions_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `post_likes`
--
ALTER TABLE `post_likes`
  ADD CONSTRAINT `post_likes_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `registration_details`
--
ALTER TABLE `registration_details`
  ADD CONSTRAINT `registration_details_ibfk_1` FOREIGN KEY (`login_id`) REFERENCES `login_credentials` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD CONSTRAINT `user_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `login_credentials` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
