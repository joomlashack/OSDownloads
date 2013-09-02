<?php
/**
 * @version 1.0.0
 * @author Open Source Training (www.ostraining.com)
 * @copyright (C) 2011- Open Source Training
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
**/
defined('_JEXEC') or die( 'Restricted access' );
JHTML::_('behavior.modal');

$mainframe 			= JFactory::getApplication();
$params 			= clone($mainframe->getParams('com_osdownloads')); 

?>
<div class="contentopen">
	<form method="post">
        <h4><?php echo($this->item->name);?></h4>
        <?php if ($this->params->get("show_category", 0)):?>
            <div class="cate_info">
                Category:
                <?php for ($i = count($this->paths) - 1; $i >= 0; $i--):?>
                    <a href="<?php echo(JRoute::_("index.php?option=com_osdownloads&view=downloads&id={$this->paths[$i]->id}"));?>">
                    <?php echo($this->paths[$i]->title);?>
                    </a>
                    <?php if ($i):?>
                         <span class="divider">::</span>
                    <?php endif;?>
                <?php endfor;?>
            </div>
        <?php endif;?>
        <?php if ($this->params->get("show_download_count", 0)):?>
        <div><?php echo(JText::_("Downloaded"));?>: <?php echo($this->item->downloaded);?></div>
        <?php endif;?>
        <div class="reference">
        	<?php if ($this->item->documentation_link):?>
            	<div class="readmore"><a href="<?php echo($this->item->documentation_link);?>"><?php echo(JText::_("Documentation"));?></a></div>
            <?php endif;?>
        	<?php if ($this->item->demo_link):?>
            	<div class="readmore"><a href="<?php echo($this->item->demo_link);?>"><?php echo(JText::_("Demo"));?></a></div>
            <?php endif;?>
        	<?php if ($this->item->support_link):?>
            	<div class="readmore"><a href="<?php echo($this->item->support_link);?>"><?php echo(JText::_("Support"));?></a></div>
            <?php endif;?>
        	<?php if ($this->item->other_link):?>
            	<div class="readmore"><a href="<?php echo($this->item->other_link);?>"><?php echo($this->item->other_name);?></a></div>
            <?php endif;?>
            <div class="clr"></div>
        </div>
        <?php if ($this->item->brief || $this->item->description_1):?>
	        <div class="description1"><?php echo($this->item->brief . $this->item->description_1);?></div>
        <?php endif;?>
        
        <?php if ($this->item->require_email || $this->item->show_email):?>
            <div class="osdownloadsemail"><?php echo(JText::_("Email"));?> <?php if ($this->item->require_email):?>(*)<?php endif;?>: <input type="text" name="require_email" id="require_email" /></div>
        <?php endif;?>
        
		<?php if ($this->item->description_2):?>
	        <div class="description2"><?php echo($this->item->description_2);?></div>
        <?php endif;?>
        <div class="osdownloadsactions">
			<?php if ($this->item->require_agree):?>
                <div><input type="checkbox" name="require_agree" id="require_agree" /> * <?php echo(JText::_("Download term"));?></div>
            <?php endif;?>
            <div class="btn_download">
                <a href="<?php echo(JRoute::_("index.php?option=com_osdownloads&task=getdownloadlink&tmpl=component&Itemid=".JRequest::getVar("Itemid")."&id={$this->item->id}"));?>"  id="btn_download" style="color:<?php echo($this->item->download_color);?>"  class="readmore"><span><?php echo($this->item->download_text ? $this->item->download_text : JText::_("Download"));?></span></a>
            </div>
        </div>
        <div><?php echo($this->item->description_3);?></div>
    </form>
</div>
<script>
window.addEvent('domready', function() {

	$("btn_download").addEvent('click', function (e) {
		new Event(e).stop();
		if (Validate())
		{
			var request = this.href;
			if ($("require_email"))
			{
				request += "&email=" + $("require_email").value;
			}
			SqueezeBox.open(request, {onClose: function onClose() {directPage();},handler: 'iframe', size: {x: <?php echo($params->get("width", 350));?>, y: <?php echo($params->get("height", 150));?>}});
		}
	});
});

function directPage()
{
	<?php if ($this->item->direct_page):?>
		window.location = '<?php echo($this->item->direct_page);?>';
	<?php endif;?>
}


function Validate()
{
	var reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
	var msg = "";
	if ($("require_agree") && !$("require_agree").checked)
		msg += "<?php echo(JText::_("You have agree terms to download this"));?>\r\n";
	<?php if ($this->item->require_email):?>
		$("require_email").value = $("require_email").value.trim();
		if ($("require_email") && ($("require_email").value == "" || !reg.test($("require_email").value)))
			msg += "<?php echo(JText::_("You have input correct email to get download link"));?>\r\n";
	<?php else:?>
		if ($("require_email"))
			$("require_email").value = $("require_email").value.trim();
		if ($("require_email") && ($("require_email").value != "" && !reg.test($("require_email").value)))
			msg += "<?php echo(JText::_("You must input correct email to get download link"));?>\r\n";
	<?php endif;?>
	if (msg)
	{
		alert(msg);
		return false;
	}
	return true;
}
</script>