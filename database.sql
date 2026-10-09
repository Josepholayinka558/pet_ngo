-- PawBridge Rescue Database Schema
-- Version 1.0
-- Animal Rescue NGO Platform

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS pawbridge_rescue DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE pawbridge_rescue;

-- 1. Admins Table
CREATE TABLE IF NOT EXISTS `admins` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(100) NOT NULL,
  `role` ENUM('super_admin', 'admin', 'editor', 'viewer') NOT NULL DEFAULT 'admin',
  `profile_image` VARCHAR(255) DEFAULT NULL,
  `last_login` DATETIME DEFAULT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Site Settings Table
CREATE TABLE IF NOT EXISTS `site_settings` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `setting_key` VARCHAR(100) NOT NULL UNIQUE,
  `setting_value` TEXT DEFAULT NULL,
  `setting_group` VARCHAR(50) DEFAULT 'general',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_setting_group` (`setting_group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Animals Table
CREATE TABLE IF NOT EXISTS `animals` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `species` ENUM('Dog', 'Cat', 'Rabbit', 'Bird', 'Other') NOT NULL DEFAULT 'Dog',
  `breed` VARCHAR(100) DEFAULT NULL,
  `age_years` INT DEFAULT 0,
  `age_months` INT DEFAULT 0,
  `gender` ENUM('Male', 'Female', 'Unknown') NOT NULL DEFAULT 'Unknown',
  `size` ENUM('Small', 'Medium', 'Large', 'Extra Large') DEFAULT 'Medium',
  `location` VARCHAR(150) DEFAULT NULL,
  `color` VARCHAR(50) DEFAULT NULL,
  `weight` DECIMAL(5,2) DEFAULT NULL,
  `personality` TEXT DEFAULT NULL,
  `medical_info` TEXT DEFAULT NULL,
  `vaccinated` TINYINT(1) NOT NULL DEFAULT 0,
  `spayed_neutered` TINYINT(1) NOT NULL DEFAULT 0,
  `good_with_children` TINYINT(1) DEFAULT NULL,
  `good_with_other_animals` TINYINT(1) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `rescue_story` TEXT DEFAULT NULL,
  `rescue_date` DATE DEFAULT NULL,
  `status` ENUM('Available', 'Adoption Pending', 'Adopted', 'Fostered', 'Medical Hold', 'Rescued') NOT NULL DEFAULT 'Available',
  `featured` TINYINT(1) NOT NULL DEFAULT 0,
  `created_by` INT UNSIGNED DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_species` (`species`),
  INDEX `idx_status` (`status`),
  INDEX `idx_featured` (`featured`),
  FOREIGN KEY (`created_by`) REFERENCES `admins`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Animal Images Table
CREATE TABLE IF NOT EXISTS `animal_images` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `animal_id` INT UNSIGNED NOT NULL,
  `image_path` VARCHAR(255) NOT NULL,
  `is_primary` TINYINT(1) NOT NULL DEFAULT 0,
  `sort_order` INT DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_animal_id` (`animal_id`),
  FOREIGN KEY (`animal_id`) REFERENCES `animals`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Adoption Applications Table
