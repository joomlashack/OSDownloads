<?php
/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016-2017 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

$includePath = __DIR__ . '/admin/library/Installer/include.php';
if (file_exists($includePath)) {
    require_once $includePath;
} else {
    require_once __DIR__ . '/library/Installer/include.php';
}

use Alledia\Installer\AbstractScript;
use Alledia\OSDownloads\Free\Container;

class AbstractOSDownloadsInstallerScript extends AbstractScript
{
    /**
     * Flag to set if the old mod_osdownloads is installed
     * @var boolean
     */
    protected $deprecatedModOSDownloadsIsInstalled;

    /**
     * Method to run after an install/update method
     *
     * @return void
     */
    public function postFlight($type, $parent)
    {
        $db = JFactory::getDBO();

        // The upload destination path has changed, so let's move the files to the new destination
        $this->moveCurrentUploadedFiles();

        // Check if mod_osdownloads is installed to show the warning of deprecated
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from('#__extensions')
            ->where($db->qn('type') . '=' . $db->q('module'))
            ->where($db->qn('element') . '=' . $db->q('mod_osdownloads'));
        $db->setQuery($query);
        $this->deprecatedModOSDownloadsIsInstalled = (int) $db->loadResult();

        parent::postFlight($type, $parent);

        // Legacy database update
        $db->setQuery("SHOW COLUMNS FROM #__osdownloads_documents");
        $rows = $db->loadObjectList();
        $db_version = "1.0.0";

        $has_show_email           = false;
        $has_description_3        = false;
        $has_direct_field         = false;
        $has_file_url             = false;
        $has_parent_id            = false;
        $has_cms_version          = false;
        $has_picture              = false;
        $has_external_ref         = false;
        $has_access               = false;
        $has_agreement_article_id = false;

        foreach ($rows as $row) {

            if ($row->Field == "show_email") {
                $has_show_email = true;
            }

            if ($row->Field == "description_3") {
                $has_description_3 = true;
            }

            if ($row->Field == "direct_page") {
                $has_direct_field = true;
            }

            if ($row->Field == "file_url") {
                $has_file_url = true;
            }

            if ($row->Field == "file_url") {
                $has_file_url = true;
            }

            if ($row->Field == 'parent_id') {
                $has_parent_id = true;
            }

            if ($row->Field == 'cms_version') {
                $has_cms_version = true;
            }

            if ($row->Field == 'picture') {
                $has_picture = true;
            }

            if ($row->Field == 'external_ref') {
                $has_external_ref = true;
            }

            if ($row->Field == 'access') {
                $has_access = true;
            }

            if ($row->Field == 'agreement_article_id') {
                $has_agreement_article_id = true;
            }
        }

        if ($has_show_email && !$has_description_3) {
            $db_version = "1.0.1";
        }

        if ($has_show_email && $has_description_3) {
            $db_version = "1.0.2";
        }

        if ($has_direct_field) {
            $db_version = "1.0.3";
        }

        if ($db_version == "1.0.0") {
            echo("<div>Migrate database from version 1.0.0 to 1.0.3</div>");
            $db->setQuery(file_get_contents(JPATH_ADMINISTRATOR.DS."components".DS."com_osdownloads".DS."sql".DS."update_1.0.0_1.0.3.mysql.utf8.sql"));
            $db->query();
        }
        if ($db_version == "1.0.1") {
            echo("<div>Migrate database from version 1.0.1 to 1.0.3</div>");
            $db->setQuery(file_get_contents(JPATH_ADMINISTRATOR.DS."components".DS."com_osdownloads".DS."sql".DS."update_1.0.1_1.0.3.mysql.utf8.sql"));
            $db->query();
        }

        if ($db_version == "1.0.2") {
            echo("<div>Migrate database from version 1.0.2 to 1.0.3</div>");
            $db->setQuery(file_get_contents(JPATH_ADMINISTRATOR.DS."components".DS."com_osdownloads".DS."sql".DS."update_1.0.2_1.0.3.mysql.utf8.sql"));
            $db->query();
        }
        if (!$has_description_3) {
            // Fix database if upgrade from version 1.0.2 (new install). If upgrade from 1.0.x to 1.0.2 it still correct
            $db->setQuery("SHOW COLUMNS FROM #__osdownloads_documents");
            $rows = $db->loadObjectList();
            $has_description_3 = false;
            foreach ($rows as $row) {
                if ($row->Field == "description_3") {
                    $has_description_3 = true;
                }
            }
            if (!$has_description_3) {
                echo("<div>Apply patch for database version 1.0.2</div>");
                $db->setQuery("ALTER TABLE `#__osdownloads_documents` ADD `description_3` TEXT NOT NULL AFTER `description_2`");
                $db->query();
            }
        }

        if (!$has_file_url) {
            echo("<div>Migrate database for remote files</div>");
            $sql = file_get_contents(JPATH_ADMINISTRATOR."/components/com_osdownloads/sql/updates/mysql/1.0.17.sql");
            $db->setQuery($sql);
            $db->query();
        }

        // Legacy database update
        $columns = array(
            'external_ref'         => 'VARCHAR(100)',
            'access'               => 'INT(11) NOT NULL DEFAULT 1',
            'agreement_article_id' => 'INT(11)',
            'created_user_id'      => 'INT(10) UNSIGNED NOT NULL DEFAULT "0"',
            'created_time'         => 'DATETIME NOT NULL DEFAULT "0000-00-00 00:00:00"',
            'modified_user_id'     => 'INT(10) unsigned NOT NULL DEFAULT "0"',
            'modified_time'        => 'DATETIME NOT NULL DEFAULT "0000-00-00 00:00:00"'
        );
        $this->addColumnsIfNotExists('#__osdownloads_documents', $columns);

        $columns = array(
            'parent_id',
            'cms_version',
            'picture'
        );
        $this->dropColumnsIfExists('#__osdownloads_documents', $columns);

        // Remove the old pkg_osdownloads, if existent
        $query = 'DELETE FROM `#__extensions` WHERE `type`="package" AND `element`="pkg_osdownloads"';
        $db->setQuery($query);
        $db->execute();

        $this->checkAndCreateDefaultCategory();

        if ($has_show_email) {
            $this->removeDeprecatedFieldShowEmail();
        }

        $db->setQuery('ALTER TABLE `#__osdownloads_emails` CHANGE `id` `id` BIGINT(20)  NOT NULL  AUTO_INCREMENT')
            ->execute();

        $this->fixOrderingParamForMenus();
    }

