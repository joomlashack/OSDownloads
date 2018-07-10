<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016-2018 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die();

while (ob_get_level()) {
    ob_end_clean();
}

if (empty($this->headers['Content-Disposition'])) {
    header('Content-Disposition: attachment; filename="' . $this->realName . '";');
} else {
    header('Content-Disposition: ' . $this->headers['Content-Disposition']);
}
header('Content-Description: File Transfer');
header('Content-Transfer-Encoding: binary');
header('Content-Type: ' . $this->contentType);
header('Connection: Keep-Alive');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');

if ($this->fileSize > 0) {
    header('Content-Length: ' . $this->fileSize);
}

if ($this->isLocal) {
    @readfile($this->fileFullPath);

} else {
    $ch = curl_init($this->fileFullPath);
    curl_exec($ch);
    curl_close($ch);
}

jexit();