CREATE TABLE IF NOT EXISTS `adoption_applications` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `animal_id` INT UNSIGNED DEFAULT NULL,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `address` VARCHAR(255) DEFAULT NULL,
  `city` VARCHAR(100) DEFAULT NULL,
  `country` VARCHAR(100) DEFAULT NULL,
  `preferred_animal` VARCHAR(150) DEFAULT NULL,
  `housing_type` ENUM('House', 'Apartment', 'Condo', 'Townhouse', 'Other') DEFAULT NULL,
  `own_rent` ENUM('Own', 'Rent', 'Other') DEFAULT NULL,
  `landlord_permission` TINYINT(1) DEFAULT NULL,
  `household_members` INT DEFAULT 0,
  `household_children` TINYINT(1) DEFAULT 0,
  `children_ages` VARCHAR(255) DEFAULT NULL,
  `existing_pets` TINYINT(1) DEFAULT 0,
  `existing_pets_details` TEXT DEFAULT NULL,
  `previous_experience` TEXT DEFAULT NULL,
  `reason_for_adoption` TEXT DEFAULT NULL,
  `hours_alone` VARCHAR(50) DEFAULT NULL,
  `additional_info` TEXT DEFAULT NULL,
  `status` ENUM('New', 'Reviewing', 'Interview', 'Approved', 'Rejected', 'Completed') NOT NULL DEFAULT 'New',
  `reviewed_by` INT UNSIGNED DEFAULT NULL,
  `review_notes` TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_status` (`status`),
  INDEX `idx_animal_id` (`animal_id`),
  INDEX `idx_email` (`email`),
  FOREIGN KEY (`animal_id`) REFERENCES `animals`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`reviewed_by`) REFERENCES `admins`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Foster Applications Table
CREATE TABLE IF NOT EXISTS `foster_applications` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `address` VARCHAR(255) DEFAULT NULL,
  `city` VARCHAR(100) DEFAULT NULL,
  `country` VARCHAR(100) DEFAULT NULL,
  `housing_type` ENUM('House', 'Apartment', 'Condo', 'Townhouse', 'Other') DEFAULT NULL,
  `housing_details` TEXT DEFAULT NULL,
  `previous_experience` TEXT DEFAULT NULL,
  `animal_types` VARCHAR(255) DEFAULT NULL,
  `availability` VARCHAR(100) DEFAULT NULL,
  `duration` VARCHAR(100) DEFAULT NULL,
  `existing_pets` TINYINT(1) DEFAULT 0,
  `existing_pets_details` TEXT DEFAULT NULL,
  `can_foster_count` INT DEFAULT 1,
  `has_children` TINYINT(1) DEFAULT 0,
  `transport_available` TINYINT(1) DEFAULT NULL,
  `additional_info` TEXT DEFAULT NULL,
  `status` ENUM('New', 'Reviewing', 'Interview', 'Approved', 'Rejected', 'Active') NOT NULL DEFAULT 'New',
  `reviewed_by` INT UNSIGNED DEFAULT NULL,
  `review_notes` TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_status` (`status`),
  INDEX `idx_email` (`email`),
  FOREIGN KEY (`reviewed_by`) REFERENCES `admins`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Volunteers Table
CREATE TABLE IF NOT EXISTS `volunteers` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `address` VARCHAR(255) DEFAULT NULL,
  `city` VARCHAR(100) DEFAULT NULL,
  `country` VARCHAR(100) DEFAULT NULL,
  `category` VARCHAR(100) DEFAULT NULL,
  `skills` TEXT DEFAULT NULL,
  `availability` VARCHAR(100) DEFAULT NULL,
  `hours_per_week` INT DEFAULT 0,
  `previous_experience` TEXT DEFAULT NULL,
  `additional_info` TEXT DEFAULT NULL,
  `status` ENUM('New', 'Reviewing', 'Interview', 'Approved', 'Rejected', 'Active') NOT NULL DEFAULT 'New',
  `reviewed_by` INT UNSIGNED DEFAULT NULL,
  `review_notes` TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_status` (`status`),
  INDEX `idx_email` (`email`),
  INDEX `idx_category` (`category`),
  FOREIGN KEY (`reviewed_by`) REFERENCES `admins`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Donations Table
CREATE TABLE IF NOT EXISTS `donations` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `donor_name` VARCHAR(100) NOT NULL,
  `donor_email` VARCHAR(100) DEFAULT NULL,
  `donor_phone` VARCHAR(20) DEFAULT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `currency` VARCHAR(10) NOT NULL DEFAULT 'USD',
  `donation_type` ENUM('One Time', 'Monthly', 'Annual', 'In Memory', 'Other') DEFAULT 'One Time',
  `payment_method` VARCHAR(50) DEFAULT NULL,
  `transaction_id` VARCHAR(100) DEFAULT NULL,
  `message` TEXT DEFAULT NULL,
  `anonymous` TINYINT(1) NOT NULL DEFAULT 0,
  `status` ENUM('Pending', 'Completed', 'Failed', 'Refunded') NOT NULL DEFAULT 'Pending',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_status` (`status`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Rescue Stories Table
CREATE TABLE IF NOT EXISTS `rescue_stories` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL UNIQUE,
  `animal_id` INT UNSIGNED DEFAULT NULL,
  `category` VARCHAR(100) DEFAULT NULL,
  `rescue_date` DATE DEFAULT NULL,
  `location` VARCHAR(150) DEFAULT NULL,
  `author_id` INT UNSIGNED DEFAULT NULL,
  `featured_image` VARCHAR(255) DEFAULT NULL,
  `excerpt` TEXT DEFAULT NULL,
  `content` LONGTEXT NOT NULL,
  `status` ENUM('Draft', 'Published', 'Unpublished', 'Archived') NOT NULL DEFAULT 'Draft',
  `featured` TINYINT(1) NOT NULL DEFAULT 0,
  `views` INT UNSIGNED NOT NULL DEFAULT 0,
  `seo_title` VARCHAR(255) DEFAULT NULL,
  `seo_description` TEXT DEFAULT NULL,
  `published_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_status` (`status`),
  INDEX `idx_featured` (`featured`),
  INDEX `idx_published_at` (`published_at`),
  FOREIGN KEY (`animal_id`) REFERENCES `animals`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`author_id`) REFERENCES `admins`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Rescue Story Images (Gallery)
