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

use Joomla\CMS\Button\PublishedButton;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Session\Session;
use Joomla\Utilities\ArrayHelper;

defined('_JEXEC') or die();

$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');
$saveOrder = $listOrder === 'doc.ordering';

if ($saveOrder && $this->items) {
    HTMLHelper::_('draggablelist.draggable');

    $saveOrderingUrl = 'index.php?' . http_build_query([
            'option'                => 'com_osdownloads',
            'task'                  => 'files.saveOrderAjax',
            'tmpl'                  => 'component',
            Session::getFormToken() => '1'
        ]);

    $bodyAttribs = ArrayHelper::toString([
        'class'          => 'js-draggable',
        'data-url'       => $saveOrderingUrl,
        'data-direction' => strtolower($listDirn),
        'data-nested'    => 'true'
    ]);
}
?>
<form action="index.php?option=com_osdownloads&view=files"
      method="post"
      name="adminForm"
      id="adminForm">
    <div class="row">
        <div class="col-md-12">
            <div id="j-main-container" class="j-main-container">
                <?php
                echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this]);
                ?>
                <?php if (empty($this->items)) : ?>
                    <div class="alert alert-info">
                        <span class="icon-info-circle" aria-hidden="true"></span>
                        <span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
                        <?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
                    </div>

                <?php else : ?>
                    <table class="adminlist table table-striped" id="documentList" style="width: 100%; border: none;">
                        <thead>
                        <tr>
                            <th scope="col" class="w-1 text-center d-none d-md-table-cell">
                                <?php echo HTMLHelper::_('grid.checkall'); ?>
                            </th>
                            <th scope="col" class="w-1 text-center d-none d-md-table-cell">
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
                            <th scope="col" class="w-1 text-center">
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
                            <th class="has-context w-50">
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
                            <th class="text-center text-nowrap">
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
                            <th class="text-center d-none d-md-table-cell">
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

                        <tbody <?php echo $bodyAttribs ?? ''; ?>>
                        <?php
                        foreach ($this->items as $i => $item) :
                            $link = 'index.php?option=com_osdownloads&task=file.edit&id=' . $item->id;
                            ?>
                            <tr class="<?php echo 'row' . ($i % 2); ?>"
                                data-draggable-group="<?php echo $item->cate_id; ?>">
                                <td class="text-center d-none d-md-table-cell">
                                    <?php echo HTMLHelper::_('grid.checkedout', $item, $i); ?>
                                </td>

                                <td class="text-center d-none d-md-table-cell">
                                    <?php
                                    $class = 'sortable-handler' . ($saveOrder ? '' : ' inactive');

                                    if (!$saveOrder) :
                                        $title = HTMLHelper::tooltipText('JORDERINGDISABLED');
                                    endif;
                                    ?>
                                    <span class="<?php echo $class; ?>" title="<?php echo $title ?? ''; ?>">
                                        <span class="icon-ellipsis-v"></span>
                                    </span>
                                    <?php if ($saveOrder) : ?>
                                        <input type="text"
                                               style="display:none"
                                               name="order[]"
                                               value="<?php echo $item->ordering; ?>"
                                               class="width-20 text-area-order "/>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php
                                    $options = [
                                        'task_prefix' => 'files.',
                                        'id'          => 'state-' . $item->id
                                    ];

                                    echo (new PublishedButton())->render(
                                        (int)$item->published,
                                        $i,
                                        $options,
                                        $item->publish_up ?? null,
                                        $item->publish_down ?? null
                                    );
                                    ?>
                                </td>
                                <td class="w-50 has-context">
                                    <?php echo HTMLHelper::_('link', $link, $item->name); ?>
                                    <span class="small">
                                        <?php echo Text::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?>
                                    </span>
                                    <div class="small">
                                        <?php echo Text::_('JCATEGORY') . ": " . $this->escape($item->cat_title); ?>
                                    </div>
                                </td>
                                <td>
                                    <?php echo($item->access_title); ?>
                                </td>
                                <td class="text-center text-nowrap"><?php echo($item->downloaded); ?></td>
                                <?php
                                if ($this->extension->isPro()) :
                                    $this->item = $item;
                                    echo $this->loadTemplate('pro_columns');
                                endif;
                                ?>
                                <td class="text-center d-none d-md-table-cell"><?php echo($item->id); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>

                    <?php
                    echo $this->pagination->getListFooter();
                endif; ?>

                <input type="hidden" name="task" value="">
                <input type="hidden" name="boxchecked" value="0">
                <?php echo HTMLHelper::_('form.token'); ?>
            </div>
        </div>
    </div>
</form>
