<?php
/**
 * @package   OSDownloads
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2014 Alledia.com, All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */
defined('_JEXEC') or die;
JHTML::_('behavior.modal');

JHtml::_('stylesheet', JUri::base() . 'media/com_osdownloads/css/osdownloads.css');

$app = JFactory::getApplication();
$itemId = (int) $app->input->get('Itemid');

$moduleTag = $params->get('module_tag', 'div');
$headerTag = $params->get('header_tag', 'h3');
$linkTo    = $params->get('link_to', 'download');
$doc       = JFactory::getDocument();

// Module body

if ($params->get('load_jquery', false)) {
    $doc->addScript('media/com_osdownloads/js/jquery.js');
}

if ($linkTo === 'download') {
    $doc->addScript('media/com_osdownloads/js/jquery.browser.min.js');
    $doc->addScript('media/com_osdownloads/js/jquery.reveal.min.js');
    $doc->addScript('media/com_osdownloads/js/jquery.iframe-auto-height.js');
}
?>

<<?php echo $moduleTag; ?> class="mod_osdownloadsfiles<?php echo $params->get('moduleclass_sfx'); ?>">
    <ul>
        <?php foreach ($list as $file) : ?>
            <li>
                <h4><?php echo $file->name; ?></h4>
                <p><?php echo $file->description_1; ?></p>
                <p>
                    <?php if ($linkTo === 'download') : ?>
                        <a class="modOSDownloadsButton" href="<?php echo JRoute::_('index.php?option=com_osdownloads&task=getdownloadlink&tmpl=component&Itemid=' . $itemId . '&id=' . $file->id); ?>" data-direct-page="<?php echo $file->direct_page; ?>">
                            <?php echo $params->get('link_label', JText::_('MOD_OSDOWNLOADSFILES_DOWNLOAD')); ?>
                        </a>
                    <?php else: ?>
                        <a class="modOSDownloadsButton" href="<?php echo JRoute::_('index.php?option=com_osdownloads&view=item&Itemid=' . $itemId . '&id=' . $file->id); ?>" data-direct-page="<?php echo $file->direct_page; ?>">
                            <?php echo $params->get('link_label', JText::_('MOD_OSDOWNLOADSFILES_READ_MORE')); ?>
                        </a>
                    <?php endif; ?>
                </p>
            </li>
        <?php endforeach; ?>
    </ul>
</<?php echo $moduleTag; ?>>

<?php if ($linkTo === 'download') : ?>
    <div id="modosdownloads-popup-iframe" class="reveal-modal">
        <iframe src="" class="auto-height" scrolling="no"></iframe>
        <a class="close-reveal-modal">&#215;</a>
    </div>

    <?php if ($linkTo === 'download') : ?>
        <script>
            (function ($) {
                $(function() {
                    // Move the popup container to the body
                    $('.reveal-modal').appendTo($('body'));

                    $('iframe.auto-height').iframeAutoHeight({
                        heightOffset: 10
                    });

                    function showModal(selector) {
                        $(selector).reveal({
                             animation: '<?php echo $params->get("popup_animation", "fade"); ?>',
                             animationspeed: 200,
                             closeonbackgroundclick: true,
                             dismissmodalclass: 'close-reveal-modal'
                        });
                    }

                    $(".modOSDownloadsButton").each(function(index, el) {
                        var directPage = function() {
                            var dp = $(el).data('direct-page');

                            if (dp) {
                                window.location = dp;
                            }
                        };

                        $(el).on('click', function(event) {
                            event.preventDefault();

                            var url = this.href;

                            var $iframe = $('#modosdownloads-popup-iframe iframe');
                            $iframe.attr('src', url);

                            setTimeout(function timeourShowModal() {
                                showModal('#modosdownloads-popup-iframe');
                            }, 500);
                        });
                    });
                });
            })(jQuery);
        </script>
    <?php endif; ?>
<?php endif;
