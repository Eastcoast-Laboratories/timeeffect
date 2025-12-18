SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


CREATE TABLE IF NOT EXISTS `<%db_prefix%>auth` (
  `id` int(11) unsigned NOT NULL,
  `permissions` varchar(255) NOT NULL default '',
  `gids` varchar(255) NOT NULL default '',
  `allow_nc` smallint(1) NOT NULL default 0,
  `username` varchar(50) NOT NULL default '',
  `password` varchar(32) NOT NULL default '',
  `firstname` varchar(128) default NULL,
  `lastname` varchar(128) default NULL,
  `email` varchar(255) default NULL,
  `telephone` varchar(64) default NULL,
  `facsimile` varchar(64) default NULL,
  `confirmed` tinyint(1) NOT NULL default 1,
  `confirmation_token` varchar(64) default NULL,
  `reset_token` varchar(64) default NULL,
  `reset_expires` datetime default NULL,
  `theme_preference` varchar(10) default 'system',
  `company_name` varchar(255) default NULL,
  `company_address` text default NULL,
  `company_postal_code` varchar(20) default NULL,
  `company_city` varchar(100) default NULL,
  `company_country` varchar(100) default NULL,
  `tax_number` varchar(50) default NULL,
  `vat_number` varchar(50) default NULL,
  `bank_name` varchar(100) default NULL,
  `bank_iban` varchar(34) default NULL,
  `bank_bic` varchar(11) default NULL,
  `invoice_logo_path` varchar(255) default NULL,
  `invoice_letterhead_path` varchar(255) default NULL,
  `invoice_footer_path` varchar(255) default NULL,
  `invoice_number_format` varchar(50) default 'R-{YYYY}-{MM}-{###}',
  `default_vat_rate` decimal(5,2) default 19.00,
  `payment_terms_days` int(11) default 14,
  `payment_terms_text` text default NULL
) ENGINE=MyISAM default CHARSET=latin1 COLLATE=latin1_swedish_ci;

INSERT INTO `<%db_prefix%>auth` (`id`, `permissions`, `gids`, `allow_nc`, `username`, `password`, `firstname`, `lastname`, `email`, `telephone`, `facsimile`, `confirmed`, `confirmation_token`, `reset_token`, `reset_expires`, `theme_preference`, `company_name`, `company_address`, `company_postal_code`, `company_city`, `company_country`, `tax_number`, `vat_number`, `bank_name`, `bank_iban`, `bank_bic`, `invoice_logo_path`, `invoice_letterhead_path`, `invoice_footer_path`, `invoice_number_format`, `default_vat_rate`, `payment_terms_days`, `payment_terms_text`) VALUES
(1, 'admin', '', 1, 'admin', 'c2fa504e445c78399aabc97290d3350f', '', 'Administrator', 'ruben.barkow@eclabs.de', '', '', 1, NULL, NULL, NULL, 'system', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'R-{YYYY}-{MM}-{###}', '19.00', 14, NULL);

