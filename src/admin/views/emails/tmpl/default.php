<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016-2018 Open Source Training, LLC. All rights reserved
 * @license   GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

use Alledia\OSDownloads\Free\Factory;

defined('_JEXEC') or die;

JHtml::_('formbehavior.chosen', 'select');

$container = Factory::getContainer();

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
<form action="<?php echo $container->helperRoute->getAdminEmailListRoute(); ?>" method="post" name="adminForm" id="adminForm">
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
                                <input type="text" name="search" id="search"
                                       placeholder="<?php echo JText::_('JSEARCH_FILTER'); ?>"
                                       value="<?php echo htmlspecialchars($this->flt->search); ?>" class="text_area"
                                       onchange="doc.adminForm.submit();"/>
                                <button class="btn hasTooltip" title="" type="submit" data-original-title="Search">
                                    <?php echo JText::_('COM_OSDOWNLOADS_GO'); ?>
                                </button>
                            </div>
                            <div class="btn-wrapper">
                                <button
                                    onclick="doc.getElementById('search').value='';this.form.getElementById('cate_id').value='';this.form.submit();"
                                    class="btn hasTooltip js-stools-btn-clear">
                                    <?php echo JText::_('COM_OSDOWNLOADS_RESET'); ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </td>
                <td align="right">
                    <div class="js-stools clearfix">
                        <?php if ($this->isPro) : ?>
                            <?php echo $this->loadTemplate('pro_filters'); ?>
                        <?php endif; ?>

                        <?php echo category('cate_id', 'com_osdownloads', $this->flt->cate_id,
                            "onchange='this.form.submit();'", 'title', $size = 1, $sel_cat = 1); ?>
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
                <th class="has-context span6"><?php echo JHTML::_('grid.sort', 'COM_OSDOWNLOADS_EMAIL', 'email.email',
                        @$this->lists['order_Dir'], @$this->lists['order']); ?> </th>
                <?php if ($this->isPro) : ?>
                    <?php echo $this->loadTemplate('pro_headers'); ?>
                <?php endif; ?>
                <th><?php echo JHTML::_('grid.sort', 'COM_OSDOWNLOADS_FILE', 'doc.name', @$this->lists['order_Dir'],
                        @$this->lists['order']); ?> </th>
                <th><?php echo JHTML::_('grid.sort', 'COM_OSDOWNLOADS_CATEGORY', 'cat.title',
                        @$this->lists['order_Dir'],
                        @$this->lists['order']); ?> </th>
                <th><?php echo JHTML::_('grid.sort', 'COM_OSDOWNLOADS_DATE', 'email.downloaded_date',
                        @$this->lists['order_Dir'], @$this->lists['order']); ?> </th>
                <th class="hidden-phone center"><?php echo JHTML::_('grid.sort', 'COM_OSDOWNLOADS_ID', 'email.id',
                        @$this->lists['order_Dir'], @$this->lists['order']); ?></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($this->items as $i => $item) :
                ?>
                <tr class="row<?php echo $i % 2; ?>">
                    <td class="hidden-phone" width="1%"><?php echo JHTML::_('grid.id', $i, $item->id); ?></td>
                    <td class="has-context span6"><?php echo($item->email); ?></td>
                    <?php if ($this->isPro) : ?>
                        <?php
                        $this->item = $item;
                        echo $this->loadTemplate('pro_columns');
                        ?>
                    <?php endif; ?>
                    <td><?php echo($item->doc_name); ?></td>
                    <td class="small"><?php echo($item->cate_name); ?></td>
                    <td class="small"><?php echo(JHTML::_("date", $item->downloaded_date, "d-m-Y H:m:s")); ?></td>
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
        <?php echo JHTML::_('form.token'); ?>
    </div>
</form>

<?php echo $this->extension->getFooterMarkup(); ?>
