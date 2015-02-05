<?php
/**
 * @package   OSDownloads
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2014 Alledia.com, All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */
defined('_JEXEC') or die;

use Alledia\Framework\Helper as AllediaHelper;

jimport('joomla.application.component.helper');

$app    = JFactory::getApplication();
$doc    = JFactory::getDocument();
$lang   = JFactory::getLanguage();
$itemId = (int) $app->input->get('Itemid');

$moduleTag = $this->params->get('module_tag', 'div');
$headerTag = $this->params->get('header_tag', 'h3');
$linkTo    = $this->params->get('link_to', 'download');

$requireEmail = false;
$requireAgree = false;
$requireShare = false;
$showModal    = false;

// Load language from component


// Module body
$doc->addStylesheet(JUri::base() . 'media/com_osdownloads/css/frontend.css');

if ($this->loadJQuery && !defined('ALLEDIA_JQUERY_LOADED')) {
    define('ALLEDIA_JQUERY_LOADED', 1);
    $doc->addScript('media/com_osdownloads/js/jquery.js');
}

if ($linkTo === 'download') {
    $doc->addScript('media/com_osdownloads/js/jquery.osdownloads.bundle.min.js', 'text/javascript', true);
}
?>

<<?php echo $moduleTag; ?> class="mod_osdownloadsfiles<?php echo $this->params->get('moduleclass_sfx'); ?>" id="mod_osdownloads_<?php echo $this->id; ?>">
    <ul>
        <?php foreach ($this->list as $file) : ?>
            <?php
            $requireEmail = $file->require_user_email;
            $requireAgree = (bool) $file->require_agree;
            $requireShare = (bool) @$file->require_share;

            if (!$showModal) {
                $showModal = $requireEmail || $requireAgree || $requireShare;
            }

            ?>
            <li>
                <h4><?php echo $file->name; ?></h4>
                <p><?php echo $file->description_1; ?></p>
                <p>
                    <?php if ($linkTo === 'download') : ?>
                        <?php
                        $fileURL = JRoute::_('index.php?option=com_osdownloads&view=item&Itemid=' . $itemId . '&id=' . $file->id);
                        ?>
                        <a
                            href="<?php echo JRoute::_('index.php?option=com_osdownloads&task=routedownload&tmpl=component&Itemid=' . $itemId . '&id=' . $file->id); ?>"
                            class="modosdownloadsDownloadButton"
                            style="color:<?php echo $file->download_color;?>"
                            data-direct-page="<?php echo $file->direct_page; ?>"
                            data-require-email="<?php echo $requireEmail; ?>"
                            data-require-agree="<?php echo $requireAgree ? 1 : 0; ?>"
                            data-require-share="<?php echo $requireShare ? 1 : 0; ?>"
                            data-id="<?php echo $file->id; ?>"
                            data-url="<?php echo $fileURL; ?>"
                            data-lang="<?php echo $lang->getTag(); ?>"
                            data-name="<?php echo $file->name; ?>"
                            <?php if ($this->isPro()) : ?>
                                data-hashtags="<?php echo str_replace('#', '', @$file->twitter_hashtags); ?>"
                                data-via="<?php echo str_replace('@', '', @$file->twitter_via); ?>"
                            <?php endif; ?>
                            >
                            <span>
                                <?php echo $this->params->get('link_label', JText::_('MOD_OSDOWNLOADSFILES_DOWNLOAD')); ?>
                            </span>
                        </a>
                    <?php else: ?>
                        <a class="modosdownloadsDownloadButton readmore" href="<?php echo JRoute::_('index.php?option=com_osdownloads&view=item&Itemid=' . $itemId . '&id=' . $file->id); ?>" data-direct-page="<?php echo $file->direct_page; ?>">
                            <?php echo $this->params->get('link_label', JText::_('MOD_OSDOWNLOADSFILES_READ_MORE')); ?>
                        </a>
                        <br clear="all" />
                    <?php endif; ?>
                </p>
            </li>
        <?php endforeach; ?>
    </ul>
</<?php echo $moduleTag; ?>>

<?php if ($linkTo === 'download') : ?>
    <?php if ($showModal) : ?>
        <div id="modosdownloads<?php echo $this->id; ?>RequirementsPopup" class="reveal-modal osdownloads-modal <?php echo AllediaHelper::getJoomlaVersionCssClass(); ?>">
            <h1 class="title"><?php echo JText::_('COM_OSDOWNLOADS_BEFORE_DOWNLOAD'); ?></h1>

            <div id="modosdownloads<?php echo $this->id; ?>EmailGroup" class="osdownloadsemail" style="display: none;">

                <label for="modosdownloads<?php echo $this->id; ?>RequireEmail">
                    <input type="email" aria-required="true" required name="require_email" id="modosdownloads<?php echo $this->id; ?>RequireEmail" placeholder="<?php echo JText::_("COM_OSDOWNLOADS_ENTER_EMAIL_ADDRESS"); ?>" />
                </label>

                <div class="error" style="display: none;" id="modosdownloads<?php echo $this->id; ?>ErrorInvalidEmail">
                    <?php echo JText::_("COM_OSDOWNLOADS_INVALID_EMAIL"); ?>
                </div>
            </div>

            <div id="modosdownloads<?php echo $this->id; ?>AgreeGroup" class="osdownloadsagree" style="display: none;">
                <label for="modosdownloads<?php echo $this->id; ?>RequireAgree">
                    <input type="checkbox" name="require_agree" id="modosdownloads<?php echo $this->id; ?>RequireAgree" value="1" />
                    <span>
                        * <?php echo(JText::_("COM_OSDOWNLOADS_DOWNLOAD_TERM"));?>
                    </span>
                </label>

                <div class="error" style="display: none;" id="modosdownloads<?php echo $this->id; ?>ErrorAgreeTerms">
                    <?php echo JText::_("COM_OSDOWNLOADS_YOU_HAVE_AGREE_TERMS_TO_DOWNLOAD_THIS"); ?>
                </div>
            </div>

            <div id="modosdownloads<?php echo $this->id; ?>ShareGroup" class="osdownloadsshare" style="display: none;">
                <!-- Facebook -->
                <div id="fb-root"></div>

                <p id="modosdownloads<?php echo $this->id; ?>RequiredShareMessage" style="display: none;">
                    <?php echo JText::_('COM_OSDOWNLOADS_YOU_MUST_TWEET_SHARE_FACEBOOK'); ?>
                </p>

                <div class="error" style="display: none;" id="modosdownloads<?php echo $this->id; ?>ErrorShare">
                    <?php echo JText::_("COM_OSDOWNLOADS_SHARE_TO_DOWNLOAD_THIS"); ?>
                </div>
            </div>

            <a href="#"  id="modosdownloads<?php echo $this->id; ?>DownloadContinue" class="readmore">
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
                $('#mod_osdownloads_<?php echo $this->id; ?> .modosdownloadsDownloadButton').osdownloads({
                    animation: '<?php echo $this->popupAnimation; ?>',
                    elementsPrefix: 'modosdownloads<?php echo $this->id; ?>',
                    popupElementId: 'modosdownloads<?php echo $this->id; ?>RequirementsPopup'
                });
            });

        })(jQuery);
    </script>
<?php endif;
