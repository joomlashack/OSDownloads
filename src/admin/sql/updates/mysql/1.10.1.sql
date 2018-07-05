-- MySQL Workbench Synchronization
-- Generated: 2018-07-05 09:57
-- Model: New Model
-- Version: 1.0
-- Project: Name of the project
-- Author: Bill Tomczak

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='';

ALTER TABLE `#__osdownloads_documents`
  CHARACTER SET = utf8mb4 , COLLATE = utf8mb4_general_ci,
  CHANGE COLUMN `created_user_id` `created_user_id` INT(10) UNSIGNED NULL DEFAULT NULL AFTER `access`,
  CHANGE COLUMN `created_time` `created_time` DATETIME NULL DEFAULT NULL AFTER `created_user_id`,
  CHANGE COLUMN `modified_user_id` `modified_user_id` INT(10) UNSIGNED NULL DEFAULT NULL AFTER `created_time`,
  CHANGE COLUMN `modified_time` `modified_time` DATETIME NULL DEFAULT NULL AFTER `modified_user_id`,
  CHANGE COLUMN `id` `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT ,
  CHANGE COLUMN `cate_id` `cate_id` INT(11) UNSIGNED NOT NULL ,
  CHANGE COLUMN `brief` `brief` TEXT NOT NULL ,
  CHANGE COLUMN `description_1` `description_1` TEXT NOT NULL ,
  CHANGE COLUMN `description_2` `description_2` TEXT NOT NULL ,
  CHANGE COLUMN `description_3` `description_3` TEXT NOT NULL ,
  CHANGE COLUMN `file_path` `file_path` VARCHAR(255) NOT NULL ,
  CHANGE COLUMN `downloaded` `downloaded` BIGINT(20) NOT NULL ,
  CHANGE COLUMN `external_ref` `external_ref` VARCHAR(100) NOT NULL ,
  CHANGE COLUMN `publish_up` `publish_up` DATETIME NULL DEFAULT NULL ,
  CHANGE COLUMN `publish_down` `publish_down` DATETIME NULL DEFAULT NULL ,
  ADD COLUMN `params` TEXT NULL DEFAULT NULL AFTER `twitter_text`;

ALTER TABLE `#__osdownloads_emails`
  CHARACTER SET = utf8mb4 , COLLATE = utf8mb4_general_ci,
  CHANGE COLUMN `id` `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT ;

ALTER TABLE `#__osdownloads_download_log`
  CHARACTER SET = utf8mb4 , COLLATE = utf8mb4_general_ci , ROW_FORMAT = DEFAULT ;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
