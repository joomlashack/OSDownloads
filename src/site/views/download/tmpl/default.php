<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2005-2019 Joomlashack.com. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 *
 * This file is part of OSDownloads.
 *
 * OSDownloads is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * OSDownloads is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OSDownloads.  If not, see <http://www.gnu.org/licenses/>.
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
