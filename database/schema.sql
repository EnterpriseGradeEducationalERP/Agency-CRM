-- OneStop Agency CRM - Database Schema
-- Version: 2.0
-- Date: October 2025

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- ============================================================
-- TABLE: users
-- ============================================================
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(255) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `first_name` VARCHAR(100) NOT NULL,
  `last_name` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `role` ENUM('admin', 'project_manager', 'sales_executive', 'team_member', 'finance_officer', 'client') NOT NULL DEFAULT 'team_member',
  `avatar` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('active', 'inactive', 'suspended') NOT NULL DEFAULT 'active',
  `last_login` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `role` (`role`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: password_resets
-- ============================================================
CREATE TABLE IF NOT EXISTS `password_resets` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(255) NOT NULL,
  `token` VARCHAR(255) NOT NULL,
  `expires_at` DATETIME NOT NULL,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `email` (`email`),
  KEY `token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: activity_logs
-- ============================================================
CREATE TABLE IF NOT EXISTS `activity_logs` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) UNSIGNED NOT NULL,
  `action` VARCHAR(100) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `user_agent` TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `action` (`action`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: clients
-- ============================================================
CREATE TABLE IF NOT EXISTS `clients` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `company_name` VARCHAR(255) NOT NULL,
  `contact_person` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `website` VARCHAR(255) DEFAULT NULL,
  `address` TEXT DEFAULT NULL,
  `city` VARCHAR(100) DEFAULT NULL,
  `state` VARCHAR(100) DEFAULT NULL,
  `country` VARCHAR(100) DEFAULT NULL,
  `postal_code` VARCHAR(20) DEFAULT NULL,
  `gstin` VARCHAR(50) DEFAULT NULL,
  `source` ENUM('lead', 'referral', 'website', 'social_media', 'cold_call', 'advertisement') DEFAULT 'lead',
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `assigned_to` INT(11) UNSIGNED DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `email` (`email`),
  KEY `assigned_to` (`assigned_to`),
  KEY `status` (`status`),
  FOREIGN KEY (`assigned_to`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: pipeline_stages
-- ============================================================
CREATE TABLE IF NOT EXISTS `pipeline_stages` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL,
  `color` VARCHAR(7) DEFAULT '#007bff',
  `order_position` INT(11) NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `order_position` (`order_position`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default pipeline stages
INSERT INTO `pipeline_stages` (`name`, `slug`, `color`, `order_position`, `created_at`, `updated_at`) VALUES
('Lead', 'lead', '#6c757d', 1, NOW(), NOW()),
('Contacted', 'contacted', '#17a2b8', 2, NOW(), NOW()),
('Qualified', 'qualified', '#ffc107', 3, NOW(), NOW()),
('Proposal Sent', 'proposal_sent', '#007bff', 4, NOW(), NOW()),
('Negotiation', 'negotiation', '#fd7e14', 5, NOW(), NOW()),
('Won', 'won', '#28a745', 6, NOW(), NOW()),
('Lost', 'lost', '#dc3545', 7, NOW(), NOW());

-- ============================================================
-- TABLE: deals
-- ============================================================
CREATE TABLE IF NOT EXISTS `deals` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `client_id` INT(11) UNSIGNED NOT NULL,
  `stage_id` INT(11) UNSIGNED NOT NULL,
  `value` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `currency` VARCHAR(3) NOT NULL DEFAULT 'INR',
  `probability` INT(11) DEFAULT 50,
  `expected_close_date` DATE DEFAULT NULL,
  `actual_close_date` DATE DEFAULT NULL,
  `assigned_to` INT(11) UNSIGNED DEFAULT NULL,
  `next_followup` DATETIME DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,
  `status` ENUM('open', 'won', 'lost') NOT NULL DEFAULT 'open',
  `lost_reason` TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `client_id` (`client_id`),
  KEY `stage_id` (`stage_id`),
  KEY `assigned_to` (`assigned_to`),
  KEY `status` (`status`),
  FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`stage_id`) REFERENCES `pipeline_stages`(`id`) ON DELETE RESTRICT,
  FOREIGN KEY (`assigned_to`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: services
-- ============================================================
CREATE TABLE IF NOT EXISTS `services` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `category` VARCHAR(100) DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: job_roles
-- ============================================================
CREATE TABLE IF NOT EXISTS `job_roles` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL,
  `hourly_rate` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `currency` VARCHAR(3) NOT NULL DEFAULT 'INR',
  `description` TEXT DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: service_role_map
-- ============================================================
CREATE TABLE IF NOT EXISTS `service_role_map` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `service_id` INT(11) UNSIGNED NOT NULL,
  `role_id` INT(11) UNSIGNED NOT NULL,
  `default_hours` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `service_id` (`service_id`),
  KEY `role_id` (`role_id`),
  UNIQUE KEY `service_role_unique` (`service_id`, `role_id`),
  FOREIGN KEY (`service_id`) REFERENCES `services`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`role_id`) REFERENCES `job_roles`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: overheads
-- ============================================================
CREATE TABLE IF NOT EXISTS `overheads` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `type` ENUM('percentage', 'fixed') NOT NULL DEFAULT 'percentage',
  `value` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `description` TEXT DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: quotes
-- ============================================================
CREATE TABLE IF NOT EXISTS `quotes` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `quote_number` VARCHAR(50) NOT NULL,
  `client_id` INT(11) UNSIGNED NOT NULL,
  `deal_id` INT(11) UNSIGNED DEFAULT NULL,
  `title` VARCHAR(255) NOT NULL,
  `subtotal` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `overhead_percentage` DECIMAL(5,2) NOT NULL DEFAULT 20.00,
  `overhead_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `margin_percentage` DECIMAL(5,2) NOT NULL DEFAULT 30.00,
  `margin_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `tax_percentage` DECIMAL(5,2) NOT NULL DEFAULT 18.00,
  `tax_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `total` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `offer_multiplier` DECIMAL(5,2) NOT NULL DEFAULT 2.00,
  `offer_value` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `currency` VARCHAR(3) NOT NULL DEFAULT 'INR',
  `valid_until` DATE DEFAULT NULL,
  `status` ENUM('draft', 'sent', 'accepted', 'rejected', 'expired') NOT NULL DEFAULT 'draft',
  `version` INT(11) NOT NULL DEFAULT 1,
  `notes` TEXT DEFAULT NULL,
  `terms` TEXT DEFAULT NULL,
  `created_by` INT(11) UNSIGNED DEFAULT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `quote_number` (`quote_number`),
  KEY `client_id` (`client_id`),
  KEY `deal_id` (`deal_id`),
  KEY `status` (`status`),
  KEY `created_by` (`created_by`),
  FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`deal_id`) REFERENCES `deals`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: quote_items
