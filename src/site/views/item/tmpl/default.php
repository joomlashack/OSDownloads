<?php
/**
 * @package   OSDownloads
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2014 Alledia.com, All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */
defined('_JEXEC') or die( 'Restricted access' );
JHTML::_('behavior.modal');

$mainframe = JFactory::getApplication();
$params    = clone($mainframe->getParams('com_osdownloads'));
$doc       = JFactory::getDocument();

if ($this->params->get('load_jquery', false)) {
    $doc->addScript('media/com_osdownloads/js/jquery.js');
}

$doc->addScript('media/com_osdownloads/js/jquery.browser.min.js');
$doc->addScript('media/com_osdownloads/js/jquery.reveal.min.js');
$doc->addScript('media/com_osdownloads/js/jquery.iframe-auto-height.min.js');

?>
<div class="contentopen osdownloads-container">
    <form method="post" id="adminForm" name="adminForm">
        <h1><?php echo($this->item->name);?></h1>
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
        <div><?php echo(JText::_("COM_OSDOWNLOADS_DOWNLOADED"));?>: <?php echo($this->item->downloaded);?></div>
        <?php endif;?>
        <div class="reference">
            <?php if ($this->item->documentation_link):?>
                <div class="readmore"><a href="<?php echo($this->item->documentation_link);?>"><?php echo(JText::_("COM_OSDOWNLOADS_DOCUMENTATION"));?></a></div>
            <?php endif;?>
            <?php if ($this->item->demo_link):?>
                <div class="readmore"><a href="<?php echo($this->item->demo_link);?>"><?php echo(JText::_("COM_OSDOWNLOADS_DEMO"));?></a></div>
            <?php endif;?>
            <?php if ($this->item->support_link):?>
                <div class="readmore"><a href="<?php echo($this->item->support_link);?>"><?php echo(JText::_("COM_OSDOWNLOADS_SUPPORT"));?></a></div>
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
            <div class="osdownloadsemail"><?php echo(JText::_("COM_OSDOWNLOADS_EMAIL"));?> <?php if ($this->item->require_email):?>(*)<?php endif;?>: <input type="email" aria-required="true" required name="require_email" id="require_email" /></div>
        <?php endif;?>

        <?php if ($this->item->description_2):?>
            <div class="description2"><?php echo($this->item->description_2);?></div>
        <?php endif;?>
        <div class="osdownloadsactions">
            <?php if ($this->item->require_agree):?>
                <div><input type="checkbox" name="require_agree" id="require_agree" /> * <?php echo(JText::_("COM_OSDOWNLOADS_DOWNLOAD_TERM"));?></div>
            <?php endif;?>
            <div class="btn_download">
                <a href="<?php echo(JRoute::_("index.php?option=com_osdownloads&task=getdownloadlink&tmpl=component&Itemid=".JRequest::getVar("Itemid")."&id={$this->item->id}"));?>"  id="btn_download" style="color:<?php echo($this->item->download_color);?>"  class="readmore"><span><?php echo($this->item->download_text ? $this->item->download_text : JText::_("COM_OSDOWNLOADS_DOWNLOAD"));?></span></a>
            </div>
        </div>
        <div><?php echo($this->item->description_3);?></div>
    </form>
</div>

<div id="osdownloads-popup-warning" class="reveal-modal">
     <h1 class="title"><?php echo JText::_('COM_OSDOWNLOADS_WARNING'); ?></h1>
     <p>
         <ul class="messages"></ul>
     </p>
     <a class="close-reveal-modal">&#215;</a>
</div>
<div id="osdownloads-popup-iframe" class="reveal-modal">
     <iframe src="" class="auto-height" scrolling="no"></iframe>
     <a class="close-reveal-modal">&#215;</a>
</div>
<script>
(function ($) {
    $(function() {
        $('iframe.auto-height').iframeAutoHeight({
            heightOffset: 10
        });

        function isValidForm() {
            var exp = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/,
                msg = [];

            <?php if ($this->item->require_agree) : ?>
                if (! $("#require_agree").is(':checked')) {
                    msg.push("<?php echo JText::_("COM_OSDOWNLOADS_YOU_HAVE_AGREE_TERMS_TO_DOWNLOAD_THIS"); ?>");
                }
            <?php endif; ?>

            <?php if ($this->item->require_email) : ?>
                var email = $("#require_email").val().trim();

                if (email === "" || ! exp.test(email)) {
                    msg.push("<?php echo JText::_("COM_OSDOWNLOADS_YOU_HAVE_INPUT_CORRECT_EMAIL_TO_GET_DOWNLOAD_LINK"); ?>");
                }
            <?php else:?>
                if ($("#require_email").length > 0) {
                    var email = $("#require_email").val().trim();

                    if (email != "" && ! exp.test(email)) {
                        msg.push("<?php echo JText::_("COM_OSDOWNLOADS_YOU_MUST_INPUT_CORRECT_EMAIL_TO_GET_DOWNLOAD_LINK"); ?>");
                    }
                }
            <?php endif;?>
            if (msg.length > 0) {
                var $messageContainer = $('#osdownloads-popup-warning .messages');
                $messageContainer.html('');

                for (var i = 0; i < msg.length; i++) {
                    $messageContainer.append($('<li>' + msg[i] + '</li>'));
                }

                showModal('#osdownloads-popup-warning', true);

                return false;
            }

            return true;
        }

        function showModal(selector) {
            $(selector).reveal({
                 animation: '<?php echo $params->get("popup_animation", "fade"); ?>',
                 animationspeed: 200,
                 closeonbackgroundclick: true,
                 dismissmodalclass: 'close-reveal-modal'
            });
        }

        function directPage() {
            <?php if ($this->item->direct_page):?>
                window.location = '<?php echo($this->item->direct_page);?>';
            <?php endif;?>
        }

        $("#btn_download").on('click', function(event) {
            event.preventDefault();

            if (isValidForm()) {
                var url = this.href;

                if ($("#require_email").length > 0) {
                    url += "&email=" + $("#require_email").val().trim();
                }

                var $iframe = $('#osdownloads-popup-iframe iframe');
                $iframe.attr('src', url);

                setTimeout(function timeourShowModal() {
                    showModal('#osdownloads-popup-iframe');
                }, 500);
            }
        });
    });
})(jQuery);
</script>
