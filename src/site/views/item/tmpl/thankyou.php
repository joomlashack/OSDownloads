<?php
/**
 * @package   OSDownloads
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2015 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

$app = JFactory::getApplication();
$db  = JFactory::getDBO();

$defaultThankYou = "
    <h2>" . JText::_('COM_OSDOWNLOADS_THANK_YOU') . "</h2>
    <p>" . JText::sprintf("COM_OSDOWNLOADS_CLICK_TO_DOWNLOAD_FILE", $this->item->download_url) . "</p>";
$thankyoupage    = $this->params->get("thankyoupage", $defaultThankYou);

if (!empty($this->item->file_url)) {
    $this->model->incrementDownloadCount($this->item->id);
}

// Replace found tags in the thank you message by the respective information
$thankyoupage = str_replace('{{download_url}}', $this->item->download_url, $thankyoupage);
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
        <?php echo($thankyoupage);?>

        <meta http-equiv="refresh" content="0;url=<?php echo $this->item->download_url;?>">
    </div>
</div>