-- ============================================================
CREATE TABLE IF NOT EXISTS `quote_items` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `quote_id` INT(11) UNSIGNED NOT NULL,
  `service_id` INT(11) UNSIGNED DEFAULT NULL,
  `role_id` INT(11) UNSIGNED DEFAULT NULL,
  `description` VARCHAR(255) NOT NULL,
  `hours` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `hourly_rate` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `order_position` INT(11) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `quote_id` (`quote_id`),
  KEY `service_id` (`service_id`),
  KEY `role_id` (`role_id`),
  FOREIGN KEY (`quote_id`) REFERENCES `quotes`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`service_id`) REFERENCES `services`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`role_id`) REFERENCES `job_roles`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: projects
-- ============================================================
CREATE TABLE IF NOT EXISTS `projects` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `code` VARCHAR(50) NOT NULL,
  `client_id` INT(11) UNSIGNED NOT NULL,
  `deal_id` INT(11) UNSIGNED DEFAULT NULL,
  `quote_id` INT(11) UNSIGNED DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `start_date` DATE DEFAULT NULL,
  `end_date` DATE DEFAULT NULL,
  `budget` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `currency` VARCHAR(3) NOT NULL DEFAULT 'INR',
  `status` ENUM('planned', 'active', 'on_hold', 'completed', 'closed') NOT NULL DEFAULT 'planned',
  `progress` INT(11) NOT NULL DEFAULT 0,
  `project_manager_id` INT(11) UNSIGNED DEFAULT NULL,
  `priority` ENUM('low', 'medium', 'high', 'urgent') NOT NULL DEFAULT 'medium',
  `notes` TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `client_id` (`client_id`),
  KEY `deal_id` (`deal_id`),
  KEY `quote_id` (`quote_id`),
  KEY `project_manager_id` (`project_manager_id`),
  KEY `status` (`status`),
  FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`deal_id`) REFERENCES `deals`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`quote_id`) REFERENCES `quotes`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`project_manager_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: project_team
-- ============================================================
CREATE TABLE IF NOT EXISTS `project_team` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `project_id` INT(11) UNSIGNED NOT NULL,
  `user_id` INT(11) UNSIGNED NOT NULL,
  `role` VARCHAR(100) DEFAULT NULL,
  `allocation_percentage` INT(11) NOT NULL DEFAULT 100,
  `hourly_rate` DECIMAL(10,2) DEFAULT NULL,
  `joined_at` DATE NOT NULL,
  `left_at` DATE DEFAULT NULL,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`),
  KEY `user_id` (`user_id`),
  UNIQUE KEY `project_user_unique` (`project_id`, `user_id`),
  FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: tasks
