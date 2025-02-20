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

use Alledia\OSDownloads\Factory;
use Alledia\OSDownloads\Free\Joomla\Component\Site as FreeComponentSite;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Language;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

defined('_JEXEC') or die();

/**
 * @var OSDownloadsViewDownloads $this
 * @var string                   $template
 * @var string                   $layout
 * @var string                   $layoutTemplate
 * @var Language                 $lang
 * @var string                   $filetofind
 */

$this->app                    = Factory::getApplication();
$this->lang                   = Factory::getLanguage();
$this->container              = Factory::getPimpleContainer();
$this->params                 = $this->app->getParams();
$this->numberOfColumns        = (int)$this->params->get('number_of_column', 1);
$this->authorizedAccessLevels = Factory::getUser()->getAuthorisedViewLevels();
$this->itemId                 = $this->app->input->getInt('Itemid');
$this->id                     = $this->app->input->getInt('id');
$this->showModal              = false;
$this->component              = FreeComponentSite::getInstance();
$this->isPro                  = $this->component->isPro();
$this->version                = $this->component->getMediaVersion();

HTMLHelper::_('jquery.framework');

$options = ['version' => $this->version, 'relative' => true];

HTMLHelper::_('script', 'com_osdownloads/jquery.osdownloads.bundle.min.js', $options, []);

?>
<div class="contentopen osdownloads-container">
    <?php
    if (
        $this->params->get('show_page_heading')
        && ($heading = $this->params->get('page_heading'))
    ) :
        ?>
        <div class="page-header">
            <h1> <?php echo $this->escape($this->params->get('page_heading')); ?> </h1>
        </div>
    <?php endif;

    if ($this->showCategoryFilter && !empty($this->categories)) :
        ?>
        <div class="category_filter columns-<?php echo $this->numberOfColumns; ?>">
            <?php
            $i = 0;
            foreach ($this->categories as $category) :
                $column = $i % $this->numberOfColumns;

                if (in_array($category->access, $this->authorizedAccessLevels)) :
                    $classes = [
                        'column',
                        'column-' . $column,
                        'item' . $column,
                        'cate_' . $category->id,
                    ];
                    ?>
                    <div class="<?php echo join(' ', $classes); ?>">
                        <h3>
                            <?php
                            echo HTMLHelper::_(
                                'link',
                                Route::_(
                                    $this->container->helperRoute->getFileListRoute($category->id, $this->itemId)
                                ),
                                $category->title
                            );
                            ?>
                        </h3>
                        <div class="item_content">
                            <?php echo $category->description; ?>
                        </div>
                    </div>
                    <?php
                    if ($this->numberOfColumns && $column == $this->numberOfColumns - 1) : ?>
                        <div class="clr"></div>
                    <?php endif;
                    $i++;
                endif;
            endforeach;
            ?>
            <div class="clr"></div>
        </div>
    <?php endif;

    if (!empty($this->items)) :
        ?>
        <div class="items columns-<?php echo $this->numberOfColumns; ?>">
            <?php
            $i              = 0;
            foreach ($this->items as $file) :
                $column = $i % $this->numberOfColumns;
                $this->item = $file;
                $classes    = [
                    'column',
                    'column-' . $column,
                    'item' . $column,
                    'file_' . $this->item->id,
                ];
                ?>

                <div class="<?php echo join(' ', $classes); ?>">
                    <?php
                    $this->requireEmail = $this->item->require_user_email;
                    $this->requireAgree = (bool)$this->item->require_agree;

                    if (!$this->showModal) :
                        $this->showModal = $this->requireEmail || $this->requireAgree;
                    endif;

                    if (in_array($this->item->access, $this->authorizedAccessLevels)) :
                        echo $this->loadTemplate(($this->isPro ? 'pro' : 'free') . '_item');
                    endif;
                    ?>
                </div>

                <?php
                if ($this->numberOfColumns && $column == $this->numberOfColumns - 1) : ?>
                    <div class="clr"></div>
                <?php endif;
            endforeach;
            ?>
        </div>

    <?php else : ?>
        <div class="osd-alert">
            <?php echo Text::_('COM_OSDOWNLOADS_NO_DOWNLOADS'); ?>
        </div>
    <?php endif; ?>
    <div class="pagination">
        <div class="osdownloads-pages-counter"><?php echo $this->pagination->getPagesCounter(); ?></div>
        <div class="osdownloads-pagination"><?php echo $this->pagination->getPagesLinks(); ?></div>
    </div>
</div>
