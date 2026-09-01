-- ZAMZY Digital Agency Platform Database Schema
-- Database: `zamzy_db`

CREATE DATABASE IF NOT EXISTS `zamzy_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `zamzy_db`;

-- 1. Admin Users Table
CREATE TABLE IF NOT EXISTS `zamzy_admin_users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `role` VARCHAR(20) DEFAULT 'admin',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Project Inquiries / Briefs
CREATE TABLE IF NOT EXISTS `zamzy_inquiries` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(30) NOT NULL,
    `role` VARCHAR(100) NULL,
    `company` VARCHAR(100) NULL,
    `tier` VARCHAR(100) DEFAULT 'Tier 02 — Custom App & Web',
    `launch_window` VARCHAR(100) NULL,
    `project_type` VARCHAR(100) NULL,
    `requirements` TEXT NOT NULL,
    `reference_url` VARCHAR(255) NULL,
    `status` ENUM('new', 'contacted', 'in_progress', 'converted', 'archived') DEFAULT 'new',
    `admin_notes` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Demo Requests Table
CREATE TABLE IF NOT EXISTS `zamzy_demo_requests` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `product_name` VARCHAR(150) NOT NULL,
    `phone` VARCHAR(30) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `status` ENUM('pending', 'dispatched', 'completed') DEFAULT 'pending',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Testimonials & Client Reviews Table
CREATE TABLE IF NOT EXISTS `zamzy_testimonials` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `client_name` VARCHAR(100) NOT NULL,
    `company_name` VARCHAR(100) NOT NULL,
    `role` VARCHAR(100) NOT NULL,
    `location` VARCHAR(100) DEFAULT 'Chennai',
    `rating` INT DEFAULT 5,
    `review_text` TEXT NOT NULL,
    `project_type` VARCHAR(100) DEFAULT 'Custom SaaS & Web Platform',
    `is_featured` TINYINT(1) DEFAULT 1,
    `is_approved` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
