-- =============================================================================
-- HỆ THỐNG QUẢN LÝ TALENT STREAMERS (MCN PLATFORM)
-- Database Schema for MySQL 8.0+ (InnoDB, UTF8mb4)
-- =============================================================================

CREATE DATABASE IF NOT EXISTS `mcn_platform` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `mcn_platform`;

SET FOREIGN_KEY_CHECKS = 0;

-- 1. Bảng users
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `email_verified_at` TIMESTAMP NULL DEFAULT NULL,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('admin', 'manager', 'streamer', 'client') NOT NULL DEFAULT 'client',
    `remember_token` VARCHAR(100) NULL DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `users_email_unique` (`email`),
    INDEX `idx_users_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Bảng streamer_profiles
DROP TABLE IF EXISTS `streamer_profiles`;
CREATE TABLE `streamer_profiles` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `manager_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `stage_name` VARCHAR(100) NOT NULL,
    `category` VARCHAR(100) NOT NULL DEFAULT 'Gaming',
    `bio` TEXT NULL,
    `rate_per_hour` DECIMAL(12,2) NOT NULL DEFAULT 500000.00,
    `avatar_url` VARCHAR(255) NULL DEFAULT NULL,
    `banner_url` VARCHAR(255) NULL DEFAULT NULL,
    `status` ENUM('active', 'inactive', 'on_leave') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `streamer_profiles_user_id_unique` (`user_id`),
    INDEX `idx_streamer_profiles_manager_id` (`manager_id`),
    INDEX `idx_streamer_profiles_category` (`category`),
    INDEX `idx_streamer_profiles_rate` (`rate_per_hour`),
    INDEX `idx_streamer_profiles_status` (`status`),
    CONSTRAINT `fk_streamer_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_streamer_manager` FOREIGN KEY (`manager_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Bảng bookings
DROP TABLE IF EXISTS `bookings`;
CREATE TABLE `bookings` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `client_id` BIGINT UNSIGNED NOT NULL,
    `streamer_id` BIGINT UNSIGNED NOT NULL,
    `manager_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `start_time` DATETIME NOT NULL,
    `end_time` DATETIME NOT NULL,
    `job_description` TEXT NOT NULL,
    `budget` DECIMAL(12,2) NOT NULL,
    `commission_rate` DECIMAL(5,2) NOT NULL DEFAULT 15.00,
    `status` ENUM('pending', 'reviewing', 'approved', 'rejected', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
    `reject_reason` TEXT NULL DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_bookings_client_id` (`client_id`),
    INDEX `idx_bookings_streamer_id` (`streamer_id`),
    INDEX `idx_bookings_manager_id` (`manager_id`),
    INDEX `idx_bookings_status` (`status`),
    INDEX `idx_bookings_time` (`start_time`, `end_time`),
    CONSTRAINT `fk_bookings_client` FOREIGN KEY (`client_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_bookings_streamer` FOREIGN KEY (`streamer_id`) REFERENCES `streamer_profiles` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_bookings_manager` FOREIGN KEY (`manager_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Bảng schedules (Lịch tổng hợp tránh trùng lặp)
DROP TABLE IF EXISTS `schedules`;
CREATE TABLE `schedules` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `streamer_id` BIGINT UNSIGNED NOT NULL,
    `event_type` ENUM('stream', 'booking', 'training', 'personal') NOT NULL DEFAULT 'stream',
    `reference_id` BIGINT UNSIGNED NULL DEFAULT NULL COMMENT 'Trỏ về booking_id nếu là lịch book',
    `start_time` DATETIME NOT NULL,
    `end_time` DATETIME NOT NULL,
    `title` VARCHAR(150) NOT NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_schedules_streamer_time` (`streamer_id`, `start_time`, `end_time`),
    INDEX `idx_schedules_reference` (`reference_id`),
    CONSTRAINT `fk_schedules_streamer` FOREIGN KEY (`streamer_id`) REFERENCES `streamer_profiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Bảng stream_metrics
DROP TABLE IF EXISTS `stream_metrics`;
CREATE TABLE `stream_metrics` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `streamer_id` BIGINT UNSIGNED NOT NULL,
    `platform` ENUM('youtube', 'twitch', 'tiktok', 'facebook') NOT NULL DEFAULT 'youtube',
    `stream_date` DATE NOT NULL,
    `duration_hours` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    `avg_viewers` INT NOT NULL DEFAULT 0,
    `peak_viewers` INT NOT NULL DEFAULT 0,
    `followers_gained` INT NOT NULL DEFAULT 0,
    `source_type` ENUM('manual_entry', 'csv_import', 'mock_api') NOT NULL DEFAULT 'manual_entry',
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_metrics_streamer_date` (`streamer_id`, `stream_date`),
    INDEX `idx_metrics_platform` (`platform`),
    CONSTRAINT `fk_metrics_streamer` FOREIGN KEY (`streamer_id`) REFERENCES `streamer_profiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Bảng kpis
DROP TABLE IF EXISTS `kpis`;
CREATE TABLE `kpis` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `streamer_id` BIGINT UNSIGNED NOT NULL,
    `month_year` CHAR(7) NOT NULL COMMENT 'Định dạng YYYY-MM',
    `target_hours` DECIMAL(6,2) NOT NULL DEFAULT 60.00,
    `target_revenue` DECIMAL(12,2) NOT NULL DEFAULT 10000000.00,
    `target_avg_viewers` INT NOT NULL DEFAULT 1000,
    `achieved_hours` DECIMAL(6,2) NOT NULL DEFAULT 0.00,
    `achieved_revenue` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `status` ENUM('in_progress', 'achieved', 'failed') NOT NULL DEFAULT 'in_progress',
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `kpis_streamer_month_unique` (`streamer_id`, `month_year`),
    INDEX `idx_kpis_month` (`month_year`),
    INDEX `idx_kpis_status` (`status`),
    CONSTRAINT `fk_kpis_streamer` FOREIGN KEY (`streamer_id`) REFERENCES `streamer_profiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
