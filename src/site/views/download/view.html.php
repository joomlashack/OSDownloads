<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2005-2025 Joomlashack.com. All rights reserved
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
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
 * along with OSDownloads.  If not, see <https://www.gnu.org/licenses/>.
 */

use Alledia\OSDownloads\Factory;
use Alledia\OSDownloads\Free\File;
use Alledia\OSDownloads\Free\Joomla\Component\Site as FreeComponentSite;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Plugin\PluginHelper;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die();
// phpcs:enable PSR1.Files.SideEffects
// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace

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

        $this->isLocal      = $item->isLocal;
        $this->realName     = $item->realName;
        $this->fileFullPath = $item->fullPath;

        if ($this->isLocal) {
            $this->fileSize = $item->fileSize;

        } elseif ($item->file_url) {
            // Trigger the onOSDownloadsGetExternalDownloadLink event
            PluginHelper::importPlugin('osdownloads');
            $app->triggerEvent('onOSDownloadsGetExternalDownloadLink', [&$item]);

            $this->headers = File::getHeaders($item->file_url);
            if (empty($this->headers['http_code']) == false && $this->headers['http_code'] >= 400) {
                $this->displayError(
                    Text::sprintf('COM_OSDOWNLOADS_ERROR_DOWNLOAD_SERVER_ERROR', $this->headers['http_code'])
                );
                return;
            }

            if (empty($this->headers['Content-Length']) == false) {
                $this->fileSize = $this->headers['Content-Length'];
            }

            if (empty($this->headers['Location'])) {
                $this->fileFullPath = $item->file_url;
            } else {
                // Adjust for redirects
                $this->fileFullPath = $this->headers['Location'];
            }
        }

        if (empty($this->fileFullPath)) {
            $this->displayError(Text::_('COM_OSDOWNLOADS_ERROR_DOWNLOAD_NOT_AVAILABLE'));
            return;
        }

        $this->contentType = File::getContentTypeFromFileName($this->fileFullPath);

        $model->incrementDownloadCount($id);
        parent::display($tpl);
    }

    /**
     * @param string $message
     *
     * @return void
     * @throws Exception
     */
    protected function displayError(string $message): void
    {
        Factory::getApplication()->enqueueMessage($message, 'error');

        $this->setLayout('error');
        parent::display();
    }
}