-- ============================================================
CREATE TABLE IF NOT EXISTS `tasks` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `project_id` INT(11) UNSIGNED NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `status` ENUM('todo', 'in_progress', 'blocked', 'done') NOT NULL DEFAULT 'todo',
  `priority` ENUM('low', 'medium', 'high', 'urgent') NOT NULL DEFAULT 'medium',
  `assigned_to` INT(11) UNSIGNED DEFAULT NULL,
  `estimated_hours` DECIMAL(10,2) DEFAULT NULL,
  `actual_hours` DECIMAL(10,2) DEFAULT 0.00,
  `start_date` DATE DEFAULT NULL,
  `due_date` DATE DEFAULT NULL,
  `completed_at` DATETIME DEFAULT NULL,
  `parent_task_id` INT(11) UNSIGNED DEFAULT NULL,
  `order_position` INT(11) NOT NULL DEFAULT 0,
  `tags` VARCHAR(255) DEFAULT NULL,
  `created_by` INT(11) UNSIGNED DEFAULT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`),
  KEY `assigned_to` (`assigned_to`),
  KEY `status` (`status`),
  KEY `parent_task_id` (`parent_task_id`),
  KEY `created_by` (`created_by`),
  FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`assigned_to`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`parent_task_id`) REFERENCES `tasks`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: task_comments
-- ============================================================
CREATE TABLE IF NOT EXISTS `task_comments` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `task_id` INT(11) UNSIGNED NOT NULL,
  `user_id` INT(11) UNSIGNED NOT NULL,
  `comment` TEXT NOT NULL,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `task_id` (`task_id`),
  KEY `user_id` (`user_id`),
  FOREIGN KEY (`task_id`) REFERENCES `tasks`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: time_logs
-- ============================================================
CREATE TABLE IF NOT EXISTS `time_logs` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) UNSIGNED NOT NULL,
  `project_id` INT(11) UNSIGNED NOT NULL,
  `task_id` INT(11) UNSIGNED DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `start_time` DATETIME NOT NULL,
  `end_time` DATETIME DEFAULT NULL,
  `duration_minutes` INT(11) NOT NULL DEFAULT 0,
  `is_billable` TINYINT(1) NOT NULL DEFAULT 1,
  `hourly_rate` DECIMAL(10,2) DEFAULT NULL,
  `amount` DECIMAL(15,2) DEFAULT 0.00,
  `is_manual` TINYINT(1) NOT NULL DEFAULT 0,
  `manual_justification` TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `project_id` (`project_id`),
  KEY `task_id` (`task_id`),
  KEY `is_billable` (`is_billable`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`task_id`) REFERENCES `tasks`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: invoices
-- ============================================================
CREATE TABLE IF NOT EXISTS `invoices` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `invoice_number` VARCHAR(50) NOT NULL,
  `client_id` INT(11) UNSIGNED NOT NULL,
  `project_id` INT(11) UNSIGNED DEFAULT NULL,
  `quote_id` INT(11) UNSIGNED DEFAULT NULL,
  `issue_date` DATE NOT NULL,
  `due_date` DATE NOT NULL,
  `subtotal` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `tax_percentage` DECIMAL(5,2) NOT NULL DEFAULT 18.00,
  `tax_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `discount_percentage` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `discount_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `total` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `paid_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `balance` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `currency` VARCHAR(3) NOT NULL DEFAULT 'INR',
  `status` ENUM('draft', 'sent', 'partially_paid', 'paid', 'void', 'overdue') NOT NULL DEFAULT 'draft',
  `notes` TEXT DEFAULT NULL,
  `terms` TEXT DEFAULT NULL,
  `created_by` INT(11) UNSIGNED DEFAULT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoice_number` (`invoice_number`),
  KEY `client_id` (`client_id`),
  KEY `project_id` (`project_id`),
  KEY `quote_id` (`quote_id`),
  KEY `status` (`status`),
  KEY `created_by` (`created_by`),
  FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`quote_id`) REFERENCES `quotes`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: invoice_items
-- ============================================================
CREATE TABLE IF NOT EXISTS `invoice_items` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `invoice_id` INT(11) UNSIGNED NOT NULL,
  `description` VARCHAR(255) NOT NULL,
  `quantity` DECIMAL(10,2) NOT NULL DEFAULT 1.00,
  `unit_price` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `order_position` INT(11) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `invoice_id` (`invoice_id`),
  FOREIGN KEY (`invoice_id`) REFERENCES `invoices`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: payments
-- ============================================================
CREATE TABLE IF NOT EXISTS `payments` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `invoice_id` INT(11) UNSIGNED NOT NULL,
  `client_id` INT(11) UNSIGNED NOT NULL,
  `payment_method` ENUM('razorpay', 'stripe', 'bank_transfer', 'cash', 'cheque') NOT NULL,
  `transaction_id` VARCHAR(255) DEFAULT NULL,
  `amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `currency` VARCHAR(3) NOT NULL DEFAULT 'INR',
  `status` ENUM('pending', 'success', 'failed', 'refunded') NOT NULL DEFAULT 'pending',
  `payment_date` DATETIME DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,
  `gateway_response` TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `invoice_id` (`invoice_id`),
  KEY `client_id` (`client_id`),
  KEY `status` (`status`),
  KEY `transaction_id` (`transaction_id`),
  FOREIGN KEY (`invoice_id`) REFERENCES `invoices`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: notifications
