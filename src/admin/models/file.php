<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2005-2025 Joomlashack.com. All rights reserved
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
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
 * along with OSDownloads.  If not, see <https://www.gnu.org/licenses/>.
 */

use Alledia\OSDownloads\Factory;
use Alledia\OSDownloads\Free\Helper\Helper;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Table\Table;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die();
// phpcs:enable PSR1.Files.SideEffects
// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace

class OsdownloadsModelFile extends AdminModel
{
    /**
     * @var string
     */
    protected $uploadDir = OSDOWNLOADS_MEDIA . '/files';

    /**
     * @var string[]
     */
    protected $uploadErrors = [
        UPLOAD_ERR_CANT_WRITE => 'COM_OSDOWNLOADS_UPLOAD_ERR_CANT_WRITE',
        UPLOAD_ERR_EXTENSION  => 'COM_OSDOWNLOADS_UPLOAD_ERR_EXTENSION',
        UPLOAD_ERR_FORM_SIZE  => 'COM_OSDOWNLOADS_UPLOAD_ERR_FORM_SIZE',
        UPLOAD_ERR_INI_SIZE   => 'COM_OSDOWNLOADS_UPLOAD_ERR_INI_SIZE',
        UPLOAD_ERR_NO_TMP_DIR => 'COM_OSDOWNLOADS_UPLOAD_ERR_NO_TMP_DIR',
        UPLOAD_ERR_PARTIAL    => 'COM_OSDOWNLOADS_UPLOAD_ERR_PARTIAL',
    ];

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function getForm($data = [], $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm('com_osdownloads.file', 'file', ['control' => 'jform', 'load_data' => $loadData]);

        // Load the extension
        $extension = Factory::getExtension();
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

    /**
     * @inheritDoc
     */
    public function getTable($name = 'Document', $prefix = 'OSDownloadsTable', $options = [])
    {
        return Table::getInstance($name, $prefix, $options);
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    protected function loadFormData()
    {
        $data = Factory::getApplication()->getUserState('com_osdownloads.edit.file.data', []);

        if (empty($data)) {
            $data = $this->getItem();
        }

        $this->preprocessData('com_osdownloads.file', $data);

        return $data;
    }

    /**
     * @inheritDoc
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
            $item->agreementLink = $agreementRequired ? Helper::getArticleLink($agreementId) : '';

            $item->type = empty($item->file_url) ? 'upload' : 'url';
        }

        return $item;
    }

    /**
     * @inheritDoc
     */
    public function save($data)
    {
        try {
            $app = Factory::getApplication();

            if ($app->input->getCmd('task') == 'save2copy') {
                $original = clone $this->getTable();
                $original->load($app->input->getInt('id'));

                if ($data['name'] == $original->name) {
                    [$name, $alias] = $this->generateNewTitle($data['cate_id'], $data['alias'], $data['name']);

                    $data['name']  = $name;
                    $data['alias'] = $alias;

                } elseif ($data['alias'] == $original->alias) {
                    $data['alias'] = '';
                }

                $data['published']  = 0;
                $data['downloaded'] = 0;
            }

            $mainText = $data['description_1'];
            if (preg_match('#<hr\s+id=["|\']system-readmore["|\']\s*/*>#i', $mainText, $match)) {
                $splitText = explode($match[0], $mainText, 2);
            } else {
                $splitText = [$mainText, ''];
            }

            $data['description_1'] = array_pop($splitText);
            $data['brief']         = array_pop($splitText);

            $data['require_email']        = (int)$data['require_email'];
            $data['require_agree']        = (int)$data['require_agree'];
            $data['agreement_article_id'] = (int)$data['agreement_article_id'];

            $type = empty($data['type']) ? null : $data['type'];
            switch ($type) {
                case 'url':
                    $data['file_path'] = '';
                    break;

                case 'upload':
                    $this->uploadFile($data);
                    $data['file_url'] = '';
                    break;

                default:
                    throw new Exception(Text::sprintf('COM_OSDOWNLOADS_ERROR_FILE_TYPE_UNKNOWN', $type));
            }

        } catch (Throwable $e) {
            $this->setError($e->getMessage());
            return false;
        }

        if (parent::save($data)) {
            $this->gcFileUploads();

            return true;
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function delete(&$pks)
    {
        if (parent::delete($pks)) {
            $this->gcFileUploads();

            return true;
        }

        return false;
    }

    /**
     * Check for any files in the upload folder that are not linked
     */
    protected function gcFileUploads()
    {
        $db = $this->getDbo();

        $query = $db->getQuery(true)
            ->select('file_path')
            ->from('#__osdownloads_documents')
            ->where('file_path != ' . $db->quote(''));

        $files         = $db->setQuery($query)->loadColumn();
        $uploadedFiles = Folder::files($this->uploadDir);

        $unlinkedFiles = array_diff($uploadedFiles, $files);
        foreach ($unlinkedFiles as $file) {
            File::delete($this->uploadDir . '/' . $file);
        }
    }

    /**
     * Handle all the work of uploading a selected file
     *
     * @param array $data
     *
     * @return void
     * @throws Exception
     */
    protected function uploadFile(array &$data): void
    {
        $app    = Factory::getApplication();
        $files  = $app->input->files->get('jform', [], 'raw');
        $upload = new Registry($files['file_path_upload']);

        $fileName = $upload->get('name');
        if (empty($fileName)) {
            if (empty($data['file_path'])) {
                if (empty($files['file_path_upload']['name'])) {
                    $error = 'COM_OSDOWNLOADS_UPLOAD_ERR_EMPTY_FIELD';

                } else {
                    $error = 'COM_OSDOWNLOADS_UPLOAD_ERR_REQUIRED';
                }

                throw new Exception(Text::_($error));
            }

            return;
        }

        $uploadError = $upload->get('error');
        if ($uploadError == UPLOAD_ERR_NO_FILE) {
            throw new Exception(Text::_('COM_OSDOWNLOADS_UPLOAD_ERR_NO_FILE'));

        } elseif ($uploadError != UPLOAD_ERR_OK) {
            if (isset($this->uploadErrors[$uploadError])) {
                $errorMessage = Text::_($this->uploadErrors[$uploadError]);

            } else {
                $errorMessage = Text::sprintf('COM_OSDOWNLOADS_UPLOAD_ERR_UNKNOWN', $uploadError);
            }

            throw new Exception($errorMessage);
        }

        if ($fileName = File::makeSafe($fileName)) {
            if (!is_dir($this->uploadDir)) {
                if (is_file($this->uploadDir)) {
                    throw new Exception(Text::_('COM_OSDOWNLOADS_UPLOAD_ERR_FILESYSTEM'));
                }

                Folder::create($this->uploadDir);

                $accessFile = [
                    'Order Deny,Allow',
                    'Deny from all',
                    '',
                ];
                file_put_contents($this->uploadDir . '/.htaccess', join("\n", $accessFile));
            }

            $fileHash = md5(microtime()) . '_' . $fileName;
            $filePath = Path::clean($this->uploadDir . '/' . $fileHash);

            $tempPath = $upload->get('tmp_name');

            // Disable file extension and tag checks
            $safeFileOptions = [
                'forbidden_extensions'       => [],
                'php_tag_in_content'         => false,
                'shorttag_in_content'        => false,
                'shorttag_extensions'        => [],
                'fobidden_ext_in_content'    => false,
                'php_ext_content_extensions' => [],
            ];

            if (!File::upload($tempPath, $filePath, false, false, $safeFileOptions)) {
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
                    $uploadErrorMessage = Text::_('COM_OSDOWNLOADS_UPLOAD_ERR_UNKNOWN');
                }

                throw new Exception($uploadErrorMessage);
            }

            $data['file_path'] = $fileHash;

            return;
        }

        $fileName = $upload->get('name');
        throw new Exception(Text::sprintf('COM_OSDOWNLOADS_UPLOAD_ERR_FILENAME', $fileName));
    }
}
