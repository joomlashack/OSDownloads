<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2005-2022 Joomlashack.com. All rights reserved
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

use Alledia\OSDownloads\Factory;
use Alledia\OSDownloads\Free\File;
use Alledia\OSDownloads\Free\Helper\Helper;
use Alledia\OSDownloads\Free\Joomla\Component\Site as FreeComponentSite;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Plugin\PluginHelper;


class OSDownloadsViewDownload extends HtmlView
{
    /**
     * @var string
     */
    protected $realName = null;

    /**
     * @var string
     */
    protected $contentType = null;

    /**
     * @var string
     */
    protected $fileSize = null;

    /**
     * @var string
     */
    protected $fileFullPath = null;

    /**
     * @var string[]
     */
    protected $headers = null;

    /**
     * @var bool
     */
    protected $isLocal = null;

    /**
     * @inheritDoc
     */
    public function display($tpl = null)
    {
        $app = Factory::getApplication();
        $id  = $app->input->getInt('id');

        $component = FreeComponentSite::getInstance();
        $model     = $component->getModel('Item');
        $item      = $model->getItem($id);

        if (empty($item)) {
            $this->displayError(Text::_('COM_OSDOWNLOADS_ERROR_DOWNLOAD_DENIED'));
            return;
        }

        if ($item->file_url) {
            $url = explode('?', $item->file_url);

            $this->realName = basename(reset($url));
            $this->isLocal = Helper::isLocalPath($item->file_url);

            if ($this->isLocal) {
                $fileFullPath = realpath(JPATH_SITE . '/' . ltrim($item->file_url, '/'));
                if (is_file($fileFullPath)) {
                    $this->fileSize = filesize($fileFullPath);
                } else {
                    $fileFullPath = null;
                }

            } else {
                // Triggers the onOSDownloadsGetExternalDownloadLink event
                PluginHelper::importPlugin('osdownloads');

                $app->triggerEvent('onOSDownloadsGetExternalDownloadLink', [&$item]);

                $fileFullPath = $item->file_url;

                $this->headers = File::getHeaders($fileFullPath);
                if (!empty($this->headers['http_code']) && $this->headers['http_code'] >= 400) {
                    $this->displayError(
                        Text::sprintf('COM_OSDOWNLOADS_ERROR_DOWNLOAD_SERVER_ERROR', $this->headers['http_code'])
                    );
                    return;
                }

                if (!empty($this->headers['Content-Length'])) {
                    $this->fileSize = $this->headers['Content-Length'];

                }

                // Adjust for redirects
                if (!empty($this->headers['Location'])) {
                    $fileFullPath = $this->headers['Location'];
                }
            }

        } else {
            $this->isLocal = true;
            $fileFullPath   = realpath(JPATH_SITE . '/media/com_osdownloads/files/' . $item->file_path);
            $this->realName = substr($item->file_path, strpos($item->file_path, '_') + 1);
            $this->fileSize = filesize($fileFullPath);
    }

        if (empty($fileFullPath)) {
            $this->displayError(Text::_('COM_OSDOWNLOADS_ERROR_DOWNLOAD_NOT_AVAILABLE'));
            return;
        }

        $this->contentType  = File::getContentTypeFromFileName($fileFullPath);
        $this->fileFullPath = $fileFullPath;

        if ($this->checkMemory()) {
            $model->incrementDownloadCount($id);
            parent::display($tpl);

            return;
        }

        $this->displayError(Text::_('COM_OSDOWNLOADS_ERROR_DOWNLOAD_TOO_BIG'));
    }

    /**
     * @param string $message
     *
     * @return void
     * @throws Exception
     */
    protected function displayError($message)
    {
        Factory::getApplication()->enqueueMessage($message, 'error');

        $this->setLayout('error');
        parent::display();
    }

    /**
     * Try to make sure there is enough memory in the case of large files.
     * If the file is too large, a zero-length file gets saved.
     *
     * @TODO: See if this is required for remote files
     *
     * @return bool
     */
    protected function checkMemory(): bool
    {
        if ($this->fileSize) {
            $memoryLimit = ini_get('memory_limit');
            if (preg_match('/(\d+)([a-z])/i', $memoryLimit, $memory)) {
                $memory = $memory[1] * pow(2, stripos('-KMG', $memory[2]) * 10);
            } else {
                $memory = (int)ini_get('memory_limit');
            }

            if ($this->fileSize > $memory) {
                ini_set('memory_limit', -1);
                return (ini_get('memory_limit') == -1);
            }
        }

        return true;
    }
}
