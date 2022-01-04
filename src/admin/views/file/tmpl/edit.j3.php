<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2005-2022 Joomlashack.com. All rights reserved
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
use Alledia\OSDownloads\Free\Joomla\Component\Site as FreeComponentSite;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

defined('_JEXEC') or die();

HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');

HTMLHelper::_('behavior.formvalidation');
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('formbehavior.chosen', 'select');

OutputFilter::objectHtmlSafe($this->item);

$container  = Factory::getPimpleContainer();
$formAction = Route::_('index.php?option=com_osdownloads&view=file&layout=edit&id=' . (int)$this->item->id);
?>
<script type="text/javascript">
    Joomla.submitbutton = function(task) {
        if (task === 'file.cancel' || document.formvalidator.isValid(document.id('adminForm'))) {
            Joomla.submitform(task, document.getElementById('adminForm'));
        }
    }
</script>

<form name="adminForm"
      id="adminForm"
      action="<?php echo $formAction; ?>"
      method="post"
      enctype="multipart/form-data"
      class="form-validate">

    <?php echo LayoutHelper::render('joomla.edit.title_alias', $this); ?>

    <div class="form-horizontal">
        <?php
        echo HTMLHelper::_('bootstrap.startTabSet', 'myTab', ['active' => 'general']);

        echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'general', Text::_('COM_OSDOWNLOADS_FILE', true));
        ?>
        <div class="row-fluid">
            <div class="span9">
                <?php echo $this->form->renderFieldset('file'); ?>

                <div class="control-group">
                    <div class="control-label">
                        <?php echo Text::_('COM_OSDOWNLOADS_DESCRIPTIONS'); ?>
                    </div>
                    <div class="controls">
                        <?php
                        echo HTMLHelper::_(
                            'bootstrap.startTabSet',
                            'myTabDescriptions',
                            ['active' => 'description1']
                        );

                        // Main Description
                        echo HTMLHelper::_(
                            'bootstrap.addTab',
                            'myTabDescriptions',
                            'description1',
                            Text::_('COM_OSDOWNLOADS_DESCRIPTION_1H', true)
                        );
                        echo $this->form->getField('description_1')->input;
                        echo HTMLHelper::_('bootstrap.endTab');

                        // Before Button text
                        echo HTMLHelper::_(
                            'bootstrap.addTab',
                            'myTabDescriptions',
                            'description2',
                            Text::_('COM_OSDOWNLOADS_DESCRIPTION_2H', true)
                        );
                        echo $this->form->getField('description_2')->input;
                        echo HTMLHelper::_('bootstrap.endTab');

                        // After button text
                        echo HTMLHelper::_(
                            'bootstrap.addTab',
                            'myTabDescriptions',
                            'description3',
                            Text::_('COM_OSDOWNLOADS_DESCRIPTION_3H', true)
                        );
                        echo $this->form->getField('description_3')->input;
                        echo HTMLHelper::_('bootstrap.endTab');

                        echo HTMLHelper::_('bootstrap.endTabSet');
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
        echo HTMLHelper::_('bootstrap.endTab');

        /** @var Licensed $component */
        $component = FreeComponentSite::getInstance();
        if ($component->isPro()) :
            echo $this->loadTemplate('custom_fields');
        endif;

        // Requirements tab
        echo HTMLHelper::_(
            'bootstrap.addTab',
            'myTab',
            'requirements',
            Text::_('COM_OSDOWNLOADS_REQUIREMENTS_TO_DOWNLOAD', true)
        );

        echo $this->form->renderFieldset('requirements');

        echo HTMLHelper::_('bootstrap.endTab');

        // Advanced tab
        echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'advanced', Text::_('COM_OSDOWNLOADS_ADVANCED', true));

        echo $this->form->renderFieldset('advanced');

        echo HTMLHelper::_('bootstrap.endTab');
        echo HTMLHelper::_('bootstrap.endTabSet');
        ?>
    </div>

    <input type="hidden" name="task" value=""/>
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
