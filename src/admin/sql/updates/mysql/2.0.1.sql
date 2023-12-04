-- MySQL Workbench Synchronization
-- Generated: 2023-12-04 14:06
-- Model: New Model
-- Version: 1.0
-- Project: Name of the project
-- Author: Bill Tomczak

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='';

ALTER TABLE `#__osdownloads_documents`
DROP COLUMN `twitter_text`,
DROP COLUMN `twitter_via`,
DROP COLUMN `twitter_hashtags`,
DROP COLUMN `require_share`;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
