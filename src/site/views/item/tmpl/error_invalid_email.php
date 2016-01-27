<?php
/**
 * @package   OSDownloads
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2016 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );
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

<h1><?php echo JText::_('COM_OSDOWNLOADS_ERROR'); ?></h1>
<strong><?php echo JText::_('COM_OSDOWNLOADS_INVALID_EMAIL'); ?></strong>
<p><?php echo JText::_('COM_OSDOWNLOADS_DOWNLOAD_DENIED'); ?></p>
