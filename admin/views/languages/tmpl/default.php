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
<form action="index.php?option=com_osdownloads" method="post" name="adminForm">
	<table border="0">
    	<tr>
        	<td>
            	<select name="language_code" onchange="this.form.submit();">
                	<?php foreach ($this->languages as $language):?>
                    	<option value="<?php echo($language->element);?>" <?php if ($this->lang == $language->element) echo('selected="selected"');?> ><?php echo($language->name);?></option>
                    <?php endforeach;?>
                </select>
            </td>
        </tr>
    </table>
	<table border="0">
    	<thead>
        	<tr>
            	<th><?php echo(JText::_('Key'));?></th>
            	<th><?php echo(JText::_('Value'));?></th>
            </tr>
        </thead>
        <tbody>
			<?php foreach ($this->defines as $key => $value):?>
                <tr>
                    <td><?php echo($key);?></td>
                    <td><input type="text" name="defines['<?php echo($key);?>']" value="<?php echo($value);?>" size="150" /></td>
                </tr>            
            <?php endforeach;?>
    	</tbody>
    </table>
	<input type="hidden" name="option" value="com_osdownloads" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="view" value="languages" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>