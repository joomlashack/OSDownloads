<?php
/**
 * @package   OSDownloads
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2015 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */
defined('_JEXEC') or die( 'Restricted access' );

header("Content-Disposition: attachment; filename=\"" . $this->realName . "\";");
header('Content-Description: File Transfer');
header('Content-Transfer-Encoding: binary');
header('Content-Type: ' . $this->contentType);
header('Connection: Keep-Alive');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
header('Content-Length: ' . $this->fileSize);

@readfile($this->fileFullPath);
