-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: May 04, 2026 at 12:49 PM
-- Server version: 8.0.39
-- PHP Version: 8.2.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `tupay`
--

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `queue` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `personal_access_tokens`
--

INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(1, 'App\\Models\\User', 1, 'tupay_access_token', 'f7f0a35cd51569bc8dacd4b80be2fe68755fc03fabee56e1ba99f8d101ad1da7', '[\"*\"]', NULL, NULL, '2026-05-04 08:54:08', '2026-05-04 08:54:08'),
(2, 'App\\Models\\User', 1, 'tupay_access_token', '0d7ca910595c283c9c0cc279cdb522584c37a230116712d78893cbbb0cf8b289', '[\"*\"]', '2026-05-04 11:25:52', NULL, '2026-05-04 09:01:43', '2026-05-04 11:25:52');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` bigint UNSIGNED NOT NULL,
  `wallet_id` bigint UNSIGNED NOT NULL,
  `currency` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` bigint NOT NULL,
  `balance_before` bigint NOT NULL,
  `balance_after` bigint NOT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `reference` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `wallet_id`, `currency`, `amount`, `balance_before`, `balance_after`, `type`, `reference`, `status`, `metadata`, `created_at`, `updated_at`) VALUES
(1, 1, '', 10000, 100002, 90002, 'swap_out', 'SWP-NMAYQBMSI4', 'completed', '{\"rate\": 0.00475, \"target\": \"CNY\"}', '2026-05-04 10:00:46', '2026-05-04 11:10:59'),
(2, 2, '', 48, 0, 48, 'swap_in', 'SWP-NMAYQBMSI4-IN', 'completed', '{\"source\": \"NGN\"}', '2026-05-04 10:00:46', '2026-05-04 10:00:46'),
(3, 1, '', 10000, 90002, 80002, 'swap_out', 'SWP-ZZVCTHGT2X', 'completed', '{\"rate\": 0.00475, \"target\": \"CNY\"}', '2026-05-04 10:02:22', '2026-05-04 11:11:03'),
(4, 2, '', 48, 48, 96, 'swap_in', 'SWP-ZZVCTHGT2X-IN', 'completed', '{\"source\": \"NGN\"}', '2026-05-04 10:02:22', '2026-05-04 10:02:22'),
(5, 1, '', 10000, 80002, 70002, 'swap_out', 'SWP-47DZBJXDVH', 'completed', '{\"rate\": 0.00475, \"target\": \"CNY\", \"browser\": \"Unknown\", \"device_id\": \"unknown\", \"ip_address\": \"127.0.0.1\", \"user_agent\": \"PostmanRuntime/7.51.1\"}', '2026-05-04 10:05:39', '2026-05-04 11:11:05'),
(6, 2, '', 48, 96, 144, 'swap_in', 'SWP-47DZBJXDVH-IN', 'completed', '{\"source\": \"NGN\", \"browser\": \"Unknown\", \"device_id\": \"unknown\", \"ip_address\": \"127.0.0.1\", \"user_agent\": \"PostmanRuntime/7.51.1\"}', '2026-05-04 10:05:39', '2026-05-04 10:05:39'),
(7, 1, 'NGN', -10000, 70002, 60002, 'swap_out', 'SWP-SWVRYO5ZWV', 'completed', '{\"rate\": 0.00475, \"target\": \"CNY\", \"browser\": \"Unknown\", \"device_id\": \"unknown\", \"ip_address\": \"127.0.0.1\", \"user_agent\": \"PostmanRuntime/7.51.1\"}', '2026-05-04 10:13:08', '2026-05-04 10:13:08'),
(8, 2, 'CNY', 48, 144, 192, 'swap_in', 'SWP-SWVRYO5ZWV-IN', 'completed', '{\"source\": \"NGN\", \"browser\": \"Unknown\", \"device_id\": \"unknown\", \"ip_address\": \"127.0.0.1\", \"user_agent\": \"PostmanRuntime/7.51.1\"}', '2026-05-04 10:13:08', '2026-05-04 10:13:08'),
(9, 1, 'NGN', -10000, 60002, 50002, 'swap_out', 'SWP-CVBH6NF8OH', 'completed', '{\"rate\": 0.00475, \"target\": \"CNY\", \"browser\": \"Unknown\", \"device_id\": \"unknown\", \"ip_address\": \"127.0.0.1\", \"user_agent\": \"PostmanRuntime/7.51.1\", \"idempotency_key\": \"SWP123\"}', '2026-05-04 10:16:55', '2026-05-04 10:16:55'),
(10, 2, 'CNY', 48, 192, 240, 'swap_in', 'SWP-CVBH6NF8OH-IN', 'completed', '{\"source\": \"NGN\", \"browser\": \"Unknown\", \"device_id\": \"unknown\", \"ip_address\": \"127.0.0.1\", \"user_agent\": \"PostmanRuntime/7.51.1\", \"idempotency_key\": \"SWP123\"}', '2026-05-04 10:16:55', '2026-05-04 10:16:55'),
(11, 1, 'NGN', -10000, 50002, 40002, 'swap_out', 'SWP-UVYL1XX8WK', 'completed', '{\"rate\": 0.00475, \"target\": \"CNY\", \"browser\": \"Unknown\", \"device_id\": \"unknown\", \"ip_address\": \"127.0.0.1\", \"user_agent\": \"PostmanRuntime/7.51.1\", \"idempotency_key\": \"adaf277e-441b-45e0-8463-1ace9bd5b645\"}', '2026-05-04 10:20:12', '2026-05-04 10:20:12'),
(12, 2, 'CNY', 48, 240, 288, 'swap_in', 'SWP-UVYL1XX8WK-IN', 'completed', '{\"source\": \"NGN\", \"browser\": \"Unknown\", \"device_id\": \"unknown\", \"ip_address\": \"127.0.0.1\", \"user_agent\": \"PostmanRuntime/7.51.1\", \"idempotency_key\": \"adaf277e-441b-45e0-8463-1ace9bd5b645\"}', '2026-05-04 10:20:12', '2026-05-04 10:20:12'),
(13, 1, 'NGN', -10000, 40002, 30002, 'swap_out', 'SWP-PGAUVRD0TJ', 'completed', '{\"rate\": 0.00475, \"target\": \"CNY\", \"browser\": \"Other/Mobile\", \"device_id\": \"web-client\", \"ip_address\": \"127.0.0.1\", \"user_agent\": \"PostmanRuntime/7.51.1\", \"idempotency_key\": \"41fedd18-58ff-49ff-817e-a85bae9c74bf\"}', '2026-05-04 10:30:43', '2026-05-04 10:30:43'),
(14, 2, 'CNY', 48, 288, 336, 'swap_in', 'SWP-PGAUVRD0TJ-IN', 'completed', '{\"source\": \"NGN\", \"browser\": \"Other/Mobile\", \"device_id\": \"web-client\", \"ip_address\": \"127.0.0.1\", \"user_agent\": \"PostmanRuntime/7.51.1\", \"idempotency_key\": \"41fedd18-58ff-49ff-817e-a85bae9c74bf\"}', '2026-05-04 10:30:43', '2026-05-04 10:30:43'),
(15, 1, 'NGN', 10000, 30002, 40002, 'deposit', 'FND-7MPS6WM6GK', 'pending', '{\"method\": \"manual_test_funding\", \"browser\": \"Other/Mobile\", \"ip_address\": \"127.0.0.1\"}', '2026-05-04 10:36:08', '2026-05-04 12:12:13');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone_number` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `two_factor_secret` text COLLATE utf8mb4_unicode_ci,
  `two_factor_recovery_codes` text COLLATE utf8mb4_unicode_ci,
  `two_factor_confirmed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone_number`, `email_verified_at`, `password`, `remember_token`, `two_factor_secret`, `two_factor_recovery_codes`, `two_factor_confirmed_at`, `created_at`, `updated_at`) VALUES
(1, 'Test User', 'test@example.com', '+2348000000000', '2026-05-04 12:26:13', '$2y$12$WyTcR2qUGKuysFh0FMahoefN7CLYd1x09EBu/i6okb23BcjphDxBe', NULL, NULL, NULL, '2026-05-04 09:01:43', '2026-05-04 09:45:35', '2026-05-04 12:26:15');

-- --------------------------------------------------------

--
-- Table structure for table `wallets`
--

CREATE TABLE `wallets` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `currency` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL,
  `balance` bigint NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `wallets`
--

INSERT INTO `wallets` (`id`, `user_id`, `currency`, `balance`, `created_at`, `updated_at`) VALUES
(1, 1, 'NGN', 40002, '2026-05-04 09:04:08', '2026-05-04 10:36:08'),
(2, 1, 'CNY', 336, '2026-05-04 09:04:08', '2026-05-04 10:30:43');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transactions_reference_unique` (`reference`),
  ADD KEY `idx_transactions_wallet_status` (`wallet_id`,`status`),
  ADD KEY `idx_transactions_currency` (`currency`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_phone_unique` (`phone_number`);

--
-- Indexes for table `wallets`
--
ALTER TABLE `wallets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_currency_unique` (`user_id`,`currency`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `wallets`
--
ALTER TABLE `wallets`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `fk_transactions_wallet_id` FOREIGN KEY (`wallet_id`) REFERENCES `wallets` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wallets`
--
ALTER TABLE `wallets`
  ADD CONSTRAINT `fk_wallets_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
