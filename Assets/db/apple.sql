-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 16, 2025 at 02:54 PM
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
-- Database: `apple`
--

-- --------------------------------------------------------

--
-- Table structure for table `add_to_cart`
--

CREATE TABLE `add_to_cart` (
  `cart_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `storage_id` int(11) NOT NULL,
  `color_name` varchar(50) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `name`) VALUES
(1, 'Iphone'),
(2, 'MacBook'),
(3, 'Watches'),
(4, 'Accessories'),
(5, 'TV'),
(6, 'AirPods'),
(7, 'Ipad');

-- --------------------------------------------------------

--
-- Table structure for table `colors`
--

CREATE TABLE `colors` (
  `color_id` int(11) NOT NULL,
  `color_name` varchar(50) DEFAULT NULL,
  `color_hex` varchar(7) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `colors`
--

INSERT INTO `colors` (`color_id`, `color_name`, `color_hex`) VALUES
(1, 'Black', '#000000'),
(2, 'Silver', '#C0C0C0'),
(3, 'Gold', '#FFD700'),
(4, 'Green', '#008000'),
(5, 'Space Gray', '#4B4B4D'),
(6, 'White', '#FFFFFF'),
(7, 'Deep Purple', '#6A0DAD'),
(8, 'Starlight', '#E6E6FA'),
(9, 'Titanium', '#878787'),
(10, 'Midnight', '#191970');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `customer_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `phone_number` varchar(15) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `state` varchar(50) DEFAULT NULL,
  `zip_code` varchar(10) DEFAULT NULL,
  `country` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`customer_id`, `user_id`, `first_name`, `last_name`, `phone_number`, `address`, `city`, `state`, `zip_code`, `country`) VALUES
