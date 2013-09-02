<?php
/**
 * @version 1.0.0
 * @author Open Source Training (www.ostraining.com)
 * @copyright (C) 2011- Open Source Training
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
**/
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );
?>
<form action="index.php?option=com_osdownloads&view=emails" method="post" name="adminForm">
    <table>
        <tr>
            <td align="left" width="100%">
                <?php echo JText::_( 'Filter' ); ?>:
                <input type="text" name="search" id="search" value="<?php echo htmlspecialchars($this->flt->search);?>" class="text_area" onchange="document.adminForm.submit();" />
                <button onclick="this.form.submit();"><?php echo JText::_( 'Go' ); ?></button>
                <button onclick="document.getElementById('search').value='';this.form.submit();"><?php echo JText::_( 'Reset' ); ?></button>
            </td>
            <td nowrap="nowrap">
            	<?php echo(JHTML::_('list.category',  'cate_id', 'com_osdownloads', $this->flt->cate_id, "onchange='this.form.submit();'"));?>
            </td>
        </tr>
    </table>
	<table class="adminlist" width="100%" border="0">
        <thead>
            <tr>
            <th width="2%"><input type="checkbox" onclick="Joomla.checkAll(this)" title="check All" value="" name="checkall-toggle" /> </th>
                <th style="min-width:200px;"><?php echo JHTML::_('grid.sort',   JText::_('Name'), 'email.email', @$this->lists['order_Dir'], @$this->lists['order'] ); ?> </th>
                <th style="min-width:200px;"><?php echo JHTML::_('grid.sort',   JText::_('File'), 'document.name', @$this->lists['order_Dir'], @$this->lists['order'] ); ?> </th>
                <th style="min-width:200px;"><?php echo JHTML::_('grid.sort',   JText::_('Category'), 'cate.title', @$this->lists['order_Dir'], @$this->lists['order'] ); ?> </th>
                <th style="min-width:80px;"><?php echo JHTML::_('grid.sort',   JText::_('Date'), 'email.downloaded_date', @$this->lists['order_Dir'], @$this->lists['order'] ); ?> </th>
                <th><?php echo JHTML::_('grid.sort',   JText::_('ID'), 'email.id', @$this->lists['order_Dir'], @$this->lists['order'] ); ?></th>
            </tr>
            <tfoot>
                <tr>
                    <td colspan="5">
                        <?php echo $this->pagination->getListFooter(); ?>
                    </td>
                </tr>
            </tfoot>     
            <tbody> 
                <?php foreach ($this->items as $i => $item) : 
                ?>
                    <tr class="row<?php echo $i % 2; ?>"> 
                    	<td valign="top" nowrap="nowrap"><?php echo JHTML::_('grid.id',$id,$item->id);?></td>
                        <td valign="top" nowrap="nowrap"><?php echo($item->email);?></td>
                        <td valign="top" nowrap="nowrap"><?php echo($item->doc_name);?></td>
                        <td valign="top" nowrap="nowrap"><?php echo($item->cate_name);?></td>
                        <td valign="top" nowrap="nowrap"><?php echo(JHTML::_("date", $item->downloaded_date, "d-m-Y H:m:s"));?></td>
                        <td valign="top" nowrap="nowrap"><?php echo($item->id);?></td>
                    </tr>
                <?php endforeach;?>
            </tbody>        
        </thead>
    </table>
	<input type="hidden" name="option" value="com_osdownloads" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>