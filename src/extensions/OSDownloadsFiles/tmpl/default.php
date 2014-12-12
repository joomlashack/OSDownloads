<?php
/**
 * @package   OSDownloads
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2014 Alledia.com, All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */
defined('_JEXEC') or die;

jimport('joomla.application.component.helper');

$app    = JFactory::getApplication();
$doc    = JFactory::getDocument();
$itemId = (int) $app->input->get('Itemid');

$moduleTag = $params->get('module_tag', 'div');
$headerTag = $params->get('header_tag', 'h3');
$linkTo    = $params->get('link_to', 'download');

$comParams = JComponentHelper::getParams('com_osdownloads');

$showEmail    = false;
$requireEmail = false;
$requireAgree = false;

// Load language from component
JFactory::getLanguage()->load('com_osdownloads');

// Module body

$doc->addStylesheet(JUri::base() . 'media/com_osdownloads/css/osdownloads.css');

if ($comParams->get('load_jquery', false)) {
    $doc->addScript('media/com_osdownloads/js/jquery.js');
}

if ($linkTo === 'download') {
    $doc->addScript('media/com_osdownloads/js/jquery.osdownload.bundle.min.js', 'text/javascript', true);
}
?>

<<?php echo $moduleTag; ?> class="mod_osdownloadsfiles<?php echo $params->get('moduleclass_sfx'); ?>" id="mod_osdownloads_<?php echo $module->id; ?>">
    <ul>
        <?php foreach ($list as $file) : ?>
            <?php
            if ($file->show_email) {
                $showEmail = true;
            }

            if ($file->require_email) {
                $requireEmail = true;
            }

            if ($file->require_agree) {
                $requireAgree = true;
            }
            ?>
            <li>
                <h4><?php echo $file->name; ?></h4>
                <p><?php echo $file->description_1; ?></p>
                <p>
                    <?php if ($linkTo === 'download') : ?>
                        <a
                            href="<?php echo JRoute::_('index.php?option=com_osdownloads&task=getdownloadlink&tmpl=component&Itemid=' . $itemId . '&id=' . $file->id); ?>"
                            class="modosdownloadsDownloadButton"
                            style="color:<?php echo $file->download_color;?>"
                            data-direct-page="<?php echo $file->direct_page; ?>"
                            data-show-email="<?php echo $file->show_email; ?>"
                            data-require-email="<?php echo $file->require_email; ?>"
                            data-require-agree="<?php echo $file->require_agree; ?>"
                            data-id="<?php echo $file->id; ?>"
                            >
                            <span>
                                <?php echo $params->get('link_label', JText::_('MOD_OSDOWNLOADSFILES_DOWNLOAD')); ?>
                            </span>
                        </a>
                    <?php else: ?>
                        <a class="modosdownloadsDownloadButton readmore" href="<?php echo JRoute::_('index.php?option=com_osdownloads&view=item&Itemid=' . $itemId . '&id=' . $file->id); ?>" data-direct-page="<?php echo $file->direct_page; ?>">
                            <?php echo $params->get('link_label', JText::_('MOD_OSDOWNLOADSFILES_READ_MORE')); ?>
                        </a>
                        <br clear="all" />
                    <?php endif; ?>
                </p>
            </li>
        <?php endforeach; ?>
    </ul>
</<?php echo $moduleTag; ?>>

<?php if ($linkTo === 'download') : ?>
    <?php if ($requireEmail || $showEmail || $requireAgree) : ?>
        <div id="modosdownloads<?php echo $module->id; ?>RequirementsPopup" class="reveal-modal">
            <h1 class="title"><?php echo JText::_('COM_OSDOWNLOADS_BEFORE_DOWNLOAD'); ?></h1>

            <div id="modosdownloads<?php echo $module->id; ?>EmailGroup" class="osdownloadsemail" style="display: none;">

                <p id="modosdownloads<?php echo $module->id; ?>RequiredEmailMessage" style="display: none;">
                    <?php echo JText::_('COM_OSDOWNLOADS_YOU_HAVE_INPUT_CORRECT_EMAIL_TO_GET_DOWNLOAD_LINK'); ?>
                </p>

                <label for="modosdownloads<?php echo $module->id; ?>RequireEmail">
                    <span>
                        <?php echo(JText::_("COM_OSDOWNLOADS_EMAIL")); ?>:
                    </span>
                    <input type="email" aria-required="true" required name="require_email" id="modosdownloads<?php echo $module->id; ?>RequireEmail" />
                </label>

                <div class="error" style="display: none;" id="modosdownloads<?php echo $module->id; ?>ErrorInvalidEmail">
                    <?php echo JText::_("COM_OSDOWNLOADS_INVALID_EMAIL"); ?>
                </div>
            </div>

            <div id="modosdownloads<?php echo $module->id; ?>AgreeGroup" class="osdownloadsagree" style="display: none;">
                <label for="modosdownloads<?php echo $module->id; ?>RequireAgree">
                    <input type="checkbox" name="require_agree" id="modosdownloads<?php echo $module->id; ?>RequireAgree" />
                    <span>
                        * <?php echo(JText::_("COM_OSDOWNLOADS_DOWNLOAD_TERM"));?>
                    </span>
                </label>

                <div class="error" style="display: none;" id="modosdownloads<?php echo $module->id; ?>ErrorAgreeTerms">
                    <?php echo JText::_("COM_OSDOWNLOADS_YOU_HAVE_AGREE_TERMS_TO_DOWNLOAD_THIS"); ?>
                </div>
            </div>

            <a href="#"  id="modosdownloads<?php echo $module->id; ?>DownloadContinue" class="readmore">
                <span>
                    <?php echo JText::_("COM_OSDOWNLOADS_CONTINUE"); ?>
                </span>
            </a>

            <a class="close-reveal-modal">&#215;</a>
        </div>
    <?php endif;?>

    <script>
        (function ($) {

            $(function modosdownloadsDomReady() {
                $('#mod_osdownloads_<?php echo $module->id; ?> .modosdownloadsDownloadButton').osdownloads({
                    animation: '<?php echo $comParams->get("popup_animation", "fade"); ?>',
                    elementsPrefix: 'modosdownloads<?php echo $module->id; ?>',
                    popupElementId: 'modosdownloads<?php echo $module->id; ?>RequirementsPopup'
                });
            });

        })(jQuery);
    </script>
<?php endif;
