-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 13, 2026 at 05:07 PM
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
-- Database: `mediquick_pharmacy`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`) VALUES
(1, 'Prescription Medicine'),
(2, 'Over-the-Counter'),
(3, 'Wellness'),
(4, 'Personal Care');

-- --------------------------------------------------------

--
-- Table structure for table `inquiries`
--

CREATE TABLE `inquiries` (
  `inquiry_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `subject` varchar(150) DEFAULT NULL,
  `message` text NOT NULL,
  `status` enum('open','answered') NOT NULL DEFAULT 'open',
  `reply` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `prescription_id` int(11) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','confirmed','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending',
  `payment_status` enum('unpaid','paid') NOT NULL DEFAULT 'unpaid',
  `shipping_address` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `order_item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `prescriptions`
--

CREATE TABLE `prescriptions` (
  `prescription_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `reviewed_by` int(11) DEFAULT NULL,
  `review_notes` varchar(255) DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reviewed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `product_name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `dosage_info` text DEFAULT NULL,
  `safety_info` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock_qty` int(11) NOT NULL DEFAULT 0,
  `requires_prescription` tinyint(1) NOT NULL DEFAULT 0,
  `image_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `category_id`, `product_name`, `description`, `dosage_info`, `safety_info`, `price`, `stock_qty`, `requires_prescription`, `image_path`, `created_at`) VALUES
(1, 1, 'Amoxicillin 500mg', 'Antibiotic capsule for bacterial infections', 'One capsule every 8 hours', 'Complete full course. Consult doctor if allergic to penicillin.', 450.00, 100, 1, 'uploads/products/prod_1788470167_2552.jpg', '2026-09-03 21:05:07'),
(2, 1, 'Metformin 500mg', 'Oral medication for type 2 diabetes management', 'One tablet twice daily with meals', 'Monitor blood sugar regularly. Consult doctor before use.', 380.00, 90, 1, 'uploads/products/prod_1788470720_7900.jpg', '2026-09-03 21:05:07'),
(3, 1, 'Amlodipine 5mg', 'Blood pressure medication', 'One tablet daily, same time each day', 'Do not stop suddenly without medical advice', 420.00, 70, 1, 'uploads/products/prod_1788470127_7964.jpg', '2026-09-03 21:05:07'),
(4, 1, 'Ventolin Inhaler', 'Bronchodilator inhaler for asthma relief', 'Two puffs as needed, max 8 per day', 'Seek medical attention if breathing does not improve', 1250.00, 40, 1, 'uploads/products/prod_1788471266_8610.jpg', '2026-09-03 21:05:07'),
(5, 1, 'Omeprazole 20mg', 'Reduces stomach acid, treats reflux and ulcers', 'One capsule daily before breakfast', 'Long-term use should be monitored by a doctor', 350.00, 85, 1, 'uploads/products/prod_1788470960_5400.jpg', '2026-09-03 21:05:07'),
(6, 1, 'Cetirizine 10mg', 'Antihistamine for severe allergic reactions', 'One tablet daily', 'May cause drowsiness', 180.00, 120, 1, 'uploads/products/prod_1788470415_8477.jpg', '2026-09-03 21:05:07'),
(7, 2, 'Paracetamol 500mg', 'Pain reliever and fever reducer', 'One to two tablets every 4-6 hours', 'Do not exceed 8 tablets in 24 hours', 120.00, 200, 0, 'uploads/products/prod_1788471043_7971.jpg', '2026-09-03 21:05:07'),
(8, 2, 'Ibuprofen 400mg', 'Anti-inflammatory pain relief', 'One tablet every 6-8 hours with food', 'Avoid on empty stomach. Not for those with ulcers.', 160.00, 150, 0, 'uploads/products/prod_1788470689_5491.jpg', '2026-09-03 21:05:07'),
(9, 2, 'Cough Syrup 100ml', 'Relieves dry and chesty cough', 'Two teaspoons three times a day', 'Not recommended for children under 6', 320.00, 100, 0, 'uploads/products/prod_1788471484_1079.jpg', '2026-09-03 21:05:07'),
(10, 2, 'Antacid Tablets', 'Fast relief from heartburn and indigestion', 'Chew 1-2 tablets as needed', 'Do not exceed 8 tablets per day', 140.00, 180, 0, 'uploads/products/prod_1788470219_7471.jpg', '2026-09-03 21:05:07'),
(11, 2, 'ORS Rehydration Sachets', 'Oral rehydration salts for dehydration', 'Dissolve one sachet in 200ml water', 'Discard mixed solution after 24 hours', 60.00, 250, 0, 'uploads/products/prod_1788470995_8698.jpg', '2026-09-03 21:05:07'),
(12, 2, 'Throat Lozenges', 'Soothes sore throat and cough', 'One lozenge every 2-3 hours', 'Not for children under 5', 250.00, 130, 0, 'uploads/products/prod_1788471235_2377.jpg', '2026-09-03 21:05:07'),
(13, 3, 'Multivitamin Tablets', 'Daily multivitamin supplement', 'One tablet daily with food', 'Keep out of reach of children', 850.00, 80, 0, 'uploads/products/prod_1788470860_2894.jpg', '2026-09-03 21:05:07'),
(14, 3, 'Vitamin C 1000mg', 'Immune support supplement', 'One tablet daily', 'Excess intake may cause stomach upset', 690.00, 100, 0, 'uploads/products/prod_1788471305_4690.jpg', '2026-09-03 21:05:07'),
(15, 3, 'Omega-3 Fish Oil Capsules', 'Supports heart and brain health', 'One to two capsules daily with food', 'Consult doctor if on blood-thinning medication', 1450.00, 60, 0, 'uploads/products/prod_1788470929_7320.jpg', '2026-09-03 21:05:07'),
(16, 3, 'Protein Powder 500g', 'Whey protein supplement for muscle recovery', 'One scoop with water or milk daily', 'Not intended for children', 3200.00, 35, 0, 'uploads/products/prod_1788471151_3736.jpg', '2026-09-03 21:05:07'),
(17, 3, 'Herbal Sleep Aid Tablets', 'Natural supplement to support sleep', 'One tablet 30 minutes before bed', 'Do not combine with alcohol or sedatives', 780.00, 55, 0, 'uploads/products/prod_1788470658_8441.jpg', '2026-09-03 21:05:07'),
(18, 4, 'Hand Sanitizer 200ml', 'Alcohol-based hand sanitizer', 'Apply as needed', 'Flammable, keep away from open flame', 350.00, 150, 0, 'uploads/products/prod_1788470586_4221.jpg', '2026-09-03 21:05:07'),
(19, 4, 'Antiseptic Liquid 100ml', 'For cleaning minor wounds and cuts', 'Dilute as directed before use', 'For external use only', 280.00, 100, 0, 'uploads/products/prod_1788470369_3425.jpg', '2026-09-03 21:05:07'),
(20, 4, 'Sunscreen SPF 50', 'Broad-spectrum sun protection lotion', 'Apply generously 15 minutes before sun exposure', 'Reapply every 2 hours if outdoors', 950.00, 70, 0, 'uploads/products/prod_1788471178_6787.jpg', '2026-09-03 21:05:07'),
(21, 4, 'Adhesive Bandages (Box of 40)', 'Assorted sizes for minor cuts and wounds', 'Apply to clean, dry skin', 'Replace if wet or dirty', 220.00, 200, 0, 'uploads/products/prod_1788470064_3610.jpg', '2026-09-03 21:05:07'),
(22, 4, 'Digital Thermometer', 'Fast and accurate body temperature reading', 'Follow device instructions', 'Clean before and after each use', 890.00, 45, 0, 'uploads/products/prod_1788470531_2440.jpg', '2026-09-03 21:05:07'),
(23, 4, 'N95 Face Masks (Pack of 10)', 'Protective face masks', 'Single use, discard after wearing', 'Do not reuse if damaged or damp', 650.00, 120, 0, 'uploads/products/prod_1788470895_3896.jpg', '2026-09-03 21:05:07');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `role` enum('customer','staff','admin') NOT NULL DEFAULT 'customer',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `email`, `password`, `phone`, `address`, `role`, `created_at`) VALUES
(1, 'System Admin', 'admin@mediquick.com', '$2b$10$oH66YFZsjkaisG.j/PfAbuU90iUZMAxrNBhQ4u9eOTLbTW7m1ez6u', NULL, NULL, 'admin', '2026-09-03 21:05:07'),
(3, 'Mohamed Rushdi', 'rushdiahamed3232@gmail.com', '$2y$10$WOavEOKEufiJntYarmseGeaPZQ0uacYbzxCm24ZfG80CRIzYw6hVm', '0759484842', 'Mawanella', 'customer', '2026-09-13 14:45:45'),
(4, 'Mohamed Rahiq', 'rahiq123@gmail.com', '$2y$10$whyiU5VoUZmuUBqUmp1QPuV1apdH2AwgBH0F2GnI2HagCzjoyNab.', NULL, NULL, 'staff', '2026-09-13 14:56:32');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `inquiries`
--
ALTER TABLE `inquiries`
  ADD PRIMARY KEY (`inquiry_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `prescription_id` (`prescription_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `prescriptions`
--
ALTER TABLE `prescriptions`
  ADD PRIMARY KEY (`prescription_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `reviewed_by` (`reviewed_by`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `inquiries`
--
ALTER TABLE `inquiries`
  MODIFY `inquiry_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `prescriptions`
--
ALTER TABLE `prescriptions`
  MODIFY `prescription_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `inquiries`
--
ALTER TABLE `inquiries`
  ADD CONSTRAINT `inquiries_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`prescription_id`) REFERENCES `prescriptions` (`prescription_id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- Constraints for table `prescriptions`
--
ALTER TABLE `prescriptions`
  ADD CONSTRAINT `prescriptions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `prescriptions_ibfk_2` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
