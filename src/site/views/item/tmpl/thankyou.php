<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016-2018 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die();

use Alledia\OSDownloads\Free\File;

$app = JFactory::getApplication();

// Get the download URL for the file
$downloadUrl = File::getDownloadUrl($this->item->id);

/**
 * Patch for iOS and text/image/pdf files
 *
 * Fixes the issue with download in iOS devices.
 * The browser doesn't recognize the headers or tag attributes to force
 * download as an attachment. It displays the content, if possible. So as
 * an workaround we display a link instead of the content. The link takes to
 * a new page, where the content is nativelly displayed and available for
 * download in the iOS way.
 */

// Is coming from an iOS browser?
$iOSBrowser = stripos($_SERVER['HTTP_USER_AGENT'],"iPod")
    || stripos($_SERVER['HTTP_USER_AGENT'],"iPhone")
    || stripos($_SERVER['HTTP_USER_AGENT'],"iPad");

// Get a custom thank you page from the settings
$thankyoupage = $this->params->get("thankyoupage");

if (empty($thankyoupage)) {
    $defaultMessage = $iOSBrowser ? 'COM_OSDOWNLOADS_CLICK_TO_DOWNLOAD_FILE_IOS' : 'COM_OSDOWNLOADS_CLICK_TO_DOWNLOAD_FILE';

    $thankyoupage = sprintf(
        '<h2>%s</h2><p>%s</p>',
        JText::_('COM_OSDOWNLOADS_THANK_YOU'),
        JText::sprintf($defaultMessage, $downloadUrl)
    );
} elseif ($iOSBrowser) {
    $thankyoupage .= sprintf(
        '<p>%s</p>',
        JText::sprintf('COM_OSDOWNLOADS_CLICK_TO_DOWNLOAD_FILE_IOS', $downloadUrl)
    );
}

// Replace found tags in the thank you message by the respective information
$thankyoupage = str_replace('{{download_url}}', $downloadUrl, $thankyoupage);
?>

<style>
    body,
    div#all,
    div#main {
        background: none transparent !important;
    }

    div#main {
        min-height: 0 !important;
    }
</style>

<div id="osdownloads-thankyou">
    <div class="contentopen thank">
        <?php echo $thankyoupage; ?>
        <?php if (!$iOSBrowser) : ?>
            <meta http-equiv="refresh" content="0;url=<?php echo $downloadUrl; ?>">
        <?php endif; ?>
    </div>
</div>
