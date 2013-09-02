<?php
/**
 * @version 1.0.0
 * @author Open Source Training (www.ostraining.com)
 * @copyright (C) 2011- Open Source Training
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
**/
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

$mainframe 			= & JFactory::getApplication();
$params 			= clone($mainframe->getParams('com_osdownloads')); 
$thankyoupage 	= $params->get("thankyoupage", "Thank you for your downloading");

$email = trim(JRequest::getVar("email"));
if ($this->item->require_email && !preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $email))
{
?>
<div class="error"><?php echo(JText::_("Wrong email"));?></div>	
<?php
}
else
{?>
<div class="contentopen">
	<h1 class="thank"><?php echo($thankyoupage);?></h1>
	<p class="download_link"><?php echo(JText::sprintf("CLICK TO DOWNLOAD FILE", JRoute::_("index.php?option=com_osdownloads&task=download&tmpl=component&id={$this->item->id}")));?> 
        <meta http-equiv="refresh" content="0;url=<?php echo(JRoute::_("index.php?option=com_osdownloads&task=download&tmpl=component&id={$this->item->id}"));?>">

	</p>
</div>
<?php
}
?>