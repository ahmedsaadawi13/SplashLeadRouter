-- FILE: /database.sql
-- SplashLeadRouter Database Schema
-- Multi-tenant Real Estate Lead Routing SaaS Platform
-- Compatible with MySQL 5.7+ and MariaDB 10.2+

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- Drop existing tables
DROP TABLE IF EXISTS `routing_logs`;
DROP TABLE IF EXISTS `lead_notes`;
DROP TABLE IF EXISTS `leads`;
DROP TABLE IF EXISTS `routing_rules`;
DROP TABLE IF EXISTS `zone_agents`;
DROP TABLE IF EXISTS `zones`;
DROP TABLE IF EXISTS `lead_sources`;
DROP TABLE IF EXISTS `payments`;
DROP TABLE IF EXISTS `invoices`;
DROP TABLE IF EXISTS `tenant_subscriptions`;
DROP TABLE IF EXISTS `subscription_plans`;
DROP TABLE IF EXISTS `usage_tracking`;
DROP TABLE IF EXISTS `api_keys`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `tenants`;

-- =====================================================
-- TENANTS TABLE
-- =====================================================
CREATE TABLE `tenants` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL COMMENT 'Agency name',
  `slug` VARCHAR(255) NOT NULL UNIQUE COMMENT 'URL-friendly identifier',
  `logo` VARCHAR(255) DEFAULT NULL,
  `email` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(50) DEFAULT NULL,
  `country` VARCHAR(100) DEFAULT NULL,
  `city` VARCHAR(100) DEFAULT NULL,
  `timezone` VARCHAR(50) DEFAULT 'UTC',
  `status` ENUM('active', 'suspended', 'canceled') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_slug` (`slug`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- USERS TABLE
-- =====================================================
CREATE TABLE `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED DEFAULT NULL COMMENT 'NULL for platform_admin',
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(50) DEFAULT NULL,
  `role` ENUM('platform_admin', 'tenant_admin', 'agent', 'viewer') NOT NULL,
  `agent_level` ENUM('junior', 'senior') DEFAULT NULL COMMENT 'For agents only',
  `max_active_leads` INT DEFAULT 10 COMMENT 'Max open leads for agents',
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_email` (`email`),
  INDEX `idx_tenant_id` (`tenant_id`),
  INDEX `idx_role` (`role`),
  INDEX `idx_is_active` (`is_active`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- API KEYS TABLE
-- =====================================================
CREATE TABLE `api_keys` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED NOT NULL,
  `api_key` VARCHAR(64) NOT NULL UNIQUE,
  `name` VARCHAR(255) DEFAULT NULL COMMENT 'Key description',
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `last_used_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_api_key` (`api_key`),
  INDEX `idx_tenant_id` (`tenant_id`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- SUBSCRIPTION PLANS TABLE
-- =====================================================
CREATE TABLE `subscription_plans` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `price_monthly` DECIMAL(10,2) NOT NULL,
  `price_yearly` DECIMAL(10,2) NOT NULL,
  `max_agents` INT NOT NULL,
  `max_leads_per_month` INT NOT NULL,
  `max_zones` INT NOT NULL,
  `max_routing_rules` INT NOT NULL,
  `features` TEXT DEFAULT NULL COMMENT 'JSON array of features',
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TENANT SUBSCRIPTIONS TABLE
-- =====================================================
CREATE TABLE `tenant_subscriptions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED NOT NULL,
  `plan_id` INT UNSIGNED NOT NULL,
  `billing_cycle` ENUM('monthly', 'yearly') DEFAULT 'monthly',
  `status` ENUM('trialing', 'active', 'past_due', 'canceled', 'suspended') DEFAULT 'trialing',
  `trial_ends_at` TIMESTAMP NULL DEFAULT NULL,
  `current_period_start` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `current_period_end` TIMESTAMP NULL DEFAULT NULL,
  `canceled_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_tenant_id` (`tenant_id`),
  INDEX `idx_plan_id` (`plan_id`),
  INDEX `idx_status` (`status`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`plan_id`) REFERENCES `subscription_plans`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- USAGE TRACKING TABLE
