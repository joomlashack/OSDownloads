<?php
/**
 * @package   OSDownloads
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2014 Alledia.com, All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

$app = JFactory::getApplication();
$db  = JFactory::getDBO();

$defaultThankYou = "
    <h1>Thank you</h1>
    <p>" . JText::sprintf("COM_OSDOWNLOADS_CLICK_TO_DOWNLOAD_FILE", $this->item->download_url) . "</p>";
$thankyoupage    = $this->params->get("thankyoupage", $defaultThankYou);

// Replace found tags in the thank you message by the respective information
$thankyoupage = str_replace('{{download_url}}', $this->item->download_url, $thankyoupage);


$email      = trim(JRequest::getVar("email"));
$emailRegex = "/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/";
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
    <?php if ($this->item->require_email == 1 && !preg_match($emailRegex, $email)) : ?>
        <div class="error">
            <h1><?php echo JText::_("COM_OSDOWNLOADS_ERROR"); ?></h1>
            <p>
                <?php echo JText::_("COM_OSDOWNLOADS_WRONG_EMAIL"); ?>
            </p>
        </div>
    <?php else : ?>
        <div class="contentopen thank">
            <?php echo($thankyoupage);?>

            <meta http-equiv="refresh" content="0;url=<?php echo $this->item->download_url;?>">
        </div>
    <?php endif; ?>
</div>