    /**
     * Method to move the current files to the new upload folder
     *
     * @return void
     */
    protected function moveCurrentUploadedFiles()
    {
        jimport('joomla.filesystem.folder');
        jimport('joomla.filesystem.file');

        $oldUploadPath = JPATH_SITE . '/media/OSDownloads';
        $newUploadPath = JPATH_SITE . '/media/com_osdownloads/files';

        if (JFolder::exists($oldUploadPath)) {
            $files = JFolder::files($oldUploadPath);
            if (!empty($files)) {
                // Move all the files
                if (!JFolder::exists($newUploadPath)) {
                    JFolder::create($newUploadPath);
                }

                foreach ($files as $file) {
                    $current = "{$oldUploadPath}/{$file}";
                    $new     = "{$newUploadPath}/{$file}";
                    $a = JFile::move($current, $new);
                }
            }

            // Try to remove the old folder, if it is empty
            $files = JFolder::files($oldUploadPath);
            $oldUploadRelativePath = str_replace(JPATH_SITE . DIRECTORY_SEPARATOR, '', $oldUploadPath);
            $newUploadRelativePath = str_replace(JPATH_SITE . DIRECTORY_SEPARATOR, '', $newUploadPath);
            if (empty($files)) {
                JFolder::delete($oldUploadPath);
                $this->setMessage(JText::sprintf('COM_OSDOWNLOADS_INSTALL_REMOVED_FOLDER', $oldUploadRelativePath, $newUploadRelativePath));
            } else {
                $this->setMessage(JText::sprintf('COM_OSDOWNLOADS_INSTALL_COULD_NOT_REMOVE_FOLDER', $oldUploadRelativePath));
            }
        }
    }

