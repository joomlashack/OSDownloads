<?php
/**
 * @version 1.0.0
 * @author Open Source Training (www.ostraining.com)
 * @copyright (C) 2011- Open Source Training
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
**/
defined('_JEXEC') or die( 'Restricted access' );
JHTML::_('behavior.modal');

$mainframe 			= & JFactory::getApplication();
$params 			= clone($mainframe->getParams('com_osdownloads')); 

?>
<div class="contentopen">
	<form method="post">
        <h3><?php echo($this->item->name);?></h3>
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

		<?php if ($this->item->description_2):?>
	        <div class="description2"><?php echo($this->item->description_2);?></div>
        <?php endif;?>

		<div class="info_box">
			<?php if ($this->item->picture):?>
                <div style="float:left; margin-right:20px; margin-top:10px;"><img src="<?php echo(JURI::root()."media/OSDownloads/".$this->item->picture);?>" height="50px" /></div>
            <?php endif;?>
            
			<?php
				$db = JFactory::getDBO();
				$db->setQuery("SELECT * FROM #__osdownloads_documents WHERE parent_id = ".$this->item->id);
				$items = $db->loadObjectList();
			?>
            
            <div style="float:left; margin:20px 15px;">
				<?php if (count($items)):?>
                    <select name="product_id" id="product_id">
                        <option value="<?php echo($this->item->id);?>">Version <?php echo($this->item->cms_version);?></option>
                        <?php foreach ($items as $item):?>
                            <option value="<?php echo($item->id);?>">Version <?php echo($item->cms_version);?></option>
                        <?php endforeach;?>
                    </select>
                <?php else:?>
                    Version <?php echo($this->item->cms_version);?>
					<input type="hidden" id="product_id" value="<?php echo($this->item->id);?>" />
                <?php endif;?>
			</div>

            <div class="reference" style="float:left; margin-top:15px;">
                <?php if ($this->item->documentation_link):?>
                    <div class="readmore" onclick="VisitPage('document')"><?php echo(JText::_("Documentation"));?></div>
                <?php endif;?>
                <?php if ($this->item->demo_link):?>
                    <div class="readmore" onclick="VisitPage('demo')"><?php echo(JText::_("Demo"));?></div>
                <?php endif;?>
                <?php if ($this->item->support_link):?>
                    <div class="readmore" onclick="VisitPage('support')"><?php echo(JText::_("Support"));?></div>
                <?php endif;?>
                <?php if ($this->item->other_link):?>
                    <div class="readmore" onclick="VisitPage('other')"><?php echo($this->item->other_name);?></div>
                <?php endif;?>
                <div class="readmore btn_download" onclick="<?php if (JFactory::getUser()->get("id")) echo('DownloadLink()'); else echo('ShowConfirmBox()');?>">
                	<?php echo($this->item->download_text ? $this->item->download_text : JText::_("Download"));?>
                </div>
                <div class="clr"></div>
            </div>

        	<div class="clr"></div>
        </div>        
        
        <div><?php echo($this->item->description_3);?></div>
    </form>

</div>
<div id="downloadbox" style="display:none" >
	<div class="reference download_box">
        <form method="post">
            <h3>To download, please enter your email address</h3>
            <div style="padding:10px 20px;">
				<?php if ($this->item->require_email || $this->item->show_email):?>
                    <div class="osdownloadsemail"><?php echo(JText::_("Email address"));?> (*): <input type="text" name="require_email" style="width:210px;" id="require_email_temp" /></div>
                <?php endif;?>
                <?php if ($this->item->require_agree):?>
                    <div><input type="checkbox" name="require_agree" id="require_agree" /> * <?php echo(JText::_("Download term"));?></div>
                <?php endif;?>
                <div class="readmore btn_download" onclick="Download()" style="float:right; margin-top:20px; padding-top:3px;font-size:12px">
                    Download
                </div>
                <div class="clr"></div>
            </div>
        </form>
    </div>
</div>

<script>

function VisitPage(page)
{
	window.location = '<?php echo(JRoute::_("index.php?option=com_osdownloads&Itemid=".JRequest::getVar("Itemid")));?>?task=link'+page+'&id='+$("product_id").value;
}

function ShowConfirmBox()
{
	
	var newDiv = new Element("div", {"id" : "download_content", "html" : $('downloadbox').innerHTML.replace("require_email_temp", "require_email")});
	SqueezeBox.open(newDiv, {
		handler: 'adopt',
		size: {x: 400, y: 180}
	});	
}

function Download()
{
	var product_id = $("product_id").value;
	if (Validate())
	{
		var request = '<?php echo(JRoute::_("index.php?option=com_osdownloads&task=getdownloadlink&tmpl=component&Itemid=".JRequest::getVar("Itemid")));?>';
		if ($("require_email"))
		{
			request += "&email=" + $("require_email").value;
		}
		request += "&id="+product_id;
		SqueezeBox.close();
		SqueezeBox.open(request, {onClose: function onClose() {directPage();},handler: 'iframe', size: {x: <?php echo($params->get("width", 450));?>, y: <?php echo($params->get("height", 150));?>}});
	}
}

function DownloadLink()
{
	var product_id = $("product_id").value;
	var request = '<?php echo(JRoute::_("index.php?option=com_osdownloads&task=getdownloadlink&tmpl=component&Itemid=".JRequest::getVar("Itemid")));?>';
	request += "&id="+product_id;
	SqueezeBox.open(request, {onClose: function onClose() {directPage();},handler: 'iframe', size: {x: <?php echo($params->get("width", 450));?>, y: <?php echo($params->get("height", 150));?>}});
}


window.addEvent('domready', function() {
	SqueezeBox.initialize();

	/*
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
	*/
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