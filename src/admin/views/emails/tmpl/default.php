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

use Alledia\OSDownloads\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

defined('_JEXEC') or die();

$container = Factory::getPimpleContainer();

$listOrder     = $this->state->get('list.ordering');
$listDirection = $this->state->get('list.direction');

?>
<form action="<?php echo $container->helperRoute->getAdminEmailListRoute(); ?>" method="post" name="adminForm"
      id="adminForm">
    <div class="row">
        <div class="col-md-12">
            <div id="j-main-container" class="j-main-container">
                <?php
                echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this]);

                if ($this->items) :
                    ?>
                    <table class="adminlist table table-striped" style="width: 100%; border: none;">
                        <thead>
                        <tr>
                            <th scope="col" class="w-1 text-center d-none d-md-table-cell">
                                <?php echo HTMLHelper::_('grid.checkall'); ?>
                            </th>
                            <th class="has-context w-50">
                                <?php echo HTMLHelper::_(
                                    'searchtools.sort',
                                    'COM_OSDOWNLOADS_EMAIL',
                                    'email.email',
                                    $listDirection,
                                    $listOrder
                                ); ?>
                            </th>

                            <?php if ($this->extension->isPro()) : ?>
                                <?php echo $this->loadTemplate('pro_headers'); ?>
                            <?php endif; ?>

                            <th>
                                <?php echo HTMLHelper::_(
                                    'searchtools.sort',
                                    'COM_OSDOWNLOADS_FILE',
                                    'doc.name',
                                    $listDirection,
                                    $listOrder
                                ); ?>
                            </th>
                            <th>
                                <?php echo HTMLHelper::_(
                                    'searchtools.sort',
                                    'COM_OSDOWNLOADS_CATEGORY',
                                    'cat.title',
                                    $listDirection,
                                    $listOrder
                                ); ?>
                            </th>
                            <th>
                                <?php echo HTMLHelper::_(
                                    'searchtools.sort',
                                    'COM_OSDOWNLOADS_DATE',
                                    'email.downloaded_date',
                                    $listDirection,
                                    $listOrder
                                ); ?>
                            </th>
                            <th class="text-center d-none d-md-table-cell">
                                <?php echo HTMLHelper::_(
                                    'searchtools.sort',
                                    'COM_OSDOWNLOADS_ID',
                                    'email.id',
                                    $listDirection,
                                    $listOrder
                                ); ?>
                            </th>
                        </tr>
                        </thead>

                        <tbody>
                        <?php foreach ($this->items as $i => $item) :
                            ?>
                            <tr class="row<?php echo $i % 2; ?>">
                                <td class="w-1 d-none d-md-table-cell">
                                    <?php echo HTMLHelper::_('grid.checkedout', $item, $i); ?>
                                </td>
                                <td class="has-context w-50"><?php echo($item->email); ?></td>
                                <?php if ($this->extension->isPro()) : ?>
                                    <?php
                                    $this->item = $item;
                                    echo $this->loadTemplate('pro_columns');
                                    ?>
                                <?php endif; ?>
                                <td><?php echo($item->doc_name); ?></td>
                                <td><?php echo($item->cate_name); ?></td>
                                <td>
                                    <?php echo HTMLHelper::_(
                                        'date',
                                        $item->downloaded_date,
                                        'd-m-Y H:m:s'
                                    ); ?>
                                </td>
                                <td class="text-center d-none d-md-table-cell"><?php echo($item->id); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>

                    <?php
                    echo $this->pagination->getListFooter();

                else : ?>
                    <div class="alert alert-info">
                        <span class="icon-info-circle"></span>
                        <?php
                        if ($this->activeFilters || $this->state->get('filter.search')) :
                            echo Text::_('COM_OSDOWNLOADS_EMAILS_NO_RESULTS');
                        else :
                            echo Text::_('COM_OSDOWNLOADS_EMAILS_NONE');
                        endif;
                        ?>
                    </div>
                <?php endif; ?>

                <input type="hidden" name="task" value=""/>
                <input type="hidden" name="boxchecked" value="0"/>
                <?php echo HTMLHelper::_('form.token'); ?>
            </div>
        </div>
    </div>
</form>
