<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016-2018 Open Source Training, LLC. All rights reserved
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

<h2><?php echo JText::_('COM_OSDOWNLOADS_ERROR'); ?></h2>
<strong><?php echo JText::_('COM_OSDOWNLOADS_INVALID_EMAIL'); ?></strong>
<p><?php echo JText::_('COM_OSDOWNLOADS_ERROR_DOWNLOAD_DENIED'); ?></p>
