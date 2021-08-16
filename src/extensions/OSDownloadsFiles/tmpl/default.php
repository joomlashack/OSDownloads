<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2005-2021 Joomlashack.com. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 *
 * This file is part of OSDownloads.
 *
 * OSDownloads is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * OSDownloads is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OSDownloads.  If not, see <http://www.gnu.org/licenses/>.
 */

defined('_JEXEC') or die();

use Alledia\OSDownloads\Factory;
use Alledia\OSDownloads\Free\Joomla\Component\Site as FreeComponentSite;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\Utilities\ArrayHelper;

$app       = Factory::getApplication();
$doc       = Factory::getDocument();
$lang      = Factory::getLanguage();
$container = Factory::getPimpleContainer();

$this->itemId = $app->input->getInt('Itemid');

$moduleTag = $this->params->get('module_tag', 'div');
$headerTag = $this->params->get('header_tag', 'h3');
$linkTo    = $this->params->get('link_to', 'download');

$requireEmail = false;
$requireAgree = false;
$requireShare = false;
$showModal    = false;


// Module body
$component = FreeComponentSite::getInstance();
$options   = ['version' => $component->getMediaVersion(), 'relative' => true];

HTMLHelper::_('stylesheet', 'com_osdownloads/frontend.css', $options, []);

if ($linkTo === 'download') :
    HTMLHelper::_('jquery.framework');
    HTMLHelper::_('script', 'com_osdownloads/jquery.osdownloads.bundle.min.js', $options, []);
endif;

$moduleAttribs = [
    'class' => 'mod_osdownloadsfiles' . $this->params->get('moduleclass_sfx'),
    'id'    => 'mod_osdownloads_' . $this->id
];

echo sprintf('<%s %s>', $moduleTag, ArrayHelper::toString($moduleAttribs));
?>
    <ul>
        <?php
        foreach ($this->list as $file) :
            $requireEmail = $file->require_user_email;
            $requireAgree = (bool)$file->require_agree;
            $requireShare = (bool)($file->require_share ?? false);

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
                            echo LayoutHelper::render(
                                'buttons.download',
                                $this,
                                null,
                                ['component' => 'com_osdownloads']
                            );
                            ?>
                        </div>
                    </div>
                <?php else :
                    echo HTMLHelper::_(
                        'link',
                        Route::_($container->helperRoute->getViewItemRoute($file->id, $this->itemId)),
                        $this->params->get('link_label', Text::_('COM_OSDOWNLOADS_FILES_READ_MORE')),
                        sprintf(
                            'class="modosdownloadsDownloadButton osdownloads-readmore readmore" data-direct-page="%s"',
                            $file->direct_page
                        )
                    );
                    ?>
                    <div style="clear: both;"></div>
                <?php endif;
                ?>
            </li>
        <?php endforeach; ?>
    </ul>
<?php
echo sprintf('</%s>', $moduleTag);
