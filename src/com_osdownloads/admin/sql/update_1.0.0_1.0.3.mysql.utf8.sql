ALTER TABLE `#__osdownloads_documents` ADD `show_email` INT NOT NULL AFTER `description_2`, 
ADD `other_name` VARCHAR( 200 ) NOT NULL AFTER `support_link` ,
ADD `other_link` VARCHAR( 100 ) NOT NULL AFTER `other_name`,
ADD `description_3` TEXT NOT NULL AFTER `description_2`,
CHANGE `download_text` `download_text` VARCHAR( 200 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
ADD `direct_page` VARCHAR( 250 ) NOT NULL AFTER `downloaded` ;