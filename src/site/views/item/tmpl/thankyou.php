<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2005-2023 Joomlashack.com. All rights reserved
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

use Alledia\OSDownloads\Free\File;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Language;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die();

/**
 * @var OSDownloadsViewItem $this
 * @var string              $template
 * @var string              $layout
 * @var string              $layoutTemplate
 * @var Language            $lang
 * @var string              $filetofind
 */

$app = Factory::getApplication();

// Get the download URL for the file
$downloadUrl = File::getDownloadUrl($this->item->id);

/**
 * Patch for iOS and text/image/pdf files
 *
 * Fixes the issue with download in iOS devices.
 * The browser doesn't recognize the headers or tag attributes to force
 * download as an attachment. It displays the content, if possible. So as
 * a workaround we display a link instead of the content. The link takes to
 * a new page, where the content is nativelly displayed and available for
 * download in the iOS way.
 */

// Is coming from an iOS browser?
$iOSBrowser = stripos($_SERVER['HTTP_USER_AGENT'], 'iPod')
    || stripos($_SERVER['HTTP_USER_AGENT'], 'iPhone')
    || stripos($_SERVER['HTTP_USER_AGENT'], 'iPad');

// Get a custom thank-you page from the settings
$thankYouPage = $this->params->get('thankyoupage');

if (empty($thankYouPage)) {
    $defaultMessage = $iOSBrowser
        ? 'COM_OSDOWNLOADS_CLICK_TO_DOWNLOAD_FILE_IOS'
        : 'COM_OSDOWNLOADS_CLICK_TO_DOWNLOAD_FILE';

    $thankYouPage = sprintf(
        '<h2>%s</h2><p>%s</p>',
        Text::_('COM_OSDOWNLOADS_THANK_YOU'),
        Text::sprintf($defaultMessage, $downloadUrl)
    );
} elseif ($iOSBrowser) {
    $thankYouPage .= sprintf(
        '<p>%s</p>',
        Text::sprintf('COM_OSDOWNLOADS_CLICK_TO_DOWNLOAD_FILE_IOS', $downloadUrl)
    );
}

// Replace found tags in the thank-you message by the respective information
$thankYouPage = str_replace('{{download_url}}', $downloadUrl, $thankYouPage);

Factory::getDocument()->addStyleDeclaration('* { background: none transparent !important; }');

?>
<div id="osdownloads-thankyou">
    <div class="contentopen thank">
        <?php echo $thankYouPage; ?>
        <?php if ($iOSBrowser == false) : ?>
            <meta http-equiv="refresh" content="0;url=<?php echo $downloadUrl; ?>">
        <?php endif; ?>
    </div>
</div>
