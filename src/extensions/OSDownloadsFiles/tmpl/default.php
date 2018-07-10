<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016-2018 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */
defined('_JEXEC') or die;

use Alledia\Framework\Helper as AllediaHelper;
use Alledia\OSDownloads\Free\Factory;
use Alledia\OSDownloads\Free\Joomla\Component\Site as FreeComponentSite;
use Joomla\Utilities\ArrayHelper;

jimport('joomla.application.component.helper');

$app       = JFactory::getApplication();
$doc       = JFactory::getDocument();
$lang      = JFactory::getLanguage();
$container = Factory::getContainer();

$this->itemId = (int)$app->input->getInt('Itemid');

$moduleTag = $this->params->get('module_tag', 'div');
$headerTag = $this->params->get('header_tag', 'h3');
$linkTo    = $this->params->get('link_to', 'download');

$requireEmail = false;
$requireAgree = false;
$requireShare = false;
$showModal    = false;


// Module body
$component = FreeComponentSite::getInstance();
$options   = array('version' => $component->getMediaVersion(), 'relative' => true);

JHtml::_('stylesheet', 'com_osdownloads/frontend.css', $options, array());

if ($linkTo === 'download') :
    JHtml::_('jquery.framework');
    JHtml::_('script', 'com_osdownloads/jquery.osdownloads.bundle.min.js', $options, array());
endif;

$moduleAttribs = array(
    'class' => 'mod_osdownloadsfiles' . $this->params->get('moduleclass_sfx'),
    'id'    => 'mod_osdownloads_' . $this->id
);

echo sprintf('<%s %s>', $moduleTag, ArrayHelper::toString($moduleAttribs));
?>
    <ul>
        <?php
        foreach ($this->list as $file) :
            $requireEmail = $file->require_user_email;
            $requireAgree = (bool)$file->require_agree;
            $requireShare = (bool)@$file->require_share;

            if (!$showModal) :
                $showModal = $requireEmail || $requireAgree || $requireShare;
            endif;

            ?>
            <li>
                <h4><?php echo $file->name; ?></h4>
                <p><?php echo $file->description_1; ?></p>
                <?php
                if ($linkTo === 'download') :
                    ?>
                    <div class="osdownloadsaction">
                        <div class="btn_download">
                            <?php
                            $this->item = $file;
                            echo JLayoutHelper::render(
                                'download_button',
                                $this,
                                JPATH_SITE . '/components/com_osdownloads/layouts'
                            );
                            ?>
                        </div>
                    </div>
                <?php
                else :
                    echo JHtml::_(
                        'link',
                        JRoute::_($container->helperRoute->getViewItemRoute($file->id, $this->itemId)),
                        $this->params->get('link_label', JText::_('COM_OSDOWNLOADS_FILES_READ_MORE')),
                        sprintf(
                            'class="modosdownloadsDownloadButton osdownloads-readmore readmore" data-direct-page="%s"',
                            $file->direct_page
                        )
                    );
                    ?>
                    <br clear="all"/>
                <?php
                endif;
                ?>
            </li>
        <?php
        endforeach;
        ?>
    </ul>
<?php
echo sprintf('</%s>', $moduleTag);
