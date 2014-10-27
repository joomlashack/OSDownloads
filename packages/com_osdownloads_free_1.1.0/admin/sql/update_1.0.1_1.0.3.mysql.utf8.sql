ALTER TABLE `#__osdownloads_documents` ADD `description_3` TEXT NOT NULL AFTER `description_2` ,
CHANGE `download_text` `download_text` VARCHAR( 200 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
CHANGE `other_name` `other_name` VARCHAR( 200 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
ADD `direct_page` VARCHAR( 250 ) NOT NULL AFTER `downloaded` ;