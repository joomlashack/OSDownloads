CREATE TABLE IF NOT EXISTS `#__osdownloads_documents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cate_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `alias` varchar(100) NOT NULL,
  `brief` text NOT NULL,
  `description_1` text NOT NULL,
  `description_2` text NOT NULL,
  `description_3` text NOT NULL,
  `show_email` int(11) NOT NULL,
  `require_email` int(11) NOT NULL,
  `require_agree` int(11) NOT NULL,
  `download_text` varchar(100) NOT NULL,
  `download_color` varchar(10) NOT NULL,
  `documentation_link` varchar(100) NOT NULL,
  `demo_link` varchar(100) NOT NULL,
  `support_link` varchar(100) NOT NULL,
  `other_name` varchar(100) NOT NULL,
  `other_link` varchar(100) NOT NULL,
  `file_path` varchar(100) NOT NULL,
  `downloaded` int(11) NOT NULL,
  `direct_page` varchar(250) NOT NULL,
  `published` int(11) NOT NULL,
  `ordering` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__osdownloads_emails` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `document_id` INT NOT NULL,
  `downloaded_date` DATETIME NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
