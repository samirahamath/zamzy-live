-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 01, 2026 at 10:14 PM
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
-- Database: `zamzy_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `zamzy_admin_users`
--

CREATE TABLE `zamzy_admin_users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` varchar(20) DEFAULT 'admin',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `zamzy_admin_users`
--

INSERT INTO `zamzy_admin_users` (`id`, `username`, `password_hash`, `name`, `email`, `role`, `created_at`) VALUES
(1, 'admin', '$2y$10$m351r5NDXvQk1YFPwFOrvepOmdfpaoAyRkLukZzMI2/LJK4x3ASwa', 'ZAMZY Admin', 'admin@zamzy.in', 'superadmin', '2026-09-01 17:34:08');

-- --------------------------------------------------------

--
-- Table structure for table `zamzy_careers_applications`
--

CREATE TABLE `zamzy_careers_applications` (
  `id` int(11) NOT NULL,
  `job_id` int(11) DEFAULT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `location_college` varchar(150) NOT NULL,
  `primary_skills` varchar(255) NOT NULL,
  `experience_level` varchar(50) DEFAULT 'College Student / Fresher',
  `availability_hours` varchar(50) DEFAULT '15-20 hrs/week',
  `expected_payout` varchar(100) DEFAULT 'Project Commission',
  `portfolio_url` varchar(255) DEFAULT NULL,
  `past_work_notes` text DEFAULT NULL,
  `status` enum('new','shortlisted','assigned_project','active_guild','rejected') DEFAULT 'new',
  `internal_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `zamzy_careers_jobs`
--

