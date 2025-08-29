# Datenbank: `timeeffect`
# --------------------------------------------------------

#
# Tabellenstruktur fuer Tabelle `auth`
#

DROP TABLE IF EXISTS `<%db_prefix%>auth`;
CREATE TABLE `<%db_prefix%>auth` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `permissions` varchar(255) NOT NULL default '',
  `gids` varchar(255) NOT NULL default '',
  `allow_nc` smallint(1) NOT NULL default '0',
  `username` varchar(50) NOT NULL default '',
  `password` varchar(32) NOT NULL default '',
  `firstname` varchar(128) default NULL,
  `lastname` varchar(128) default NULL,
  `email` varchar(255) default NULL,
  `telephone` varchar(64) default NULL,
  `facsimile` varchar(64) default NULL,
  `confirmed` tinyint(1) NOT NULL default '1',
  `confirmation_token` varchar(64) default NULL,
  `reset_token` varchar(64) default NULL,
  `reset_expires` datetime default NULL,
  `theme_preference` varchar(10) default 'system',
  `company_name` varchar(255) NULL,
  `company_address` text NULL,
  `company_postal_code` varchar(20) NULL,
  `company_city` varchar(100) NULL,
  `company_country` varchar(100) NULL,
  `tax_number` varchar(50) NULL,
  `vat_number` varchar(50) NULL,
  `bank_name` varchar(100) NULL,
  `bank_iban` varchar(34) NULL,
  `bank_bic` varchar(11) NULL,
  `invoice_logo_path` varchar(255) NULL,
  `invoice_letterhead_path` varchar(255) NULL,
  `invoice_footer_path` varchar(255) NULL,
  `invoice_number_format` varchar(50) default 'R-{YYYY}-{MM}-{###}',
  `default_vat_rate` decimal(5,2) default '19.00',
  `payment_terms_days` int(11) default '14',
  `payment_terms_text` text NULL,
  PRIMARY KEY  (`id`),
  KEY `gids` (`gids`),
  KEY `username` (`username`,`password`),
  KEY `confirmation_token` (`confirmation_token`),
  KEY `reset_token` (`reset_token`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

#
# Daten fuer Tabelle `auth`
#

INSERT INTO `<%db_prefix%>auth` VALUES (1, 'admin,agent', '', 1, '<%admin_user%>', '<%admin_password%>', 'Administrator', '', '', '', '', 1, NULL, NULL, NULL, 'system', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'R-{YYYY}-{MM}-{###}', 19.00, 14, NULL);

# --------------------------------------------------------

#
# Tabellenstruktur fuer Tabelle `customer`
#

DROP TABLE IF EXISTS `customer`;
CREATE TABLE `customer` (
  `id` int(32) unsigned NOT NULL auto_increment,
  `gid` int(32) unsigned NOT NULL default '0',
  `access` varchar(9) NOT NULL default 'rwxrwxr--',
  `readforeignefforts` smallint(1) NOT NULL default '1',
  `user` int(32) unsigned NOT NULL default '0',
  `active` enum('yes','no') NOT NULL default 'yes',
  `customer_name` varchar(64) NOT NULL default '',
  `customer_desc` text,
  `customer_budget` int(10) unsigned NOT NULL default '0',
  `customer_budget_currency` enum('$','EUR','USD') NOT NULL default '$',
  `customer_logo` varchar(255) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `id` (`id`),
  KEY `id_2` (`id`,`customer_name`),
  KEY `active` (`active`),
  KEY `access` (`access`),
  KEY `gid` (`gid`),
  KEY `user` (`user`),
  KEY `readforeignefforts` (`readforeignefforts`),
  FULLTEXT KEY `description` (`customer_desc`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

#
# Daten fuer Tabelle `customer`
#

# --------------------------------------------------------

#
# Tabellenstruktur fuer Tabelle `effort`
#

DROP TABLE IF EXISTS `effort`;
CREATE TABLE `effort` (
  `id` int(32) unsigned NOT NULL auto_increment,
  `gid` int(32) unsigned NOT NULL default '0',
  `access` varchar(9) NOT NULL default 'rw-rw-r--',
  `project_id` int(32) unsigned NOT NULL default '0',
  `date` date NULL,
  `begin` time NOT NULL default '00:00:00',
  `end` time NOT NULL default '00:00:00',
  `description` text,
  `note` text,
  `billed` date NULL,
  `rate` decimal(10, 2) NOT NULL default '0',
  `user` int(32) unsigned default NULL,
  `last` timestamp NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `id` (`id`),
  KEY `id_2` (`id`,`project_id`,`date`,`begin`,`end`,`billed`,`rate`,`user`),
  KEY `gid` (`gid`),
  KEY `access` (`access`),
  FULLTEXT KEY `note` (`note`,`description`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

#
# Daten fuer Tabelle `effort`
#

# --------------------------------------------------------

#
# Tabellenstruktur fuer Tabelle `gids`
#

DROP TABLE IF EXISTS `gids`;
CREATE TABLE `gids` (
  `id` int(32) unsigned NOT NULL auto_increment,
  `name` varchar(25) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

#
# Daten fuer Tabelle `gids`
#

# --------------------------------------------------------

#
# Tabellenstruktur fuer Tabelle `group`
#
# Erzeugt am: 22. Oktober 2004 um 15:49
# Aktualisiert am: 22. Oktober 2004 um 15:49
#

DROP TABLE IF EXISTS `group`;
CREATE TABLE `group` (
  `id` int(32) unsigned NOT NULL auto_increment,
  `level` smallint(1) unsigned NOT NULL default '1',
  `name` varchar(64) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 ;

#
# Daten fuer Tabelle `group`
#

INSERT INTO `group` VALUES (1, 65535, 'admin');
INSERT INTO `group` VALUES (2, 6, 'agent');
INSERT INTO `group` VALUES (3, 4, 'client');
# --------------------------------------------------------

#
# Tabellenstruktur fuer Tabelle `project`
#

DROP TABLE IF EXISTS `project`;
CREATE TABLE `project` (
  `id` int(32) unsigned NOT NULL auto_increment,
  `gid` int(32) unsigned NOT NULL default '0',
  `access` varchar(9) NOT NULL default 'rwxrwxr--',
  `user` int(32) unsigned NOT NULL default '0',
  `customer_id` int(32) unsigned NOT NULL default '0',
  `project_name` varchar(64) NOT NULL default '',
  `project_desc` text,
  `project_budget` int(10) unsigned NOT NULL default '0',
  `project_budget_currency` enum('$','EUR','USD') NOT NULL default '$',
  `last` timestamp(6) NOT NULL,
  `closed` enum('No','Yes') NOT NULL default 'No',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `id` (`id`),
  KEY `id_2` (`id`,`project_name`,`customer_id`,`closed`),
  KEY `gid` (`gid`),
  KEY `access` (`access`),
  KEY `user` (`user`),
  FULLTEXT KEY `description` (`project_desc`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

#
# Daten fuer Tabelle `project`
#

# --------------------------------------------------------

#
# Tabellenstruktur fuer Tabelle `rate`
#

DROP TABLE IF EXISTS `rate`;
CREATE TABLE `rate` (
  `id` int(32) unsigned NOT NULL auto_increment,
  `customer_id` int(32) unsigned NOT NULL default '1',
  `name` varchar(64) NOT NULL default '',
  `price` decimal(10, 2) NOT NULL default '0',
  `currency` enum('$','EUR','USD') NOT NULL default '$',
  PRIMARY KEY  (`id`),
  KEY `id` (`id`,`customer_id`,`name`,`price`,`currency`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

#
# Daten fuer Tabelle `rate`
#

# --------------------------------------------------------

#
# Tabellenstruktur fuer Tabelle `customer_contracts`
#

DROP TABLE IF EXISTS `customer_contracts`;
CREATE TABLE `customer_contracts` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `customer_id` int(11) unsigned NOT NULL,
  `project_id` int(11) unsigned NULL,
  `contract_type` enum('hourly','fixed','retainer') NOT NULL default 'hourly',
  `hourly_rate` decimal(10,2) NULL,
  `fixed_amount` decimal(10,2) NULL,
  `retainer_hours` int(11) NULL,
  `start_date` date NOT NULL,
  `end_date` date NULL,
  `active` tinyint(1) NOT NULL default '1',
  `created_at` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL default CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_customer_id` (`customer_id`),
  KEY `idx_project_id` (`project_id`),
  KEY `idx_active` (`active`),
  KEY `idx_dates` (`start_date`, `end_date`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

#
# Daten fuer Tabelle `customer_contracts`
#
