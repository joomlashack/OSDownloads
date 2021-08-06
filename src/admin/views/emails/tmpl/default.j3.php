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

use Alledia\OSDownloads\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Language;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die();

/**
 * @var OSDownloadsViewEmails $this
 * @var string                $template
 * @var string                $layout
 * @var string                $layoutTemplate
 * @var Language              $lang
 * @var string                $filetofind
 */

HTMLHelper::_('formbehavior.chosen', 'select');

$container = Factory::getPimpleContainer();

$buttonAction = join('', [
    "doc.getElementById('search').value='';",
    "this.form.getElementById('cate_id').value='';",
    "this.form.submit();"
]);

$categorySelect = $this->categorySelect(
    'cate_id',
    'com_osdownloads',
    $this->flt->cate_id,
    'this.form.submit();'
);

?>
<form action="<?php echo $container->helperRoute->getAdminEmailListRoute(); ?>"
      method="post"
      name="adminForm"
      id="adminForm">
    <div id="j-sidebar-container" class="span2">
        <?php echo $this->sidebar; ?>
    </div>
    <div id="j-main-container" class="span10">
        <table style="width: 100%">
            <tr>
                <td>
                    <div class="js-stools clearfix">
                        <div class="clearfix">
                            <div class="btn-wrapper input-append">
                                <input type="text" name="search" id="search"
                                       placeholder="<?php echo Text::_('JSEARCH_FILTER'); ?>"
                                       value="<?php echo htmlspecialchars($this->flt->search); ?>" class="text_area"
                                       onchange="doc.adminForm.submit();"/>

                                <button class="btn hasTooltip" title="" type="submit" data-original-title="Search">
                                    <?php echo Text::_('COM_OSDOWNLOADS_GO'); ?>
                                </button>
                            </div>
                            <div class="btn-wrapper">
                                <button
                                    onclick="<?php echo $buttonAction; ?>"
                                    class="btn hasTooltip js-stools-btn-clear">
                                    <?php echo Text::_('COM_OSDOWNLOADS_RESET'); ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </td>
                <td style="text-align: right;">
                    <div class="js-stools clearfix">
                        <?php if ($this->isPro) : ?>
                            <?php echo $this->loadTemplate('pro_filters'); ?>
                        <?php endif; ?>

                        <?php echo $categorySelect; ?>
                    </div>
                </td>
            </tr>
        </table>
        <table class="adminlist table table-striped" width="100%" border="0">
            <thead>
            <tr>
                <th class="hidden-phone"><input type="checkbox" onclick="Joomla.checkAll(this)" title="check All"
                                                value=""
                                                name="checkall-toggle"/></th>
                <th class="has-context span6">
                    <?php echo HTMLHelper::_(
                        'grid.sort',
                        'COM_OSDOWNLOADS_EMAIL',
                        'email.email',
                        $this->lists['order_Dir'] ?? null,
                        $this->lists['order'] ?? null
                    ); ?>
                </th>
                <?php if ($this->isPro) : ?>
                    <?php echo $this->loadTemplate('pro_headers'); ?>
                <?php endif; ?>
                <th>
                    <?php echo HTMLHelper::_(
                        'grid.sort',
                        'COM_OSDOWNLOADS_FILE',
                        'doc.name',
                        $this->lists['order_Dir'] ?? null,
                        $this->lists['order'] ?? null
                    ); ?>
                </th>
                <th>
                    <?php echo HTMLHelper::_(
                        'grid.sort',
                        'COM_OSDOWNLOADS_CATEGORY',
                        'cat.title',
                        $this->lists['order_Dir'] ?? null,
                        $this->lists['order'] ?? null
                    ); ?>
                </th>
                <th>
                    <?php echo HTMLHelper::_(
                        'grid.sort',
                        'COM_OSDOWNLOADS_DATE',
                        'email.downloaded_date',
                        $this->lists['order_Dir'] ?? null,
                        $this->lists['order'] ?? null
                    ); ?>
                </th>
                <th class="hidden-phone center">
                    <?php echo HTMLHelper::_(
                        'grid.sort',
                        'COM_OSDOWNLOADS_ID',
                        'email.id',
                        $this->lists['order_Dir'] ?? null,
                        $this->lists['order'] ?? null
                    ); ?>
                </th>
            </tr>
            </thead>

            <tbody>
            <?php foreach ($this->items as $i => $item) : ?>
                <tr class="row<?php echo $i % 2; ?>">
                    <td class="hidden-phone" style="width: 1%;">
                        <?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
                    </td>
                    <td class="has-context span6"><?php echo($item->email); ?></td>
                    <?php if ($this->isPro) : ?>
                        <?php
                        $this->item = $item;
                        echo $this->loadTemplate('pro_columns');
                        ?>
                    <?php endif; ?>
                    <td><?php echo($item->doc_name); ?></td>
                    <td class="small"><?php echo($item->cate_name); ?></td>
                    <td class="small"><?php echo(HTMLHelper::_("date", $item->downloaded_date, "d-m-Y H:m:s")); ?></td>
                    <td class="hidden-phone center"><?php echo($item->id); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
            <tr>
                <td colspan="<?php echo $this->isPro ? 8 : 6; ?>">
                    <?php echo $this->pagination->getListFooter(); ?>
                </td>
            </tr>
            </tfoot>
        </table>
        <input type="hidden" name="option" value="com_osdownloads"/>
        <input type="hidden" name="task" value=""/>
        <input type="hidden" name="boxchecked" value="0"/>
        <input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>"/>
        <input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>"/>
        <?php echo HTMLHelper::_('form.token'); ?>
    </div>
</form>