    protected function checkAndCreateDefaultCategory()
    {
        $db = JFactory::getDBO();

        // Make sure we have at least one category
        $query = $db->getQuery(true)
            ->select('count(*)')
            ->from('#__categories')
            ->where(
                array(
                    'extension = ' . $db->quote('com_osdownloads'),
                    'published >= 0'
                )
            );
        $db->setQuery($query);
        $total = (int) $db->loadResult();

        if ($total === 0) {
            $row = JTable::getInstance('category');

            $data = array(
                'title'     => 'General',
                'parent_id' => 1,
                'extension' => 'com_osdownloads',
                'published' => 1,
                'language'  => '*'
            );

            $row->setLocation($data['parent_id'], 'last-child');
            $row->bind($data);
            if ($row->check()) {
                $row->store();
                $this->setMessage(JText::_('COM_OSDOWNLOADS_INSTALL_GENERAL_CATEGORY_CREATED'));
            } else {
                $this->setMessage(JText::_('COM_OSDOWNLOADS_INSTALL_GENERAL_CATEGORY_WARNING'), 'notice');
            }
        } else {
            // Make sure to fix the undefined language
            $query = $db->getQuery(true)
                ->update('#__categories')
                ->set($db->quoteName('language') . '=' . $db->quote('*'))
                ->where(
                    array(
                        $db->quoteName('extension') . ' = ' . $db->quote('com_osdownloads'),
                        '(' . $db->quoteName('language') . ' IS NULL OR ' . $db->quoteName('language') . ' = ' . $db->quote('') . ')',
                    )
                );
            $db->setQuery($query);
            $db->execute();
        }
    }

    protected function removeDeprecatedFieldShowEmail()
    {
        $db = JFactory::getDBO();

        // Fix the current data
        $query = $db->getQuery(true)
            ->update('#__osdownloads_documents')
            ->set('require_email = 2')
            ->where('show_email = 1')
            ->where('require_email = 0');
        $db->setQuery($query);
        $db->execute();

        // Drop the show_email column
        $db->setQuery('ALTER TABLE `#__osdownloads_documents` DROP COLUMN `show_email`');
        $db->execute();
    }

    /**
     * Fix old values for the ordering param in menus, adding the table prefix.
     */
    protected function fixOrderingParamForMenus()
    {
        require JPATH_SITE . '/administrator/components/com_osdownloads/include.php';
        
        $db        = JFactory::getDbo();
        $container = Container::getInstance();

        $query = $db->getQuery(true)
            ->select('link')
            ->select('params')
            ->select('id')
            ->from('#__menu')
            ->where('link = ' . $db->quote($container->getHelperRoute()->getFileListRoute()));
        $db->setQuery($query);
        $menus = $db->loadObjectList();

        if (!empty($menus)) {
            foreach ($menus as $menu) {
                $params = @json_decode($menu->params);
                $legacyOrderings = array(
                    'ordering',
                    'name',
                    'downloaded',
                    'created_time',
                    'modified_time'
                );

                if (isset($params->ordering) && in_array($params->ordering, $legacyOrderings)) {
                    $params->ordering = 'doc.' . $params->ordering;
                    $params = json_encode($params);

                    $query = $db->getQuery(true)
                        ->update('#__menu')
                        ->set('params = ' . $db->quote($params))
                        ->where('id = ' . $menu->id);
                    $db->setQuery($query);
                    $db->execute();
                }
            }
        }
    }
}
