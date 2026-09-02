<?php
// ZAMZY Platform — Database Connection & Auto-Migration Engine
// Database: zamzy_db (Host: localhost, User: root, Password: '')

if (!function_exists('loadEnv')) {
    function loadEnv($path = __DIR__ . '/.env') {
        if (!file_exists($path)) return;
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || $line[0] === '#') continue;
            if (strpos($line, '=') !== false) {
                list($name, $value) = explode('=', $line, 2);
                $name = trim($name);
                $value = trim($value);
                $value = trim($value, '"\'');
                if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                    putenv(sprintf('%s=%s', $name, $value));
                    $_ENV[$name] = $value;
                    $_SERVER[$name] = $value;
                }
            }
        }
    }
}
loadEnv();

if (!defined('DB_HOST')) define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
if (!defined('DB_USER')) define('DB_USER', getenv('DB_USER') ?: 'root');
if (!defined('DB_PASS')) define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
if (!defined('DB_NAME')) define('DB_NAME', getenv('DB_NAME') ?: 'zamzy_db');
if (!defined('DEEPSEEK_API_KEY')) define('DEEPSEEK_API_KEY', getenv('DEEPSEEK_API_KEY') ?: 'sk-71bbb2ea1a0e45dcbf2574d6f115aac1');

// Dynamic BASE_URL and ADMIN_URL Detection Engine
if (!defined('BASE_URL')) {
    $envUrl = getenv('APP_URL');
    if (!empty($envUrl) && strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost') === false) {
        define('BASE_URL', rtrim($envUrl, '/'));
    } else {
        $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
        $scriptDir = rtrim($scriptDir, '/');
        if (substr($scriptDir, -6) === '/admin') {
            $scriptDir = substr($scriptDir, 0, -6);
        }
        define('BASE_URL', $scheme . '://' . $host . ($scriptDir ? $scriptDir : ''));
    }
}

if (!defined('ADMIN_URL')) {
    define('ADMIN_URL', BASE_URL . '/admin');
}

function getDbConnection() {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    try {
        // Attempt database creation if user has permissions (e.g. local XAMPP)
        try {
            $pdoServer = new PDO("mysql:host=" . DB_HOST . ";charset=utf8mb4", DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
            $pdoServer->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        } catch (Exception $e) {
            // Safe fallback on shared cPanel hosting where database is pre-created
        }
        
        // Connect directly to target database
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);

        // Auto create tables and columns if not exists
        initTables($pdo);

        return $pdo;
    } catch (PDOException $e) {
        error_log("ZAMZY DB Connection Error: " . $e->getMessage());
        return null;
    }
}

