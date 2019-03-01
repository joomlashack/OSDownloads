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

JLoader::register('ContentHelperRoute', JPATH_SITE . '/components/com_content/helpers/route.php');

abstract class OSDownloadsModelFileAbstract extends JModelAdmin
{
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
        $data = JFactory::getApplication()->getUserState("com_osdownloads.edit.file.data", array());

        if (empty($data)) {
            $data = $this->getItem();
        }

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
            if ($description= $item->get('description_1')) {
                $item->description_1 = sprintf(
                    '%s<hr id="system-readmore" />%s',
                    $item->get('brief'),
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

    public function save($data)
    {
        $mainText = $data['description_1'];
        if (preg_match('#<hr\s+id=("|\')system-readmore("|\')\s*\/*>#i', $mainText, $match)) {
            $splitText = explode($match[0], $mainText, 2);
        } else {
            $splitText = array($mainText, '');
        }

        $data['description_1'] = array_pop($splitText);
        $data['brief']         = array_pop($splitText);

        $data['require_email'] = (int)$data['require_email'];
        $data['require_agree'] = (int)$data['require_agree'];

        if (empty($data['file_url'])) {
            $this->uploadFile($data);
        } else {
            $this->clearFilePath($data);
        }

        return parent::save($data);
    }

    protected function clearFilePath(&$data) {
        $app = JFactory::getApplication();
        $app->enqueueMessage(__METHOD__);
    }

    protected function uploadFile($data)
    {
        $app = JFactory::getApplication();
        $app->enqueueMessage(__METHOD__ . '<pre>' . print_r($data, 1) . '</pre>');
        return;

        $files = $app->input->files->get('jform', null, 'raw');
        if (isset($files['file'])) {
            $file        = $files['file'];
            $fileName    = $file["name"];
            $fileTmpName = $file["tmp_name"];
        } else {
            $fileName    = '';
            $fileTmpName = '';
        }

        if (!empty($fileName)) {
            $fileName = JFile::makeSafe($fileName);

            if (isset($fileName) && $fileName) {
                $uploadDir = JPATH_SITE . "/media/com_osdownloads/files/";

                $oldFile = $app->input->getPath('old_dir');
                if ($oldFile && JFile::exists(JPath::clean($uploadDir . $oldFile))) {
                    unlink(JPath::clean($uploadDir . $oldFile));
                }

                if (!JFolder::exists(JPath::clean($uploadDir))) {
                    JFolder::create(JPath::clean($uploadDir));
                }

                $timestamp      = md5(microtime());
                $filepath       = JPath::clean($uploadDir . $timestamp . "_" . $fileName);
                $row->file_path = $timestamp . "_" . $fileName;


                $safeFileOptions = array(
                    // Null byte in file name
                    'null_byte'                  => true,
                    // Forbidden string in extension (e.g. php matched .php, .xxx.php, .php.xxx and so on)
                    'forbidden_extensions'       => array(),
                    // <?php tag in file contents
                    'php_tag_in_content'         => false,
                    // <? tag in file contents
                    'shorttag_in_content'        => false,
                    // Which file extensions to scan for short tags
                    'shorttag_extensions'        => array(),
                    // Forbidden extensions anywhere in the content
                    'fobidden_ext_in_content'    => false,
                    // Which file extensions to scan for .php in the content
                    'php_ext_content_extensions' => array(),
                );

                if (!JFile::upload($fileTmpName, $filepath, false, false, $safeFileOptions)) {
                    // Upload failed and message already queued
                    $this->setRedirect(
                        $container->helperRoute->getAdminFileFormRoute($row->id)
                    );
                    return;
                }
            }
        }

    }
}