-- =====================================================
CREATE TABLE `usage_tracking` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED NOT NULL,
  `period_month` VARCHAR(7) NOT NULL COMMENT 'YYYY-MM format',
  `total_leads` INT DEFAULT 0,
  `total_agents` INT DEFAULT 0,
  `total_zones` INT DEFAULT 0,
  `total_routing_rules` INT DEFAULT 0,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_tenant_month` (`tenant_id`, `period_month`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- INVOICES TABLE
-- =====================================================
CREATE TABLE `invoices` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED NOT NULL,
  `subscription_id` INT UNSIGNED NOT NULL,
  `invoice_number` VARCHAR(50) NOT NULL UNIQUE,
  `amount` DECIMAL(10,2) NOT NULL,
  `tax` DECIMAL(10,2) DEFAULT 0.00,
  `total` DECIMAL(10,2) NOT NULL,
  `currency` VARCHAR(3) DEFAULT 'USD',
  `status` ENUM('draft', 'pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
  `due_date` DATE NOT NULL,
  `paid_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_tenant_id` (`tenant_id`),
  INDEX `idx_subscription_id` (`subscription_id`),
  INDEX `idx_status` (`status`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`subscription_id`) REFERENCES `tenant_subscriptions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- PAYMENTS TABLE
-- =====================================================
CREATE TABLE `payments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED NOT NULL,
  `invoice_id` INT UNSIGNED NOT NULL,
  `payment_method` VARCHAR(50) DEFAULT 'credit_card',
  `transaction_id` VARCHAR(255) DEFAULT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `currency` VARCHAR(3) DEFAULT 'USD',
  `status` ENUM('pending', 'success', 'failed', 'refunded') DEFAULT 'pending',
  `payment_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `notes` TEXT DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_tenant_id` (`tenant_id`),
  INDEX `idx_invoice_id` (`invoice_id`),
  INDEX `idx_status` (`status`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`invoice_id`) REFERENCES `invoices`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- LEAD SOURCES TABLE
-- =====================================================
CREATE TABLE `lead_sources` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `type` VARCHAR(50) DEFAULT NULL COMMENT 'portal, website, manual, api, etc',
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_tenant_id` (`tenant_id`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- ZONES TABLE
-- =====================================================
CREATE TABLE `zones` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `country` VARCHAR(100) DEFAULT NULL,
  `region` VARCHAR(100) DEFAULT NULL,
  `city` VARCHAR(100) DEFAULT NULL,
  `subarea` VARCHAR(100) DEFAULT NULL,
  `custom_label` VARCHAR(255) DEFAULT NULL,
  `polygon_data` TEXT DEFAULT NULL COMMENT 'JSON for map coordinates',
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_tenant_id` (`tenant_id`),
  INDEX `idx_city` (`city`),
  INDEX `idx_is_active` (`is_active`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- ZONE AGENTS TABLE (Many-to-Many)
-- =====================================================
CREATE TABLE `zone_agents` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `zone_id` INT UNSIGNED NOT NULL,
  `agent_id` INT UNSIGNED NOT NULL,
  `priority` INT DEFAULT 1 COMMENT 'Lower number = higher priority',
  `weight` INT DEFAULT 1 COMMENT 'For weighted distribution',
  `is_primary` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_zone_agent` (`zone_id`, `agent_id`),
  INDEX `idx_zone_id` (`zone_id`),
  INDEX `idx_agent_id` (`agent_id`),
  FOREIGN KEY (`zone_id`) REFERENCES `zones`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`agent_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- ROUTING RULES TABLE
-- =====================================================
CREATE TABLE `routing_rules` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `rule_type` ENUM('zone_based', 'budget_based', 'property_type_based', 'source_based', 'custom') NOT NULL,
  `priority` INT DEFAULT 1 COMMENT 'Lower number = higher priority',
  `conditions` TEXT DEFAULT NULL COMMENT 'JSON conditions',
  `assignment_strategy` ENUM('round_robin', 'least_active', 'priority_based', 'weighted') DEFAULT 'round_robin',
  `target_agent_ids` TEXT DEFAULT NULL COMMENT 'JSON array of agent IDs',
  `target_zone_ids` TEXT DEFAULT NULL COMMENT 'JSON array of zone IDs',
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_tenant_id` (`tenant_id`),
  INDEX `idx_rule_type` (`rule_type`),
  INDEX `idx_priority` (`priority`),
  INDEX `idx_is_active` (`is_active`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- LEADS TABLE
-- =====================================================
CREATE TABLE `leads` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(50) NOT NULL,
  `email` VARCHAR(255) DEFAULT NULL,
  `source` VARCHAR(100) DEFAULT NULL,
  `source_id` INT UNSIGNED DEFAULT NULL COMMENT 'FK to lead_sources',
  `campaign` VARCHAR(255) DEFAULT NULL,
  `property_type` VARCHAR(100) DEFAULT NULL,
  `budget_min` DECIMAL(12,2) DEFAULT NULL,
  `budget_max` DECIMAL(12,2) DEFAULT NULL,
  `preferred_city` VARCHAR(100) DEFAULT NULL,
  `preferred_area` VARCHAR(100) DEFAULT NULL,
  `preferred_location` TEXT DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,
  `status` ENUM('new', 'assigned', 'contacted', 'qualified', 'unqualified', 'closed_won', 'closed_lost') DEFAULT 'new',
  `assigned_agent_id` INT UNSIGNED DEFAULT NULL,
  `assigned_at` TIMESTAMP NULL DEFAULT NULL,
  `assignment_rule_id` INT UNSIGNED DEFAULT NULL,
  `first_contacted_at` TIMESTAMP NULL DEFAULT NULL,
  `qualified_at` TIMESTAMP NULL DEFAULT NULL,
  `closed_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_tenant_id` (`tenant_id`),
  INDEX `idx_status` (`status`),
  INDEX `idx_assigned_agent_id` (`assigned_agent_id`),
  INDEX `idx_source` (`source`),
  INDEX `idx_created_at` (`created_at`),
  INDEX `idx_preferred_city` (`preferred_city`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`assigned_agent_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`source_id`) REFERENCES `lead_sources`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`assignment_rule_id`) REFERENCES `routing_rules`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- LEAD NOTES TABLE
-- =====================================================
CREATE TABLE `lead_notes` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `lead_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `note` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_lead_id` (`lead_id`),
  INDEX `idx_user_id` (`user_id`),
  FOREIGN KEY (`lead_id`) REFERENCES `leads`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- ROUTING LOGS TABLE
-- =====================================================
CREATE TABLE `routing_logs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED NOT NULL,
  `lead_id` INT UNSIGNED NOT NULL,
  `selected_agent_id` INT UNSIGNED DEFAULT NULL,
  `rule_id` INT UNSIGNED DEFAULT NULL,
  `zone_id` INT UNSIGNED DEFAULT NULL,
  `reason` VARCHAR(255) DEFAULT NULL COMMENT 'Why this agent was selected',
  `strategy_used` VARCHAR(50) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_tenant_id` (`tenant_id`),
  INDEX `idx_lead_id` (`lead_id`),
  INDEX `idx_selected_agent_id` (`selected_agent_id`),
  INDEX `idx_created_at` (`created_at`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`lead_id`) REFERENCES `leads`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`selected_agent_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`rule_id`) REFERENCES `routing_rules`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`zone_id`) REFERENCES `zones`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================
-- DEMO DATA - Subscription Plans
-- =====================================================
INSERT INTO `subscription_plans` (`name`, `slug`, `price_monthly`, `price_yearly`, `max_agents`, `max_leads_per_month`, `max_zones`, `max_routing_rules`, `features`, `is_active`) VALUES
('Starter', 'starter', 49.00, 490.00, 5, 100, 5, 5, '["Basic lead routing","Email notifications","CSV import","API access"]', 1),
('Professional', 'professional', 149.00, 1490.00, 20, 500, 20, 20, '["Advanced routing rules","Priority support","Analytics dashboard","Unlimited API calls","Custom integrations"]', 1),
('Enterprise', 'enterprise', 399.00, 3990.00, 100, 5000, 100, 100, '["Everything in Professional","Dedicated account manager","Custom features","SLA guarantee","White-label option"]', 1);

-- =====================================================
-- DEMO DATA - Platform Admin
-- =====================================================
INSERT INTO `users` (`tenant_id`, `name`, `email`, `password`, `role`, `is_active`) VALUES
(NULL, 'Platform Admin', 'admin@splashleadrouter.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'platform_admin', 1);
-- Password: password

-- =====================================================
-- DEMO DATA - Tenant 1: Dubai Real Estate Agency
-- =====================================================
INSERT INTO `tenants` (`name`, `slug`, `email`, `phone`, `country`, `city`, `timezone`, `status`) VALUES
('Dubai Premium Properties', 'dubai-premium-properties', 'info@dubaiproperties.com', '+971-4-123-4567', 'United Arab Emirates', 'Dubai', 'Asia/Dubai', 'active');

SET @tenant1_id = LAST_INSERT_ID();

-- API Key for Tenant 1
INSERT INTO `api_keys` (`tenant_id`, `api_key`, `name`, `is_active`) VALUES
(@tenant1_id, 'dprop_live_4f8a9b2c1d3e5f6g7h8i9j0k1l2m3n4o5p6q7r8s9t0u1v2w3x4y5z6', 'Website API Key', 1);

-- Subscription for Tenant 1
INSERT INTO `tenant_subscriptions` (`tenant_id`, `plan_id`, `billing_cycle`, `status`, `trial_ends_at`, `current_period_start`, `current_period_end`) VALUES
(@tenant1_id, 2, 'monthly', 'active', NULL, '2025-01-01 00:00:00', '2025-02-01 00:00:00');

SET @tenant1_sub_id = LAST_INSERT_ID();

-- Invoice for Tenant 1
INSERT INTO `invoices` (`tenant_id`, `subscription_id`, `invoice_number`, `amount`, `tax`, `total`, `status`, `due_date`, `paid_at`) VALUES
(@tenant1_id, @tenant1_sub_id, 'INV-2025-0001', 149.00, 14.90, 163.90, 'paid', '2025-01-01', '2025-01-01 10:30:00');

SET @tenant1_inv_id = LAST_INSERT_ID();

-- Payment for Tenant 1
INSERT INTO `payments` (`tenant_id`, `invoice_id`, `payment_method`, `transaction_id`, `amount`, `status`, `payment_date`) VALUES
(@tenant1_id, @tenant1_inv_id, 'credit_card', 'txn_1234567890abcdef', 163.90, 'success', '2025-01-01 10:30:00');

-- Usage tracking for Tenant 1
INSERT INTO `usage_tracking` (`tenant_id`, `period_month`, `total_leads`, `total_agents`, `total_zones`, `total_routing_rules`) VALUES
(@tenant1_id, '2025-01', 45, 6, 8, 4);

-- Users for Tenant 1
INSERT INTO `users` (`tenant_id`, `name`, `email`, `password`, `phone`, `role`, `agent_level`, `max_active_leads`, `is_active`) VALUES
(@tenant1_id, 'Ahmed Al Mansouri', 'ahmed@dubaiproperties.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+971-50-123-4567', 'tenant_admin', NULL, NULL, 1),
(@tenant1_id, 'Sarah Johnson', 'sarah@dubaiproperties.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+971-50-234-5678', 'agent', 'senior', 15, 1),
(@tenant1_id, 'Mohammed Hassan', 'mohammed@dubaiproperties.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+971-50-345-6789', 'agent', 'senior', 15, 1),
(@tenant1_id, 'Lisa Chen', 'lisa@dubaiproperties.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+971-50-456-7890', 'agent', 'junior', 10, 1),
(@tenant1_id, 'Omar Abdullah', 'omar@dubaiproperties.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+971-50-567-8901', 'agent', 'junior', 10, 1),
(@tenant1_id, 'Emily Rodriguez', 'emily@dubaiproperties.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+971-50-678-9012', 'agent', 'senior', 15, 1),
(@tenant1_id, 'Khalid Rashid', 'khalid@dubaiproperties.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+971-50-789-0123', 'agent', 'junior', 10, 1);

-- Lead Sources for Tenant 1
INSERT INTO `lead_sources` (`tenant_id`, `name`, `type`, `is_active`) VALUES
(@tenant1_id, 'Property Finder', 'portal', 1),
(@tenant1_id, 'Bayut', 'portal', 1),
(@tenant1_id, 'Company Website', 'website', 1),
(@tenant1_id, 'Facebook Ads', 'social_media', 1),
(@tenant1_id, 'Walk-in', 'manual', 1),
(@tenant1_id, 'Referral', 'manual', 1);

-- Zones for Tenant 1
INSERT INTO `zones` (`tenant_id`, `name`, `country`, `region`, `city`, `subarea`, `is_active`) VALUES
(@tenant1_id, 'Dubai Marina', 'UAE', 'Dubai', 'Dubai', 'Dubai Marina', 1),
(@tenant1_id, 'Downtown Dubai', 'UAE', 'Dubai', 'Dubai', 'Downtown', 1),
(@tenant1_id, 'Palm Jumeirah', 'UAE', 'Dubai', 'Dubai', 'Palm Jumeirah', 1),
(@tenant1_id, 'Business Bay', 'UAE', 'Dubai', 'Dubai', 'Business Bay', 1),
(@tenant1_id, 'JBR', 'UAE', 'Dubai', 'Dubai', 'Jumeirah Beach Residence', 1),
(@tenant1_id, 'Arabian Ranches', 'UAE', 'Dubai', 'Dubai', 'Arabian Ranches', 1),
(@tenant1_id, 'Emirates Hills', 'UAE', 'Dubai', 'Dubai', 'Emirates Hills', 1),
(@tenant1_id, 'Dubai Creek Harbour', 'UAE', 'Dubai', 'Dubai', 'Creek Harbour', 1);

-- Zone-Agent assignments for Tenant 1
-- Sarah handles Dubai Marina and JBR
INSERT INTO `zone_agents` (`zone_id`, `agent_id`, `priority`, `weight`, `is_primary`) VALUES
((SELECT id FROM zones WHERE name='Dubai Marina' AND tenant_id=@tenant1_id), (SELECT id FROM users WHERE email='sarah@dubaiproperties.com'), 1, 2, 1),
((SELECT id FROM zones WHERE name='JBR' AND tenant_id=@tenant1_id), (SELECT id FROM users WHERE email='sarah@dubaiproperties.com'), 1, 2, 1);

-- Mohammed handles Downtown and Business Bay
INSERT INTO `zone_agents` (`zone_id`, `agent_id`, `priority`, `weight`, `is_primary`) VALUES
((SELECT id FROM zones WHERE name='Downtown Dubai' AND tenant_id=@tenant1_id), (SELECT id FROM users WHERE email='mohammed@dubaiproperties.com'), 1, 2, 1),
((SELECT id FROM zones WHERE name='Business Bay' AND tenant_id=@tenant1_id), (SELECT id FROM users WHERE email='mohammed@dubaiproperties.com'), 1, 2, 1);

-- Emily handles luxury areas (Palm, Emirates Hills)
INSERT INTO `zone_agents` (`zone_id`, `agent_id`, `priority`, `weight`, `is_primary`) VALUES
((SELECT id FROM zones WHERE name='Palm Jumeirah' AND tenant_id=@tenant1_id), (SELECT id FROM users WHERE email='emily@dubaiproperties.com'), 1, 3, 1),
((SELECT id FROM zones WHERE name='Emirates Hills' AND tenant_id=@tenant1_id), (SELECT id FROM users WHERE email='emily@dubaiproperties.com'), 1, 3, 1);

-- Lisa and Omar handle Arabian Ranches
INSERT INTO `zone_agents` (`zone_id`, `agent_id`, `priority`, `weight`, `is_primary`) VALUES
((SELECT id FROM zones WHERE name='Arabian Ranches' AND tenant_id=@tenant1_id), (SELECT id FROM users WHERE email='lisa@dubaiproperties.com'), 1, 1, 1),
((SELECT id FROM zones WHERE name='Arabian Ranches' AND tenant_id=@tenant1_id), (SELECT id FROM users WHERE email='omar@dubaiproperties.com'), 2, 1, 0);

-- Khalid handles Dubai Creek Harbour
INSERT INTO `zone_agents` (`zone_id`, `agent_id`, `priority`, `weight`, `is_primary`) VALUES
((SELECT id FROM zones WHERE name='Dubai Creek Harbour' AND tenant_id=@tenant1_id), (SELECT id FROM users WHERE email='khalid@dubaiproperties.com'), 1, 1, 1);

-- Routing Rules for Tenant 1
INSERT INTO `routing_rules` (`tenant_id`, `name`, `rule_type`, `priority`, `conditions`, `assignment_strategy`, `target_agent_ids`, `is_active`) VALUES
(@tenant1_id, 'Luxury Properties to Emily', 'budget_based', 1, '{"budget_min": 2000000}', 'priority_based', '["' + CAST((SELECT id FROM users WHERE email='emily@dubaiproperties.com') AS CHAR) + '"]', 1),
(@tenant1_id, 'Dubai Marina Zone Rule', 'zone_based', 2, '{"zones": ["Dubai Marina"]}', 'round_robin', NULL, 1),
(@tenant1_id, 'Property Finder Leads to Senior Agents', 'source_based', 3, '{"source": "Property Finder"}', 'least_active', '["' + CAST((SELECT id FROM users WHERE email='sarah@dubaiproperties.com') AS CHAR) + '","' + CAST((SELECT id FROM users WHERE email='mohammed@dubaiproperties.com') AS CHAR) + '","' + CAST((SELECT id FROM users WHERE email='emily@dubaiproperties.com') AS CHAR) + '"]', 1),
(@tenant1_id, 'Villa Properties', 'property_type_based', 4, '{"property_type": "Villa"}', 'weighted', NULL, 1);

-- Leads for Tenant 1
INSERT INTO `leads` (`tenant_id`, `name`, `phone`, `email`, `source`, `campaign`, `property_type`, `budget_min`, `budget_max`, `preferred_city`, `preferred_area`, `notes`, `status`, `assigned_agent_id`, `assigned_at`, `first_contacted_at`) VALUES
(@tenant1_id, 'John Smith', '+971-55-111-2222', 'john.smith@email.com', 'Property Finder', 'Summer Sale 2025', 'Apartment', 800000, 1200000, 'Dubai', 'Dubai Marina', 'Looking for 2BR with sea view', 'contacted', (SELECT id FROM users WHERE email='sarah@dubaiproperties.com'), '2025-01-15 09:30:00', '2025-01-15 11:20:00'),
(@tenant1_id, 'Fatima Al Zaabi', '+971-55-222-3333', 'fatima.alzaabi@email.com', 'Bayut', NULL, 'Villa', 3500000, 5000000, 'Dubai', 'Emirates Hills', 'Family villa with pool', 'qualified', (SELECT id FROM users WHERE email='emily@dubaiproperties.com'), '2025-01-16 10:15:00', '2025-01-16 14:30:00'),
(@tenant1_id, 'David Lee', '+971-55-333-4444', 'david.lee@email.com', 'Company Website', 'Google Ads', 'Apartment', 1500000, 2000000, 'Dubai', 'Downtown Dubai', 'Investment property', 'contacted', (SELECT id FROM users WHERE email='mohammed@dubaiproperties.com'), '2025-01-17 08:45:00', '2025-01-17 09:15:00'),
(@tenant1_id, 'Maria Garcia', '+971-55-444-5555', 'maria.garcia@email.com', 'Facebook Ads', 'FB Campaign Jan', 'Apartment', 600000, 900000, 'Dubai', 'JBR', 'Studio or 1BR near beach', 'assigned', (SELECT id FROM users WHERE email='sarah@dubaiproperties.com'), '2025-01-18 13:20:00', NULL),
(@tenant1_id, 'Ahmed Ibrahim', '+971-55-555-6666', 'ahmed.ibrahim@email.com', 'Walk-in', NULL, 'Townhouse', 2000000, 2800000, 'Dubai', 'Arabian Ranches', 'Family home with garden', 'contacted', (SELECT id FROM users WHERE email='lisa@dubaiproperties.com'), '2025-01-19 11:00:00', '2025-01-19 15:45:00'),
(@tenant1_id, 'Sophie Martin', '+971-55-666-7777', 'sophie.martin@email.com', 'Property Finder', 'Winter Promo', 'Penthouse', 5000000, 8000000, 'Dubai', 'Palm Jumeirah', 'Luxury penthouse with private pool', 'qualified', (SELECT id FROM users WHERE email='emily@dubaiproperties.com'), '2025-01-20 09:00:00', '2025-01-20 10:30:00'),
(@tenant1_id, 'Omar Khalifa', '+971-55-777-8888', 'omar.khalifa@email.com', 'Referral', NULL, 'Apartment', 1200000, 1800000, 'Dubai', 'Business Bay', 'Modern 2BR for rental', 'new', NULL, NULL, NULL),
(@tenant1_id, 'Lisa Wang', '+971-55-888-9999', 'lisa.wang@email.com', 'Bayut', NULL, 'Apartment', 700000, 1000000, 'Dubai', 'Dubai Creek Harbour', 'First-time buyer', 'assigned', (SELECT id FROM users WHERE email='khalid@dubaiproperties.com'), '2025-01-21 14:30:00', NULL);

-- Lead Notes
INSERT INTO `lead_notes` (`lead_id`, `user_id`, `note`) VALUES
((SELECT id FROM leads WHERE email='john.smith@email.com'), (SELECT id FROM users WHERE email='sarah@dubaiproperties.com'), 'First call completed. Client wants to view properties this weekend.'),
((SELECT id FROM leads WHERE email='fatima.alzaabi@email.com'), (SELECT id FROM users WHERE email='emily@dubaiproperties.com'), 'Very interested. Scheduled villa viewing for next Tuesday.'),
((SELECT id FROM leads WHERE email='david.lee@email.com'), (SELECT id FROM users WHERE email='mohammed@dubaiproperties.com'), 'Investment buyer. Looking for high ROI properties.'),
((SELECT id FROM leads WHERE email='ahmed.ibrahim@email.com'), (SELECT id FROM users WHERE email='lisa@dubaiproperties.com'), 'Family relocating from Abu Dhabi. Need to move by March.');

-- Routing Logs for Tenant 1
INSERT INTO `routing_logs` (`tenant_id`, `lead_id`, `selected_agent_id`, `rule_id`, `zone_id`, `reason`, `strategy_used`) VALUES
(@tenant1_id, (SELECT id FROM leads WHERE email='john.smith@email.com'), (SELECT id FROM users WHERE email='sarah@dubaiproperties.com'), NULL, (SELECT id FROM zones WHERE name='Dubai Marina' AND tenant_id=@tenant1_id), 'Zone-based assignment: Dubai Marina', 'round_robin'),
(@tenant1_id, (SELECT id FROM leads WHERE email='fatima.alzaabi@email.com'), (SELECT id FROM users WHERE email='emily@dubaiproperties.com'), (SELECT id FROM routing_rules WHERE name='Luxury Properties to Emily' AND tenant_id=@tenant1_id), (SELECT id FROM zones WHERE name='Emirates Hills' AND tenant_id=@tenant1_id), 'Budget exceeds 2M - Luxury property rule', 'priority_based'),
(@tenant1_id, (SELECT id FROM leads WHERE email='david.lee@email.com'), (SELECT id FROM users WHERE email='mohammed@dubaiproperties.com'), NULL, (SELECT id FROM zones WHERE name='Downtown Dubai' AND tenant_id=@tenant1_id), 'Zone-based assignment: Downtown Dubai', 'priority_based');

-- =====================================================
-- DEMO DATA - Tenant 2: Cairo Real Estate Agency
-- =====================================================
INSERT INTO `tenants` (`name`, `slug`, `email`, `phone`, `country`, `city`, `timezone`, `status`) VALUES
('Cairo Elite Estates', 'cairo-elite-estates', 'info@cairoelite.com', '+20-2-123-4567', 'Egypt', 'Cairo', 'Africa/Cairo', 'active');

SET @tenant2_id = LAST_INSERT_ID();

-- API Key for Tenant 2
INSERT INTO `api_keys` (`tenant_id`, `api_key`, `name`, `is_active`) VALUES
(@tenant2_id, 'celite_live_7a8b9c0d1e2f3g4h5i6j7k8l9m0n1o2p3q4r5s6t7u8v9w0x1y2z3a4', 'Website Integration', 1);

-- Subscription for Tenant 2
INSERT INTO `tenant_subscriptions` (`tenant_id`, `plan_id`, `billing_cycle`, `status`, `trial_ends_at`, `current_period_start`, `current_period_end`) VALUES
(@tenant2_id, 1, 'monthly', 'active', NULL, '2025-01-01 00:00:00', '2025-02-01 00:00:00');

SET @tenant2_sub_id = LAST_INSERT_ID();

-- Invoice for Tenant 2
INSERT INTO `invoices` (`tenant_id`, `subscription_id`, `invoice_number`, `amount`, `tax`, `total`, `status`, `due_date`, `paid_at`) VALUES
(@tenant2_id, @tenant2_sub_id, 'INV-2025-0002', 49.00, 4.90, 53.90, 'paid', '2025-01-01', '2025-01-01 08:15:00');

SET @tenant2_inv_id = LAST_INSERT_ID();

-- Payment for Tenant 2
INSERT INTO `payments` (`tenant_id`, `invoice_id`, `payment_method`, `transaction_id`, `amount`, `status`, `payment_date`) VALUES
(@tenant2_id, @tenant2_inv_id, 'credit_card', 'txn_abcdef1234567890', 53.90, 'success', '2025-01-01 08:15:00');

-- Usage tracking for Tenant 2
INSERT INTO `usage_tracking` (`tenant_id`, `period_month`, `total_leads`, `total_agents`, `total_zones`, `total_routing_rules`) VALUES
(@tenant2_id, '2025-01', 23, 4, 5, 2);

-- Users for Tenant 2
INSERT INTO `users` (`tenant_id`, `name`, `email`, `password`, `phone`, `role`, `agent_level`, `max_active_leads`, `is_active`) VALUES
(@tenant2_id, 'Hassan Mahmoud', 'hassan@cairoelite.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+20-10-123-4567', 'tenant_admin', NULL, NULL, 1),
(@tenant2_id, 'Nour El-Din', 'nour@cairoelite.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+20-10-234-5678', 'agent', 'senior', 12, 1),
(@tenant2_id, 'Yasmine Ahmed', 'yasmine@cairoelite.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+20-10-345-6789', 'agent', 'junior', 8, 1),
(@tenant2_id, 'Karim Fathi', 'karim@cairoelite.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+20-10-456-7890', 'agent', 'junior', 8, 1),
(@tenant2_id, 'Mona Salah', 'mona@cairoelite.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+20-10-567-8901', 'agent', 'senior', 12, 1);

-- Lead Sources for Tenant 2
INSERT INTO `lead_sources` (`tenant_id`, `name`, `type`, `is_active`) VALUES
(@tenant2_id, 'OLX Egypt', 'portal', 1),
(@tenant2_id, 'Aqarmap', 'portal', 1),
(@tenant2_id, 'Company Website', 'website', 1),
(@tenant2_id, 'Instagram Ads', 'social_media', 1);

-- Zones for Tenant 2
INSERT INTO `zones` (`tenant_id`, `name`, `country`, `region`, `city`, `subarea`, `is_active`) VALUES
(@tenant2_id, 'New Cairo - Fifth Settlement', 'Egypt', 'Cairo', 'New Cairo', 'Fifth Settlement', 1),
(@tenant2_id, 'New Cairo - Tagamoa', 'Egypt', 'Cairo', 'New Cairo', 'Tagamoa', 1),
(@tenant2_id, 'Zamalek', 'Egypt', 'Cairo', 'Cairo', 'Zamalek', 1),
(@tenant2_id, 'Heliopolis', 'Egypt', 'Cairo', 'Cairo', 'Heliopolis', 1),
(@tenant2_id, '6th October City', 'Egypt', 'Giza', '6th October', 'Central', 1);

-- Zone-Agent assignments for Tenant 2
INSERT INTO `zone_agents` (`zone_id`, `agent_id`, `priority`, `weight`, `is_primary`) VALUES
((SELECT id FROM zones WHERE name='New Cairo - Fifth Settlement' AND tenant_id=@tenant2_id), (SELECT id FROM users WHERE email='nour@cairoelite.com'), 1, 2, 1),
((SELECT id FROM zones WHERE name='Zamalek' AND tenant_id=@tenant2_id), (SELECT id FROM users WHERE email='mona@cairoelite.com'), 1, 2, 1),
((SELECT id FROM zones WHERE name='Heliopolis' AND tenant_id=@tenant2_id), (SELECT id FROM users WHERE email='yasmine@cairoelite.com'), 1, 1, 1),
((SELECT id FROM zones WHERE name='6th October City' AND tenant_id=@tenant2_id), (SELECT id FROM users WHERE email='karim@cairoelite.com'), 1, 1, 1),
((SELECT id FROM zones WHERE name='New Cairo - Tagamoa' AND tenant_id=@tenant2_id), (SELECT id FROM users WHERE email='nour@cairoelite.com'), 2, 1, 0);

-- Routing Rules for Tenant 2
INSERT INTO `routing_rules` (`tenant_id`, `name`, `rule_type`, `priority`, `conditions`, `assignment_strategy`, `is_active`) VALUES
(@tenant2_id, 'New Cairo Zone Distribution', 'zone_based', 1, '{"zones": ["New Cairo - Fifth Settlement", "New Cairo - Tagamoa"]}', 'round_robin', 1),
(@tenant2_id, 'Premium Listings to Senior Agents', 'budget_based', 2, '{"budget_min": 5000000}', 'priority_based', 1);

-- Leads for Tenant 2
INSERT INTO `leads` (`tenant_id`, `name`, `phone`, `email`, `source`, `property_type`, `budget_min`, `budget_max`, `preferred_city`, `preferred_area`, `notes`, `status`, `assigned_agent_id`, `assigned_at`) VALUES
(@tenant2_id, 'Mohamed Essam', '+20-11-111-2222', 'mohamed.essam@email.com', 'Aqarmap', 'Apartment', 1500000, 2500000, 'New Cairo', 'Fifth Settlement', 'Looking for 3BR', 'assigned', (SELECT id FROM users WHERE email='nour@cairoelite.com'), '2025-01-18 10:00:00'),
(@tenant2_id, 'Sara Ibrahim', '+20-11-222-3333', 'sara.ibrahim@email.com', 'OLX Egypt', 'Villa', 8000000, 12000000, 'New Cairo', 'Fifth Settlement', 'Luxury villa required', 'contacted', (SELECT id FROM users WHERE email='nour@cairoelite.com'), '2025-01-19 09:30:00'),
(@tenant2_id, 'Amr Khaled', '+20-11-333-4444', 'amr.khaled@email.com', 'Company Website', 'Apartment', 3000000, 4000000, 'Cairo', 'Zamalek', 'Nile view preferred', 'new', NULL, NULL),
(@tenant2_id, 'Heba Mostafa', '+20-11-444-5555', 'heba.mostafa@email.com', 'Instagram Ads', 'Apartment', 2000000, 3000000, 'Cairo', 'Heliopolis', 'First home buyer', 'assigned', (SELECT id FROM users WHERE email='yasmine@cairoelite.com'), '2025-01-20 11:15:00');

-- Routing Logs for Tenant 2
INSERT INTO `routing_logs` (`tenant_id`, `lead_id`, `selected_agent_id`, `zone_id`, `reason`, `strategy_used`) VALUES
(@tenant2_id, (SELECT id FROM leads WHERE email='mohamed.essam@email.com'), (SELECT id FROM users WHERE email='nour@cairoelite.com'), (SELECT id FROM zones WHERE name='New Cairo - Fifth Settlement' AND tenant_id=@tenant2_id), 'Zone assignment: New Cairo Fifth Settlement', 'round_robin'),
(@tenant2_id, (SELECT id FROM leads WHERE email='sara.ibrahim@email.com'), (SELECT id FROM users WHERE email='nour@cairoelite.com'), (SELECT id FROM zones WHERE name='New Cairo - Fifth Settlement' AND tenant_id=@tenant2_id), 'High budget property - Senior agent', 'priority_based');

-- =====================================================
-- END OF DATABASE SCHEMA
-- =====================================================