CREATE TABLE IF NOT EXISTS `rescue_story_images` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `story_id` INT UNSIGNED NOT NULL,
  `image_path` VARCHAR(255) NOT NULL,
  `caption` VARCHAR(255) DEFAULT NULL,
  `sort_order` INT DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_story_id` (`story_id`),
  FOREIGN KEY (`story_id`) REFERENCES `rescue_stories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. Blog Posts Table
CREATE TABLE IF NOT EXISTS `blog_posts` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL UNIQUE,
  `category_id` INT UNSIGNED DEFAULT NULL,
  `author_id` INT UNSIGNED DEFAULT NULL,
  `featured_image` VARCHAR(255) DEFAULT NULL,
  `excerpt` TEXT DEFAULT NULL,
  `content` LONGTEXT NOT NULL,
  `tags` VARCHAR(500) DEFAULT NULL,
  `status` ENUM('Draft', 'Published', 'Unpublished', 'Archived') NOT NULL DEFAULT 'Draft',
  `featured` TINYINT(1) NOT NULL DEFAULT 0,
  `views` INT UNSIGNED NOT NULL DEFAULT 0,
  `seo_title` VARCHAR(255) DEFAULT NULL,
  `seo_description` TEXT DEFAULT NULL,
  `published_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_status` (`status`),
  INDEX `idx_featured` (`featured`),
  INDEX `idx_published_at` (`published_at`),
  FOREIGN KEY (`author_id`) REFERENCES `admins`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. Blog Categories Table
