<?php
/**
 * @version 1.0.0
 * @author Open Source Training (www.ostraining.com)
 * @copyright (C) 2011- Open Source Training
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
**/
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

$listOrder	= $this->lists['order'];
$listDirn	= $this->lists['order_Dir'];
$saveOrder	= $listOrder == 'documents.ordering'; 
?>
<form action="index.php?option=com_osdownloads" method="post" name="adminForm">
    <table>
        <tr>
            <td align="left" width="100%">
                <?php echo JText::_( 'Filter' ); ?>:
                <input type="text" name="search" id="search" value="<?php echo htmlspecialchars($this->flt->search);?>" class="text_area" onchange="document.adminForm.submit();" />
                <button onclick="this.form.submit();"><?php echo JText::_( 'Go' ); ?></button>
                <button onclick="document.getElementById('search').value='';this.form.getElementById('cate_id').value='';this.form.submit();"><?php echo JText::_( 'Reset' ); ?></button>
            </td>
            <td nowrap="nowrap">
            	<?php echo(JHTML::_('list.category',  'flt_cate_id', 'com_osdownloads', $this->flt->cate_id, "onchange='this.form.submit();'"));?>
                <?php //JHTML::_('grid.state',  $filter_state );?>
            </td>
        </tr>
    </table>
	<table class="adminlist" width="100%" border="0">
        <thead>
            <tr> 
                <th width="20" align="center"><input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $this->items ); ?>);" />  </th>
                <th style="min-width:200px;"><?php echo JHTML::_('grid.sort',   JText::_('Name'), 'documents.name', @$this->lists['order_Dir'], @$this->lists['order'] ); ?> </th>
                <th style="min-width:200px;"><?php echo JHTML::_('grid.sort',   JText::_('Category'), 'cate.title', @$this->lists['order_Dir'], @$this->lists['order'] ); ?> </th>
                <th width="50"><?php echo JHTML::_('grid.sort',   JText::_('Downloaded'), 'documents.downloaded', @$this->lists['order_Dir'], @$this->lists['order'] ); ?></th>
                <th width="50"><?php echo JHTML::_('grid.sort',   JText::_('Published'), 'documents.published', @$this->lists['order_Dir'], @$this->lists['order'] ); ?></th>
				<th width="10%">
					<?php echo JHtml::_('grid.sort',  'JGRID_HEADING_ORDERING', 'documents.ordering', $listDirn, $listOrder); ?>
					<?php if ($saveOrder) :?>
						<?php echo JHtml::_('grid.order',  $this->items, 'filesave.png', 'file.saveorder'); ?>
					<?php endif; ?>
				</th> 
                <th><?php echo JHTML::_('grid.sort',   JText::_('ID'), 'documents.id', @$this->lists['order_Dir'], @$this->lists['order'] ); ?></th>
            </tr>
            <tfoot>
                <tr>
                    <td colspan="6">
                        <?php echo $this->pagination->getListFooter(); ?>
                    </td>
                </tr>
            </tfoot>     
            <tbody> 
                <?php foreach ($this->items as $i => $item) : 
					$item->checked_out = false;
					$ordering	= ($listOrder == 'documents.ordering'); 
					$published 	= JHTML::_('grid.published', $item, $i, 'tick.png', 'publish_x.png', 'file.'); 
					$checked 	= JHTML::_('grid.checkedout', $item, $i ); 
                ?>
                    <tr class="row<?php echo $i % 2; ?>"> 
                    	<td><?php echo $checked; ?></td>
                        <td><a href="index.php?option=com_osdownloads&view=file&cid[]=<?php echo($item->id);?>"><?php echo ($item->name); ?></a></td>
                        <td valign="top" nowrap="nowrap"><?php echo($item->cate_name);?></td>
                        <td valign="top" nowrap="nowrap"><?php echo($item->downloaded);?></td>
                        <td valign="top" nowrap="nowrap"><?php echo($published);?></td>
                        <td class="order">
							<?php if ($saveOrder) :?>
                                <?php if ($listDirn == 'asc') : ?>
                                    <span><?php echo $this->pagination->orderUpIcon($i, ($item->cate_id == @$this->items[$i-1]->cate_id), 'file.orderup', 'JLIB_HTML_MOVE_UP', $ordering); ?></span>
                                    <span><?php echo $this->pagination->orderDownIcon($i, $this->pagination->total, ($item->cate_id == @$this->items[$i+1]->cate_id), 'file.orderdown', 'JLIB_HTML_MOVE_DOWN', $ordering); ?></span>
                                <?php elseif ($listDirn == 'desc') : ?>
                                    <span><?php echo $this->pagination->orderUpIcon($i, ($item->cate_id == @$this->items[$i-1]->cate_id), 'file.orderdown', 'JLIB_HTML_MOVE_UP', $ordering); ?></span>
                                    <span><?php echo $this->pagination->orderDownIcon($i, $this->pagination->total, ($item->cate_id == @$this->items[$i+1]->cate_id), 'file.orderup', 'JLIB_HTML_MOVE_DOWN', $ordering); ?></span>
                                <?php endif; ?>
                            <?php endif; ?>
                            <?php $disabled = $saveOrder ?  '' : 'disabled="disabled"'; ?>
                            <input type="text" name="order[]" size="5" value="<?php echo $item->ordering;?>" <?php echo $disabled ?> class="text-area-order" />
                        </td> 
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