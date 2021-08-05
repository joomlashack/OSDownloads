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

use Alledia\Framework\Joomla\Extension\Licensed;
use Alledia\OSDownloads\Factory;

defined('_JEXEC') or die();

use Alledia\OSDownloads\Free\Joomla\Component\Site as FreeComponentSite;

$container = Factory::getPimpleContainer();

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
    ->useScript('form.validate');
?>
<form name="adminForm"
          id="adminForm"
          action="<?php echo JRoute::_($container->helperRoute->getAdminMainViewRoute()); ?>"
          method="post"
          enctype="multipart/form-data"
          class="form-validate">

    <?php echo JLayoutHelper::render('joomla.edit.title_alias', $this); ?>

    <div class="main-card">
        <?php echo JHTML::_('uitab.startTabSet', 'myTab', array('active' => 'general')); ?>

        <?php echo JHTML::_('uitab.addTab', 'myTab', 'general', JText::_('COM_OSDOWNLOADS_FILE', true)); ?>
        <div class="row">
            <div class="col-lg-9">
                <div>
                    <fieldset class="adminform">

                        <?php echo $this->form->renderFieldset('file'); ?>

                        <div class="control-group">
                            <div class="control-label">
                                <?php echo JText::_('COM_OSDOWNLOADS_DESCRIPTIONS'); ?>
                            </div>
                            <div class="controls">
                                <?php
                                echo JHTML::_('uitab.startTabSet', 'myTabDescriptions', array('active' => 'description1'));

                                echo JHTML::_('uitab.addTab', 'myTabDescriptions', 'description1', JText::_('COM_OSDOWNLOADS_DESCRIPTION_1H', true));
                                echo $this->form->getField('description_1')->input;
                                echo JHtml::_('uitab.endTab');

                                echo JHTML::_('uitab.addTab', 'myTabDescriptions', 'description2', JText::_('COM_OSDOWNLOADS_DESCRIPTION_2H', true));
                                echo $this->form->getField('description_2')->input;
                                echo JHtml::_('uitab.endTab');

                                echo JHTML::_('uitab.addTab', 'myTabDescriptions', 'description3', JText::_('COM_OSDOWNLOADS_DESCRIPTION_3H', true));
                                echo $this->form->getField('description_3')->input;
                                echo JHtml::_('uitab.endTab');

                                echo JHTML::_('uitab.endTabSet');
                                ?>
                            </div>
                        </div>

                    </fieldset>
                </div>
            </div>
            <div class="col-lg-3">
                <?php echo $this->form->renderFieldset('file-vertical'); ?>
            </div>
        </div>
        <?php
        echo JHTML::_('uitab.endTab');

        /*************************************
         *          Pro parameters           *
         *************************************/
        /** @var Licensed $component */
        $component = FreeComponentSite::getInstance();

        if ($component->isPro()) :
            echo $this->loadTemplate('custom_fields');
        endif;

        echo JHTML::_('uitab.addTab', 'myTab', 'requirements', JText::_('COM_OSDOWNLOADS_REQUIREMENTS_TO_DOWNLOAD', true));
        echo $this->form->renderFieldset('requirements');
        echo JHTML::_('uitab.endTab');

        echo JHTML::_('uitab.addTab', 'myTab', 'advanced', JText::_('COM_OSDOWNLOADS_ADVANCED', true));
        echo $this->form->renderFieldset('advanced');
        echo JHTML::_('uitab.endTab');

        echo JHTML::_('uitab.endTabSet');
        ?>

    </div>


    <input type="hidden" name="task" value=""/>
    <input type="hidden" name="option" value="com_osdownloads"/>
    <input type="hidden" name="id" value="<?php echo $this->item->id; ?>"/>
    <input type="hidden" name="cid[]" value="<?php echo $this->item->id; ?>"/>
    <?php echo JHTML::_('form.token'); ?>
</form>