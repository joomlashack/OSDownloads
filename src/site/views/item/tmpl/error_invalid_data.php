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

use Joomla\CMS\Language\Text;

defined('_JEXEC') or die();
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

<h2><?php echo Text::_('COM_OSDOWNLOADS_ERROR'); ?></h2>
<strong><?php echo Text::_('COM_OSDOWNLOADS_INVALID_DATA'); ?></strong>
<p><?php echo Text::_('COM_OSDOWNLOADS_ERROR_DOWNLOAD_DENIED'); ?></p>
