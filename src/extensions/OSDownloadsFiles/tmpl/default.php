<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016-2017 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */
defined('_JEXEC') or die;

use Alledia\Framework\Helper as AllediaHelper;
use Alledia\OSDownloads\Free\Factory;

jimport('joomla.application.component.helper');

$app       = JFactory::getApplication();
$doc       = JFactory::getDocument();
$lang      = JFactory::getLanguage();
$container = Factory::getContainer();

$this->itemId = (int) $app->input->getInt('Itemid');

$moduleTag = $this->params->get('module_tag', 'div');
$headerTag = $this->params->get('header_tag', 'h3');
$linkTo    = $this->params->get('link_to', 'download');

$requireEmail = false;
$requireAgree = false;
$requireShare = false;
$showModal    = false;


// Module body
JHtml::stylesheet(JUri::root() . '/media/com_osdownloads/css/frontend.css');

if ($linkTo === 'download') {
    JHtml::_('jquery.framework');
    JHtml::script(JUri::root() . '/media/com_osdownloads/js/jquery.osdownloads.bundle.min.js');
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
                        <div class="osdownloadsaction">
                            <div class="btn_download">
                                <?php
                                    $this->item = $file;
                                    echo JLayoutHelper::render('download_button', $this, JPATH_SITE . '/components/com_osdownloads/layouts');
                                ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <a class="modosdownloadsDownloadButton osdownloads-readmore readmore" href="<?php echo JRoute::_($container->helperRoute->getViewItemRoute($file->id, $this->itemId)); ?>" data-direct-page="<?php echo $file->direct_page; ?>">
                            <?php echo $this->params->get('link_label', JText::_('MOD_OSDOWNLOADSFILES_READ_MORE')); ?>
                        </a>
                        <br clear="all" />
                    <?php endif; ?>
                </p>
            </li>
        <?php endforeach; ?>
    </ul>
</<?php echo $moduleTag; ?>>
