<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2005-2021 Joomlashack.com. All rights reserved
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

use Alledia\OSDownloads\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('formbehavior.chosen', 'select');

$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');
$saveOrder = $listOrder === 'doc.ordering';
$container = Factory::getPimpleContainer();

if ($saveOrder) :
    HTMLHelper::_(
        'sortablelist.sortable',
        'documentList',
        'adminForm',
        $listDirn,
        $container->helperRoute->getAdminSaveOrderingRoute(),
        false,
        true
    );
endif;

?>
<form action="<?php echo $container->helperRoute->getAdminMainViewRoute(); ?>"
      method="post"
      name="adminForm"
      id="adminForm">
    <div id="j-sidebar-container" class="span2">
        <?php echo $this->sidebar; ?>
    </div>
    <div id="j-main-container" class="span10">
        <?php
        echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this]);

        if (!$this->items) : ?>
            <div class="alert alert-no-items">
                <?php
                if ($this->activeFilters || $this->state->get('filter.search')) :
                    echo Text::_('COM_OSDOWNLOADS_FILES_NO_RESULTS');
                else :
                    echo Text::_('COM_OSDOWNLOADS_FILES_CREATE');
                endif;
                ?>
            </div>
        <?php else : ?>
            <table class="adminlist table table-striped" id="documentList" style="width:100%; border: none;">
                <thead>
                <tr>
                    <th style="width: 1%;" class="nowrap center hidden-phone">
                        <?php
                        echo HTMLHelper::_(
                            'searchtools.sort',
                            '',
                            'doc.ordering',
                            $listDirn,
                            $listOrder,
                            null,
                            'asc',
                            'JGRID_HEADING_ORDERING',
                            'icon-menu-2'
                        );
                        ?>
                    </th>
                    <th style="width: 1%;" class="hidden-phone">
                        <input type="checkbox"
                               onclick="Joomla.checkAll(this)"
                               title="<?php echo Text::_('COM_OSDOWNLOADS_CHECK_All'); ?>"
                               value=""
                               name="checkall-toggle"/>
                    </th>
                    <th style="width: 1%; min-width:55px" class="nowrap center">
                        <?php
                        echo HTMLHelper::_(
                            'searchtools.sort',
                            'COM_OSDOWNLOADS_PUBLISHED',
                            'doc.published',
                            $listDirn,
                            $listOrder
                        );
                        ?>
                    </th>
                    <th class="has-context span6">
                        <?php
                        echo HTMLHelper::_(
                            'searchtools.sort',
                            'COM_OSDOWNLOADS_NAME',
                            'doc.name',
                            $listDirn,
                            $listOrder
                        );
                        ?>
                    </th>
                    <th>
                        <?php
                        echo HTMLHelper::_(
                            'searchtools.sort',
                            'COM_OSDOWNLOADS_ACCESS',
                            'doc.access',
                            $listDirn,
                            $listOrder
                        );
                        ?>
                    </th>
                    <th class="center nowrap">
                        <?php
                        echo HTMLHelper::_(
                            'searchtools.sort',
                            'COM_OSDOWNLOADS_DOWNLOADED',
                            'doc.downloaded',
                            $listDirn,
                            $listOrder
                        );
                        ?>
                    </th>
                    <?php
                    if ($this->extension->isPro()) :
                        echo $this->loadTemplate('pro_headers');
                    endif;
                    ?>
                    <th class="hidden-phone center">
                        <?php
                        echo HTMLHelper::_(
                            'searchtools.sort',
                            'COM_OSDOWNLOADS_ID',
                            'doc.id',
                            $listDirn,
                            $listOrder
                        );
                        ?>
                    </th>
                </tr>
                </thead>

                <tbody>
                <?php
                foreach ($this->items as $i => $item) :
                    $link = 'index.php?option=com_osdownloads&task=file.edit&id=' . $item->id;

                    $item->checked_out = false;
                    $checked           = HTMLHelper::_('grid.checkedout', $item, $i);
                    ?>
                    <tr class="<?php echo 'row' . ($i % 2); ?>"
                        sortable-group-id="<?php echo $item->cate_id; ?>">
                        <td class="order nowrap center hidden-phone">
                            <?php
                            $class = 'sortable-handler' . ($saveOrder ? '' : ' inactive');

                            if (!$saveOrder) :
                                $class .= ' tip-top hasTooltip';
                                $title = HTMLHelper::tooltipText('JORDERINGDISABLED');
                            endif;
                            ?>
                            <span class="<?php echo $class; ?>" title="<?php echo $title ?? ''; ?>">
                            <i class="icon-menu"></i>
                        </span>
                            <?php if ($saveOrder) : ?>
                                <input type="text"
                                       style="display:none"
                                       name="order[]"
                                       value="<?php echo $item->ordering; ?>"
                                       class="width-20 text-area-order "/>
                            <?php endif; ?>
                        </td>
                        <td class="hidden-phone"><?php echo $checked; ?></td>
                        <td class="center">
                            <div class="btn-group">
                                <?php
                                echo HTMLHelper::_(
                                    'jgrid.published',
                                    $item->published,
                                    $i,
                                    'files.',
                                    true,
                                    'cb',
                                    $item->publish_up ?? null,
                                    $item->publish_down ?? null
                                );
                                ?>
                            </div>
                        </td>
                        <td class="has-context span6">
                            <?php echo HTMLHelper::_('link', $link, $item->name); ?>
                            <span class="small">
                                <?php echo Text::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?>
                            </span>
                            <div class="small">
                                <?php echo sprintf(
                                    '%s: %s',
                                    Text::_('JCATEGORY'),
                                    $this->escape($item->cat_title)
                                ); ?>
                            </div>
                        </td>
                        <td class="small">
                            <?php echo($item->access_title); ?>
                        </td>
                        <td class="center nowrap"><?php echo($item->downloaded); ?></td>

                        <?php
                        if ($this->extension->isPro()) :
                            $this->item = $item;
                            echo $this->loadTemplate('pro_columns');
                        endif;
                        ?>
                        <td class="hidden-phone center"><?php echo($item->id); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot>
                <tr>
                    <?php
                    $colspan = $this->extension->isPro() ? 9 : 7;
                    ?>
                    <td colspan="<?php echo $colspan; ?>">
                        <?php echo $this->pagination->getListFooter(); ?>
                    </td>
                </tr>
                </tfoot>
            </table>
        <?php endif; ?>
    </div>
    <input type="hidden" name="task" value=""/>
    <input type="hidden" name="boxchecked" value="0"/>
    <?php echo HTMLHelper::_('form.token'); ?>
</form>