CREATE TABLE `zamzy_careers_jobs` (
  `id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `department` varchar(100) NOT NULL,
  `employment_type` varchar(50) DEFAULT 'Freelance / Project-Based',
  `location` varchar(100) DEFAULT 'Remote / Chennai',
  `experience_level` varchar(50) DEFAULT 'College Student / Freelancer',
  `stipend_salary` varchar(100) DEFAULT 'Project Commission + Milestones',
  `description` text NOT NULL,
  `requirements` text NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `zamzy_careers_jobs`
--

INSERT INTO `zamzy_careers_jobs` (`id`, `title`, `department`, `employment_type`, `location`, `experience_level`, `stipend_salary`, `description`, `requirements`, `is_active`, `created_at`) VALUES
(1, 'Flutter & Mobile App Engineer', 'Mobile Engineering', 'Freelance / Project Basis', 'Remote / Anna Nagar Workshop', 'Students & Freelancers Welcome', '₹15,000 – ₹45,000 per project milestone', 'Build high-performance Flutter iOS and Android applications for enterprise clients. Work with offline-first SQLite sync, REST APIs, and smooth UI animations.', 'Proficiency in Flutter & Dart; Experience with Riverpod or Bloc; API integration knowledge; Git workflow.', 1, '2026-09-01 19:03:37'),
(2, 'Full-Stack React & Node.js Developer', 'Web & SaaS Platforms', 'Project Commission + Retainer', 'Remote / Chennai', 'College Friends & Developers', '₹20,000 – ₹60,000 per SaaS delivery', 'Develop scalable SaaS dashboards, customer portals, and web apps using Next.js/React, TypeScript, Node.js, and PostgreSQL/MySQL databases.', 'Solid JavaScript/TypeScript foundations; React/Next.js; REST/GraphQL APIs; TailwindCSS or Vanilla CSS.', 1, '2026-09-01 19:03:37'),
(3, 'UI/UX Product Designer', 'Design & Design Systems', 'Freelance / Per Screen Model', 'Remote', 'Portfolio-Driven (Beginner to Pro)', '₹10,000 – ₹30,000 per UI design brief', 'Create futuristic, ultra-modern dark UI dashboards, mobile app flows in Figma, interactive micro-interactions, and design systems for client platforms.', 'Figma mastery; Knowledge of mobile and responsive UX; Design systems thinking; Portfolio showcasing web or mobile apps.', 1, '2026-09-01 19:03:37'),
(4, 'Campus Tech Ambassador & Project Scout', 'Community & Partnerships', 'High Commission (10%–20% of Project)', 'Your College / City', 'College Students & Networkers', 'Up to ₹10,000 – ₹50,000 per closed client deal', 'Represent ZAMZY in your college and professional network. Connect local businesses, startups, and college final-year project requirements with our engineering team for high commissions.', 'Strong communication skills; Active network in college or local businesses; Basic tech awareness.', 1, '2026-09-01 19:03:37');

-- --------------------------------------------------------

--
-- Table structure for table `zamzy_demo_requests`
--

CREATE TABLE `zamzy_demo_requests` (
  `id` int(11) NOT NULL,
  `product_name` varchar(150) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `email` varchar(100) NOT NULL,
  `status` enum('pending','dispatched','completed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `zamzy_inquiries`
--

CREATE TABLE `zamzy_inquiries` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `preferred_language` varchar(50) DEFAULT 'English',
  `budget` varchar(100) DEFAULT '₹25k – ₹75k',
  `role` varchar(100) DEFAULT NULL,
  `company` varchar(100) DEFAULT NULL,
  `tier` varchar(100) DEFAULT 'Tier 02 — Custom App & Web',
  `launch_window` varchar(100) DEFAULT NULL,
  `project_type` varchar(100) DEFAULT NULL,
  `requirements` text NOT NULL,
  `reference_url` varchar(255) DEFAULT NULL,
  `status` enum('partial','new','contacted','in_progress','converted','archived') DEFAULT 'new',
  `admin_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `zamzy_inquiries`
--

INSERT INTO `zamzy_inquiries` (`id`, `name`, `email`, `phone`, `preferred_language`, `budget`, `role`, `company`, `tier`, `launch_window`, `project_type`, `requirements`, `reference_url`, `status`, `admin_notes`, `created_at`) VALUES
(1, 'B.sameer ahamath', 'Samir52790@gmail.com', '+916382973087', 'English', '₹25,000 – ₹75,000 (Custom Web & App)', '', '', 'Tier 02 — Custom App & Web', '', 'Custom SaaS Platform', 'dfdfd', '', 'new', NULL, '2026-09-01 18:50:03');

-- --------------------------------------------------------

--
-- Table structure for table `zamzy_testimonials`
--

CREATE TABLE `zamzy_testimonials` (
  `id` int(11) NOT NULL,
  `client_name` varchar(100) NOT NULL,
  `company_name` varchar(100) NOT NULL,
  `role` varchar(100) NOT NULL,
  `location` varchar(100) DEFAULT 'Chennai',
  `rating` int(11) DEFAULT 5,
  `review_text` text NOT NULL,
  `project_type` varchar(100) DEFAULT 'Custom SaaS & Web Platform',
  `is_featured` tinyint(1) DEFAULT 1,
  `is_published` tinyint(1) DEFAULT 1,
  `is_approved` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `zamzy_testimonials`
--

INSERT INTO `zamzy_testimonials` (`id`, `client_name`, `company_name`, `role`, `location`, `rating`, `review_text`, `project_type`, `is_featured`, `is_published`, `is_approved`, `created_at`) VALUES
(1, 'Karthik Subramanian', 'Apex Retail & Logistics', 'Founder & CEO', 'Anna Nagar, Chennai', 5, 'ZAMZY engineered our complete multi-warehouse inventory and WhatsApp automated billing system within 12 days. The code quality, database speed, and clean architecture exceeded all our expectations.', 'Custom SaaS & WhatsApp Automation', 1, 1, 1, '2026-09-01 17:34:08'),
(2, 'Ananya Ramachandran', 'Velammal EduTech Hub', 'Director of Digital Tech', 'Chennai, India', 5, 'We replaced 4 disparate school software vendors with ZAMZY’s School ERP ecosystem. Parents and teachers love the intuitive Flutter mobile apps. 99.9% uptime with zero glitches during annual admissions.', 'School ERP & Multi-App Ecosystem', 1, 1, 1, '2026-09-01 17:34:08'),
(3, 'Devraj Nambiar', 'Nambiar Real Estate Holdings', 'Managing Director', 'Chennai & Bengaluru', 5, 'Their rapid delivery turnaround is real. Our Real Estate CRM and property site-visit tracking system was fully deployed in 2 weeks. The automated IVR call tree alone boosted our deal conversions by 45%.', 'Real Estate CRM & IVR Telephony', 1, 1, 1, '2026-09-01 17:34:08'),
(4, 'Dr. Meera Krishnan', 'CareFirst Medical Clinics', 'Chief Technology Officer', 'Anna Nagar West, Chennai', 5, 'ZAMZY’s team works like a true engineering squad. They delivered high-concurrency patient scheduling, automated WhatsApp reminder notifications, and GST-compliant billing with flawless execution.', 'Healthcare ERP & WhatsApp Gateway', 1, 1, 1, '2026-09-01 17:34:08');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `zamzy_admin_users`
--
ALTER TABLE `zamzy_admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `zamzy_careers_applications`
--
ALTER TABLE `zamzy_careers_applications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `zamzy_careers_jobs`
--
ALTER TABLE `zamzy_careers_jobs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `zamzy_demo_requests`
--
ALTER TABLE `zamzy_demo_requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `zamzy_inquiries`
--
ALTER TABLE `zamzy_inquiries`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `zamzy_testimonials`
--
ALTER TABLE `zamzy_testimonials`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `zamzy_admin_users`
--
ALTER TABLE `zamzy_admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `zamzy_careers_applications`
--
ALTER TABLE `zamzy_careers_applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `zamzy_careers_jobs`
--
ALTER TABLE `zamzy_careers_jobs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `zamzy_demo_requests`
--
ALTER TABLE `zamzy_demo_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `zamzy_inquiries`
--
ALTER TABLE `zamzy_inquiries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `zamzy_testimonials`
--
ALTER TABLE `zamzy_testimonials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
