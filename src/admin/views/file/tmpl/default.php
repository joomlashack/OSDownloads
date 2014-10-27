<?php
/**
 * @package   OSDownloads
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2014 Alledia.com, All rights reserved
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

JHtml::_('behavior.tooltip');

JFilterOutput::objectHTMLSafe($this->item);
$editor = JFactory::getEditor();

$index = strpos($this->item->file_path, "_");
$realname = substr($this->item->file_path, $index + 1);

    function category($name, $extension, $selected = null, $javascript = null, $order = null, $size = 1, $sel_cat = 1)
    {
        // Deprecation warning.
        JLog::add('JList::category is deprecated.', JLog::WARNING, 'deprecated');

        $categories = JHtml::_('category.options', $extension);
        if ($sel_cat) {
            array_unshift($categories, JHtml::_('select.option', '0', JText::_('JOPTION_SELECT_CATEGORY')));
        }

        $category = JHtml::_(
            'select.genericlist', $categories, $name, 'class="inputbox" size="' . $size . '" ' . $javascript, 'value', 'text',
            $selected
        );

        return $category;
    }

?>
<form action="<?php echo(JRoute::_("index.php?option=com_osdownloads"));?>" method="post" name="adminForm" enctype="multipart/form-data" id="adminForm">
    <table width="100%">
        <tr>
            <td width="90"><?php echo(JText::_("COM_OSDOWNLOADS_NAME"));?></td>
            <td><input type="text" name="name" value="<?php echo $this->item->name; ?>" style="width:200px" /></td>
        </tr>
        <tr>
            <td><?php echo(JText::_("COM_OSDOWNLOADS_ALIAS"));?></td>
            <td><input type="text" name="alias" value="<?php echo $this->item->alias; ?>" style="width:200px" /></td>
        </tr>
        <tr>
            <td valign="top"><?php echo(JText::_("COM_OSDOWNLOADS_UPLOAD_FILE"));?></td>
            <td>
                <?php if ($this->item->file_path):?>
                    <div><?php echo($realname);?></div>
                    <input type="hidden" name="old_file" value="<?php echo($this->item->file_path);?>" />
                <?php endif;?>
                <input type="file" name="file" />
            </td>
        </tr>
        <tr>
            <td><?php echo(JText::_("COM_OSDOWNLOADS_FILE_URL"));?></td>
            <td><input type="text" name="file_url" value="<?php echo $this->item->file_url; ?>" style="width:200px" /></td>
        </tr>
        <tr>
            <td><?php echo(JText::_("COM_OSDOWNLOADS_CATEGORY"));?></td>
            <td>
                  <?php echo category('cate_id', 'com_osdownloads', $this->item->cate_id, $javascript = null, 'title', $size = 1, $sel_cat = 1); ?>
            </td>
        </tr>
        <tr>
            <td><?php echo JText::_('COM_OSDOWNLOADS_ACCESS_LEVEL'); ?></td>
            <td>
                <?php echo JHtml::_('access.level', 'access', $this->item->access, null, array(), 'access'); ?>
            </td>
        </tr>
        <tr>
            <td valign="top"><?php echo(JText::_("COM_OSDOWNLOADS_DESCRIPTION_1h"));?></td>
            <td><?php echo $editor->display( 'description_1',  $this->item->description_1 , '100%', '200', '75', '20' ); ?></td>
        </tr>
        <tr>
            <td valign="top"><?php echo(JText::_("COM_OSDOWNLOADS_DESCRIPTION_2h"));?></td>
            <td><?php echo $editor->display( 'description_2',  $this->item->description_2 , '100%', '200', '75', '20' ); ?></td>
        </tr>
        <tr>
            <td valign="top"><?php echo(JText::_("COM_OSDOWNLOADS_DESCRIPTION_3h"));?></td>
            <td><?php echo $editor->display( 'description_3',  $this->item->description_3 , '100%', '200', '75', '20' ); ?></td>
        </tr>
        <tr>
            <td><?php echo(JText::_("COM_OSDOWNLOADS_EMAIL"));?> </td>
            <td>
                <input type="checkbox" name="show_email" value="1" <?php if ($this->item->show_email) echo('checked="checked"');?> />
                <?php echo(JText::_("COM_OSDOWNLOADS_SHOW_EMAIL_ONLY"));?>
                <input type="checkbox" name="require_email" value="1" <?php if ($this->item->require_email) echo('checked="checked"');?> />
                <?php echo(JText::_("COM_OSDOWNLOADS_REQUIRE_EMAIL"));?> (<i><?php echo(JText::_("COM_OSDOWNLOADS_REQUIRE_EMAIL_MEANS_SHOW_EMAIL_TOO"));?></i>)
            </td>
        </tr>
        <tr>
            <td><?php echo(JText::_("COM_OSDOWNLOADS_REQUIRE_AGREEMENT"));?></td>
            <td><input type="checkbox" name="require_agree" value="1" <?php if ($this->item->require_agree) echo('checked="checked"');?> />
                <?php echo(JHTML::_('tooltip', strip_tags($this->escape(JText::_("COM_OSDOWNLOADS_AGREEMENT_TIP"))) ));?>
            </td>
        </tr>
        <tr>
            <td><?php echo(JText::_("COM_OSDOWNLOADS_DOWNLOAD_TEXT"));?></td>
            <td><input type="text" name="download_text" value="<?php echo $this->item->download_text; ?>" /></td>
        </tr>
        <tr>
            <td><?php echo(JText::_("COM_OSDOWNLOADS_DOWNLOAD_COLOR"));?></td>
            <td><input type="text" name="download_color" value="<?php echo $this->item->download_color; ?>" /></td>
        </tr>
        <tr>
            <td><?php echo(JText::_("COM_OSDOWNLOADS_DOCUMENTATION_LINK"));?></td>
            <td>
                <input type="text" name="documentation_link" value="<?php echo $this->item->documentation_link; ?>" style="width:500px" />
                <?php echo(JHTML::_('tooltip', strip_tags($this->escape(JText::_("COM_OSDOWNLOADS_LINK_TIP"))) ));?>
            </td>
        </tr>
        <tr>
            <td><?php echo(JText::_("COM_OSDOWNLOADS_DEMO_LINK"));?></td>
            <td><input type="text" name="demo_link" value="<?php echo $this->item->demo_link; ?>" style="width:500px" />
                <?php echo(JHTML::_('tooltip', strip_tags($this->escape(JText::_("COM_OSDOWNLOADS_LINK_TIP"))) ));?>
            </td>
        </tr>
        <tr>
            <td><?php echo(JText::_("COM_OSDOWNLOADS_SUPPORT_LINK"));?></td>
            <td><input type="text" name="support_link" value="<?php echo $this->item->support_link; ?>" style="width:500px" />
                <?php echo(JHTML::_('tooltip', strip_tags($this->escape(JText::_("COM_OSDOWNLOADS_LINK_TIP"))) ));?>
            </td>
        </tr>
        <tr>
            <td><?php echo(JText::_("COM_OSDOWNLOADS_OTHER"));?></td>
            <td>
                Name <input type="text" name="other_name" value="<?php echo $this->item->other_name; ?>" style="width:150px" />
                Link <input type="text" name="other_link" value="<?php echo $this->item->other_link; ?>" style="width:500px" />
                <?php echo(JHTML::_('tooltip', strip_tags($this->escape(JText::_("COM_OSDOWNLOADS_OTHER_TIP"))) ));?>
            </td>
        </tr>
        <tr>
            <td><?php echo(JText::_("COM_OSDOWNLOADS_DIRECT_PAGE"));?></td>
            <td><input type="text" name="direct_page" value="<?php echo $this->item->direct_page; ?>" style="width:500px" />
            </td>
        </tr>
        <tr>
            <td><?php echo(JText::_("COM_OSDOWNLOADS_PUBLISHED"));?></td>
            <td>
                <input type="radio" name="published" value="1" <?php if ($this->item->published) echo('checked="checked"');?> /> <?php echo(JText::_("COM_OSDOWNLOADS_YES"));?>
                <input type="radio" name="published" value="0" <?php if (!$this->item->published) echo('checked="checked"');?> /><?php echo(JText::_("COM_OSDOWNLOADS_NO"));?></td>
        </tr>
        <tr>
            <td><?php echo(JText::_("COM_OSDOWNLOADS_EXTERNAL_REFERENCE"));?></td>
            <td><input type="text" name="external_ref" value="<?php echo $this->item->external_ref; ?>" style="width:500px" />
            </td>
        </tr>
    </table>
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="option" value="com_osdownloads" />
    <input type="hidden" name="id" value="<?php echo $this->item->id; ?>" />
    <input type="hidden" name="cid[]" value="<?php echo $this->item->id; ?>" />
    <?php echo JHTML::_( 'form.token' ); ?>
</form>