(1, 4, 'Mohamed', 'Shukry', '0786543033', 'No. 23, Flower Road', 'Kalptiya', 'Puttalam', '61360', 'Sri Lanka'),
(2, 3, 'Kamal', 'Fernando', '0719876543', 'No. 12, Kandy Road', 'Kandy', 'Central', '20000', 'Sri Lanka'),
(3, 6, 'Lakshman', 'Perera', '0776543210', 'No. 45, Lake Road', 'Colombo', 'Western', '00300', 'Sri Lanka'),
(4, 7, 'Chaminda', 'Rajapaksa', '0712345678', 'No. 10, High Street', 'Galle', 'Southern', '80000', 'Sri Lanka'),
(5, 8, 'Nadeesha', 'Gunawardena', '0789876543', 'No. 56, Beach Road', 'Negombo', 'Western', '11500', 'Sri Lanka'),
(6, 9, 'Tharindu', 'Kumarasinghe', '0773456789', 'No. 32, Main Street', 'Kandy', 'Central', '20000', 'Sri Lanka'),
(7, 10, 'Samantha', 'Silva', '0724567890', 'No. 78, Hill Road', 'Nuwara Eliya', 'Central', '22200', 'Sri Lanka');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `order_date` datetime DEFAULT current_timestamp(),
  `total_amount` decimal(10,2) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'completed',
  `item_color` varchar(50) DEFAULT NULL,
  `storage_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `total_quantity` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `customer_id`, `order_date`, `total_amount`, `status`, `item_color`, `storage_id`, `product_id`, `total_quantity`) VALUES
(1, 1, '2024-07-01 00:00:00', 500000.00, 'completed', 'Black', 9, 2, 2),
(2, 2, '2024-07-02 00:00:00', 280000.00, 'completed', 'Silver', 12, 3, 1),
(3, 1, '2024-07-03 00:00:00', 290000.00, 'completed', 'Gold', 8, 4, 1),
(4, 2, '2024-07-04 00:00:00', 500000.00, 'completed', 'Green', 11, 3, 2),
(5, 1, '2024-07-05 00:00:00', 780000.00, 'completed', 'Black', 7, 4, 3),
(6, 2, '2024-07-29 19:08:40', 560000.00, 'Completed', 'Silver', 10, 3, 2),
(8, 1, '2024-07-08 00:00:00', 600000.00, 'completed', 'Black', 9, 10, 2),
(10, 1, '2024-07-10 00:00:00', 300000.00, 'completed', 'White', 11, 12, 2),
(17, 4, '2024-08-01 00:00:00', 250000.00, 'completed', 'Starlight', 41, 24, 1),
(19, 1, '2024-08-02 00:00:00', 75000.00, 'completed', 'White', 45, 26, 3),
(20, 5, '2024-08-03 00:00:00', 50000.00, 'completed', 'Black', 47, 27, 1);

-- --------------------------------------------------------

--
-- Table structure for table `order_details`
--

CREATE TABLE `order_details` (
  `order_detail_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL,
  `storage_id` int(11) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `order_date` date DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_details`
--

INSERT INTO `order_details` (`order_detail_id`, `product_id`, `quantity`, `price`, `color`, `storage_id`, `customer_id`, `order_date`) VALUES
(7, 9, 1, 300000.00, 'Silver', 8, 2, '2024-07-07'),
(9, 11, 1, 80000.00, 'Black', 10, 2, '2024-07-09'),
(16, 23, 1, 380000.00, 'Deep Purple', 39, 3, '2024-08-01'),
(18, 25, 1, 90000.00, 'Titanium', 43, 2, '2024-08-02'),
(21, 28, 1, 300000.00, 'Midnight', 49, 2, '2024-08-03'),
(23, 30, 2, 120000.00, 'Starlight', 53, 4, '2024-08-04'),
(24, 31, 1, 32000.00, 'Black', 55, 1, '2024-08-05'),
(25, 32, 1, 110000.00, 'White', 57, 2, '2024-08-05'),
(30, 8, 1, 400000.00, 'Space Gray', 7, 1, '2024-07-06');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `model` varchar(100) DEFAULT NULL,
  `stock` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `name`, `model`, `stock`, `description`, `image_url`) VALUES
(2, 'iPhone 12 Pro', 'A2407', 8, '6.1-inch display, A14 Bionic chip, Triple-camera system', '../upload/35249.png'),
(3, 'iPhone 13', 'A2482', 15, '6.1-inch display, A15 Bionic chip, Dual-camera system', '../upload/35252.png'),
(4, 'iPhone 13 Pro', 'A2483', 12, '6.1-inch display, A15 Bionic chip, Triple-camera system', '../upload/35251.png'),
(6, 'Iphone 14 pro max', 'A2651', 7, '6.7-inch Super Retina XDR Display\r\nA16 Bionic Chip\r\nTriple-Camera System\r\n5G Connectivity\r\nCeramic Shield & Surgical-Grade Stainless Steel\r\niOS 16\r\nBattery Life 4323mAh', '../upload/iPhone_14_Pro_Max_Violet_1To_avec_abonnement_Série_limitée_40Go_Apple_1_-removebg-preview.png'),
(7, 'Iphone 11', 'A2111', 5, '6.1-inch (15 cm) IPS LCD with a resolution of 1792 × 828 pixels (1.4 megapixels) at a pixel density of 326 PPI with a maximum brightness of 625 nits and a 1400:1 contrast ratio', '../upload/1d8953b382f9fffca27523a86cff36b9-removebg-preview.png'),
(8, 'MacBook Pro 14\"', 'A2484', 10, '14.2-inch Liquid Retina XDR display, M1 Pro chip, 16GB RAM, 512GB SSD', '../upload/14.png'),
(9, 'MacBook Air 13\"', 'A2337', 15, '13.3-inch Retina display, M1 chip, 8GB RAM, 256GB SSD', '../upload/13.png'),
(10, 'Apple Watch Series 7', 'A2477', 20, '45mm or 41mm case, Always-On Retina display, GPS', '../upload/7.png'),
(11, 'Apple TV 4K', 'A2843', 25, '64GB or 128GB storage, A12 Bionic chip, 4K HDR', '../upload/4k.png'),
(12, 'AirPods Pro', 'A2084', 30, 'Active Noise Cancellation, Transparency mode, Customizable fit', '../upload/airpods.png'),
(13, 'Magic Mouse 2', 'A1657', 30, 'Wireless mouse with multi-touch surface', '../upload/2.png'),
(14, 'Magic Trackpad 2', 'A1339', 18, 'Wireless trackpad with a smooth surface for precision control', '../upload/t2.png'),
(15, 'Leather Wallet for iPhone', 'A2300', 12, 'Premium leather wallet that attaches to iPhone MagSafe', '../upload/w.png'),
(16, 'USB-C to Lightning Cable', 'A1882', 35, 'Durable USB-C to Lightning cable for fast charging and data transfer', '../upload/c.png'),
(17, 'Magic Keyboard for iPad Pro', 'A2229', 15, 'Keyboard designed for iPad Pro with a responsive typing experience', '../upload/key.png'),
(18, 'iPad Pro 11\"', 'A2377', 10, '11-inch Liquid Retina display, M1 chip, 128GB storage', '../upload/11.png'),
(19, 'iPad Air 5th Gen', 'A2588', 15, '10.9-inch Liquid Retina display, A14 Bionic chip, 64GB storage', '../upload/5.png'),
(20, 'iPad Mini 6', 'A2567', 12, '8.3-inch Liquid Retina display, A15 Bionic chip, 64GB storage', '../upload/6.png'),
(23, 'iPhone 14 Pro', 'A2890', 30, '6.1-inch Super Retina XDR display, A16 Bionic chip, Triple-camera system', '../upload/35253.png'),
(24, 'MacBook Air M2', 'A2680', 20, '13.6-inch Liquid Retina display, M2 chip, 8GB RAM, 512GB SSD', '../upload/m2.png'),
(25, 'Apple Watch Ultra', 'A2720', 15, '49mm case, GPS + Cellular, Advanced health and safety features', '../upload/ul.png'),
(26, 'AirPods 3rd Gen', 'A2565', 25, 'In-ear headphones, Adaptive EQ, Spatial Audio', '../upload/3g.png'),
(27, 'Apple TV HD', 'A1625', 40, '32GB storage, A8 chip, 1080p HD', '../upload/hd.png'),
(28, 'iPhone 13 Mini', 'A2620', 22, '5.4-inch Super Retina XDR display, A15 Bionic chip, Dual-camera system', '../upload/35250.png'),
(30, 'Apple Watch Series 8', 'A2719', 20, '41mm case, GPS + Cellular, Enhanced health monitoring', '../upload/s8.png'),
(31, 'AirPods Pro (2nd Gen)', 'A2700', 12, 'In-ear headphones, Active Noise Cancellation, Transparency mode', '../upload/2g.png'),
(32, 'Apple TV 4K (3rd Gen)', 'A2870', 35, '128GB storage, A16 Bionic chip, 4K HDR, Dolby Vision', '../upload/4k3.png');

-- --------------------------------------------------------

--
-- Table structure for table `product_battery_life`
--

CREATE TABLE `product_battery_life` (
  `battery_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `battery_life` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_battery_life`
--

INSERT INTO `product_battery_life` (`battery_id`, `product_id`, `battery_life`) VALUES
(1, 2, 'Up to 20 hours'),
(2, 3, 'Up to 22 hours'),
(3, 4, 'Up to 18 hours'),
(4, 6, 'Up to 21 hours'),
(5, 7, 'Up to 12 hours'),
(6, 8, 'Up to 10 hours'),
(7, 9, 'Up to 15 hours'),
(8, 10, 'Up to 18 hours'),
(9, 11, 'Up to 20 hours'),
(10, 12, 'Up to 24 hours'),
(11, 13, 'Up to 18 hours'),
(12, 14, 'Up to 6 hours'),
(13, 15, 'Up to 12 hours'),
(14, 16, 'Up to 20 hours'),
(15, 17, 'Up to 8 hours'),
(16, 18, 'Up to 10 hours'),
(17, 19, 'Up to 8 hours'),
(18, 20, 'Up to 10 hours'),
(19, 23, 'Up to 12 hours'),
(20, 24, 'Up to 14 hours'),
(21, 25, 'Up to 10 hours'),
(22, 26, 'Up to 24 hours'),
(23, 27, 'Up to 30 hours'),
(24, 28, 'Up to 15 hours'),
(26, 30, 'Up to 18 hours'),
(27, 31, 'Up to 24 hours'),
(28, 32, 'Up to 20 hours');

-- --------------------------------------------------------

--
-- Table structure for table `product_categories`
--

CREATE TABLE `product_categories` (
  `product_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_categories`
--

INSERT INTO `product_categories` (`product_id`, `category_id`) VALUES
(2, 1),
(3, 1),
(4, 1),
(6, 1),
(7, 1),
(8, 2),
(9, 2),
(10, 3),
(11, 5),
(12, 6),
(13, 4),
(14, 4),
(15, 4),
(16, 4),
(17, 4),
(18, 7),
(19, 7),
(20, 7),
(23, 1),
(24, 2),
(25, 3),
(26, 6),
(27, 5),
(28, 1),
(30, 3),
(31, 6),
(32, 5);

-- --------------------------------------------------------

--
-- Table structure for table `product_color`
--

CREATE TABLE `product_color` (
  `product_id` int(11) NOT NULL,
  `color_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_color`
--

INSERT INTO `product_color` (`product_id`, `color_id`) VALUES
(2, 3),
(2, 6),
(3, 5),
(3, 6),
(4, 5),
(4, 6),
(4, 8),
(6, 2),
(6, 5),
(6, 7),
(7, 4),
(7, 5),
(7, 8),
(8, 2),
(8, 6),
(9, 5),
(9, 7),
(10, 1),
(10, 3),
(10, 4),
(11, 5),
(12, 8),
(12, 9),
(13, 2),
(13, 5),
(14, 2),
(14, 5),
(15, 3),
(15, 5),
(16, 2),
(16, 5),
(17, 2),
(17, 5),
(18, 1),
(18, 2),
(19, 3),
(19, 5),
(20, 4),
(20, 7),
(23, 6),
(23, 8),
(24, 5),
(24, 7),
(25, 1),
(25, 2),
(26, 8),
(26, 9),
(27, 1),
(28, 6),
(28, 7),
(30, 5);

-- --------------------------------------------------------

--
-- Table structure for table `product_connectivity`
--

CREATE TABLE `product_connectivity` (
  `connectivity_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `connectivity_type` varchar(255) NOT NULL,
  `details` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_connectivity`
--

INSERT INTO `product_connectivity` (`connectivity_id`, `product_id`, `connectivity_type`, `details`) VALUES
(1, 2, 'Wi-Fi', 'Wi-Fi 6'),
(2, 2, 'Bluetooth', '5.0'),
(3, 3, 'Wi-Fi', 'Wi-Fi 6'),
(4, 3, 'Bluetooth', '5.0'),
(5, 4, 'Wi-Fi', 'Wi-Fi 6E'),
(6, 4, 'Bluetooth', '5.3'),
(7, 6, 'Wi-Fi', 'Wi-Fi 6E'),
(8, 6, 'Bluetooth', '5.3'),
(9, 7, 'Wi-Fi', 'Wi-Fi 5'),
(10, 7, 'Bluetooth', '4.2'),
(11, 8, 'Wi-Fi', 'Wi-Fi 6'),
(12, 8, 'Bluetooth', '5.0'),
(13, 9, 'Wi-Fi', 'Wi-Fi 6'),
(14, 9, 'Bluetooth', '5.0'),
(15, 10, 'Bluetooth', '5.0'),
(16, 11, 'Bluetooth', '4.2'),
(17, 12, 'Bluetooth', '5.0'),
(18, 13, 'Bluetooth', '5.0'),
(19, 14, 'Bluetooth', '5.0'),
(20, 15, 'Bluetooth', '5.0'),
(21, 16, 'USB', 'USB-C'),
(22, 17, 'Bluetooth', '5.0'),
(23, 18, 'Bluetooth', '5.0'),
(24, 19, 'Bluetooth', '5.0'),
(25, 20, 'Bluetooth', '5.0'),
(26, 23, 'Wi-Fi', 'Wi-Fi 6'),
(27, 24, 'Wi-Fi', 'Wi-Fi 6E'),
(28, 25, 'Bluetooth', '5.0'),
(29, 26, 'Bluetooth', '5.0'),
(30, 27, 'Bluetooth', '4.2'),
(31, 28, 'Wi-Fi', 'Wi-Fi 6'),
(33, 30, 'Bluetooth', '5.0'),
(34, 31, 'Bluetooth', '5.0'),
(35, 32, 'Bluetooth', '5.0');

-- --------------------------------------------------------

--
-- Table structure for table `product_dimensions`
--

CREATE TABLE `product_dimensions` (
  `dimension_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `dimension_name` varchar(255) NOT NULL,
  `dimension_value` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_dimensions`
--

INSERT INTO `product_dimensions` (`dimension_id`, `product_id`, `dimension_name`, `dimension_value`) VALUES
(85, 2, 'Height', '146.7 mm'),
(86, 2, 'Width', '71.5 mm'),
(87, 2, 'Depth', '7.4 mm'),
(88, 3, 'Height', '146.7 mm'),
(89, 3, 'Width', '71.5 mm'),
(90, 3, 'Depth', '7.65 mm'),
(91, 4, 'Height', '160.8 mm'),
(92, 4, 'Width', '78.1 mm'),
(93, 4, 'Depth', '7.65 mm'),
(94, 6, 'Height', '160.8 mm'),
(95, 6, 'Width', '78.1 mm'),
(96, 6, 'Depth', '7.65 mm'),
(97, 7, 'Height', '138.4 mm'),
(98, 7, 'Width', '67.3 mm'),
(99, 7, 'Depth', '7.4 mm'),
(100, 8, 'Height', '312.6 mm'),
(101, 8, 'Width', '221.2 mm'),
(102, 8, 'Depth', '16.8 mm'),
(103, 9, 'Height', '304.1 mm'),
(104, 9, 'Width', '212.4 mm'),
(105, 9, 'Depth', '16.1 mm'),
(106, 10, 'Height', '45 mm'),
(107, 10, 'Width', '45 mm'),
(108, 10, 'Depth', '10.7 mm'),
(109, 11, 'Height', '98 mm'),
(110, 11, 'Width', '98 mm'),
(111, 11, 'Depth', '35 mm'),
(112, 12, 'Height', '30.9 mm'),
(113, 12, 'Width', '21.8 mm'),
(114, 12, 'Depth', '24.3 mm'),
(115, 13, 'Height', '113.5 mm'),
(116, 13, 'Width', '60.7 mm'),
(117, 13, 'Depth', '21.5 mm'),
(118, 14, 'Height', '160 mm'),
(119, 14, 'Width', '114 mm'),
(120, 14, 'Depth', '7 mm'),
(121, 15, 'Height', '101 mm'),
(122, 15, 'Width', '75 mm'),
(123, 15, 'Depth', '15 mm'),
(124, 16, 'Height', '100 mm'),
(125, 16, 'Width', '10 mm'),
(126, 16, 'Depth', '10 mm'),
(127, 17, 'Height', '280 mm'),
(128, 17, 'Width', '210 mm'),
(129, 17, 'Depth', '7 mm'),
(130, 18, 'Height', '247.6 mm'),
(131, 18, 'Width', '178.5 mm'),
(132, 18, 'Depth', '6.4 mm'),
(133, 19, 'Height', '247.6 mm'),
(134, 19, 'Width', '178.5 mm'),
(135, 19, 'Depth', '6.1 mm'),
(136, 20, 'Height', '195.4 mm'),
(137, 20, 'Width', '134.8 mm'),
(138, 20, 'Depth', '6.3 mm'),
(139, 23, 'Height', '146.7 mm'),
(140, 23, 'Width', '71.5 mm'),
(141, 23, 'Depth', '7.65 mm'),
(142, 24, 'Height', '307.5 mm'),
(143, 24, 'Width', '229.5 mm'),
(144, 24, 'Depth', '16.1 mm'),
(145, 25, 'Height', '49 mm'),
(146, 25, 'Width', '49 mm'),
(147, 25, 'Depth', '14.7 mm'),
(148, 26, 'Height', '30.9 mm'),
(149, 26, 'Width', '21.8 mm'),
(150, 26, 'Depth', '24.3 mm'),
(151, 27, 'Height', '98 mm'),
(152, 27, 'Width', '98 mm'),
(153, 27, 'Depth', '35 mm'),
(154, 28, 'Height', '131.5 mm'),
(155, 28, 'Width', '64.2 mm'),
(156, 28, 'Depth', '7.7 mm'),
(160, 30, 'Height', '41 mm'),
(161, 30, 'Width', '41 mm'),
(162, 30, 'Depth', '14.4 mm'),
(163, 31, 'Height', '30.9 mm'),
(164, 31, 'Width', '21.8 mm'),
(165, 31, 'Depth', '24.3 mm'),
(166, 32, 'Height', '98 mm'),
(167, 32, 'Width', '98 mm'),
(168, 32, 'Depth', '35 mm');

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `image_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `image_url` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_images`
--

INSERT INTO `product_images` (`image_id`, `product_id`, `image_url`) VALUES
(1, 2, 'https://example.com/images/product2_1.jpg'),
(2, 2, 'https://example.com/images/product2_2.jpg'),
(3, 3, 'https://example.com/images/product3_1.jpg'),
(4, 3, 'https://example.com/images/product3_2.jpg'),
(5, 4, 'https://example.com/images/product4_1.jpg'),
(6, 4, 'https://example.com/images/product4_2.jpg'),
(7, 6, 'https://example.com/images/product6_1.jpg'),
(8, 6, 'https://example.com/images/product6_2.jpg'),
(9, 7, 'https://example.com/images/product7_1.jpg'),
(10, 7, 'https://example.com/images/product7_2.jpg'),
(11, 8, 'https://example.com/images/product8_1.jpg'),
(12, 8, 'https://example.com/images/product8_2.jpg'),
(13, 9, 'https://example.com/images/product9_1.jpg'),
(14, 9, 'https://example.com/images/product9_2.jpg'),
(15, 10, 'https://example.com/images/product10_1.jpg'),
(16, 11, 'https://example.com/images/product11_1.jpg'),
(17, 12, 'https://example.com/images/product12_1.jpg'),
(18, 12, 'https://example.com/images/product12_2.jpg'),
(19, 13, 'https://example.com/images/product13_1.jpg'),
(20, 14, 'https://example.com/images/product14_1.jpg'),
(21, 15, 'https://example.com/images/product15_1.jpg'),
(22, 16, 'https://example.com/images/product16_1.jpg'),
(23, 17, 'https://example.com/images/product17_1.jpg'),
(24, 18, 'https://example.com/images/product18_1.jpg'),
(25, 19, 'https://example.com/images/product19_1.jpg'),
(26, 20, 'https://example.com/images/product20_1.jpg'),
(27, 23, 'https://example.com/images/product23_1.jpg'),
(28, 24, 'https://example.com/images/product24_1.jpg'),
(29, 25, 'https://example.com/images/product25_1.jpg'),
(30, 26, 'https://example.com/images/product26_1.jpg'),
(31, 27, 'https://example.com/images/product27_1.jpg'),
(32, 28, 'https://example.com/images/product28_1.jpg'),
(34, 30, 'https://example.com/images/product30_1.jpg'),
(35, 31, 'https://example.com/images/product31_1.jpg'),
(36, 32, 'https://example.com/images/product32_1.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `product_specifications`
--

CREATE TABLE `product_specifications` (
  `spec_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `spec_name` varchar(255) NOT NULL,
  `spec_value` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_specifications`
--

INSERT INTO `product_specifications` (`spec_id`, `product_id`, `spec_name`, `spec_value`) VALUES
(64, 2, 'Display', '6.1-inch Super Retina XDR'),
(65, 2, 'Chip', 'A14 Bionic'),
(66, 2, 'Camera', 'Triple-camera system'),
(67, 3, 'Display', '6.1-inch Super Retina XDR'),
(68, 3, 'Chip', 'A15 Bionic'),
(69, 3, 'Camera', 'Dual-camera system'),
(70, 4, 'Display', '6.7-inch Super Retina XDR'),
(71, 4, 'Chip', 'A16 Bionic'),
(72, 4, 'Camera', 'Triple-camera system'),
(73, 6, 'Display', '6.7-inch Super Retina XDR'),
(74, 6, 'Chip', 'A16 Bionic'),
(75, 6, 'Camera', 'Triple-camera system'),
(76, 7, 'Display', '6.1-inch Retina HD'),
(77, 7, 'Chip', 'A13 Bionic'),
(78, 7, 'Camera', 'Dual-camera system'),
(79, 8, 'Display', '14.2-inch Liquid Retina XDR'),
(80, 8, 'Chip', 'M1 Pro'),
(81, 8, 'RAM', '16GB'),
(82, 9, 'Display', '13.3-inch Retina'),
(83, 9, 'Chip', 'M1'),
(84, 9, 'RAM', '8GB'),
(85, 10, 'Case Size', '45mm or 41mm'),
(86, 10, 'Display', 'Always-On Retina'),
(87, 11, 'Storage', '64GB or 128GB'),
(88, 11, 'Chip', 'A12 Bionic'),
(89, 12, 'Noise Cancellation', 'Active'),
(90, 12, 'Transparency Mode', 'Yes'),
(91, 13, 'Type', 'Wireless'),
(92, 13, 'Surface', 'Multi-touch'),
(93, 14, 'Type', 'Wireless'),
(94, 14, 'Surface', 'Smooth'),
(95, 15, 'Attachment', 'MagSafe'),
(96, 15, 'Material', 'Leather'),
(97, 16, 'Type', 'USB-C to Lightning'),
(98, 16, 'Length', '1 meter'),
(99, 17, 'Compatibility', 'iPad Pro'),
(100, 17, 'Type', 'Keyboard'),
(101, 18, 'Display', '11-inch Liquid Retina'),
(102, 18, 'Chip', 'M1'),
(103, 19, 'Display', '10.9-inch Liquid Retina'),
(104, 19, 'Chip', 'A14 Bionic'),
(105, 20, 'Display', '8.3-inch Liquid Retina'),
(106, 20, 'Chip', 'A15 Bionic'),
(107, 23, 'Display', '6.1-inch Super Retina XDR'),
(108, 23, 'Chip', 'A16 Bionic'),
(109, 24, 'Display', '13.6-inch Liquid Retina'),
(110, 24, 'Chip', 'M2'),
(111, 25, 'Case Size', '49mm'),
(112, 25, 'Features', 'GPS + Cellular'),
(113, 26, 'Type', 'In-ear'),
(114, 26, 'Features', 'Adaptive EQ'),
(115, 27, 'Storage', '32GB'),
(116, 27, 'Chip', 'A8'),
(117, 28, 'Display', '5.4-inch Super Retina XDR'),
(118, 28, 'Chip', 'A15 Bionic'),
(121, 30, 'Case Size', '41mm'),
(122, 30, 'Features', 'GPS + Cellular'),
(123, 31, 'Type', 'In-ear'),
(124, 31, 'Features', 'Active Noise Cancellation'),
(125, 32, 'Storage', '128GB'),
(126, 32, 'Chip', 'A16 Bionic');

-- --------------------------------------------------------

--
-- Table structure for table `product_warranties`
--

CREATE TABLE `product_warranties` (
  `warranty_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `warranty_period` varchar(255) NOT NULL,
  `warranty_details` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_warranties`
--

INSERT INTO `product_warranties` (`warranty_id`, `product_id`, `warranty_period`, `warranty_details`) VALUES
(29, 2, '1 year', 'Limited warranty covering hardware defects'),
(30, 3, '1 year', 'Limited warranty covering hardware defects'),
(31, 4, '1 year', 'Limited warranty covering hardware defects'),
(32, 6, '1 year', 'Limited warranty covering hardware defects'),
(33, 7, '1 year', 'Limited warranty covering hardware defects'),
(34, 8, '1 year', 'Limited warranty covering hardware defects'),
(35, 9, '1 year', 'Limited warranty covering hardware defects'),
(36, 10, '1 year', 'Limited warranty covering hardware defects'),
(37, 11, '1 year', 'Limited warranty covering hardware defects'),
(38, 12, '1 year', 'Limited warranty covering hardware defects'),
(39, 13, '1 year', 'Limited warranty covering hardware defects'),
(40, 14, '1 year', 'Limited warranty covering hardware defects'),
(41, 15, '1 year', 'Limited warranty covering hardware defects'),
(42, 16, '1 year', 'Limited warranty covering hardware defects'),
(43, 17, '1 year', 'Limited warranty covering hardware defects'),
(44, 18, '1 year', 'Limited warranty covering hardware defects'),
(45, 19, '1 year', 'Limited warranty covering hardware defects'),
(46, 20, '1 year', 'Limited warranty covering hardware defects'),
(47, 23, '1 year', 'Limited warranty covering hardware defects'),
(48, 24, '1 year', 'Limited warranty covering hardware defects'),
(49, 25, '1 year', 'Limited warranty covering hardware defects'),
(50, 26, '1 year', 'Limited warranty covering hardware defects'),
(51, 27, '1 year', 'Limited warranty covering hardware defects'),
(52, 28, '1 year', 'Limited warranty covering hardware defects'),
(54, 30, '1 year', 'Limited warranty covering hardware defects'),
(55, 31, '1 year', 'Limited warranty covering hardware defects'),
(56, 32, '1 year', 'Limited warranty covering hardware defects');

-- --------------------------------------------------------

--
-- Table structure for table `product_weight`
--

CREATE TABLE `product_weight` (
  `weight_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `weight_value` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_weight`
--

INSERT INTO `product_weight` (`weight_id`, `product_id`, `weight_value`) VALUES
(1, 2, '164 grams'),
(2, 3, '174 grams'),
(3, 4, '238 grams'),
(4, 6, '238 grams'),
(5, 7, '194 grams'),
(6, 8, '631 grams'),
(7, 9, '1300 grams'),
(8, 10, '32 grams'),
(9, 11, '50 grams'),
(10, 12, '60 grams'),
(11, 13, '23 grams'),
(12, 14, '10 grams'),
(13, 15, '100 grams'),
(14, 16, '50 grams'),
(15, 17, '700 grams'),
(16, 18, '460 grams'),
(17, 19, '460 grams'),
(18, 20, '300 grams'),
(19, 23, '200 grams'),
(20, 24, '1.4 kg'),
(21, 25, '90 grams'),
(22, 26, '50 grams'),
(23, 27, '45 grams'),
(24, 28, '140 grams'),
(26, 30, '30 grams'),
(27, 31, '60 grams'),
(28, 32, '50 grams');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `review_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `comment` text DEFAULT NULL,
  `review_date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`review_id`, `product_id`, `customer_id`, `rating`, `comment`, `review_date`) VALUES
(11, 2, 1, 5, 'Excellent phone with a great camera system. The performance is top-notch!', '2024-08-01 10:00:00'),
(12, 2, 2, 4, 'Great device, but the battery life could be better.', '2024-08-02 11:15:00'),
(13, 3, 3, 5, 'The iPhone 13 is a significant upgrade with improved battery life and performance.', '2024-08-03 12:30:00'),
(14, 3, 4, 3, 'Good phone but has some issues with the latest software updates.', '2024-08-04 13:45:00'),
(15, 4, 5, 5, 'Amazing performance and camera quality. Worth every penny.', '2024-08-05 14:50:00'),
(16, 4, 6, 4, 'Very good phone, but a bit on the expensive side.', '2024-08-06 15:55:00'),
(17, 6, 7, 5, 'The best iPhone I have ever used. The display and performance are exceptional.', '2024-08-07 16:00:00'),
(18, 6, 1, 4, 'Great phone, though the size might be too large for some users.', '2024-08-08 17:05:00'),
(19, 8, 2, 5, 'The MacBook Pro 14\" is powerful and versatile. Perfect for professional use.', '2024-08-09 18:10:00'),
(20, 8, 3, 4, 'Excellent laptop but quite heavy to carry around.', '2024-08-10 19:15:00'),
(21, 9, 4, 5, 'Lightweight and powerful. Ideal for everyday tasks.', '2024-08-11 20:20:00'),
(22, 9, 5, 4, 'Good performance but the screen could be brighter.', '2024-08-12 21:25:00'),
(23, 10, 6, 5, 'Fantastic smartwatch with lots of features. Very helpful for fitness tracking.', '2024-08-13 22:30:00'),
(24, 10, 7, 4, 'Great watch, though the battery life could be improved.', '2024-08-14 23:35:00'),
(25, 11, 1, 5, 'The Apple TV 4K offers an amazing viewing experience with stunning picture quality.', '2024-08-15 09:40:00'),
(26, 11, 2, 4, 'Excellent streaming device but a bit pricey.', '2024-08-16 10:45:00'),
(27, 12, 3, 5, 'Best wireless earbuds I have ever used. Noise cancellation is superb.', '2024-08-17 11:50:00'),
(28, 12, 4, 4, 'Great sound quality, but the fit could be better for some people.', '2024-08-18 12:55:00'),
(29, 13, 5, 4, 'Nice and smooth mouse. Works well with my MacBook.', '2024-08-19 13:00:00'),
(30, 13, 6, 3, 'Good mouse but could be more ergonomic.', '2024-08-20 14:05:00'),
(31, 14, 7, 5, 'Excellent for precision control. A must-have for graphic designers.', '2024-08-21 15:10:00'),
(32, 14, 1, 4, 'Great trackpad, though it takes a bit of time to get used to.', '2024-08-22 16:15:00'),
(33, 15, 2, 5, 'The leather quality is top-notch. Fits perfectly with my iPhone.', '2024-08-23 17:20:00'),
(34, 15, 3, 4, 'Nice wallet, but it could be a bit more secure.', '2024-08-24 18:25:00'),
(35, 16, 4, 5, 'Works perfectly for fast charging. Durable and reliable.', '2024-08-25 19:30:00'),
(36, 16, 5, 4, 'Good cable but a bit expensive for the length.', '2024-08-26 20:35:00'),
(37, 17, 6, 5, 'A great keyboard with a responsive typing experience. Ideal for productivity.', '2024-08-27 21:40:00'),
(38, 17, 7, 4, 'Good keyboard but a bit pricey.', '2024-08-28 22:45:00'),
(39, 18, 1, 5, 'The iPad Pro is incredibly powerful. Perfect for creative work.', '2024-08-29 23:50:00'),
(40, 18, 2, 4, 'Great iPad, but the price is on the higher side.', '2024-08-30 09:55:00'),
(41, 19, 3, 5, 'Fantastic performance and display. Ideal for everyday use.', '2024-08-31 10:00:00'),
(42, 19, 4, 4, 'Good iPad with a few minor issues.', '2024-09-01 11:05:00'),
(43, 20, 5, 5, 'Compact and powerful. Perfect for on-the-go use.', '2024-09-02 12:10:00'),
(44, 20, 6, 4, 'Nice device but a bit small for some tasks.', '2024-09-03 13:15:00'),
(45, 23, 7, 5, 'The iPhone 14 Pro has amazing features and performance.', '2024-09-04 14:20:00'),
(46, 23, 1, 4, 'Great phone but a bit too expensive.', '2024-09-05 15:25:00'),
(47, 24, 2, 5, 'The M2 chip makes this MacBook Air incredibly fast. A great value.', '2024-09-06 16:30:00'),
(48, 24, 3, 4, 'Good performance, but the screen could be brighter.', '2024-09-07 17:35:00'),
(49, 25, 4, 5, 'The Apple Watch Ultra is built for adventure and has great features.', '2024-09-08 18:40:00'),
(50, 25, 5, 4, 'Excellent watch with lots of features, but a bit bulky.', '2024-09-09 19:45:00'),
(51, 26, 6, 5, 'Great sound quality and comfortable fit.', '2024-09-10 20:50:00'),
(52, 26, 7, 4, 'Good earbuds but could use better noise isolation.', '2024-09-11 21:55:00'),
(53, 27, 1, 5, 'The Apple TV HD offers excellent streaming quality.', '2024-09-12 22:00:00'),
(54, 27, 2, 4, 'Good performance but lacks some features of the 4K model.', '2024-09-13 23:05:00'),
(55, 28, 3, 5, 'Compact but powerful. A great phone for those who prefer smaller devices.', '2024-09-14 09:10:00'),
(56, 28, 4, 4, 'Good phone, but the battery life could be better.', '2024-09-15 10:15:00'),
(59, 30, 7, 5, 'The Series 8 has an excellent design and new health features.', '2024-09-18 13:30:00'),
(60, 30, 1, 4, 'Good smartwatch but the battery life could be improved.', '2024-09-19 14:35:00'),
(66, 2, 1, 5, 'good', '2024-09-15 18:11:30'),
(71, 2, 1, 4, 'good product', '2024-09-17 16:41:23');

-- --------------------------------------------------------

--
-- Table structure for table `storage_options`
--

CREATE TABLE `storage_options` (
  `storage_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `storage` varchar(50) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `storage_options`
