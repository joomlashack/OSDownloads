<?php
/**
 * @version 1.0.0
 * @author Open Source Training (www.ostraining.com)
 * @copyright (C) 2011- Open Source Training
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
**/
defined('_JEXEC') or die( 'Restricted access' );
$mainframe 			= JFactory::getApplication();
$params 			= clone($mainframe->getParams('com_osdownloads')); 
$NumberOfColumn 	= $params->get("number_of_column", 1);
?>
<form action="<?php echo(JRoute::_("index.php?option=com_osdownloads&view=downloads&id=".JRequest::getVar("id")."&Itemid=".JRequest::getVar("Itemid")));?>" method="post" name="adminForm">
    <div class="contentopen">
        <h3>
            <?php for ($i = count($this->paths) - 1; $i >= 0; $i--):?>
                <?php if ($i):?>
                    <a href="<?php echo(JRoute::_("index.php?option=com_osdownloads&view=downloads&id={$this->paths[$i]->id}"."&Itemid=".JRequest::getVar("Itemid")));?>">
                <?php endif;?>
                <?php echo($this->paths[$i]->title);?>
                <?php if ($i):?>
                    </a> <span class="divider">::</span>
                <?php endif;?>
            <?php endfor;?>
        </h3>
        <div class="sub_title"><?php echo($this->paths[0]->description);?></div>
        
        <?php if ($this->children):?>
            <div class="category_children">
                <?php 
				$i = 0;
				$user = JFactory::getUser();
				$groups = $user->getAuthorisedViewLevels(); 
				foreach($this->children as $item):?>
                	<?php
					if (!in_array($item->access, $groups))
						continue;
					$i++;
					?>
                    <div class="item<?php echo($i % $NumberOfColumn);?>">
                        <h4><a href="<?php echo(JRoute::_("index.php?option=com_osdownloads&view=downloads&id={$item->id}"."&Itemid=".JRequest::getVar("Itemid")));?>"><?php echo($item->title . " (" . $item->total_doc .")");?></a></h4>
                        <div class="item_content">
                            <?php echo($item->description);?>
                        </div>
                    </div>
                    <?php if ($NumberOfColumn && $i % $NumberOfColumn == $NumberOfColumn - 1):?>
			            <div class="seperator"></div>
                        <div class="clr"></div>
                    <?php endif;?>
                <?php endforeach;?>
                <div class="clr"></div>
            </div>
        <?php endif;?>
        
        <?php foreach($this->items as $i => $item):?>
        	<div class="download_list_item">
            	<?php if ($item->picture):?>
            		<div style="float:left; margin-right:20px; width:150px;"><img src="<?php echo(JURI::root()."media/OSDownloads/".$item->picture);?>" style="border:1px solid #dedede" /></div>
                <?php endif;?>
                <div style="<?php if ($item->picture) echo("float:left; width:460px");?>; padding-right:10px;">
                    <h4><a href="<?php echo(JRoute::_("index.php?option=com_osdownloads&view=item&id=".$item->id."&Itemid=".JRequest::getVar("Itemid")));?>"><?php echo($item->name);?></a></h4>
                    <div class="item_content">
						<?php echo($item->brief);?>
                        <div style="float:right"><a href="<?php echo(JRoute::_("index.php?option=com_osdownloads&view=item&id=".$item->id."&Itemid=".JRequest::getVar("Itemid")));?>">
                           Go to download area &raquo;
                        </a> 
                        </div>                   
                    </div>
                </div>
                <div class="clr"></div>
            </div>
        <?php endforeach;?>
        <div class="clr"></div>
        <div><?php echo $this->pagination->getPagesCounter(); ?></div>
    </div>
    
</form>