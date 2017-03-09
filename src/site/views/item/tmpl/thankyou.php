<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die();

$app = JFactory::getApplication();
$db  = JFactory::getDbo();

$downloadUrl = JRoute::_("index.php?option=com_osdownloads&task=download&tmpl=component&id={$this->item->id}");

$defaultThankYou = "
    <h2>" . JText::_('COM_OSDOWNLOADS_THANK_YOU') . "</h2>
    <p>" . JText::sprintf("COM_OSDOWNLOADS_CLICK_TO_DOWNLOAD_FILE", $downloadUrl) . "</p>";
$thankyoupage    = $this->params->get("thankyoupage", $defaultThankYou);

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
        <meta http-equiv="refresh" content="0;url=<?php echo $downloadUrl; ?>">
    </div>
</div>
