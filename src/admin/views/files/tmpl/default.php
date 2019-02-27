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

defined('_JEXEC') or die;

use Alledia\OSDownloads\Free\Factory;

JHtml::_('bootstrap.tooltip');
JHtml::_('formbehavior.chosen', 'select');

$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');
$saveOrder = $listOrder === 'doc.ordering asc';
$container = Factory::getContainer();

if ($saveOrder) {
    JHtml::_(
        'sortablelist.sortable',
        'documentList',
        'adminForm',
        $listDirn,
        $container->helperRoute->getAdminSaveOrderingRoute(),
        false,
        true
    );
}

function category($name, $extension, $selected = null, $javascript = null, $order = null, $size = 1, $sel_cat = 1)
{
    // Deprecation warning.
    JLog::add('JList::category is deprecated.', JLog::WARNING, 'deprecated');

    $categories = JHtml::_('category.options', $extension);
    if ($sel_cat) {
        array_unshift($categories, JHtml::_('select.option', '0', JText::_('JOPTION_SELECT_CATEGORY')));
    }

    $category = JHtml::_(
        'select.genericlist',
        $categories,
        $name,
        'class="inputbox chosen" size="' . $size . '" ' . $javascript,
        'value',
        'text',
        $selected
    );

    return $category;
}

?>
    <form action="<?php echo $container->helperRoute->getAdminMainViewRoute(); ?>" method="post" name="adminForm"
          id="adminForm">
        <?php
        // Load search tools
        $options = array(
            'orderFieldSelector' => '#filter_order'
        );
        JHtml::_('searchtools.form', '#adminForm', $options);
        ?>
        <div id="j-sidebar-container" class="span2">
            <?php echo $this->sidebar; ?>
        </div>
        <div id="j-main-container" class="span10">
            <table width="100%">
                <tr>
                    <td>
                        <div class="js-stools clearfix">
                            <div class="clearfix">
                                <div class="btn-wrapper input-append">
                                    <input type="text"
                                           name="search"
                                           id="search"
                                           placeholder="<?php echo JText::_('JSEARCH_FILTER'); ?>"
                                           value="<?php echo htmlspecialchars($this->state->get('filter.search')); ?>"
                                           class="text_area"
                                           onchange="document.adminForm.submit();"/>
                                    <button class="btn hasTooltip" title="" type="submit" data-original-title="Search">
                                        <?php echo JText::_('COM_OSDOWNLOADS_GO'); ?>
                                    </button>
                                </div>
                                <div class="btn-wrapper">
                                    <button
                                        onclick="document.getElementById('search').value='';this.form.getElementById('cate_id').value='';this.form.submit();"
                                        class="btn hasTooltip js-stools-btn-clear">
                                        <?php echo JText::_('COM_OSDOWNLOADS_RESET'); ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td align="right">
                        <div class="js-stools clearfix">
                            <?php
                            echo category(
                                'flt_cate_id',
                                'com_osdownloads',
                                $this->state->get('filter.category_id'),
                                "onchange='this.form.submit();'",
                                'title',
                                $size = 1,
                                $sel_cat = 1
                            );

                            echo JHtml::_(
                                'select.genericlist',
                                array(
                                    array('value' => 5, 'text' => '5'),
                                    array('value' => 10, 'text' => '10'),
                                    array('value' => 20, 'text' => '20'),
                                    array('value' => 25, 'text' => '25'),
                                    array('value' => 50, 'text' => '50'),
                                    array('value' => 100, 'text' => '100')
                                ),
                                'limit',
                                'class="inputbox chosen inputbox input-mini" size="' . 5 . '" onchange="this.form.submit();"',
                                'value',
                                'text',
                                $this->state->get('list.limit')
                            );
                            ?>
                        </div>
                    </td>
                </tr>
            </table>
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
                    $item->checked_out = false;
                    $checked = JHtml::_('grid.checkedout', $item, $i);
                    $canChange = true;
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
                            <a href="<?php echo $container->helperRoute->getAdminFileFormRoute($item->id); ?>"><?php echo($item->name); ?></a>
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

            <input type="hidden" name="option" value="com_osdownloads"/>
            <input type="hidden" name="task" value=""/>
            <input type="hidden" name="boxchecked" value="0"/>
            <input type="hidden" name="filter_order" id="filter_order" value="<?php echo $listOrder; ?>"/>
            <input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
            <?php echo JHtml::_('form.token'); ?>
        </div>
    </form>
<?php
echo $this->extension->getFooterMarkup();

