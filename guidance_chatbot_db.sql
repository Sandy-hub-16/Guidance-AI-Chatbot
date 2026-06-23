-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 22, 2026 at 04:12 AM
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
-- Database: `guidance_chatbot_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `checkins`
--

CREATE TABLE `checkins` (
  `checkin_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `mood_score` tinyint(4) DEFAULT NULL,
  `sleep_hours` decimal(3,1) DEFAULT NULL,
  `study_hours_week` decimal(4,1) DEFAULT NULL,
  `attendance_pct` decimal(5,2) DEFAULT NULL,
  `gpa_self_report` decimal(3,2) DEFAULT NULL,
  `workload_score` tinyint(4) DEFAULT NULL,
  `social_score` tinyint(4) DEFAULT NULL,
  `financial_stress_score` tinyint(4) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `checkins`
--

INSERT INTO `checkins` (`checkin_id`, `student_id`, `mood_score`, `sleep_hours`, `study_hours_week`, `attendance_pct`, `gpa_self_report`, `workload_score`, `social_score`, `financial_stress_score`, `created_at`) VALUES
(1, 1, 1, 6.0, 8.0, 100.00, 1.37, 4, 2, 4, '2026-06-21 14:15:51'),
(2, 1, 2, 6.0, 10.0, 100.00, 1.37, 4, 2, 4, '2026-06-21 14:23:34'),
(3, 1, 2, 6.0, 10.0, 100.00, 1.37, 4, 2, 4, '2026-06-21 14:23:47');

-- --------------------------------------------------------

--
-- Table structure for table `condition_logs`
--

