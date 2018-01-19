<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016-2017 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');

use Alledia\Framework\Helper as AllediaHelper;
use Alledia\OSDownloads\Free\Factory;
use Alledia\OSDownloads\Free\Joomla\Component\Site as FreeComponentSite;

$this->app                    = Factory::getApplication();
$this->lang                   = Factory::getLanguage();
$this->container              = Factory::getContainer();
$this->params                 = $this->app->getParams();
$this->numberOfColumns        = (int)$this->params->get("number_of_column", 1);
$this->authorizedAccessLevels = Factory::getUser()->getAuthorisedViewLevels();
$this->itemId                 = $this->app->input->getInt('Itemid');
$this->id                     = $this->app->input->getInt('id');
$this->showModal              = false;
$this->component              = FreeComponentSite::getInstance();
$this->isPro                  = $component->isPro();
$this->version                = $component->getMediaVersion();

JHtml::_('jquery.framework');

$options = array('version' => $this->version, 'relative' => true);

JHtml::_('script', 'com_osdownloads/jquery.osdownloads.bundle.min.js', $options, array());

?>

<div class="contentopen osdownloads-container">
    <?php if ($this->showCategoryFilter && !empty($this->categories)) : ?>
        <div class="category_filter columns-<?php echo $this->numberOfColumns; ?>">
            <?php
            $i = 0;
            foreach ($this->categories as $category) : ?>
                <?php if (in_array($category->access, $this->authorizedAccessLevels)) : ?>
                    <?php $column = $i % $this->numberOfColumns; ?>

                    <div class="column column-<?php echo $column; ?> item<?php echo $column; ?> cate_<?php echo $category->id; ?>">
                        <h3>
                            <a href="<?php echo JRoute::_($this->container->helperRoute->getFileListRoute($category->id, $this->itemId)); ?>">
                                <?php echo $category->title; ?>
                            </a>
                        </h3>
                        <div class="item_content">
                            <?php echo $category->description; ?>
                        </div>
                    </div>
                    <?php if ($this->numberOfColumns && $i % $this->numberOfColumns == $this->numberOfColumns - 1) : ?>
                        <div class="seperator"></div>
                        <div class="clr"></div>
                    <?php endif; ?>
                    <?php $i++; ?>
                <?php endif; ?>
            <?php endforeach; ?>
            <div class="clr"></div>
        </div>
    <?php endif; ?>

    <?php if (!empty($this->items)) : ?>
        <div class="items columns-<?php echo $this->numberOfColumns; ?>">
            <?php foreach ($this->items as $file) : ?>
                <?php
                $column = $i % $this->numberOfColumns;
                $this->item = $file;
                ?>

                <div class="column column-<?php echo $column; ?> item<?php echo $column; ?> file_<?php echo $this->item->id; ?>">
                    <?php
                    $this->requireEmail = $this->item->require_user_email;
                    $this->requireAgree = (bool) $this->item->require_agree;
                    $this->requireShare = (bool) @$this->item->require_share;

                    if (!$this->showModal) {
                        $this->showModal = $this->requireEmail || $this->requireAgree || $this->requireShare;
                    }

                    if (in_array($this->item->access, $this->authorizedAccessLevels)) :
                        echo $this->loadTemplate(($this->isPro ? 'pro' : 'free') . '_item');
                    endif;
                    ?>
                </div>

                <?php if ($this->numberOfColumns && $i % $this->numberOfColumns == $this->numberOfColumns - 1) : ?>
                    <div class="seperator"></div>
                    <div class="clr"></div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    <?php else : ?>
        <div class="osd-alert">
            <?php echo JText::_('COM_OSDOWNLOADS_NO_DOWNLOADS'); ?>
        </div>
    <?php endif; ?>

    <div class="clr"></div>
    <div class="osdownloads-pages-counter"><?php echo $this->pagination->getPagesCounter(); ?></div>
    <div class="osdownloads-pagination"><?php echo $this->pagination->getPagesLinks(); ?></div>
</div>
