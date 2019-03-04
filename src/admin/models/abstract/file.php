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
use Joomla\Registry\Registry;

JLoader::register('ContentHelperRoute', JPATH_SITE . '/components/com_content/helpers/route.php');

abstract class OSDownloadsModelFileAbstract extends JModelAdmin
{
    protected $uploadDir = JPATH_SITE . '/media/com_osdownloads/files';

    protected $uploadErrors = array(
        UPLOAD_ERR_CANT_WRITE => 'COM_OSDOWNLOADS_UPLOAD_ERR_CANT_WRITE',
        UPLOAD_ERR_EXTENSION  => 'COM_OSDOWNLOADS_UPLOAD_ERR_EXTENSION',
        UPLOAD_ERR_FORM_SIZE  => 'COM_OSDOWNLOADS_UPLOAD_ERR_FORM_SIZE',
        UPLOAD_ERR_INI_SIZE   => 'COM_OSDOWNLOADS_UPLOAD_ERR_INI_SIZE',
        UPLOAD_ERR_NO_TMP_DIR => 'COM_OSDOWNLOADS_UPLOAD_ERR_NO_TMP_DIR',
        UPLOAD_ERR_PARTIAL    => 'COM_OSDOWNLOADS_UPLOAD_ERR_PARTIAL'
    );

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
            if ($description = $item->get('description_1')) {
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

            $item->type = $item->file_url ? 'url' : 'upload';
        }

        return $item;
    }

    public function save($data)
    {
        try {
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

            // File URL takes precedence over File Path
            if (empty($data['file_url'])) {
                $this->uploadFile($data);

            } else {
                $this->clearFilePath($data);
            }
        } catch (Exception $e) {
            $this->setError($e->getMessage());
            return false;

        } catch (Throwable $e) {
            $this->setError($e->getMessage());
            return false;
        }

        return parent::save($data);
    }

    /**
     * Clear file_path in the submitted data and delete the file
     *
     * @param array $data
     */
    protected function clearFilePath(array &$data)
    {
        $currentFile     = empty($data['file_path']) ? null : $data['file_path'];
        $currentFilePath = $this->uploadDir . '/' . $currentFile;
        if (file_exists($currentFilePath)) {
            unlink($currentFilePath);
        }

        $data['file_path'] = '';
    }

    /**
     * Handle all the work of uploading a selected file
     *
     * @param array $data
     *
     * @return void
     * @throws Exception
     */
    protected function uploadFile(&$data)
    {
        $app   = JFactory::getApplication();
        $files = $app->input->files->get('jform', array(), 'raw');

        if (empty($files['file_path_upload'])) {
            return;
        }

        $upload = new Registry($files['file_path_upload']);

        $uploadError = $upload->get('error');
        if ($uploadError == UPLOAD_ERR_NO_FILE) {
            return;

        } elseif ($uploadError != UPLOAD_ERR_OK) {
            if (isset($this->uploadErrors[$uploadError])) {
                $errorMessage = JText::_($this->uploadErrors[$uploadError]);

            } else {
                $errorMessage = JText::sprintf('COM_OSDOWNLOADS_UPLOAD_ERR_UNKNOWN', $uploadError);
            }

            throw new Exception($errorMessage);
        }

        jimport('joomla.filesystem.file');
        if ($fileName = JFile::makeSafe($upload->get('name'))) {
            jimport('joomla.filesystem.folder');

            if (!is_dir($this->uploadDir)) {
                if (is_file($this->uploadDir)) {
                    throw new Exception(JText::_('COM_OSDOWNLOADS_UPLOAD_ERR_FILESYSTEM'));
                }

                JFolder::create($this->uploadDir);

                $accessFile = array(
                    'Order Deny,Allow',
                    'Deny from all',
                    ''
                );
                file_put_contents($this->uploadDir . '/.htaccess', join("\n", $accessFile));
            }

            $fileHash = md5(microtime()) . '_' . $fileName;
            $filePath = JPath::clean($this->uploadDir . '/' . $fileHash);

            $tempPath = $upload->get('tmp_name');

            // Disable file extension and tag checks
            $safeFileOptions = array(
                'forbidden_extensions'       => array(),
                'php_tag_in_content'         => false,
                'shorttag_in_content'        => false,
                'shorttag_extensions'        => array(),
                'fobidden_ext_in_content'    => false,
                'php_ext_content_extensions' => array(),
            );

            if (!JFile::upload($tempPath, $filePath, false, false, $safeFileOptions)) {
                if ($messages = $app->getMessageQueue(true)) {
                    $uploadErrorMessage = array_pop($messages);

                    // Restore any previous messages
                    foreach ($messages as $message) {
                        $app->enqueueMessage($message['message'], $message['type']);
                    }

                    $uploadErrorMessage = str_replace(
                        $filePath,
                        '"' . $fileName . '"',
                        $uploadErrorMessage['message']
                    );

                } else {
                    $uploadErrorMessage = JText::_('COM_OSDOWNLOADS_UPLOAD_ERR_UNKNOWN');
                }

                throw new Exception($uploadErrorMessage);
            }

            $this->clearFilePath($data);
            $data['file_path'] = $fileHash;

            return;
        }

        $fileName = $upload->get('name');
        throw new Exception(JText::sprintf('COM_OSDOWNLOADS_UPLOAD_ERR_FILENAME', $fileName));
    }
}
