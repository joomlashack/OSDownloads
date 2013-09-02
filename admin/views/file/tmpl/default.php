<?php
/**
 * @version 1.0.0
 * @author Open Source Training (www.ostraining.com)
 * @copyright (C) 2011- Open Source Training
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
**/
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

JHtml::_('behavior.tooltip');

JFilterOutput::objectHTMLSafe($this->item); 
$editor = &JFactory::getEditor(); 

$index = strpos($this->item->file_path, "_");
$realname = substr($this->item->file_path, $index + 1);
?>
<form action="index.php?option=com_osdownloads" method="post" name="adminForm" enctype="multipart/form-data">
	<table width="100%">
    	<tr>
        	<td width="90"><?php echo(JText::_("Name"));?></td>
        	<td><input type="text" name="name" value="<?php echo $this->item->name; ?>" style="width:200px" /></td>
        </tr>
    	<tr>
        	<td><?php echo(JText::_("Alias"));?></td>
        	<td><input type="text" name="alias" value="<?php echo $this->item->alias; ?>" style="width:200px" /></td>
        </tr>
    	<tr>
        	<td valign="top"><?php echo(JText::_("Upload file"));?></td>
        	<td>
            	<?php if ($this->item->file_path):?>
                	<div><?php echo($realname);?></div>
                    <input type="hidden" name="old_file" value="<?php echo($this->item->file_path);?>" />
                <?php endif;?>
            	<input type="file" name="file" />
            </td>
        </tr>
    	<tr>
        	<td><?php echo(JText::_("Category"));?></td>
        	<td>
            	<?php echo(JHTML::_('list.category',  'cate_id', 'com_osdownloads', $this->item->cate_id));?>
            </td>
        </tr>
    	<tr>
        	<td valign="top"><?php echo(JText::_("Description 1"));?></td>
        	<td><?php echo $editor->display( 'description_1',  $this->item->description_1 , '100%', '200', '75', '20' ); ?></td>
        </tr>
    	<tr>
        	<td valign="top"><?php echo(JText::_("Description 2"));?></td>
        	<td><?php echo $editor->display( 'description_2',  $this->item->description_2 , '100%', '200', '75', '20' ); ?></td>
        </tr>
    	<tr>
        	<td valign="top"><?php echo(JText::_("Description 3"));?></td>
        	<td><?php echo $editor->display( 'description_3',  $this->item->description_3 , '100%', '200', '75', '20' ); ?></td>
        </tr>
    	<tr>
        	<td><?php echo(JText::_("Email"));?> </td>
        	<td>
            	<input type="checkbox" name="show_email" value="1" <?php if ($this->item->show_email) echo('checked="checked"');?> />
	            <?php echo(JText::_("Show email only"));?>
            	<input type="checkbox" name="require_email" value="1" <?php if ($this->item->require_email) echo('checked="checked"');?> />
	            <?php echo(JText::_("Require email"));?> (<i><?php echo(JText::_("Require email means show email too"));?></i>)
            </td>
        </tr>
    	<tr>
        	<td><?php echo(JText::_("Require agreement"));?></td>
        	<td><input type="checkbox" name="require_agree" value="1" <?php if ($this->item->require_agree) echo('checked="checked"');?> />
            	<?php echo(JHTML::_('tooltip', strip_tags($this->escape(JText::_("COM_OSDOWNLOADS_AGREEMENT_TIP"))) ));?>
            </td>
        </tr> 
    	<tr>
        	<td><?php echo(JText::_("Download text"));?></td>
        	<td><input type="text" name="download_text" value="<?php echo $this->item->download_text; ?>" /></td>
        </tr>
    	<tr>
        	<td><?php echo(JText::_("Download color"));?></td>
        	<td><input type="text" name="download_color" value="<?php echo $this->item->download_color; ?>" /></td>
        </tr>
    	<tr>
        	<td><?php echo(JText::_("Documentation link"));?></td>
        	<td>
            	<input type="text" name="documentation_link" value="<?php echo $this->item->documentation_link; ?>" style="width:500px" />
                <?php echo(JHTML::_('tooltip', strip_tags($this->escape(JText::_("COM_OSDOWNLOADS_LINK_TIP"))) ));?>
            </td>
        </tr>
    	<tr>
        	<td><?php echo(JText::_("Demo link"));?></td>
        	<td><input type="text" name="demo_link" value="<?php echo $this->item->demo_link; ?>" style="width:500px" />
            	<?php echo(JHTML::_('tooltip', strip_tags($this->escape(JText::_("COM_OSDOWNLOADS_LINK_TIP"))) ));?>
            </td>
        </tr>
    	<tr>
        	<td><?php echo(JText::_("Support link"));?></td>
        	<td><input type="text" name="support_link" value="<?php echo $this->item->support_link; ?>" style="width:500px" />
            	<?php echo(JHTML::_('tooltip', strip_tags($this->escape(JText::_("COM_OSDOWNLOADS_LINK_TIP"))) ));?>
            </td>
        </tr>
    	<tr>
        	<td><?php echo(JText::_("Other"));?></td>
        	<td>
	            Name <input type="text" name="other_name" value="<?php echo $this->item->other_name; ?>" style="width:150px" />
            	Link <input type="text" name="other_link" value="<?php echo $this->item->other_link; ?>" style="width:500px" />
            	<?php echo(JHTML::_('tooltip', strip_tags($this->escape(JText::_("COM_OSDOWNLOADS_OTHER_TIP"))) ));?>
            </td>
        </tr>
    	<tr>
        	<td><?php echo(JText::_("Direct page"));?></td>
        	<td><input type="text" name="direct_page" value="<?php echo $this->item->direct_page; ?>" style="width:500px" />
            </td>
        </tr>
    	<tr>
        	<td><?php echo(JText::_("Published"));?></td>
        	<td>
            	<input type="radio" name="published" value="1" <?php if ($this->item->published) echo('checked="checked"');?> /> <?php echo(JText::_("Yes"));?> 
            	<input type="radio" name="published" value="0" <?php if (!$this->item->published) echo('checked="checked"');?> /><?php echo(JText::_("No"));?></td>
        </tr>
    </table>
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="option" value="com_osdownloads" />
	<input type="hidden" name="id" value="<?php echo $this->item->id; ?>" />
	<input type="hidden" name="cid[]" value="<?php echo $this->item->id; ?>" />
	<?php echo JHTML::_( 'form.token' ); ?> 
</form>