CREATE TABLE IF NOT EXISTS `blog_categories` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `description` TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. Events Table
CREATE TABLE IF NOT EXISTS `events` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `event_name` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL UNIQUE,
  `image` VARCHAR(255) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `event_date` DATE NOT NULL,
  `event_time` TIME DEFAULT NULL,
  `end_time` TIME DEFAULT NULL,
  `location` VARCHAR(255) DEFAULT NULL,
  `registration_url` VARCHAR(255) DEFAULT NULL,
  `category` VARCHAR(100) DEFAULT NULL,
  `max_attendees` INT DEFAULT NULL,
  `status` ENUM('Draft', 'Published', 'Completed', 'Cancelled') NOT NULL DEFAULT 'Draft',
  `featured` TINYINT(1) NOT NULL DEFAULT 0,
  `seo_title` VARCHAR(255) DEFAULT NULL,
  `seo_description` TEXT DEFAULT NULL,
  `created_by` INT UNSIGNED DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_event_date` (`event_date`),
  INDEX `idx_status` (`status`),
  INDEX `idx_featured` (`featured`),
  FOREIGN KEY (`created_by`) REFERENCES `admins`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 14. Contact Messages Table
CREATE TABLE IF NOT EXISTS `contact_messages` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `subject` VARCHAR(255) DEFAULT NULL,
  `message` TEXT NOT NULL,
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `reply` TEXT DEFAULT NULL,
  `replied_by` INT UNSIGNED DEFAULT NULL,
  `replied_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_is_read` (`is_read`),
  INDEX `idx_email` (`email`),
  FOREIGN KEY (`replied_by`) REFERENCES `admins`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 15. Newsletter Subscribers Table
CREATE TABLE IF NOT EXISTS `newsletter_subscribers` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `name` VARCHAR(100) DEFAULT NULL,
  `source` VARCHAR(50) DEFAULT 'website',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `token` VARCHAR(64) DEFAULT NULL,
  `subscribed_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `unsubscribed_at` DATETIME DEFAULT NULL,
  INDEX `idx_email` (`email`),
  INDEX `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 16. Media Library Table
CREATE TABLE IF NOT EXISTS `media` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `file_name` VARCHAR(255) NOT NULL,
  `file_path` VARCHAR(255) NOT NULL,
  `file_type` VARCHAR(50) NOT NULL,
  `file_size` INT UNSIGNED NOT NULL,
  `mime_type` VARCHAR(100) NOT NULL,
  `alt_text` VARCHAR(255) DEFAULT NULL,
  `caption` VARCHAR(255) DEFAULT NULL,
  `uploaded_by` INT UNSIGNED DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_file_type` (`file_type`),
  INDEX `idx_created_at` (`created_at`),
  FOREIGN KEY (`uploaded_by`) REFERENCES `admins`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 17. Team Members Table
CREATE TABLE IF NOT EXISTS `team_members` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `position` VARCHAR(100) DEFAULT NULL,
  `bio` TEXT DEFAULT NULL,
  `photo` VARCHAR(255) DEFAULT NULL,
  `email` VARCHAR(100) DEFAULT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `facebook` VARCHAR(255) DEFAULT NULL,
  `twitter` VARCHAR(255) DEFAULT NULL,
  `instagram` VARCHAR(255) DEFAULT NULL,
  `linkedin` VARCHAR(255) DEFAULT NULL,
  `sort_order` INT DEFAULT 0,
  `is_founder` TINYINT(1) NOT NULL DEFAULT 0,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 18. Pages Table
CREATE TABLE IF NOT EXISTS `pages` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL UNIQUE,
  `content` LONGTEXT,
  `featured_image` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('Draft', 'Published', 'Unpublished') NOT NULL DEFAULT 'Draft',
  `template` VARCHAR(100) DEFAULT 'default',
  `seo_title` VARCHAR(255) DEFAULT NULL,
  `seo_description` TEXT DEFAULT NULL,
  `sort_order` INT DEFAULT 0,
  `created_by` INT UNSIGNED DEFAULT NULL,
  `published_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_status` (`status`),
  FOREIGN KEY (`created_by`) REFERENCES `admins`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 19. Homepage Sections Table