--

INSERT INTO `storage_options` (`storage_id`, `product_id`, `storage`, `price`) VALUES
(1, 2, '64GB', 200000.00),
(2, 2, '128GB', 220000.00),
(3, 2, '256GB', 240000.00),
(4, 3, '64GB', 160000.00),
(5, 3, '128GB', 180000.00),
(6, 3, '256GB', 200000.00),
(7, 4, '128GB', 220000.00),
(8, 4, '256GB', 240000.00),
(9, 4, '512GB', 280000.00),
(10, 6, '128GB', 220000.00),
(11, 6, '256GB', 240000.00),
(12, 6, '512GB', 280000.00),
(13, 7, '64GB', 150000.00),
(14, 7, '128GB', 170000.00),
(15, 8, '512GB SSD', 450000.00),
(16, 8, '1TB SSD', 500000.00),
(17, 9, '256GB SSD', 300000.00),
(18, 9, '512GB SSD', 350000.00),
(19, 10, '41mm GPS', 85000.00),
(20, 10, '45mm GPS', 95000.00),
(21, 10, '41mm GPS + Cellular', 115000.00),
(22, 10, '45mm GPS + Cellular', 125000.00),
(23, 11, '64GB', 60000.00),
(24, 11, '128GB', 70000.00),
(25, 12, 'Standard', 70000.00),
(26, 13, 'Standard', 25000.00),
(27, 14, 'Standard', 40000.00),
(28, 15, 'Standard', 30000.00),
(29, 16, '1m', 8000.00),
(30, 16, '2m', 12000.00),
(31, 17, 'Standard', 60000.00),
(32, 18, '128GB', 150000.00),
(33, 18, '256GB', 180000.00),
(34, 19, '64GB', 110000.00),
(35, 19, '256GB', 140000.00),
(36, 20, '64GB', 85000.00),
(37, 20, '256GB', 120000.00),
(38, 23, '128GB', 220000.00),
(39, 23, '256GB', 240000.00),
(40, 23, '512GB', 280000.00),
(41, 24, '512GB SSD', 350000.00),
(42, 24, '1TB SSD', 400000.00),
(43, 25, '49mm GPS + Cellular', 120000.00),
(44, 26, 'Standard', 50000.00),
(45, 27, '32GB', 40000.00),
(46, 28, '128GB', 160000.00),
(47, 28, '256GB', 180000.00),
(50, 30, '41mm GPS + Cellular', 115000.00),
(51, 30, '45mm GPS + Cellular', 125000.00),
(52, 31, 'Standard', 70000.00),
(53, 32, '128GB', 70000.00);

