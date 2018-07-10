-- MySQL Workbench Synchronization
-- Generated: 2018-07-05 09:20
-- Model: New Model
-- Version: 1.0
-- Project: Name of the project
-- Author: Bill Tomczak

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='';

ALTER TABLE `#__osdownloads_documents`
  ROW_FORMAT = DEFAULT ,
  ADD COLUMN `publish_up` DATETIME NULL DEFAULT '0000-00-00 00:00:00' AFTER `agreement_article_id`,
  ADD COLUMN `publish_down` DATETIME NULL DEFAULT '0000-00-00 00:00:00' AFTER `publish_up`,
  ADD COLUMN `require_share` TINYINT(1) NULL DEFAULT '0' AFTER `publish_down`,
  ADD COLUMN `twitter_hashtags` VARCHAR(50) NULL DEFAULT NULL AFTER `require_share`,
  ADD COLUMN `twitter_via` VARCHAR(50) NULL DEFAULT NULL AFTER `twitter_hashtags`,
  ADD COLUMN `twitter_text` VARCHAR(140) NULL DEFAULT NULL AFTER `twitter_via`,
  ADD COLUMN `mailchimp_list` VARCHAR(30) NULL DEFAULT NULL AFTER `twitter_text`,
  ADD COLUMN `mailchimp_groups` VARCHAR(255) NULL DEFAULT NULL AFTER `mailchimp_list`,
  ADD COLUMN `constantcontact_list` VARCHAR(30) NULL DEFAULT NULL AFTER `mailchimp_groups`;

CREATE TABLE IF NOT EXISTS `#__osdownloads_download_log` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `document_id` INT(11) UNSIGNED NOT NULL,
  `download_on` DATETIME NOT NULL,
  `user_id` INT(11) UNSIGNED NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `document_id` (`document_id` ASC),
  INDEX `user_id` (`user_id` ASC))
  ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8;

ALTER TABLE `#__osdownloads_emails`
  ROW_FORMAT = DEFAULT ,
  ADD COLUMN `hash` CHAR(32) NULL DEFAULT NULL AFTER `downloaded_date`,
  ADD COLUMN `confirmed` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' AFTER `hash`,
  ADD COLUMN `synced` CHAR(1) NOT NULL DEFAULT '0' AFTER `confirmed`,
  ADD COLUMN `sync_error` VARCHAR(255) NULL DEFAULT NULL AFTER `synced`;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
