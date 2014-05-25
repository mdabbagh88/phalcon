;use cloud;
SET @@global.innodb_large_prefix = 1;
DROP TABLE IF EXISTS `core_website`;
CREATE TABLE `core_website`(
	website_id TINYINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
	`code` VARCHAR(50), INDEX `idx_core_website_code` (`code`),
	`area` ENUM('admin','frontend','global'), INDEX `idx_core_website_area` (`area`),
	`default_design_package` VARCHAR(255),
	`default_design_layout` VARCHAR(255)
)engine=InnoDb DEFAULT CHARSET utf8;

INSERT INTO `core_website` (`code`, area, default_design_package, default_design_layout)
VALUES
(
"www", "frontend", "default", "1column"
);

ALTER TABLE core_website ADD COLUMN `session_lifetime` INT DEFAULT 8600 AFTER area;
ALTER TABLE core_website ADD COLUMN `session_cookie_lifetime` INT DEFAULT 8600 AFTER session_lifetime;
ALTER TABLE core_website ADD COLUMN `session_cookie_name` VARCHAR(50) DEFAULT NULL AFTER session_cookie_lifetime;

DROP TABLE IF EXISTS `core_website_design`;
CREATE TABLE `core_website_design` (
	website_design_id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
	website_id TINYINT UNSIGNED NOT NULL, CONSTRAINT `fk_core_website_design_website_id` FOREIGN KEY(`website_id`) REFERENCES `core_website`(`website_id`) ON DELETE CASCADE,
	design_package VARCHAR(255),
	date_from DATETIME NULL, INDEX `idx_core_website_design_date_from` (date_from),
	date_to DATETIME NULL, INDEX `idx_core_website_design_date_to` (date_to)
)engine=InnoDb DEFAULT CHARSET utf8 COMMENT "Entries in this table will override the default design package set for each website";

INSERT INTO core_website_design (website_id, design_package, date_from, date_to)
VALUES
(1, "override", "2014-04-01 00:00:00", "2014-04-30 23:59:59");

DROP TABLE IF EXISTS `core_url_rewrite`;
CREATE TABLE `core_url_rewrite`(
	`url_rewrite_id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	`source_path` VARCHAR(255), INDEX `idx_core_urL_rewrite_source_path` (`source_path`),
	`rewrite_path` VARCHAR(255), INDEX `idx_core_url_rewrite_rewrite_path` (`rewrite_path`),
	`website_id`  TINYINT UNSIGNED NOT NULL, CONSTRAINT `fk_core_url_rewrite_website_id` FOREIGN KEY(`website_id`) REFERENCES `core_website`(`website_id`) ON DELETE CASCADE,
	`redirect` TINYINT(1) DEFAULT 0, INDEX `idx_core_url_rewrite_redirect` (`redirect`),
	`redirect_external` TINYINT(1) DEFAULT 0,
	`redirect_status` VARCHAR(10) DEFAULT "301"
)engine=InnoDb DEFAULT CHARSET utf8;

/*INSERT INTO core_url_rewrite (source_path, rewrite_path, redirect, redirect_external,redirect_status) VALUES 
("/default/test/me", "/default/test/rewrite", 0, NULL, NULL),
("/default/test/redirect", "/default/test/me", 1, 0, "301"),
("/default/test/external", "http://google.com", 1, 1, "301")
;
TRUNCATE core_url_rewrite;
select * from core_url_rewrite; */

/*
INSERT INTO core_url_rewrite (source_path, rewrite_path, website_id, redirect,redirect_external,redirect_status) VALUES
("/default/test/me", "/default/test/rewrite", '1','0', '0', ''),
("/default/test/redirect", "/default/test/me", '1','1', '0', "301"),
("/default/test/external", "http://google.com", '1','1', '1', "301");*/
