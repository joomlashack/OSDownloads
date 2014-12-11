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

$params       = clone($app->getParams('com_osdownloads'));
$thankyoupage = $params->get("thankyoupage", "Thank you");

$email      = trim(JRequest::getVar("email"));
$emailRegex = "/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/";

$query = $db->getQuery(true)
    ->update('#__osdownloads_documents')
    ->set('downloaded = downloaded + 1')
    ->where("id = " . $db->q($this->item->id));
$db->setQuery($query);
$db->query();
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
    <?php if ($this->item->require_email && !preg_match($emailRegex, $email)) : ?>
        <div class="error"><?php echo(JText::_("COM_OSDOWNLOADS_WRONG_EMAIL"));?></div>
    <?php else : ?>
        <div class="contentopen">
            <h1 class="thank"><?php echo($thankyoupage);?></h1>
            <p class="download_link">
                <?php echo JText::sprintf("COM_OSDOWNLOADS_CLICK_TO_DOWNLOAD_FILE", $this->download_url);?>

                <meta http-equiv="refresh" content="0;url=<?php echo $this->download_url;?>">
            </p>
        </div>
    <?php endif; ?>
</div>
