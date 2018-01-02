<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016-2017 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>

<div class="item_<?php echo $this->file->id; ?>">
    <h3>
        <a href="<?php echo JRoute::_($this->container->helperRoute->getViewItemRoute($this->file->id, $this->itemId)); ?>">
            <?php echo $this->file->name; ?>
        </a>
    </h3>

    <div class="item_content"><?php echo $this->file->brief; ?></div>

    <?php if ($this->params->get('show_download_button', 0)) : ?>
        <div class="osdownloadsactions">
            <div class="btn_download">
                <?php
                $fileURL = JRoute::_($this->container->helperRoute->getViewItemRoute($this->file->id, $this->itemId));
                $link    = JRoute::_($this->container->helperRoute->getFileDownloadContentRoute($this->file->id, $this->itemId));
                ?>
                <a
                    href="<?php echo $link; ?>"
                    class="osdownloadsDownloadButton"
                    style="background:<?php echo $this->file->download_color; ?>"
                    data-direct-page="<?php echo $this->file->direct_page; ?>"
                    data-require-email="<?php echo $this->requireEmail; ?>"
                    data-require-agree="<?php echo $this->requireAgree ? 1 : 0; ?>"
                    data-require-share="<?php echo $this->requireShare ? 1 : 0; ?>"
                    data-id="<?php echo $this->file->id; ?>"
                    data-url="<?php echo $fileURL; ?>"
                    data-lang="<?php echo $this->lang->getTag(); ?>"
                    data-name="<?php echo $this->file->name; ?>"
                    data-agreement-article="<?php echo $this->file->agreementLink; ?>"
                    <?php if ($this->isPro) : ?>
                        data-hashtags="<?php echo str_replace('#', '', @$this->file->twitter_hashtags); ?>"
                        data-via="<?php echo str_replace('@', '', @$this->file->twitter_via); ?>"
                    <?php endif; ?>
                    >
                    <span>
                        <?php echo $this->params->get('link_label', JText::_('COM_OSDOWNLOADS_DOWNLOAD')); ?>
                    </span>
                </a>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($this->params->get('show_readmore_button', 1)) : ?>
        <div class="osdownloads-readmore-wrapper readmore_wrapper">
            <div class="osdownloads-readmore readmore">
                <a href="<?php echo JRoute::_($this->container->helperRoute->getViewItemRoute($this->file->id, $this->itemId)); ?>">
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