CREATE TABLE IF NOT EXISTS `homepage_sections` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `section_key` VARCHAR(100) NOT NULL UNIQUE,
  `section_name` VARCHAR(100) NOT NULL,
  `content` JSON DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `sort_order` INT DEFAULT 0,
  `updated_by` INT UNSIGNED DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_section_key` (`section_key`),
  FOREIGN KEY (`updated_by`) REFERENCES `admins`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 20. Statistics Table
CREATE TABLE IF NOT EXISTS `statistics` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `stat_key` VARCHAR(100) NOT NULL UNIQUE,
  `stat_label` VARCHAR(100) NOT NULL,
  `stat_value` VARCHAR(50) NOT NULL,
  `icon` VARCHAR(100) DEFAULT NULL,
  `display_on_home` TINYINT(1) NOT NULL DEFAULT 1,
  `sort_order` INT DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_display_home` (`display_on_home`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 21. Page Navigation Table (Menu Links)
CREATE TABLE IF NOT EXISTS `navigation_links` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `menu_location` VARCHAR(50) NOT NULL DEFAULT 'main',
  `parent_id` INT UNSIGNED DEFAULT NULL,
  `title` VARCHAR(100) NOT NULL,
  `url` VARCHAR(255) NOT NULL,
  `icon` VARCHAR(100) DEFAULT NULL,
  `sort_order` INT DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_menu_location` (`menu_location`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===== DEFAULT SEED DATA =====

-- Insert Default Admin (username: admin, password: admin123)
INSERT INTO `admins` (`id`, `username`, `email`, `password`, `full_name`, `role`) VALUES
(1, 'admin', 'admin@pawbridge.org', '$2y$10$63Onm7VkesDypJGl1RG/g.fE5WA1CViUF8x2RiTJ4fngTwxt5pjRu', 'PawBridge Admin', 'super_admin');

-- Insert Default Site Settings
INSERT INTO `site_settings` (`setting_key`, `setting_value`, `setting_group`) VALUES
('site_name', 'PawBridge Rescue', 'general'),
('site_tagline', 'HELPING ANIMALS MOVE FROM DANGER TO SAFETY', 'general'),
('site_email', 'hello@pawbridge.org', 'general'),
('site_phone', '+1 (555) 123-4567', 'general'),
('site_address', '123 Rescue Lane, Animal City, AC 12345', 'general'),
('site_city', 'Animal City', 'general'),
('site_country', 'United States', 'general'),
('site_logo', '', 'branding'),
('site_favicon', '', 'branding'),
('color_primary', '#064B20', 'colors'),
('color_secondary', '#0B632A', 'colors'),
('color_mint', '#DDF7E2', 'colors'),
('color_teal', '#59B9B0', 'colors'),
('color_navy', '#102238', 'colors'),
('color_accent', '#F59E42', 'colors'),
('color_soft_bg', '#F5FAF5', 'colors'),
('hero_heading_line1', 'Every Paw', 'homepage'),
('hero_heading_line2', 'Deserves a', 'homepage'),
('hero_heading_line3', 'Second Chance', 'homepage'),
('hero_description', 'We rescue, transport and rehome animals at risk, helping them move from danger to safety.', 'homepage'),
('hero_image_primary', '', 'homepage'),
('hero_image_secondary', '', 'homepage'),
('hero_image_tertiary', '', 'homepage'),
('hero_cta1_text', 'Adopt a Pet', 'homepage'),
('hero_cta1_link', 'adopt.php', 'homepage'),
('hero_cta2_text', 'Help Us Rescue', 'homepage'),
('hero_cta2_link', 'donate.php', 'homepage'),
('mission_title', 'Our Mission', 'homepage'),
('mission_text', 'Every animal deserves safety, care, and a chance to thrive.', 'homepage'),
('donation_cta_title', 'A Small Act Can Change a Life', 'homepage'),
('donation_cta_text', 'Your support helps provide rescue, transport, food, medical care and safe homes for animals in need.', 'homepage'),
('facebook_url', 'https://facebook.com/pawbridge', 'social'),
('twitter_url', 'https://twitter.com/pawbridge', 'social'),
('instagram_url', 'https://instagram.com/pawbridge', 'social'),
('youtube_url', '', 'social'),
('linkedin_url', '', 'social'),
('footer_text', '© 2026 PawBridge Rescue. All rights reserved. Built with love for animals.', 'footer'),
('contact_hours', 'Monday - Friday: 9:00 AM - 5:00 PM\nSaturday: 10:00 AM - 3:00 PM\nSunday: Closed', 'contact'),
('map_embed', '', 'contact'),
('seo_default_title', 'PawBridge Rescue | Helping Animals Find Their Way Home', 'seo'),
('seo_default_description', 'PawBridge Rescue rescues, transports, and rehomes animals at risk. Help us give every animal a second chance at a loving home.', 'seo');

-- Insert Statistics
INSERT INTO `statistics` (`stat_key`, `stat_label`, `stat_value`, `icon`, `display_on_home`, `sort_order`) VALUES
('animals_rescued', 'Animals Rescued', '1200+', 'fa-paw', 1, 1),
('homes_found', 'Forever Homes Found', '850+', 'fa-house-heart', 1, 2),
('foster_families', 'Foster Families', '300+', 'fa-users', 1, 3),
('volunteers', 'Active Volunteers', '120+', 'fa-hand-holding-heart', 1, 4),
('rescue_support', '24/7 Rescue Support', '24/7', 'fa-clock', 1, 5);

-- Insert Homepage Sections
INSERT INTO `homepage_sections` (`section_key`, `section_name`, `content`, `sort_order`) VALUES
('activities', 'Core Activities', '{"blocks":[{"title":"RESCUE","description":"Animals in danger and urgent need.","icon":"fa-shield-dog"},{"title":"FOSTER","description":"Providing temporary safe and loving homes.","icon":"fa-house-chimney-heart"},{"title":"ADOPT","description":"Helping animals find their forever families.","icon":"fa-heart"},{"title":"SUPPORT","description":"Helping us continue saving lives.","icon":"fa-hand-holding-dollar"}]}', 2),
('bridge', 'The Bridge Journey', '{"steps":[{"title":"Danger","icon":"fa-triangle-exclamation","description":"Animals facing immediate danger or neglect"},{"title":"Rescue","icon":"fa-truck-medical","description":"Our team steps in to bring them to safety"},{"title":"Care","icon":"fa-stethoscope","description":"Medical care, food, and rehabilitation"},{"title":"Foster","icon":"fa-house","description":"Temporary loving foster homes"},{"title":"Forever Home","icon":"fa-heart-circle-check","description":"Placed with their perfect family"}]}', 4),
('get_involved', 'Get Involved', '{"blocks":[{"title":"ADOPT","description":"Give an animal a forever home.","link":"adopt.php"},{"title":"FOSTER","description":"Give an animal a safe place to heal.","link":"foster.php"},{"title":"VOLUNTEER","description":"Give your time and skills.","link":"volunteer.php"},{"title":"DONATE","description":"Help us rescue more animals.","link":"donate.php"}]}', 6);

-- Insert Default Navigation Links
INSERT INTO `navigation_links` (`menu_location`, `title`, `url`, `sort_order`) VALUES
('main', 'Home', 'index.php', 1),
('main', 'Adopt', 'animals.php', 2),
('main', 'Rescue Stories', 'stories.php', 3),
('main', 'Get Involved', '#', 4),
('main', 'About', 'about.php', 5),
('main', 'Blog', 'blog.php', 6),
('footer', 'About Us', 'about.php', 1),
('footer', 'Our Team', 'about.php#team', 2),
('footer', 'Our Impact', 'about.php#impact', 3),
('footer', 'Adopt a Pet', 'animals.php', 4),
('footer', 'Foster Program', 'foster.php', 5),
('footer', 'Volunteer', 'volunteer.php', 6),
('footer', 'Donate', 'donate.php', 7),
('footer', 'Rescue Stories', 'stories.php', 8),
('footer', 'Blog', 'blog.php', 9),
('footer', 'Events', 'events.php', 10),
('footer', 'Contact Us', 'contact.php', 11);

-- Insert Sample Blog Categories
INSERT INTO `blog_categories` (`name`, `slug`, `description`) VALUES
('Animal Care Tips', 'animal-care-tips', 'Helpful advice for taking care of your pets and rescue animals.'),
('Rescue Stories', 'rescue-stories', 'Inspiring stories of animals we''ve rescued and helped.'),
('Adoption Tips', 'adoption-tips', 'Everything you need to know about adopting a rescue animal.'),
('Foster Stories', 'foster-stories', 'Heartwarming stories from our foster families.'),
('News & Updates', 'news-updates', 'Latest news from PawBridge Rescue.');

COMMIT;
