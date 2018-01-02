<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016-2017 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');

use Alledia\Framework\Helper as AllediaHelper;
use Alledia\OSDownloads\Free\Factory;
use Alledia\OSDownloads\Free\Joomla\Component\Site as FreeComponentSite;

$this->app                    = Factory::getApplication();
$this->lang                   = Factory::getLanguage();
$this->container              = Factory::getContainer();
$this->params                 = $this->app->getParams();
$this->numberOfColumns        = (int)$this->params->get("number_of_column", 1);
$this->authorizedAccessLevels = Factory::getUser()->getAuthorisedViewLevels();
$this->itemId                 = $this->app->input->getInt('Itemid');
$this->id                     = $this->app->input->getInt('id');
$this->showModal              = false;
$this->isPro                  = FreeComponentSite::getInstance()->isPro();

JHtml::_('jquery.framework');
JHtml::script(JUri::root() . '/media/com_osdownloads/js/jquery.osdownloads.bundle.min.js');

?>
<form action="<?php echo JRoute::_($this->container->helperRoute->getFileListRoute($this->id, $this->itemId)); ?>" method="post" name="adminForm" id="adminForm">
    <div class="contentopen osdownloads-container">
        <?php if ($this->showCategoryFilter && !empty($this->categories)) : ?>
            <div class="category_filter columns-<?php echo $this->numberOfColumns; ?>">
                <?php
                $i = 0;
                foreach ($this->categories as $category) : ?>
                    <?php if (in_array($category->access, $this->authorizedAccessLevels)) : ?>
                        <?php $column = $i % $this->numberOfColumns; ?>

                        <div class="column column-<?php echo $column; ?> item<?php echo $column; ?> cate_<?php echo $category->id; ?>">
                            <h3>
                                <a href="<?php echo JRoute::_($this->container->helperRoute->getFileListRoute($category->id, $this->itemId)); ?>">
                                    <?php echo $category->title; ?>
                                </a>
                            </h3>
                            <div class="item_content">
                                <?php echo $category->description; ?>
                            </div>
                        </div>
                        <?php if ($this->numberOfColumns && $i % $this->numberOfColumns == $this->numberOfColumns - 1) : ?>
                            <div class="seperator"></div>
                            <div class="clr"></div>
                        <?php endif; ?>
                        <?php $i++; ?>
                    <?php endif; ?>
                <?php endforeach; ?>
                <div class="clr"></div>
            </div>
        <?php endif; ?>

        <?php if (!empty($this->items)) : ?>
            <div class="items columns-<?php echo $this->numberOfColumns; ?>">
                <?php foreach ($this->items as $file) : ?>
                    <?php
                    $column = $i % $this->numberOfColumns;
                    $this->file = $file;
                    ?>

                    <div class="column column-<?php echo $column; ?> item<?php echo $column; ?> file_<?php echo $this->file->id; ?>">
                        <?php
                        $this->requireEmail = $this->file->require_user_email;
                        $this->requireAgree = (bool) $this->file->require_agree;
                        $this->requireShare = (bool) @$this->file->require_share;

                        if (!$this->showModal) {
                            $this->showModal = $this->requireEmail || $this->requireAgree || $this->requireShare;
                        }

                        if (in_array($this->file->access, $this->authorizedAccessLevels)) :
                            echo $this->loadTemplate(($this->isPro ? 'pro' : 'free') . '_item');
                        endif;
                        ?>
                    </div>

                    <?php if ($this->numberOfColumns && $i % $this->numberOfColumns == $this->numberOfColumns - 1) : ?>
                        <div class="seperator"></div>
                        <div class="clr"></div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php else : ?>
            <div class="osd-alert">
                <?php echo JText::_('COM_OSDOWNLOADS_NO_DOWNLOADS'); ?>
            </div>
        <?php endif; ?>

        <div class="clr"></div>
        <div class="osdownloads-pages-counter"><?php echo $this->pagination->getPagesCounter(); ?></div>
        <div class="osdownloads-pagination"><?php echo $this->pagination->getPagesLinks(); ?></div>
    </div>

</form>

<?php if ($this->params->get('show_download_button', 1)) : ?>
    <div id="osdownloadsRequirementsPopup" class="reveal-modal osdownloads-modal <?php echo AllediaHelper::getJoomlaVersionCssClass(); ?>">
        <h2 class="title"><?php echo JText::_('COM_OSDOWNLOADS_BEFORE_DOWNLOAD'); ?></h2>

        <div id="osdownloadsEmailGroup" class="osdownloadsemail" style="display: none;">

            <label for="osdownloadsRequireEmail">
                <input type="email" aria-required="true" required name="require_email" id="osdownloadsRequireEmail" placeholder="<?php echo JText::_("COM_OSDOWNLOADS_ENTER_EMAIL_ADDRESS"); ?>" />
            </label>

            <div class="error" style="display: none;" id="osdownloadsErrorInvalidEmail">
                <?php echo JText::_("COM_OSDOWNLOADS_INVALID_EMAIL"); ?>
            </div>
        </div>

        <div id="osdownloadsAgreeGroup" class="osdownloadsagree" style="display: none;">
            <label for="osdownloadsRequireAgree">
                <input type="checkbox" name="require_agree" id="osdownloadsRequireAgree" value="1" />
                <span>
                    * <?php echo JText::_("COM_OSDOWNLOADS_DOWNLOAD_TERM"); ?>
                </span>
            </label>

            <div class="error" style="display: none;" id="osdownloadsErrorAgreeTerms">
                <?php echo JText::_("COM_OSDOWNLOADS_YOU_HAVE_AGREE_TERMS_TO_DOWNLOAD_THIS"); ?>
            </div>
        </div>

        <?php if ($this->isPro) : ?>
            <div id="osdownloadsShareGroup" class="osdownloadsshare" style="display: none;">
                <!-- Facebook -->
                <div id="fb-root"></div>

                <p id="osdownloadsRequiredShareMessage" style="display: none;">
                    <?php echo JText::_('COM_OSDOWNLOADS_YOU_MUST_TWEET_SHARE_FACEBOOK'); ?>
                </p>

                <div class="error" style="display: none;" id="osdownloadsErrorShare">
                    <?php echo JText::_("COM_OSDOWNLOADS_SHARE_TO_DOWNLOAD_THIS"); ?>
                </div>
            </div>

        <?php endif; ?>

        <a href="#"  id="osdownloadsDownloadContinue" class="osdownloads-readmore readmore">
            <span>
                <?php echo JText::_("COM_OSDOWNLOADS_CONTINUE"); ?>
            </span>
        </a>

        <a class="close-reveal-modal">&#215;</a>
    </div>

    <script>
    (function ($) {

        $(function osdownloadsDomReady() {
            $('.osdownloads-container .osdownloadsDownloadButton').osdownloads({
                animation: '<?php echo $this->params->get("popup_animation", "fade"); ?>',
                elementsPrefix: 'osdownloads',
                popupElementId: 'osdownloadsRequirementsPopup'
            });
        });

    })(jQuery);
    </script>

<?php endif; ?>
