<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2005-2019 Joomlashack.com. All rights reserved
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
use Alledia\OSDownloads\Free\Factory;

defined('_JEXEC') or die();

use Alledia\OSDownloads\Free\Joomla\Component\Site as FreeComponentSite;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
JHtml::_('behavior.tabstate');
JHtml::_('behavior.tooltip');
JHtml::_('formbehavior.chosen', 'select');

JFilterOutput::objectHTMLSafe($this->item);

$container = Factory::getContainer();
?>
    <script type="text/javascript">
        Joomla.submitbutton = function(task) {
            if (task === 'cancel' || document.formvalidator.isValid(document.id('adminForm'))) {
                Joomla.submitform(task, document.getElementById('adminForm'));
            }
        }
    </script>

    <form name="adminForm"
          id="adminForm"
          action="<?php echo JRoute::_($container->helperRoute->getAdminMainViewRoute()); ?>"
          method="post"
          enctype="multipart/form-data"
          class="form-validate">

        <?php echo JLayoutHelper::render('joomla.edit.title_alias', $this); ?>

        <div class="form-horizontal">
            <?php
            echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'general'));

            echo JHtml::_('bootstrap.addTab', 'myTab', 'general', JText::_('COM_OSDOWNLOADS_FILE', true));
            ?>
            <div class="row-fluid">
                <div class="span9">
                    <?php echo $this->form->renderFieldset('file'); ?>

                    <div class="control-group">
                        <div class="control-label">
                            <?php echo JText::_('COM_OSDOWNLOADS_DESCRIPTIONS'); ?>
                        </div>
                        <div class="controls">
                            <?php
                            echo JHtml::_(
                                'bootstrap.startTabSet',
                                'myTabDescriptions',
                                array('active' => 'description1')
                            );

                            // Main Description
                            echo JHtml::_(
                                'bootstrap.addTab',
                                'myTabDescriptions',
                                'description1',
                                JText::_('COM_OSDOWNLOADS_DESCRIPTION_1H', true)
                            );
                            echo $this->form->getField('description_1')->input;
                            echo JHtml::_('bootstrap.endTab');

                            // Before Button text
                            echo JHtml::_(
                                'bootstrap.addTab',
                                'myTabDescriptions',
                                'description2',
                                JText::_('COM_OSDOWNLOADS_DESCRIPTION_2H', true)
                            );
                            echo $this->form->getField('description_2')->input;
                            echo JHtml::_('bootstrap.endTab');

                            // After button text
                            echo JHtml::_(
                                'bootstrap.addTab',
                                'myTabDescriptions',
                                'description3',
                                JText::_('COM_OSDOWNLOADS_DESCRIPTION_3H', true)
                            );
                            echo $this->form->getField('description_3')->input;
                            echo JHtml::_('bootstrap.endTab');

                            echo JHtml::_('bootstrap.endTabSet');
                            ?>
                        </div>
                    </div>
                </div>

                <div class="span3">
                    <fieldset class="form-vertical">
                        <?php echo $this->form->renderFieldset('file-vertical'); ?>
                    </fieldset>
                </div>
            </div>
            <?php
            echo JHtml::_('bootstrap.endTab');

            /*************************************
             *          Pro parameters           *
             *************************************/
            /** @var Licensed $component */
            $component = FreeComponentSite::getInstance();

            if ($component->isPro()) :
                echo $this->loadTemplate('custom_fields');
            endif;

            // Requirements tab
            echo JHtml::_(
                'bootstrap.addTab',
                'myTab',
                'requirements',
                JText::_('COM_OSDOWNLOADS_REQUIREMENTS_TO_DOWNLOAD', true)
            );

            echo $this->form->renderFieldset('requirements');

            echo JHtml::_('bootstrap.endTab');

            // Advanced tab
            echo JHtml::_('bootstrap.addTab', 'myTab', 'advanced', JText::_('COM_OSDOWNLOADS_ADVANCED', true));

            echo $this->form->renderFieldset('advanced');

            echo JHtml::_('bootstrap.endTab');
            echo JHtml::_('bootstrap.endTabSet');
            ?>
        </div>

        <input type="hidden" name="task" value=""/>
        <input type="hidden" name="option" value="com_osdownloads"/>
        <input type="hidden" name="id" value="<?php echo $this->item->id; ?>"/>
        <input type="hidden" name="cid[]" value="<?php echo $this->item->id; ?>"/>
        <?php echo JHTML::_('form.token'); ?>
    </form>
<?php
echo $this->extension->getFooterMarkup();
