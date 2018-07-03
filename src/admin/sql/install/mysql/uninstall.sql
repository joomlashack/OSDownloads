DROP TABLE IF EXISTS `#__osdownloads_documents`;
DROP TABLE IF EXISTS `#__osdownloads_emails`;
DROP TABLE IF EXISTS `#__osdownloads_download_log`;
DELETE FROM `#__categories` WHERE `extension` = 'com_osdownloads';
