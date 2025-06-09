-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 09, 2025 at 05:52 AM
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
-- Database: `afprotech`
--

-- --------------------------------------------------------

--
-- Table structure for table `afpro_announcement`
--

CREATE TABLE `afpro_announcement` (
  `announcement_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_by` varchar(100) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `afpro_announcement`
--

INSERT INTO `afpro_announcement` (`announcement_id`, `message`, `created_by`, `created_at`) VALUES
(46, '📣 ANNOUNCEMENT: BFPT DAYS ARE HERE! 🎉\r\n\r\nGet ready for an exciting celebration as BFPT Days kick off! Join us for a series of fun, engaging, and memorable events filled with energy, teamwork, and community spirit.\r\n\r\n🗓️ Date: May 12-16 2025\r\n📍 Location: USTP OROQUIETA\r\n🎯 Activities Include:\r\n\r\nGames & Competitions\r\n\r\nWorkshops & Exhibitions\r\n\r\nAwards & Recognition\r\n\r\nFood, Fun & Fellowship!\r\n\r\nDon’t miss this chance to make great memories and celebrate all that makes BFPT special. Spread the word and bring your BFPT pride!\r\n\r\nLet’s make it unforgettable! 💥\r\n#BFPTDays #TogetherWeShine\r\n#USTP #OROQUIETA #USTPOROQUIETA', 'Lester Bulay', '2025-05-11 07:03:05');

-- --------------------------------------------------------

--
-- Table structure for table `afpro_attendance`
--

CREATE TABLE `afpro_attendance` (
  `attendance_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `check_in_time` datetime DEFAULT current_timestamp(),
  `check_out_time` datetime DEFAULT NULL,
  `status` varchar(20) DEFAULT current_timestamp(),
  `period` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `afpro_attendance`
--

INSERT INTO `afpro_attendance` (`attendance_id`, `user_id`, `event_id`, `check_in_time`, `check_out_time`, `status`, `period`) VALUES
(67, 11, 75, '2025-05-14 07:00:00', '2025-05-14 12:00:00', 'Present', 'Day');

-- --------------------------------------------------------

--
-- Table structure for table `afpro_events`
--

CREATE TABLE `afpro_events` (
  `event_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `location` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `afpro_events`
--

INSERT INTO `afpro_events` (`event_id`, `title`, `description`, `start_date`, `end_date`, `location`) VALUES
(75, 'IT DAYS', 'IT DAYS', '2025-05-14 16:00:00', '2025-05-16 16:00:00', 'USTP OROQUIETA CAMPUS'),
(76, 'hibhjbhj', 'khbjbhjbjbhj', '2025-05-21 00:00:00', '2025-05-23 00:00:00', 'USTP OROQUIETA CAMPUS');

-- --------------------------------------------------------

--
-- Table structure for table `afpro_feedback`
--

CREATE TABLE `afpro_feedback` (
  `feedback_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` between 1 and 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `afpro_promotion`
--

CREATE TABLE `afpro_promotion` (
  `promotion_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(11,0) NOT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `location` varchar(100) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `created_by` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `afpro_promotion`
--

INSERT INTO `afpro_promotion` (`promotion_id`, `title`, `description`, `price`, `start_date`, `end_date`, `location`, `image_url`, `created_by`) VALUES
(23, 'Boiled Chiken Egg Salad', 'boiled chicken egg salad is a salad that combines hard-boiled chicken eggs with other ingredients, often including mayonnaise and seasonings.\r\n\r\nIngredients:\r\n cups diced cooked chicken\r\n3 large hard-boiled eggs, diced\r\n½ onion, chopped\r\n1 stalk celery, diced\r\n2 tablespoons mayonnaise, or to taste\r\n2 tablespoons chopped fresh cilantro\r\n1 tablespoon chopped fresh parsley\r\nsalt and cracked black pepper to taste', 150, '2025-05-12 00:00:00', '2025-05-16 00:00:00', 'USTP OROQUIETA CAMPUS', 'img/682030c41d6a5_boiled-chicken-egg-salad-white-plate.jpg', 10),
(24, 'Boiled Spaghetti With Slice Sausage', 'Boiled Spaghetti with Sliced Sausage\r\nA simple yet flavorful dish featuring perfectly boiled spaghetti topped with tender slices of savory sausage. The pasta is lightly tossed with cheese and garnished with a fresh sprig of dill for a touch of color and herbal aroma. Served on a modern blue-rimmed plate, this meal combines comfort and presentation, making it ideal for a quick lunch or casual dinner.\r\n\r\nIngredients: \r\n200g spaghetti\r\n2–3 pieces of sausage (beef, pork, or chicken), sliced\r\n1 tbsp cooking oil or butter (for sautéing sausage)\r\nSalt (to taste, for boiling pasta)\r\nGrated cheese (optional, for topping)\r\nFresh dill or parsley (for garnish)\r\nOptional: minced garlic or onion for added flavor', 150, '2025-05-12 00:00:00', '2025-05-16 00:00:00', 'USTP OROQUIETA CAMPUS', 'img/68203202ee4c5_boiled-spaghetti-with-sliced-sausages-blue-plate.jpg', 10),
(26, 'Chicken Wings Spaghetti', 'Chicken Wings and Spaghetti is a soulful, hearty dish that pairs crispy, juicy chicken wings with a generous serving of spaghetti in a rich, savory meat sauce. The wings are typically seasoned and fried to perfection, offering a crunchy exterior and tender inside. The spaghetti is coated in a flavorful tomato-based sauce, often with ground beef or sausage, making it a satisfying and comforting meal with bold flavors and a homemade feel.\r\n\r\nIngredients:\r\n2 lbs chicken wings (separated into flats and drumettes)\r\n1 tsp salt\r\n1 tsp black pepper\r\n1 tsp garlic powder\r\n1 tsp paprika (optional, for color)\r\n½ tsp cayenne pepper (optional, for heat)\r\n1 cup all-purpose flour (for coating, if frying)\r\nOil for frying (vegetable or canola)', 275, '2025-05-12 00:00:00', '2025-05-16 00:00:00', 'USTP OROQUIETA CAMPUS', 'img/68203df8ecd6c_chicken-wing-spaghetti-plate-marble.jpg', 10),
(27, 'Fried Choken Meat Slice', 'Crispy fried chicken breast slices are thinly cut pieces of boneless chicken breast, marinated for tenderness, coated in a seasoned flour or breadcrumb mixture, and fried until golden brown. The result is a crunchy, flavorful crust encasing juicy, tender chicken. The dish is popular in Southern cuisine and fast-food settings, often served with sides like mashed potatoes, coleslaw, or fries, or used in sandwiches with pickles and sauces. The flavor profile typically includes savory, slightly spicy notes from seasonings like paprika, garlic powder, and black pepper, with the buttermilk marinade adding tanginess and moisture.\r\n\r\nIngredients:Chicken Breasts: 2 large boneless, skinless chicken breasts (approximately 1 lb or 450g total), sliced lengthwise into thin cutlets (about ¼-inch or 6mm thick)\r\nButtermilk: 1 cup (240ml), for tenderizing and flavor\r\nEgg: 1 large, to help bind the marinade and coating\r\nHot Sauce: 1 tablespoon (15ml), such as Frank’s RedHot or Tabasco, for a slight tangy kick (optional)\r\nSalt: 1 teaspoon (5g), for seasoning\r\nGarlic Powder: 1 teaspoon (3g), for savory flavor\r\nPaprika: 1 teaspoon (2g), for mild sweetness and color\r\nBlack Pepper: ½ teaspoon (1g), freshly ground or pre-ground, for mild heat', 200, '2025-05-12 00:00:00', '2025-05-16 00:00:00', 'USTP OROQUIETA CAMPUS', 'img/6820adf006997_white-plate-chicken-fried-meat-sliced-carrot.jpg', 10),
(29, 'barbaque', 'szzcszc', 200, '2025-05-14 00:00:00', '2025-05-16 00:00:00', 'USTP OROQUIETA CAMPUS', 'img/68242aa54c003_6820adf006997_white-plate-chicken-fried-meat-sliced-carrot.jpg', 10);

-- --------------------------------------------------------

--
-- Table structure for table `afpro_reports`
--

CREATE TABLE `afpro_reports` (
  `report_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `report_url` varchar(255) DEFAULT NULL,
  `generated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `afpro_users`
--

CREATE TABLE `afpro_users` (
  `user_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `student_id` varchar(20) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `profile_image` blob DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `afpro_users`
--

INSERT INTO `afpro_users` (`user_id`, `first_name`, `last_name`, `password`, `student_id`, `username`, `email`, `profile_image`) VALUES
(3, 'Lester', 'Bulay', '$2y$10$GNumX1i2mdG9u2SSlkJjSeDPTvwad3s7xO2Tx1Zbjy1INGfYhjUgy', '2023304617', 'lesterbulay123x', '', NULL),
(6, 'Lester', 'Bulay', '$2y$10$UzpE9eD8l3qoONyQuRYO1eqMBzJCFLIn22rNGIfndHM24QLcZZcKO', '2023304611', 'lesterbulay123x45', '', NULL),
(10, 'Lester', 'Bulay', '$2y$10$IYPSLcDl3Xb5glSNqTVkluSigJ62oIFSziaWE8NbztdrgxC4QJTlG', '2023304615', 'Admin2025', '', NULL),
(11, 'Lester', 'Bulay', '$2y$10$NXiHFeiCA/QhE1urWfL6WupGeFFAz.O.XaLRCG4vqyAlwtovvM0kq', '2023304614', 'xximba12345x1', 'les**********@gmail.com', 0x696d672f70726f66696c655f31315f313734363938383238362e706e67),
(12, 'Kent', 'Carreon', '$2y$10$g2LtsCbpgbDqsGK4TcIzNe3gLGlLHrYvrSynrC4CmDn4H6oA1WUSS', '2023304618', 'lesterbulay123x454', '', NULL),
(13, 'jay mark', 'palania', '$2y$10$i2EjfLojLBLVC0oWD7YJ1uNuAfo8ci0DWTcvXjqNGUqV5aK3lLRZm', '2023306358', 'rj', '', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `announcement`
--

CREATE TABLE `announcement` (
  `announcement_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_by` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcement`
--

INSERT INTO `announcement` (`announcement_id`, `message`, `created_by`, `created_at`) VALUES
(1, 'guvgvghvhghvghvgh', 'Lester Bulay', '2025-05-21 16:31:36'),
(2, 'uvuyguyguyguyu', 'Lester Bulay', '2025-05-21 16:31:44'),
(3, 'hbhjbhjhjbhjbjh', 'Lester Bulay', '2025-05-21 16:33:37'),
(4, 'yrdrtyfytftyfytfyt', 'Lester Bulay', '2025-05-21 16:35:50');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `afpro_announcement`
--
ALTER TABLE `afpro_announcement`
  ADD PRIMARY KEY (`announcement_id`);

--
-- Indexes for table `afpro_attendance`
--
ALTER TABLE `afpro_attendance`
  ADD PRIMARY KEY (`attendance_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `afpro_events`
--
ALTER TABLE `afpro_events`
  ADD PRIMARY KEY (`event_id`);

--
-- Indexes for table `afpro_feedback`
--
ALTER TABLE `afpro_feedback`
  ADD PRIMARY KEY (`feedback_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `afpro_promotion`
--
ALTER TABLE `afpro_promotion`
  ADD PRIMARY KEY (`promotion_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `afpro_reports`
--
ALTER TABLE `afpro_reports`
  ADD PRIMARY KEY (`report_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `afpro_users`
--
ALTER TABLE `afpro_users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `student_id` (`student_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `announcement`
--
ALTER TABLE `announcement`
  ADD PRIMARY KEY (`announcement_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `afpro_announcement`
--
ALTER TABLE `afpro_announcement`
  MODIFY `announcement_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `afpro_attendance`
--
ALTER TABLE `afpro_attendance`
  MODIFY `attendance_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- AUTO_INCREMENT for table `afpro_events`
--
ALTER TABLE `afpro_events`
  MODIFY `event_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

--
-- AUTO_INCREMENT for table `afpro_feedback`
--
ALTER TABLE `afpro_feedback`
  MODIFY `feedback_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `afpro_promotion`
--
ALTER TABLE `afpro_promotion`
  MODIFY `promotion_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `afpro_reports`
--
ALTER TABLE `afpro_reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `afpro_users`
--
ALTER TABLE `afpro_users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `announcement`
--
ALTER TABLE `announcement`
  MODIFY `announcement_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `afpro_attendance`
--
ALTER TABLE `afpro_attendance`
  ADD CONSTRAINT `afpro_attendance_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `afpro_users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `afpro_attendance_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `afpro_events` (`event_id`) ON DELETE CASCADE;

--
-- Constraints for table `afpro_feedback`
--
ALTER TABLE `afpro_feedback`
  ADD CONSTRAINT `afpro_feedback_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `afpro_users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `afpro_feedback_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `afpro_events` (`event_id`) ON DELETE CASCADE;

--
-- Constraints for table `afpro_promotion`
--
ALTER TABLE `afpro_promotion`
  ADD CONSTRAINT `afpro_promotion_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `afpro_users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `afpro_reports`
--
ALTER TABLE `afpro_reports`
  ADD CONSTRAINT `afpro_reports_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `afpro_users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `afpro_reports_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `afpro_events` (`event_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