CREATE TABLE `condition_logs` (
  `log_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `condition_type` enum('academic','health','schedule','social','financial','sleep') NOT NULL,
  `severity` enum('mild','moderate','severe') NOT NULL,
  `source` enum('checkin','chat') NOT NULL,
  `source_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `condition_logs`
--

INSERT INTO `condition_logs` (`log_id`, `student_id`, `condition_type`, `severity`, `source`, `source_id`, `created_at`) VALUES
(1, 1, 'sleep', 'moderate', 'checkin', 1, '2026-06-21 14:16:03'),
(2, 1, 'health', 'severe', 'checkin', 1, '2026-06-21 14:16:03'),
(3, 1, 'schedule', 'moderate', 'checkin', 1, '2026-06-21 14:16:03'),
(4, 1, 'social', 'moderate', 'checkin', 1, '2026-06-21 14:16:03'),
(5, 1, 'financial', 'moderate', 'checkin', 1, '2026-06-21 14:16:03'),
(6, 1, 'sleep', 'moderate', 'checkin', 1, '2026-06-21 14:17:17'),
(7, 1, 'health', 'severe', 'checkin', 1, '2026-06-21 14:17:17'),
(8, 1, 'schedule', 'moderate', 'checkin', 1, '2026-06-21 14:17:17'),
(9, 1, 'social', 'moderate', 'checkin', 1, '2026-06-21 14:17:17'),
(10, 1, 'financial', 'moderate', 'checkin', 1, '2026-06-21 14:17:17'),
(11, 1, 'sleep', 'moderate', 'checkin', 3, '2026-06-21 14:23:52'),
(12, 1, 'health', 'severe', 'checkin', 3, '2026-06-21 14:23:52'),
(13, 1, 'schedule', 'moderate', 'checkin', 3, '2026-06-21 14:23:52'),
(14, 1, 'social', 'moderate', 'checkin', 3, '2026-06-21 14:23:52'),
(15, 1, 'financial', 'moderate', 'checkin', 3, '2026-06-21 14:23:52'),
(16, 1, 'sleep', 'moderate', 'checkin', 3, '2026-06-21 14:26:07'),
(17, 1, 'health', 'severe', 'checkin', 3, '2026-06-21 14:26:07'),
(18, 1, 'schedule', 'moderate', 'checkin', 3, '2026-06-21 14:26:07'),
(19, 1, 'social', 'moderate', 'checkin', 3, '2026-06-21 14:26:07'),
(20, 1, 'financial', 'moderate', 'checkin', 3, '2026-06-21 14:26:07');

-- --------------------------------------------------------

--
-- Table structure for table `conversations`
--

CREATE TABLE `conversations` (
  `conversation_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `started_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `ended_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `conversations`
--

INSERT INTO `conversations` (`conversation_id`, `student_id`, `started_at`, `ended_at`) VALUES
(1, 1, '2026-06-21 14:02:07', NULL),
(2, 1, '2026-06-21 14:03:30', NULL),
(3, 1, '2026-06-21 14:07:08', NULL),
(4, 1, '2026-06-21 14:16:01', NULL),
(5, 1, '2026-06-21 14:23:50', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `crisis_keywords`
--

CREATE TABLE `crisis_keywords` (
  `keyword_id` int(11) NOT NULL,
  `keyword` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `crisis_keywords`
--

INSERT INTO `crisis_keywords` (`keyword_id`, `keyword`) VALUES
(11, 'better off without me'),
(12, 'can\'t go on'),
(4, 'end my life'),
(13, 'give up on life'),
(9, 'hurt myself'),
(8, 'hurting myself'),
(3, 'kill myself'),
(10, 'no reason to live'),
(6, 'self harm'),
(7, 'self-harm'),
(2, 'suicidal'),
(1, 'suicide'),
(5, 'want to die');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `message_id` int(11) NOT NULL,
  `conversation_id` int(11) NOT NULL,
  `sender` enum('student','bot') NOT NULL,
  `message_text` text NOT NULL,
  `flagged_crisis` tinyint(1) DEFAULT 0,
  `recommended_rec_ids` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`message_id`, `conversation_id`, `sender`, `message_text`, `flagged_crisis`, `recommended_rec_ids`, `created_at`) VALUES
(1, 1, 'student', 'Hi, I\'m having a hard time focusing on my thesis lately.', 0, NULL, '2026-06-21 14:02:07'),
(2, 1, 'bot', 'It can be really challenging to stay motivated, especially when working on a long-term project like a thesis. Can you tell me a bit more about what\'s been going on and how you\'ve been feeling lately? Have you tried breaking down your thesis into smaller, more manageable tasks to help you regain your focus? Sometimes, taking a step back and re-evaluating our goals and priorities can be helpful. What\'s been the most difficult part of your thesis that\'s been on your mind lately?', 0, NULL, '2026-06-21 14:02:11'),
(3, 2, 'student', 'I just want to end my life', 1, NULL, '2026-06-21 14:03:30'),
(4, 2, 'bot', 'I want to make sure you get real support right now, not just a reply from me. Please reach out to the PUP Guidance Office or someone you trust nearby. You can also call the NCMH Crisis Hotline, free and available 24/7: 1553 (landline, toll-free) or 0917-899-8727 / 0919-057-1553 (mobile). You don\'t have to go through this alone.', 1, NULL, '2026-06-21 14:03:30'),
(5, 3, 'student', 'Testing with CSRF token now.', 0, NULL, '2026-06-21 14:07:08'),
(6, 3, 'bot', 'It sounds like you\'re working on a technical project, perhaps related to web development or cybersecurity. Can you tell me more about what you\'re trying to accomplish with the CSRF token, and what challenges you\'re facing? Are you working on a specific assignment or personal project at PUP? What\'s been the most frustrating part of this process so far?', 0, NULL, '2026-06-21 14:07:19'),
(7, 4, 'student', 'hi', 0, NULL, '2026-06-21 14:16:01'),
(8, 4, 'bot', 'Hello, it\'s nice to meet you. I can tell that you might be dealing with a few challenges right now, from managing your schedule to taking care of your health. Would you like to talk about what\'s been on your mind and how you\'re feeling lately?', 0, '33,34,11,12,15,16,21,22,27,28', '2026-06-21 14:16:03'),
(9, 4, 'student', 'Currently, I\'m doing all the work in our IT-Elective subject where we will build a recommendation system as a group. How can I convince my groupmates to help me', 0, NULL, '2026-06-21 14:17:14'),
(10, 4, 'bot', 'It can be really frustrating when you feel like you\'re shouldering all the responsibility. Before talking to your groupmates, it might be helpful to document specific areas where you feel like they\'re not contributing, so you can have a factual conversation about it. Have you considered talking to your thesis adviser about the workload imbalance, or would you like to try discussing it with your groupmates first and see how that goes?', 0, '33,34,11,12,15,16,21,22,27,28', '2026-06-21 14:17:17'),
(11, 5, 'student', 'Currently, I\'m doing all the work in our IT-Elective subject where we will build a recommendation system as a group. How can I convince my groupmates to help me', 0, NULL, '2026-06-21 14:23:50'),
(12, 5, 'bot', 'It can be really frustrating when you feel like you\'re shouldering all the responsibility in a group project. Before talking to your groupmates, it might be helpful to document specific contribution gaps, so the conversation stays factual and focused on finding a solution. Have you considered talking to your groupmates about redistributing tasks more evenly this week, and how do you think they\'ll respond to that suggestion?', 0, '33,34,11,12,15,16,21,22,27,28', '2026-06-21 14:23:52'),
(13, 5, 'student', 'hello may problema ako sa studies ko, pano ko papabilisin or mapapadali ung workload', 0, NULL, '2026-06-21 14:26:05'),
(14, 5, 'bot', 'It sounds like you\'re feeling overwhelmed with your studies. Let\'s break it down - are there any specific deadlines or tasks that are causing you the most stress? Maybe we can identify which ones have the most flexibility and tackle the rigid ones first, that way you can prioritize and manage your time more effectively. How are you currently managing your time, and are there any tasks that you think could be delegated or postponed?', 0, '33,34,11,12,15,16,21,22,27,28', '2026-06-21 14:26:07');

-- --------------------------------------------------------

--
-- Table structure for table `recommendations`
--

CREATE TABLE `recommendations` (
  `rec_id` int(11) NOT NULL,
  `condition_type` enum('academic','health','schedule','social','financial','sleep') NOT NULL,
  `severity` enum('mild','moderate','severe') NOT NULL,
  `activity_text` text NOT NULL,
  `activity_type` enum('self_help','campus_resource','counselor_referral') NOT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `recommendations`
--

INSERT INTO `recommendations` (`rec_id`, `condition_type`, `severity`, `activity_text`, `activity_type`, `is_active`) VALUES
(1, 'academic', 'mild', 'Try the 25-minute Pomodoro technique for your next study session to break review time into manageable chunks.', 'self_help', 1),
(2, 'academic', 'mild', 'Visit your course adviser to review which subjects need the most attention this term.', 'campus_resource', 1),
(3, 'academic', 'moderate', 'Schedule a consultation with your subject professor during consultation hours to clarify topics you are struggling with.', 'campus_resource', 1),
(4, 'academic', 'moderate', 'Form a small study group with classmates from your block for the subjects you find hardest.', 'self_help', 1),
(5, 'academic', 'severe', 'Set up an appointment with the PUP Guidance Office to discuss academic standing and possible support options.', 'counselor_referral', 1),
(6, 'academic', 'severe', 'Talk to your program chair about options like tutorial assistance or adjusting your course load.', 'campus_resource', 1),
(7, 'health', 'mild', 'Take a 10-minute walk between study sessions to reset your focus.', 'self_help', 1),
(8, 'health', 'mild', 'Make sure you are drinking enough water and eating regular meals during busy weeks.', 'self_help', 1),
(9, 'health', 'moderate', 'Consider light indoor activities like stretching or yoga instead of vigorous sports until you are feeling better.', 'self_help', 1),
(10, 'health', 'moderate', 'Visit the campus clinic if your symptoms persist for more than a few days.', 'campus_resource', 1),
(11, 'health', 'severe', 'Please consult the campus clinic or a doctor as soon as you can.', 'campus_resource', 1),
(12, 'health', 'severe', 'Reach out to the Guidance Office so they can coordinate with your professors about deadline flexibility while you recover.', 'counselor_referral', 1),
(13, 'schedule', 'mild', 'Try blocking out your week in a planner or Google Calendar to see where the heaviest days are.', 'self_help', 1),
(14, 'schedule', 'mild', 'Break large deliverables into smaller weekly milestones instead of one big deadline.', 'self_help', 1),
(15, 'schedule', 'moderate', 'Talk to your groupmates about redistributing tasks more evenly this week.', 'self_help', 1),
(16, 'schedule', 'moderate', 'Identify which of your deadlines have the most flexibility and tackle the rigid ones first.', 'self_help', 1),
(17, 'schedule', 'severe', 'Message your professors ahead of time if you are at risk of missing a deadline — most appreciate early communication.', 'self_help', 1),
(18, 'schedule', 'severe', 'Set up a meeting with your adviser to review your overall course load for the term.', 'campus_resource', 1),
(19, 'social', 'mild', 'Schedule a short one-on-one chat with the groupmate you are having friction with — a call tends to go better than a chat thread.', 'self_help', 1),
(20, 'social', 'mild', 'Bring up task expectations clearly in your next group meeting so everyone is on the same page.', 'self_help', 1),
(21, 'social', 'moderate', 'Document specific contribution gaps before raising the issue with your group, so the conversation stays factual.', 'self_help', 1),
(22, 'social', 'moderate', 'Loop in your thesis adviser if the group cannot resolve the workload imbalance on its own.', 'campus_resource', 1),
(23, 'social', 'severe', 'If the conflict is affecting your wellbeing, consider speaking with the Guidance Office for support and mediation.', 'counselor_referral', 1),
(24, 'social', 'severe', 'Request a formal sit-down between your group and your adviser to address the contribution issue directly.', 'campus_resource', 1),
(25, 'financial', 'mild', 'Check if your campus has ongoing scholarship or assistance program postings you might qualify for.', 'campus_resource', 1),
(26, 'financial', 'mild', 'Track your weekly school-related expenses to spot where you can cut back.', 'self_help', 1),
(27, 'financial', 'moderate', 'Visit the Office of Student Affairs to ask about available financial assistance programs.', 'campus_resource', 1),
(28, 'financial', 'moderate', 'Ask the Accounting/Cashier office about staggered payment options if tuition is the immediate issue.', 'campus_resource', 1),
(29, 'financial', 'severe', 'Set up a meeting with the Guidance Office — they can connect you with financial assistance programs and possible deadline extensions.', 'counselor_referral', 1),
(30, 'financial', 'severe', 'Ask the Office of Student Affairs about emergency scholarship or assistance funds.', 'campus_resource', 1),
(31, 'sleep', 'mild', 'Try setting a consistent bedtime, even during crunch periods.', 'self_help', 1),
(32, 'sleep', 'mild', 'Avoid caffeine a few hours before you plan to sleep.', 'self_help', 1),
(33, 'sleep', 'moderate', 'Consider a short 20-minute power nap instead of pushing through fatigue during the day.', 'self_help', 1),
(34, 'sleep', 'moderate', 'Review your nightly routine — screen time right before bed can be delaying your sleep.', 'self_help', 1),
(35, 'sleep', 'severe', 'Ongoing lack of sleep can affect both health and academics — consider visiting the campus clinic if this continues.', 'campus_resource', 1),
(36, 'sleep', 'severe', 'Talk to the Guidance Office about workload adjustments if sleep loss is tied to academic stress.', 'counselor_referral', 1);

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `student_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `course` varchar(100) DEFAULT NULL,
  `year_level` tinyint(4) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`student_id`, `full_name`, `email`, `password_hash`, `course`, `year_level`, `created_at`) VALUES
(1, 'Sandy Napiza', 'napiza.sandy.lsei@gmail.com', '$2y$10$vp8UHqC/GI/xy3dG5ogUtezw6aBjpP7Z8xkc71jhYbZ7vo/ciVllq', 'BSIT', 3, '2026-06-21 13:57:51');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `checkins`
--
ALTER TABLE `checkins`
  ADD PRIMARY KEY (`checkin_id`),
  ADD KEY `idx_student_created` (`student_id`,`created_at`);

--
-- Indexes for table `condition_logs`
--
ALTER TABLE `condition_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `idx_student_condition` (`student_id`,`condition_type`);

--
-- Indexes for table `conversations`
--
ALTER TABLE `conversations`
  ADD PRIMARY KEY (`conversation_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `crisis_keywords`
--
ALTER TABLE `crisis_keywords`
  ADD PRIMARY KEY (`keyword_id`),
  ADD UNIQUE KEY `keyword` (`keyword`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `idx_conversation_created` (`conversation_id`,`created_at`);

--
-- Indexes for table `recommendations`
--
ALTER TABLE `recommendations`
  ADD PRIMARY KEY (`rec_id`),
  ADD KEY `idx_condition_severity` (`condition_type`,`severity`,`is_active`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`student_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `checkins`
--
ALTER TABLE `checkins`
  MODIFY `checkin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `condition_logs`
--
ALTER TABLE `condition_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `conversations`
--
ALTER TABLE `conversations`
  MODIFY `conversation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `crisis_keywords`
--
ALTER TABLE `crisis_keywords`
  MODIFY `keyword_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `recommendations`
--
ALTER TABLE `recommendations`
  MODIFY `rec_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `checkins`
--
ALTER TABLE `checkins`
  ADD CONSTRAINT `checkins_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE;

--
-- Constraints for table `condition_logs`
--
ALTER TABLE `condition_logs`
  ADD CONSTRAINT `condition_logs_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE;

--
-- Constraints for table `conversations`
--
ALTER TABLE `conversations`
  ADD CONSTRAINT `conversations_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`conversation_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- Migration: add remember_tokens table to guidance_chatbot_db
-- Run this once in phpMyAdmin > guidance_chatbot_db > SQL tab

CREATE TABLE `remember_tokens` (
  `id`         int(11)      NOT NULL AUTO_INCREMENT,
  `student_id` int(11)      NOT NULL,
  `token_hash` char(64)     NOT NULL,
  `expires_at` datetime     NOT NULL,
  `created_at` timestamp    NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_token_hash` (`token_hash`),
  KEY `idx_student_id` (`student_id`),
  CONSTRAINT `remember_tokens_ibfk_1`
    FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;