CREATE TABLE IF NOT EXISTS `<%db_prefix%>customer` (
  `id` int(32) unsigned NOT NULL,
  `gid` int(32) unsigned NOT NULL default 0,
  `access` varchar(9) NOT NULL default 'rwxrwxr--',
  `readforeignefforts` smallint(1) NOT NULL default 1,
  `user` int(32) unsigned NOT NULL default 0,
  `active` enum('yes','no') NOT NULL default 'yes',
  `customer_name` varchar(64) NOT NULL default '',
  `customer_desc` text default NULL,
  `customer_address` text default NULL,
  `customer_budget` int(10) unsigned NOT NULL default 0,
  `customer_budget_currency` enum('€','EUR','USD') NOT NULL default '€',
  `customer_logo` varchar(255) default NULL
) ENGINE=MyISAM default CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE IF NOT EXISTS `<%db_prefix%>customer_contracts` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `project_id` int(11) default NULL,
  `contract_type` enum('hourly','fixed','retainer') default 'hourly',
  `hourly_rate` decimal(10,2) default NULL,
  `fixed_amount` decimal(10,2) default NULL,
  `fixed_hours` decimal(8,2) default NULL,
  `description` text default NULL,
  `start_date` date NOT NULL,
  `end_date` date default NULL,
  `active` tinyint(1) default 1,
  `created_at` timestamp NULL default current_timestamp(),
  `updated_at` timestamp NULL default current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB default CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `<%db_prefix%>effort` (
  `id` int(32) unsigned NOT NULL,
  `gid` int(32) unsigned NOT NULL default 0,
  `access` varchar(9) NOT NULL default 'rw-rw-r--',
  `project_id` int(32) unsigned NOT NULL default 0,
  `date` date NOT NULL default '0000-00-00',
  `begin` time NOT NULL default '00:00:00',
  `end` time NOT NULL default '00:00:00',
  `description` text default NULL,
  `note` text default NULL,
  `billed` date default NULL,
  `rate` float(10,2) unsigned NOT NULL default 1.00,
  `user` int(32) unsigned default NULL,
  `last` timestamp NOT NULL default current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM default CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE IF NOT EXISTS `<%db_prefix%>gids` (
  `id` int(32) unsigned NOT NULL,
  `name` varchar(25) NOT NULL default ''
) ENGINE=MyISAM default CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE IF NOT EXISTS `<%db_prefix%>group` (
  `id` int(32) unsigned NOT NULL,
  `level` smallint(1) unsigned NOT NULL default 1,
  `name` varchar(64) NOT NULL default ''
) ENGINE=MyISAM default CHARSET=latin1 COLLATE=latin1_swedish_ci;

INSERT INTO `<%db_prefix%>group` (`id`, `level`, `name`) VALUES
(1, 65535, 'admin'),
(2, 8, 'accountant'),
(3, 4, 'agent'),
(4, 2, 'client');

CREATE TABLE IF NOT EXISTS `<%db_prefix%>hour_carryovers` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `project_id` int(11) default NULL,
  `period_year` int(11) NOT NULL,
  `period_month` int(11) NOT NULL,
  `contracted_hours` decimal(8,2) NOT NULL,
  `actual_hours` decimal(8,2) NOT NULL,
  `carryover_hours` decimal(8,2) NOT NULL,
  `cumulative_carryover` decimal(8,2) NOT NULL,
  `invoice_id` int(11) default NULL,
  `created_at` timestamp NULL default current_timestamp()
) ENGINE=InnoDB default CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `<%db_prefix%>invoices` (
  `id` int(11) NOT NULL,
  `invoice_number` varchar(50) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `project_id` int(11) default NULL,
  `invoice_date` date NOT NULL,
  `period_start` date NOT NULL,
  `period_end` date NOT NULL,
  `contract_type` enum('hourly','fixed_monthly') default 'hourly',
  `fixed_amount` decimal(10,2) default NULL,
  `fixed_hours` decimal(8,2) default NULL,
  `total_hours` decimal(8,2) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `vat_rate` decimal(5,2) default 19.00,
  `vat_amount` decimal(10,2) NOT NULL,
  `gross_amount` decimal(10,2) NOT NULL,
  `carryover_previous` decimal(8,2) default 0.00,
  `carryover_current` decimal(8,2) default 0.00,
  `description` text default NULL,
  `status` enum('draft','sent','paid','cancelled') default 'draft',
  `created_at` timestamp NULL default current_timestamp(),
  `updated_at` timestamp NULL default current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB default CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `<%db_prefix%>invoice_efforts` (
  `id` int(11) NOT NULL,
  `invoice_id` int(11) NOT NULL,
  `effort_id` int(11) NOT NULL
) ENGINE=InnoDB default CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `<%db_prefix%>invoice_items` (
  `id` int(11) NOT NULL,
  `invoice_id` int(11) NOT NULL,
  `description` text NOT NULL,
  `quantity` decimal(8,2) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL
) ENGINE=InnoDB default CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `<%db_prefix%>invoice_payments` (
  `id` int(11) NOT NULL,
  `invoice_id` int(11) NOT NULL,
  `payment_date` date default NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) default NULL,
  `notes` text default NULL,
  `created_at` timestamp NULL default current_timestamp()
) ENGINE=InnoDB default CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `<%db_prefix%>login_attempts` (
  `id` int(11) unsigned NOT NULL,
  `ip_address` varchar(45) NOT NULL COMMENT 'IP address of the attempt',
  `username` varchar(50) NOT NULL default '' COMMENT 'Username attempted',
  `attempt_time` timestamp NOT NULL default current_timestamp(),
  `success` tinyint(1) NOT NULL default 0
) ENGINE=MyISAM default CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Tracks login attempts for brute force protection';

CREATE TABLE IF NOT EXISTS `<%db_prefix%>migrations` (
  `id` int(11) NOT NULL,
  `version` int(11) NOT NULL,
  `migration_name` varchar(255) NOT NULL,
  `executed_at` timestamp NULL default current_timestamp()
) ENGINE=MyISAM default CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `<%db_prefix%>payment_reminders` (
  `id` int(11) NOT NULL,
  `invoice_id` int(11) NOT NULL,
  `reminder_type` enum('first','second','final') NOT NULL,
  `sent_date` date default NULL,
  `due_date` date default NULL,
  `reminder_text` text default NULL,
  `status` enum('pending','sent','cancelled') default 'pending'
) ENGINE=InnoDB default CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `<%db_prefix%>project` (
  `id` int(32) unsigned NOT NULL,
  `gid` int(32) unsigned NOT NULL default 0,
  `access` varchar(9) NOT NULL default 'rwxrwxr--',
  `user` int(32) unsigned NOT NULL default 0,
  `customer_id` int(32) unsigned NOT NULL default 0,
  `project_name` varchar(64) NOT NULL default '',
  `project_desc` text default NULL,
  `project_budget` int(10) unsigned NOT NULL default 0,
  `project_budget_currency` enum('€','EUR','USD') NOT NULL default '€',
  `last` timestamp NOT NULL default current_timestamp() ON UPDATE current_timestamp(),
  `closed` enum('No','Yes') NOT NULL default 'No'
) ENGINE=MyISAM default CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE IF NOT EXISTS `<%db_prefix%>rate` (
  `id` int(32) unsigned NOT NULL,
  `customer_id` int(32) unsigned NOT NULL default 1,
  `name` varchar(64) NOT NULL default '',
  `price` float NOT NULL default 0,
  `currency` enum('€','EUR','USD') NOT NULL default '€'
) ENGINE=MyISAM default CHARSET=latin1 COLLATE=latin1_swedish_ci;