function initTables($pdo) {
    // 1. Admin Users Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `zamzy_admin_users` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `username` VARCHAR(50) NOT NULL UNIQUE,
        `password_hash` VARCHAR(255) NOT NULL,
        `name` VARCHAR(100) NOT NULL,
        `email` VARCHAR(100) NOT NULL,
        `role` VARCHAR(20) DEFAULT 'admin',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // Check if default admin exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM `zamzy_admin_users` WHERE `username` = 'admin'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $defaultPass = password_hash('zamzy@2026', PASSWORD_DEFAULT);
        $insertAdmin = $pdo->prepare("INSERT INTO `zamzy_admin_users` (`username`, `password_hash`, `name`, `email`, `role`) VALUES ('admin', :pass, 'ZAMZY Admin', 'admin@zamzy.in', 'superadmin')");
        $insertAdmin->execute([':pass' => $defaultPass]);
    }

    // 2. Project Inquiries / Briefs Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `zamzy_inquiries` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(100) NOT NULL,
        `email` VARCHAR(100) NOT NULL,
        `phone` VARCHAR(30) NOT NULL,
        `preferred_language` VARCHAR(50) DEFAULT 'English',
        `budget` VARCHAR(100) DEFAULT '₹25,000 – ₹75,000 (Custom Web & App)',
        `role` VARCHAR(100) NULL,
        `company` VARCHAR(100) NULL,
        `tier` VARCHAR(100) DEFAULT 'Tier 02 — Custom App & Web',
        `launch_window` VARCHAR(100) NULL,
        `project_type` VARCHAR(100) DEFAULT 'Custom SaaS Platform',
        `requirements` TEXT NOT NULL,
        `reference_url` VARCHAR(255) NULL,
        `status` ENUM('partial', 'new', 'contacted', 'in_progress', 'converted', 'archived') DEFAULT 'new',
        `admin_notes` TEXT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // Ensure columns exist in zamzy_inquiries
    try {
        $pdo->exec("ALTER TABLE `zamzy_inquiries` ADD COLUMN `preferred_language` VARCHAR(50) DEFAULT 'English' AFTER `phone`");
    } catch (Exception $e) {}
    try {
        $pdo->exec("ALTER TABLE `zamzy_inquiries` ADD COLUMN `budget` VARCHAR(100) DEFAULT '₹25,000 – ₹75,000 (Custom Web & App)' AFTER `preferred_language`");
    } catch (Exception $e) {}
    try {
        $pdo->exec("ALTER TABLE `zamzy_inquiries` ADD COLUMN `admin_notes` TEXT NULL AFTER `status`");
    } catch (Exception $e) {}
    // Expand ENUM to include 'partial' for abandoned/auto-captured forms
    try {
        $pdo->exec("ALTER TABLE `zamzy_inquiries` MODIFY COLUMN `status` ENUM('partial','new','contacted','in_progress','converted','archived') DEFAULT 'new'");
    } catch (Exception $e) {}

    // 3. Demo Requests Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `zamzy_demo_requests` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `product_name` VARCHAR(150) NOT NULL,
        `phone` VARCHAR(30) NOT NULL,
        `email` VARCHAR(100) NOT NULL,
        `status` ENUM('pending', 'dispatched', 'contacted', 'closed') DEFAULT 'pending',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // 4. Testimonials & Client Reviews Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `zamzy_testimonials` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `client_name` VARCHAR(100) NOT NULL,
        `company_name` VARCHAR(100) NOT NULL,
        `role` VARCHAR(100) NOT NULL,
        `location` VARCHAR(100) DEFAULT 'Anna Nagar, Chennai',
        `rating` INT DEFAULT 5,
        `review_text` TEXT NOT NULL,
        `project_type` VARCHAR(100) DEFAULT 'Custom SaaS Platform',
        `is_featured` TINYINT(1) DEFAULT 1,
        `is_published` TINYINT(1) DEFAULT 1,
        `is_approved` TINYINT(1) DEFAULT 1,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // Ensure is_published and is_approved exist
    try {
        $pdo->exec("ALTER TABLE `zamzy_testimonials` ADD COLUMN `is_published` TINYINT(1) DEFAULT 1 AFTER `is_featured`");
    } catch (Exception $e) {}
    try {
        $pdo->exec("ALTER TABLE `zamzy_testimonials` ADD COLUMN `is_approved` TINYINT(1) DEFAULT 1 AFTER `is_published`");
    } catch (Exception $e) {}

    // 5. Careers & Job Openings Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `zamzy_careers_jobs` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `title` VARCHAR(150) NOT NULL,
        `department` VARCHAR(100) NOT NULL,
        `employment_type` VARCHAR(50) DEFAULT 'Freelance / Project-Based',
        `location` VARCHAR(100) DEFAULT 'Remote / Chennai',
        `experience_level` VARCHAR(50) DEFAULT 'College Student / Freelancer',
        `stipend_salary` VARCHAR(100) DEFAULT 'Project Commission + Milestones',
        `description` TEXT NOT NULL,
        `requirements` TEXT NOT NULL,
        `is_active` TINYINT(1) DEFAULT 1,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // 6. AI Chat Sessions & Full Message History Tables
    $pdo->exec("CREATE TABLE IF NOT EXISTS `zamzy_chat_sessions` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `session_token` VARCHAR(64) UNIQUE NOT NULL,
        `user_name` VARCHAR(120) NULL,
        `user_phone` VARCHAR(40) NULL,
        `user_email` VARCHAR(120) NULL,
        `total_messages` INT DEFAULT 0,
        `last_message` TEXT NULL,
        `status` ENUM('lead_captured', 'in_progress', 'closed') DEFAULT 'in_progress',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    $pdo->exec("CREATE TABLE IF NOT EXISTS `zamzy_chat_messages` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `session_id` INT NOT NULL,
        `sender` ENUM('user', 'bot') NOT NULL,
        `message` TEXT NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (`session_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // 6. Freelance Talent Pool & Community Applications Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `zamzy_careers_applications` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `job_id` INT NULL,
        `full_name` VARCHAR(100) NOT NULL,
        `email` VARCHAR(100) NOT NULL,
        `phone` VARCHAR(30) NOT NULL,
        `location_college` VARCHAR(150) NOT NULL,
        `primary_skills` VARCHAR(255) NOT NULL,
        `experience_level` VARCHAR(50) DEFAULT 'College Student / Fresher',
        `availability_hours` VARCHAR(50) DEFAULT '15-20 hrs/week',
        `expected_payout` VARCHAR(100) DEFAULT 'Project Commission',
        `portfolio_url` VARCHAR(255) NULL,
        `resume_file` VARCHAR(255) NULL,
        `past_work_notes` TEXT NULL,
        `status` ENUM('new', 'shortlisted', 'assigned_project', 'active_guild', 'rejected') DEFAULT 'new',
        `internal_notes` TEXT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // Check if resume_file column exists in zamzy_careers_applications
    try {
        $pdo->query("SELECT `resume_file` FROM `zamzy_careers_applications` LIMIT 1");
    } catch (Exception $e) {
        $pdo->exec("ALTER TABLE `zamzy_careers_applications` ADD COLUMN `resume_file` VARCHAR(255) NULL AFTER `portfolio_url`");
    }

    // Seed default jobs if empty
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM `zamzy_careers_jobs`");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $jobs = [
            [
                'title' => 'Flutter & Mobile App Engineer',
                'department' => 'Mobile Engineering',
                'employment_type' => 'Freelance / Project Basis',
                'location' => 'Remote / Anna Nagar Workshop',
                'experience_level' => 'Students & Freelancers Welcome',
                'stipend_salary' => '₹15,000 – ₹45,000 per project milestone',
                'description' => 'Build high-performance Flutter iOS and Android applications for enterprise clients. Work with offline-first SQLite sync, REST APIs, and smooth UI animations.',
                'requirements' => 'Proficiency in Flutter & Dart; Experience with Riverpod or Bloc; API integration knowledge; Git workflow.'
            ],
            [
                'title' => 'Full-Stack React & Node.js Developer',
                'department' => 'Web & SaaS Platforms',
                'employment_type' => 'Project Commission + Retainer',
                'location' => 'Remote / Chennai',
                'experience_level' => 'College Friends & Developers',
                'stipend_salary' => '₹20,000 – ₹60,000 per SaaS delivery',
                'description' => 'Develop scalable SaaS dashboards, customer portals, and web apps using Next.js/React, TypeScript, Node.js, and PostgreSQL/MySQL databases.',
                'requirements' => 'Solid JavaScript/TypeScript foundations; React/Next.js; REST/GraphQL APIs; TailwindCSS or Vanilla CSS.'
            ],
            [
                'title' => 'UI/UX Product Designer',
                'department' => 'Design & Design Systems',
                'employment_type' => 'Freelance / Per Screen Model',
                'location' => 'Remote',
                'experience_level' => 'Portfolio-Driven (Beginner to Pro)',
                'stipend_salary' => '₹10,000 – ₹30,000 per UI design brief',
                'description' => 'Create futuristic, ultra-modern dark UI dashboards, mobile app flows in Figma, interactive micro-interactions, and design systems for client platforms.',
                'requirements' => 'Figma mastery; Knowledge of mobile and responsive UX; Design systems thinking; Portfolio showcasing web or mobile apps.'
            ],
            [
                'title' => 'Campus Tech Ambassador & Project Scout',
                'department' => 'Community & Partnerships',
                'employment_type' => 'High Commission (10%–20% of Project)',
                'location' => 'Your College / City',
                'experience_level' => 'College Students & Networkers',
                'stipend_salary' => 'Up to ₹10,000 – ₹50,000 per closed client deal',
                'description' => 'Represent ZAMZY in your college and professional network. Connect local businesses, startups, and college final-year project requirements with our engineering team for high commissions.',
                'requirements' => 'Strong communication skills; Active network in college or local businesses; Basic tech awareness.'
            ]
        ];

        $insJob = $pdo->prepare("INSERT INTO `zamzy_careers_jobs` (`title`, `department`, `employment_type`, `location`, `experience_level`, `stipend_salary`, `description`, `requirements`, `is_active`) VALUES (:title, :department, :employment_type, :location, :experience_level, :stipend_salary, :description, :requirements, 1)");
        foreach ($jobs as $j) {
            $insJob->execute($j);
        }
    }

    // Seed default verified testimonials if table is empty
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM `zamzy_testimonials`");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $testimonials = [
            [
                'client_name' => 'Karthik Subramanian',
                'company_name' => 'Apex Retail & Logistics',
                'role' => 'Founder & CEO',
                'location' => 'Anna Nagar, Chennai',
                'rating' => 5,
                'review_text' => 'ZAMZY engineered our complete multi-warehouse inventory and WhatsApp automated billing system within 12 days. The code quality, database speed, and clean architecture exceeded all our expectations.',
                'project_type' => 'Custom SaaS & WhatsApp Automation'
            ],
            [
                'client_name' => 'Ananya Ramachandran',
                'company_name' => 'Velammal EduTech Hub',
                'role' => 'Director of Digital Tech',
                'location' => 'Chennai, India',
                'rating' => 5,
                'review_text' => 'We replaced 4 disparate school software vendors with ZAMZY’s School ERP ecosystem. Parents and teachers love the intuitive Flutter mobile apps. 99.9% uptime with zero glitches during annual admissions.',
                'project_type' => 'School ERP & Multi-App Ecosystem'
            ],
            [
                'client_name' => 'Devraj Nambiar',
                'company_name' => 'Nambiar Real Estate Holdings',
                'role' => 'Managing Director',
                'location' => 'Chennai & Bengaluru',
                'rating' => 5,
                'review_text' => 'Their rapid delivery turnaround is real. Our Real Estate CRM and property site-visit tracking system was fully deployed in 2 weeks. The automated IVR call tree alone boosted our deal conversions by 45%.',
                'project_type' => 'Real Estate CRM & IVR Telephony'
            ],
            [
                'client_name' => 'Dr. Meera Krishnan',
                'company_name' => 'CareFirst Medical Clinics',
                'role' => 'Chief Technology Officer',
                'location' => 'Anna Nagar West, Chennai',
                'rating' => 5,
                'review_text' => 'ZAMZY’s team works like a true engineering squad. They delivered high-concurrency patient scheduling, automated WhatsApp reminder notifications, and GST-compliant billing with flawless execution.',
                'project_type' => 'Healthcare ERP & WhatsApp Gateway'
            ]
        ];

        $ins = $pdo->prepare("INSERT INTO `zamzy_testimonials` (`client_name`, `company_name`, `role`, `location`, `rating`, `review_text`, `project_type`, `is_featured`, `is_published`, `is_approved`) VALUES (:client_name, :company_name, :role, :location, :rating, :review_text, :project_type, 1, 1, 1)");
        foreach ($testimonials as $t) {
            $ins->execute($t);
        }
    }
}
?>
