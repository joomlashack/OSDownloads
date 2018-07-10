<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016-2018 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>

<div class="item_<?php echo $this->item->id; ?>">
    <h3>
        <a href="<?php echo JRoute::_($this->container->helperRoute->getViewItemRoute($this->item->id, $this->itemId)); ?>">
            <?php echo $this->item->name; ?>
        </a>
    </h3>

    <div class="item_content"><?php echo $this->item->brief; ?></div>

    <?php if ($this->params->get('show_download_button', 0)) : ?>
        <div class="osdownloadsactions">
            <div class="btn_download">
                <?php echo JLayoutHelper::render('download_button', $this); ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($this->params->get('show_readmore_button', 1)) : ?>
        <div class="osdownloads-readmore-wrapper readmore_wrapper">
            <div class="osdownloads-readmore readmore">
                <a href="<?php echo JRoute::_($this->container->helperRoute->getViewItemRoute($this->item->id, $this->itemId)); ?>">
                    <?php echo JText::_("COM_OSDOWNLOADS_READ_MORE"); ?>
                </a>
            </div>
            <div class="clr"></div>
        </div>
    <?php endif; ?>

    <?php if ($this->numberOfColumns == 1) : ?>
        <div class="seperator"></div>
    <?php endif; ?>

</div>