ALTER TABLE `<%db_prefix%>auth`
  ADD PRIMARY KEY (`id`),
  ADD KEY `gids` (`gids`),
  ADD KEY `username` (`username`,`password`),
  ADD KEY `confirmation_token` (`confirmation_token`),
  ADD KEY `reset_token` (`reset_token`);

ALTER TABLE `<%db_prefix%>customer`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `id_2` (`id`,`customer_name`),
  ADD KEY `active` (`active`),
  ADD KEY `access` (`access`),
  ADD KEY `gid` (`gid`),
  ADD KEY `user` (`user`),
  ADD KEY `readforeignefforts` (`readforeignefforts`);
ALTER TABLE `<%db_prefix%>customer` ADD FULLTEXT KEY `description` (`customer_desc`);

ALTER TABLE `<%db_prefix%>customer_contracts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_customer_id` (`customer_id`),
  ADD KEY `idx_project_id` (`project_id`),
  ADD KEY `idx_active` (`active`),
  ADD KEY `idx_dates` (`start_date`,`end_date`);

ALTER TABLE `<%db_prefix%>effort`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `id_2` (`id`,`project_id`,`date`,`begin`,`end`,`billed`,`rate`,`user`),
  ADD KEY `gid` (`gid`),
  ADD KEY `access` (`access`);
ALTER TABLE `<%db_prefix%>effort` ADD FULLTEXT KEY `note` (`note`,`description`);

ALTER TABLE `<%db_prefix%>gids`
  ADD PRIMARY KEY (`id`),
  ADD KEY `name` (`name`);

ALTER TABLE `<%db_prefix%>group`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `<%db_prefix%>hour_carryovers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_period` (`customer_id`,`project_id`,`period_year`,`period_month`);

ALTER TABLE `<%db_prefix%>invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoice_number` (`invoice_number`);

ALTER TABLE `<%db_prefix%>invoice_efforts`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `<%db_prefix%>invoice_items`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `<%db_prefix%>invoice_payments`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `<%db_prefix%>login_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ip_time` (`ip_address`,`attempt_time`),
  ADD KEY `username_time` (`username`,`attempt_time`);

ALTER TABLE `<%db_prefix%>migrations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `version` (`version`),
  ADD KEY `version_2` (`version`);

ALTER TABLE `<%db_prefix%>payment_reminders`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `<%db_prefix%>project`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `id_2` (`id`,`project_name`,`customer_id`,`closed`),
  ADD KEY `gid` (`gid`),
  ADD KEY `access` (`access`),
  ADD KEY `user` (`user`);
ALTER TABLE `<%db_prefix%>project` ADD FULLTEXT KEY `description` (`project_desc`);

ALTER TABLE `<%db_prefix%>rate`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id` (`id`,`customer_id`,`name`,`price`,`currency`);


ALTER TABLE `<%db_prefix%>auth`
  MODIFY `id` int(11) unsigned NOT NULL AUTO_INCREMENT;

ALTER TABLE `<%db_prefix%>customer`
  MODIFY `id` int(32) unsigned NOT NULL AUTO_INCREMENT;

ALTER TABLE `<%db_prefix%>customer_contracts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `<%db_prefix%>effort`
  MODIFY `id` int(32) unsigned NOT NULL AUTO_INCREMENT;

ALTER TABLE `<%db_prefix%>gids`
  MODIFY `id` int(32) unsigned NOT NULL AUTO_INCREMENT;

ALTER TABLE `<%db_prefix%>group`
  MODIFY `id` int(32) unsigned NOT NULL AUTO_INCREMENT;

ALTER TABLE `<%db_prefix%>hour_carryovers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `<%db_prefix%>invoices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `<%db_prefix%>invoice_efforts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `<%db_prefix%>invoice_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `<%db_prefix%>invoice_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `<%db_prefix%>login_attempts`
  MODIFY `id` int(11) unsigned NOT NULL AUTO_INCREMENT;

ALTER TABLE `<%db_prefix%>migrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `<%db_prefix%>payment_reminders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `<%db_prefix%>project`
  MODIFY `id` int(32) unsigned NOT NULL AUTO_INCREMENT;

ALTER TABLE `<%db_prefix%>rate`
  MODIFY `id` int(32) unsigned NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