-- ============================================================
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) UNSIGNED NOT NULL,
  `type` VARCHAR(100) NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `message` TEXT NOT NULL,
  `link` VARCHAR(255) DEFAULT NULL,
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `read_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `is_read` (`is_read`),
  KEY `type` (`type`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: files
-- ============================================================
CREATE TABLE IF NOT EXISTS `files` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `entity_type` VARCHAR(50) NOT NULL,
  `entity_id` INT(11) UNSIGNED NOT NULL,
  `filename` VARCHAR(255) NOT NULL,
  `original_filename` VARCHAR(255) NOT NULL,
  `file_path` VARCHAR(255) NOT NULL,
  `file_size` INT(11) UNSIGNED NOT NULL,
  `file_type` VARCHAR(100) DEFAULT NULL,
  `mime_type` VARCHAR(100) DEFAULT NULL,
  `uploaded_by` INT(11) UNSIGNED DEFAULT NULL,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `entity_type_id` (`entity_type`, `entity_id`),
  KEY `uploaded_by` (`uploaded_by`),
  FOREIGN KEY (`uploaded_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: ai_logs
-- ============================================================
CREATE TABLE IF NOT EXISTS `ai_logs` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) UNSIGNED DEFAULT NULL,
  `model_type` VARCHAR(100) NOT NULL,
  `input_data` TEXT DEFAULT NULL,
  `output_data` TEXT DEFAULT NULL,
  `confidence_score` DECIMAL(5,2) DEFAULT NULL,
  `execution_time_ms` INT(11) DEFAULT NULL,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `model_type` (`model_type`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: settings
-- ============================================================
CREATE TABLE IF NOT EXISTS `settings` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `key` VARCHAR(255) NOT NULL,
  `value` TEXT DEFAULT NULL,
  `type` VARCHAR(50) NOT NULL DEFAULT 'string',
  `group` VARCHAR(100) DEFAULT 'general',
  `description` TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`),
  KEY `group` (`group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default settings
INSERT INTO `settings` (`key`, `value`, `type`, `group`, `description`, `created_at`, `updated_at`) VALUES
('company_name', 'Outreach IT', 'string', 'company', 'Company Name', NOW(), NOW()),
('company_email', 'info@outreachit.com', 'string', 'company', 'Company Email', NOW(), NOW()),
('company_phone', '+91-XXXXXXXXXX', 'string', 'company', 'Company Phone', NOW(), NOW()),
('company_address', '', 'text', 'company', 'Company Address', NOW(), NOW()),
('company_logo', '', 'string', 'company', 'Company Logo Path', NOW(), NOW()),
('company_gstin', '', 'string', 'company', 'Company GSTIN', NOW(), NOW()),
('default_currency', 'INR', 'string', 'pricing', 'Default Currency', NOW(), NOW()),
('default_tax_percentage', '18', 'decimal', 'pricing', 'Default Tax Percentage', NOW(), NOW()),
('default_overhead_percentage', '20', 'decimal', 'pricing', 'Default Overhead Percentage', NOW(), NOW()),
('default_margin_percentage', '30', 'decimal', 'pricing', 'Default Margin Percentage', NOW(), NOW()),
('default_offer_multiplier', '2.0', 'decimal', 'pricing', 'Default Offer Multiplier', NOW(), NOW()),
('invoice_prefix', 'INV', 'string', 'invoicing', 'Invoice Number Prefix', NOW(), NOW()),
('quote_prefix', 'QOT', 'string', 'invoicing', 'Quote Number Prefix', NOW(), NOW()),
('project_prefix', 'PRJ', 'string', 'projects', 'Project Code Prefix', NOW(), NOW()),
('timezone', 'Asia/Kolkata', 'string', 'general', 'Application Timezone', NOW(), NOW()),
('date_format', 'Y-m-d', 'string', 'general', 'Date Format', NOW(), NOW()),
('items_per_page', '20', 'integer', 'general', 'Items Per Page', NOW(), NOW()),
('ai_enabled', '0', 'boolean', 'features', 'Enable AI Features', NOW(), NOW()),
('email_notifications', '1', 'boolean', 'features', 'Enable Email Notifications', NOW(), NOW());

-- ============================================================
-- Create default admin user
-- Password: admin123 (change after first login)
-- ============================================================
INSERT INTO `users` (`email`, `password`, `first_name`, `last_name`, `role`, `status`, `created_at`, `updated_at`) VALUES
('admin@onestopcrm.com', '$2y$10$DJ.IQx5EK1EUXaoncaxnguBIPnS2gssn1KTSNzOy0I3VHioLmGKjW', 'Admin', 'User', 'admin', 'active', NOW(), NOW());

-- ============================================================
-- END OF SCHEMA
-- ============================================================