-- --------------------------------------------------------

--
-- Table structure for table `storage_stock`
--

CREATE TABLE `storage_stock` (
  `product_id` int(11) NOT NULL,
  `storage_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `storage_stock`
--

INSERT INTO `storage_stock` (`product_id`, `storage_id`, `quantity`) VALUES
(2, 1, 50),
(2, 2, 30),
(2, 3, 0),
(3, 4, 40),
(3, 5, 60),
(3, 6, 25),
(4, 7, 45),
(4, 8, 35),
(4, 9, 20),
(6, 10, 50),
(6, 11, 30),
(6, 12, 15),
(7, 13, 60),
(7, 14, 50),
(8, 15, 10),
(8, 16, 5),
(9, 17, 25),
(9, 18, 20),
(10, 19, 100),
(10, 20, 75),
(10, 21, 50),
(10, 22, 30),
(11, 23, 70),
(11, 24, 40),
(12, 25, 60),
(13, 26, 80),
(14, 27, 50),
(15, 28, 45),
(16, 29, 30),
(16, 30, 20),
(17, 31, 55),
(18, 32, 25),
(18, 33, 20),
(19, 34, 40),
(19, 35, 30),
(20, 36, 55),
(20, 37, 25),
(23, 38, 20),
(23, 39, 15),
(23, 40, 10),
(24, 41, 14),
(24, 42, 10),
(25, 43, 25),
(26, 44, 30),
(27, 45, 40),
(28, 46, 20),
(28, 47, 15),
(30, 50, 30),
(30, 51, 25),
(31, 52, 50),
(32, 53, 30);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `role` varchar(20) DEFAULT 'customer'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `email`, `role`) VALUES
(1, 'admin1', 'password123', 'admin1@gmail.com', 'admin'),
(2, 'customer1', 'password123', 'customer1@gmail.com', 'customer'),
(3, 'customer2', 'password123', 'customer2@gmail.com', 'customer'),
(4, 'Shukry', '$2y$10$r975XkWDeLIXNXNCYY8feuMZ/3MfMl8fy5Ci0aZ6UX8Pl.wqaGw8S', 'mhdshukry110@gmail.com', 'customer'),
(5, 'skr', '$2y$10$8qiDYnH1NqHw/0miudUvbeG7aWxWxCZYk/i3fSF.K2aoTbJkSnRP2', 'mhdshukry111@gmail.com', 'admin'),
(6, 'srilanka1', 'password123', 'srilanka1@gmail.com', 'customer'),
(7, 'srilanka2', 'password123', 'srilanka2@gmail.com', 'customer'),
(8, 'srilanka3', 'password123', 'srilanka3@gmail.com', 'customer'),
(9, 'srilanka4', 'password123', 'srilanka4@gmail.com', 'customer'),
(10, 'srilanka5', 'password123', 'srilanka5@gmail.com', 'customer');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `add_to_cart`
--
ALTER TABLE `add_to_cart`
  ADD PRIMARY KEY (`cart_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `storage_id` (`storage_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `colors`
--
ALTER TABLE `colors`
  ADD PRIMARY KEY (`color_id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`customer_id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `fk_orders_storage_id` (`storage_id`);

--
-- Indexes for table `order_details`
--
ALTER TABLE `order_details`
  ADD PRIMARY KEY (`order_detail_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `order_details_ibfk_2` (`product_id`),
  ADD KEY `order_details_ibfk_3` (`storage_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`);

--
-- Indexes for table `product_battery_life`
--
ALTER TABLE `product_battery_life`
  ADD PRIMARY KEY (`battery_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `product_categories`
--
ALTER TABLE `product_categories`
  ADD PRIMARY KEY (`product_id`,`category_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `product_color`
--
ALTER TABLE `product_color`
  ADD PRIMARY KEY (`product_id`,`color_id`),
  ADD KEY `color_id` (`color_id`);

--
-- Indexes for table `product_connectivity`
--
ALTER TABLE `product_connectivity`
  ADD PRIMARY KEY (`connectivity_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `product_dimensions`
--
ALTER TABLE `product_dimensions`
  ADD PRIMARY KEY (`dimension_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`image_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `product_specifications`
--
ALTER TABLE `product_specifications`
  ADD PRIMARY KEY (`spec_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `product_warranties`
--
ALTER TABLE `product_warranties`
  ADD PRIMARY KEY (`warranty_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `product_weight`
--
ALTER TABLE `product_weight`
  ADD PRIMARY KEY (`weight_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `storage_options`
--
ALTER TABLE `storage_options`
  ADD PRIMARY KEY (`storage_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `storage_stock`
--
ALTER TABLE `storage_stock`
  ADD PRIMARY KEY (`product_id`,`storage_id`),
  ADD KEY `storage_id` (`storage_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `add_to_cart`
--
ALTER TABLE `add_to_cart`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `order_details`
--
ALTER TABLE `order_details`
  MODIFY `order_detail_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `product_battery_life`
--
ALTER TABLE `product_battery_life`
  MODIFY `battery_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `product_connectivity`
--
ALTER TABLE `product_connectivity`
  MODIFY `connectivity_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `product_dimensions`
--
ALTER TABLE `product_dimensions`
  MODIFY `dimension_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=169;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `image_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `product_specifications`
--
ALTER TABLE `product_specifications`
  MODIFY `spec_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=127;

--
-- AUTO_INCREMENT for table `product_warranties`
--
ALTER TABLE `product_warranties`
  MODIFY `warranty_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `product_weight`
--
ALTER TABLE `product_weight`
  MODIFY `weight_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT for table `storage_options`
--
ALTER TABLE `storage_options`
  MODIFY `storage_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `add_to_cart`
--
ALTER TABLE `add_to_cart`
  ADD CONSTRAINT `add_to_cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `customers` (`customer_id`),
  ADD CONSTRAINT `add_to_cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`),
  ADD CONSTRAINT `add_to_cart_ibfk_3` FOREIGN KEY (`storage_id`) REFERENCES `storage_options` (`storage_id`);

--
-- Constraints for table `customers`
--
ALTER TABLE `customers`
  ADD CONSTRAINT `customers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `order_details`
--
ALTER TABLE `order_details`
  ADD CONSTRAINT `order_details_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`),
  ADD CONSTRAINT `order_details_ibfk_4` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`);

--
-- Constraints for table `product_battery_life`
--
ALTER TABLE `product_battery_life`
  ADD CONSTRAINT `product_battery_life_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `product_categories`
--
ALTER TABLE `product_categories`
  ADD CONSTRAINT `product_categories_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`),
  ADD CONSTRAINT `product_categories_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`);

--
-- Constraints for table `product_color`
--
ALTER TABLE `product_color`
  ADD CONSTRAINT `product_color_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_color_ibfk_2` FOREIGN KEY (`color_id`) REFERENCES `colors` (`color_id`) ON DELETE CASCADE;

--
-- Constraints for table `product_connectivity`
--
ALTER TABLE `product_connectivity`
  ADD CONSTRAINT `product_connectivity_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `product_dimensions`
--
ALTER TABLE `product_dimensions`
  ADD CONSTRAINT `product_dimensions_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `product_specifications`
--
ALTER TABLE `product_specifications`
  ADD CONSTRAINT `product_specifications_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `product_warranties`
--
ALTER TABLE `product_warranties`
  ADD CONSTRAINT `product_warranties_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `product_weight`
--
ALTER TABLE `product_weight`
  ADD CONSTRAINT `product_weight_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`),
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`);

--
-- Constraints for table `storage_options`
--
ALTER TABLE `storage_options`
  ADD CONSTRAINT `storage_options_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `storage_stock`
--
ALTER TABLE `storage_stock`
  ADD CONSTRAINT `storage_stock_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`),
  ADD CONSTRAINT `storage_stock_ibfk_2` FOREIGN KEY (`storage_id`) REFERENCES `storage_options` (`storage_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
