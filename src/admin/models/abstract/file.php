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

defined('_JEXEC') or die();

use Alledia\Framework\Factory;
use Joomla\CMS\Application\AdministratorApplication;

JLoader::register('ContentHelperRoute', JPATH_SITE . '/components/com_content/helpers/route.php');

abstract class OSDownloadsModelFileAbstract extends JModelAdmin
{
    /**
     * The parent method is being overridden because we're doing things
     * weirdly
     *
     * @return void
     * @throws Exception
     */
    protected function populateState()
    {
        /** @var AdministratorApplication $app */
        $app = JFactory::getApplication();

        $id = $app->input->get('cid', array(), 'array');
        $id = (int)array_shift($id);

        $this->setState('file.id', $id);
    }

    /**
     * @param array $data
     * @param bool  $loadData
     *
     * @return JForm
     * @throws Exception
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm('com_osdownloads.file', 'file', array('control' => 'jform', 'load_data' => $loadData));

        // Load the extension
        $extension = Factory::getExtension('OSDownloads', 'component');
        $extension->loadLibrary();

        if (empty($form)) {
            return null;
        }

        if ($loadData) {
            $data = $this->loadFormData();

            // Load the data into the form after the plugins have operated.
            $form->bind($data);
        }

        return $form;
    }

    public function getTable($type = 'Document', $prefix = 'OSDownloadsTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    /**
     * @return array|bool|JObject|object
     * @throws Exception
     */
    protected function loadFormData()
    {
        $data = $this->getItem();

        $this->preprocessData('com_osdownloads.file', $data);

        return $data;
    }

    /**
     * @param int $pk
     *
     * @return JObject
     * @throws Exception
     */
    public function getItem($pk = null)
    {
        if ($item = parent::getItem($pk)) {
            if ($description = $item->get('description_1')) {
                $brief = $item->get('brief');

                $item->description_1 = sprintf(
                    '%s<hr id="system-readmore" />%s',
                    $brief,
                    $description
                );

            } else {
                $item->description_1 = $item->get('brief');
            }

            $agreementRequired   = (bool)$item->get('require_agree');
            $agreementId         = (int)$item->get('agreement_article_id');
            $item->agreementLink = ($agreementRequired && $agreementId)
                ? JRoute::_(ContentHelperRoute::getArticleRoute($agreementId))
                : '';
        }

        return $item;
    }
}
