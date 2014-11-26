<?php
/**
 * @package   OSDownloads
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2014 Alledia.com, All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */
defined('_JEXEC') or die( 'Restricted access' );

$mainframe              = JFactory::getApplication();
$params                 = clone($mainframe->getParams('com_osdownloads'));
$NumberOfColumn         = $params->get("number_of_column", 1);
$user                   = JFactory::getUser();
$authorizedAccessLevels = $user->getAuthorisedViewLevels();

?>
<form action="<?php echo(JRoute::_("index.php?option=com_osdownloads&view=downloads&id=".JRequest::getVar("id")."&Itemid=".JRequest::getVar("Itemid")));?>" method="post" name="adminForm" id="adminForm">
    <div class="contentopen osdownloads-container">
        <h1>
            <?php for ($i = count($this->paths) - 1; $i >= 0; $i--):?>
                <?php if ($i):?>
                    <a href="<?php echo(JRoute::_("index.php?option=com_osdownloads&view=downloads&id={$this->paths[$i]->id}"."&Itemid=".JRequest::getVar("Itemid")));?>">
                <?php endif;?>
                <?php echo($this->paths[$i]->title);?>
                <?php if ($i):?>
                    </a> <span class="divider">::</span>
                <?php endif;?>
            <?php endfor;?>
        </h1>
        <div class="sub_title">
        <?php echo($this->paths[0]->description);?></div>

        <?php if ($this->children):?>
            <div class="category_children">
                <?php
                $i = 0;
                foreach($this->children as $item):?>
                    <?php if (in_array($item->cat_access, $authorizedAccessLevels)) : ?>
                        <div class="item<?php echo($i % $NumberOfColumn);?> cate_<?php echo($item->id);?>">
                            <h3><a href="<?php echo(JRoute::_("index.php?option=com_osdownloads&view=downloads&id={$item->id}"."&Itemid=".JRequest::getVar("Itemid")));?>"><?php echo($item->title . " (" . $item->total_doc .")");?></a></h3>
                            <div class="item_content">
                                <?php echo($item->description);?>
                            </div>
                        </div>
                        <?php if ($NumberOfColumn && $i % $NumberOfColumn == $NumberOfColumn - 1):?>
                            <div class="seperator"></div>
                            <div class="clr"></div>
                        <?php endif;?>
                        <?php $i++;?>
                    <?php endif; ?>
                <?php endforeach;?>
                <div class="clr"></div>
            </div>
        <?php endif;?>

        <?php foreach($this->items as $i => $item):?>
            <?php if (in_array($item->access, $authorizedAccessLevels)) : ?>
                <div class="item_<?php echo($item->id);?>">
                    <h3><a href="<?php echo(JRoute::_("index.php?option=com_osdownloads&view=item&id=".$item->id."&Itemid=".JRequest::getVar("Itemid")));?>"><?php echo($item->name);?></a></h3>
                    <div class="item_content"><?php echo($item->brief);?></div>
                    <div class="readmore_wrapper">
                        <div class="readmore">
                            <a href="<?php echo(JRoute::_("index.php?option=com_osdownloads&view=item&id=".$item->id."&Itemid=".JRequest::getVar("Itemid")));?>">
                                <?php echo(JText::_("COM_OSDOWNLOADS_READ_MORE"));?>
                            </a>
                        </div>
                        <div class="clr"></div>
                    </div>
                    <div class="seperator"></div>
                </div>
            <?php endif; ?>
        <?php endforeach;?>
        <div class="clr"></div>
        <div><?php echo $this->pagination->getPagesCounter(); ?></div>
        <div class="osdownloads-pagination"><?php echo $this->pagination->getPagesLinks(); ?></div>
    </div>

</form>
