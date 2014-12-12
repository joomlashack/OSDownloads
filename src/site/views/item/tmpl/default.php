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

        <?php if ($this->item->description_2):?>
            <div class="description2"><?php echo($this->item->description_2);?></div>
        <?php endif;?>
        <div class="osdownloadsactions">
            <div class="btn_download">
                <a href="<?php echo(JRoute::_("index.php?option=com_osdownloads&task=getdownloadlink&tmpl=component&Itemid=".JRequest::getVar("Itemid")."&id={$this->item->id}"));?>"  id="btn_download1" style="color:<?php echo($this->item->download_color);?>"  class="readmore"><span><?php echo($this->item->download_text ? $this->item->download_text : JText::_("COM_OSDOWNLOADS_DOWNLOAD"));?></span></a>
            </div>
        </div>
        <div><?php echo($this->item->description_3);?></div>
    </form>
</div>

<?php if ($this->item->require_email || $this->item->show_email || $this->item->require_agree) : ?>
    <div id="osdownloads-requirements" class="reveal-modal">
        <h1 class="title"><?php echo JText::_('COM_OSDOWNLOADS_BEFORE_DOWNLOAD'); ?></h1>

        <?php if ($this->item->show_email || $this->item->require_email) :?>
            <div class="osdownloadsemail">
                <?php if ($this->item->require_email) : ?>
                    <p>
                        <?php echo JText::_('COM_OSDOWNLOADS_YOU_HAVE_INPUT_CORRECT_EMAIL_TO_GET_DOWNLOAD_LINK'); ?>
                    </p>
                <?php endif; ?>

                <label for="require_email">
                    <span>
                        <?php echo(JText::_("COM_OSDOWNLOADS_EMAIL")); ?><?php echo $this->item->require_email ? '*' : ''; ?>:
                    </span>
                    <input type="email" aria-required="true" required name="require_email" id="require_email" />
                </label>
                <div class="error" style="display: none;" id="osdownloads-error-invalid-email">
                    <?php echo JText::_("COM_OSDOWNLOADS_INVALID_EMAIL"); ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($this->item->require_agree):?>
            <div>
                <label for="require_agree">
                    <input type="checkbox" name="require_agree" id="require_agree" />
                    <span>
                        * <?php echo(JText::_("COM_OSDOWNLOADS_DOWNLOAD_TERM"));?>
                    </span>
                </label>
            </div>
            <div class="error" style="display: none;" id="osdownloads-error-agree-terms">
                <?php echo JText::_("COM_OSDOWNLOADS_YOU_HAVE_AGREE_TERMS_TO_DOWNLOAD_THIS"); ?>
            </div>
        <?php endif;?>

        <a href="<?php echo(JRoute::_("index.php?option=com_osdownloads&task=getdownloadlink&tmpl=component&Itemid=".JRequest::getVar("Itemid")."&id={$this->item->id}"));?>"  id="btn_download_continue" style="color:<?php echo($this->item->download_color);?>"  class="readmore">
            <span>
                <?php echo JText::_("COM_OSDOWNLOADS_CONTINUE"); ?>
            </span>
        </a>

        <a class="close-reveal-modal">&#215;</a>
    </div>
<?php endif;?>

<div id="osdownloads-popup-iframe" class="reveal-modal">
     <iframe src="" class="auto-height" scrolling="no"></iframe>
     <a class="close-reveal-modal">&#215;</a>
</div>

<script>
(function ($) {
    $(function() {
        // Move the popup container to the body
        $('.reveal-modal').appendTo($('body'));

        $('iframe.auto-height').iframeAutoHeight({
            heightOffset: 10
        });

        function isValidForm() {
            var exp = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/,
                errorElement = null,
                hasError = false;

            <?php if ($this->item->require_agree) : ?>
                errorElement = $('#osdownloads-error-agree-terms');

                if (! $("#require_agree").is(':checked')) {
                    hasError = true;
                    errorElement.show();
                } else {
                    errorElement.hide();
                }
            <?php endif; ?>

            <?php if ($this->item->require_email) : ?>
                errorElement = $('#osdownloads-error-invalid-email');

                var email = $("#require_email").val().trim();

                if (email === "" || ! exp.test(email)) {
                    hasError = true;
                    errorElement.show();
                } else {
                    errorElement.hide();
                }
            <?php else : ?>
                <?php if ($this->item->show_email) : ?>
                    errorElement = $('#osdownloads-error-invalid-email');

                    var email = $("#require_email").val().trim();

                    if (email != "" && ! exp.test(email)) {
                        hasError = true;
                        errorElement.show();
                    } else {
                        errorElement.hide();
                    }
                <?php endif; ?>
            <?php endif;?>

            if (hasError) {
                return false;
            }

            return true;
        }

        function showModal(selector, onClose) {
            $(selector).reveal({
                 animation: '<?php echo $params->get("popup_animation", "fade"); ?>',
                 animationspeed: 200,
                 closeonbackgroundclick: true,
                 dismissmodalclass: 'close-reveal-modal',
            });
        }

        function directPage() {
            <?php if ($this->item->direct_page):?>
                window.location = '<?php echo($this->item->direct_page);?>';
            <?php endif;?>
        }

        function download(elem) {
            var url = elem.href;

            if ($("#require_email").length > 0) {
                url += "&email=" + $("#require_email").val().trim();
            }

            var $iframe = $('#osdownloads-popup-iframe iframe');
            $iframe.attr('src', url);

            // Close the requirements popup
            $(elem).trigger('reveal:close');

            setTimeout(function timeourShowModal() {
                showModal('#osdownloads-popup-iframe');
            }, 500);
        }

        $("#btn_download1").on('click', function(event) {
            event.preventDefault();

            <?php if ($this->item->show_email || $this->item->require_email || $this->item->require_agree) : ?>
                showModal('#osdownloads-requirements');
                $('#osdownloads-requirements').on('reveal:close', function() {
                    // Clean fields
                    $('#require_email').val('');
                    $('#require_agree').attr('checked', false);
                    $('.reveal-modal .error').hide();
                });
            <?php else : ?>
                download(this);
            <?php endif; ?>
        });

        $("#btn_download_continue").on('click', function(event) {
            event.preventDefault();

            if (isValidForm()) {
                download(this);
            }
        });
    });
})(jQuery);
</script>
