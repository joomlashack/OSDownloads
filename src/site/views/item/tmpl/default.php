<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2005-2019 Joomlashack.com. All rights reserved
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

defined('_JEXEC') or die();

use Alledia\Framework\Helper as AllediaHelper;
use Alledia\OSDownloads\Free\Factory;
use Alledia\OSDownloads\Free\Joomla\Component\Site as FreeComponentSite;

$lang      = JFactory::getLanguage();
$container = Factory::getContainer();
$component = FreeComponentSite::getInstance();

JHtml::_('jquery.framework');

$options = array('version' => $component->getMediaVersion(), 'relative' => true);
JHtml::_('script', 'com_osdownloads/jquery.osdownloads.bundle.min.js', $options, array());

if (!$this->isPro) {
    $this->item->require_share = false;
}

?>
<div class="contentopen osdownloads-container item_<?php echo $this->item->id; ?>">
    <?php
    if ($this->params->get('show_page_heading')
        && ($heading = $this->params->get('page_heading'))
    ) :
        ?>
        <div class="page-header">
            <h1> <?php echo $this->escape($this->params->get('page_heading')); ?> </h1>
        </div>
        <?php
    endif;
    ?>

    <h2><?php echo($this->item->name); ?></h2>

    <?php echo $this->item->event->afterDisplayTitle; ?>

    <?php
    if ($this->params->get("show_category", 0) && is_object($this->category)) :
        ?>
        <div class="cate_info">
            Category:
            <a href="<?php echo JRoute::_($container->helperRoute->getFileListRoute($this->category->id)); ?>">
                <?php echo $this->category->title; ?>
            </a>
        </div>
    <?php
    endif;
    if ($this->params->get("show_download_count", 0)) :
        ?>
        <div><?php echo(JText::_("COM_OSDOWNLOADS_DOWNLOADED")); ?>: <?php echo($this->item->downloaded); ?></div>
    <?php
    endif;
    ?>

    <?php echo $this->item->event->beforeDisplayContent; ?>

    <div class="reference">
        <?php
        if ($this->item->documentation_link) :
            ?>
            <div class="osdownloads-readmore readmore">
                <a href="<?php echo($this->item->documentation_link); ?>">
                    <?php echo(JText::_("COM_OSDOWNLOADS_DOCUMENTATION")); ?>
                </a>
            </div>
        <?php
        endif;
        if ($this->item->demo_link) :
            ?>
            <div class="osdownloads-readmore readmore">
                <a href="<?php echo($this->item->demo_link); ?>">
                    <?php echo(JText::_("COM_OSDOWNLOADS_DEMO")); ?>
                </a>
            </div>
        <?php
        endif;
        if ($this->item->support_link) :
            ?>
            <div class="osdownloads-readmore readmore">
                <a href="<?php echo($this->item->support_link); ?>">
                    <?php echo(JText::_("COM_OSDOWNLOADS_SUPPORT")); ?>
                </a>
            </div>
        <?php
        endif;
        if ($this->item->other_link) :
            ?>
            <div class="osdownloads-readmore readmore">
                <a href="<?php echo($this->item->other_link); ?>">
                    <?php echo($this->item->other_name); ?>
                </a>
            </div>
        <?php
        endif;
        ?>
        <div class="clr"></div>
    </div>
    <?php
    if ($this->item->brief || $this->item->description_1) :
        ?>
        <div class="description1"><?php echo($this->item->brief . $this->item->description_1); ?></div>
    <?php
    endif;

    if ($this->item->description_2) :
        ?>
        <div class="description2"><?php echo($this->item->description_2); ?></div>
    <?php
    endif;
    ?>
    <div class="osdownloadsactions">
        <div class="btn_download">
            <?php echo JLayoutHelper::render('buttons.download', $this); ?>
        </div>
    </div>

    <?php
    if ($this->item->description_3) :
        ?>
        <div><?php echo($this->item->description_3); ?></div>
    <?php
    endif;
    ?>

    <?php echo $this->item->event->afterDisplayContent; ?>
</div>
