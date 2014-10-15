CREATE TABLE IF NOT EXISTS `#__osdownloads_documents` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `cate_id` INT(11) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `alias` VARCHAR(100) NOT NULL,
  `brief` TEXT NOT NULL,
  `description_1` TEXT NOT NULL,
  `description_2` TEXT NOT NULL,
  `description_3` TEXT NOT NULL,
  `show_email` INT(11) NOT NULL,
  `require_email` INT(11) NOT NULL,
  `require_agree` INT(11) NOT NULL,
  `download_text` VARCHAR(100) NOT NULL,
  `download_color` VARCHAR(10) NOT NULL,
  `documentation_link` VARCHAR(100) NOT NULL,
  `demo_link` VARCHAR(100) NOT NULL,
  `support_link` VARCHAR(100) NOT NULL,
  `other_name` VARCHAR(100) NOT NULL,
  `other_link` VARCHAR(100) NOT NULL,
  `file_path` VARCHAR(100) NOT NULL,
  `file_url` VARCHAR(255) NOT NULL,
  `downloaded` INT(11) NOT NULL,
  `direct_page` VARCHAR(250) NOT NULL,
  `published` INT(11) NOT NULL,
  `ordering` INT(11) NOT NULL,
  `external_ref` VARCHAR(100),
  `access` INT(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__osdownloads_emails` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(100) NOT NULL,
  `document_id` INT NOT NULL,
  `downloaded_date` DATETIME NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
