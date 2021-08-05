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

$container = Factory::getPimpleContainer();
$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');
$saveOrder = $listOrder === 'doc.ordering';
?>
    <form action="<?php echo $container->helperRoute->getAdminMainViewRoute(); ?>" method="post" name="adminForm" id="adminForm">
        <div class="row">
            <div class="col-md-12">
                <div id="j-main-container" class="j-main-container">
                    <?php
                    // Search tools bar
                    echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this));
                    ?>
                    <?php if (empty($this->items)) : ?>
                        <div class="alert alert-info">
                            <span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo JText::_('INFO'); ?></span>
                            <?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
                        </div>
                    <?php else : ?>

                        <table class="adminlist table table-striped" id="documentList" width="100%" border="0">
                            <thead>
                            <tr>
                                <th width="1%" class="nowrap center hidden-phone">
                                    <?php
                                    echo JHtml::_(
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
                                <th width="1%" class="hidden-phone">
                                    <input type="checkbox"
                                           onclick="Joomla.checkAll(this)"
                                           title="<?php echo JText::_('COM_OSDOWNLOADS_CHECK_All'); ?>"
                                           value=""
                                           name="checkall-toggle"/>
                                </th>
                                <th width="1%" style="min-width:55px" class="nowrap center">
                                    <?php
                                    echo JHtml::_(
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
                                    echo JHtml::_(
                                        'searchtools.sort',
                                        'COM_OSDOWNLOADS_NAME',
                                        'doc.name',
                                        $listDirn,
                                        $listOrder
                                    );
                                    ?>
                                </th>
                                <th class="">
                                    <?php
                                    echo JHtml::_(
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
                                    echo JHtml::_(
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
                                    echo JHtml::_(
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
                                $link = 'index.php?option=com_osdownloads&view=file&id=' . $item->id;

                                $item->checked_out = false;
                                $checked           = JHtml::_('grid.checkedout', $item, $i);
                                $canChange         = true;
                                ?>
                                <tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php echo $item->cate_id; ?>">
                                    <td class="order nowrap center hidden-phone">
                                        <?php
                                        $iconClass = '';
                                        if (!$canChange) {
                                            $iconClass = ' inactive';
                                        } elseif (!$saveOrder) {
                                            $iconClass = ' inactive tip-top hasTooltip" title="' . JHtml::tooltipText('JORDERINGDISABLED');
                                        }
                                        ?>
                                        <span class="sortable-handler<?php echo $iconClass ?>">
                            <i class="icon-menu"></i>
                        </span>
                                        <?php
                                        if ($canChange && $saveOrder) :
                                            ?>
                                            <input type="text"
                                                   style="display:none"
                                                   name="order[]"
                                                   size="5"
                                                   value="<?php echo $item->ordering; ?>"
                                                   class="width-20 text-area-order "/>
                                        <?php
                                        endif;
                                        ?>
                                    </td>
                                    <td class="hidden-phone"><?php echo $checked; ?></td>
                                    <td class="center">
                                        <div class="btn-group">
                                            <?php
                                            echo JHtml::_(
                                                'jgrid.published',
                                                $item->published,
                                                $i,
                                                'files.',
                                                $canChange,
                                                'cb',
                                                @$item->publish_up,
                                                @$item->publish_down
                                            );
                                            ?>
                                        </div>
                                    </td>
                                    <td class="has-context span6">
                                        <?php echo JHtml::_('link', $link, $item->name); ?>
                                        <span class="small">
                                <?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?>
                            </span>
                                        <div class="small">
                                            <?php echo JText::_('JCATEGORY') . ": " . $this->escape($item->cat_title); ?>
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
                            <?php
                            endforeach;
                            ?>
                            </tbody>
                            <tfoot>
                            <tr>
                                <?php
                                $colspan = 7;
                                if ($this->extension->isPro()) :
                                    $colspan += 2;
                                endif;
                                ?>
                                <td colspan="<?php echo $colspan; ?>">
                                    <?php echo $this->pagination->getListFooter(); ?>
                                </td>
                            </tr>
                            </tfoot>
                        </table>

                    <?php endif; ?>

                    <input type="hidden" name="task" value="">
                    <input type="hidden" name="boxchecked" value="0">
                    <?php echo HTMLHelper::_('form.token'); ?>
                </div>
            </div>
        </div>
    </form>
<?php
echo $this->extension->getFooterMarkup();
