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

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

defined('_JEXEC') or die();
?>

<div class="item_<?php echo $this->item->id; ?>">
    <h3>
        <?php
        echo HTMLHelper::_(
            'link',
            Route::_($this->container->helperRoute->getViewItemRoute($this->item->id, $this->itemId)),
            $this->item->name
        );
        ?>
    </h3>

    <div class="item_content"><?php echo $this->item->brief; ?></div>

    <?php if ($this->params->get('show_download_button', 0)) : ?>
        <div class="osdownloadsactions">
            <div class="btn_download">
                <?php echo LayoutHelper::render('osdownloads.buttons.download', $this); ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($this->params->get('show_readmore_button', 1)) : ?>
        <div class="osdownloads-readmore-wrapper readmore_wrapper">
            <div class="osdownloads-readmore readmore">
                <?php
                HTMLHelper::_(
                    'link',
                    Route::_($this->container->helperRoute->getViewItemRoute($this->item->id, $this->itemId)),
                    Text::_('COM_OSDOWNLOADS_READ_MORE')
                );
                ?>
            </div>
            <div class="clr"></div>
        </div>
    <?php endif; ?>

    <?php if ($this->numberOfColumns == 1) : ?>
        <div class="seperator"></div>
    <?php endif; ?>

</div>
