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
use Joomla\Registry\Registry;

class AbstractOSDownloadsInstallerScript extends AbstractScript
{
    /**
     * Flag to set if the old mod_osdownloads is installed
     *
     * @var boolean
     */
    protected $deprecatedModOSDownloadsIsInstalled;

    /**
     * Method to run after an install/update method
     *
     * @return void
     * @throws Exception
     */
    public function postFlight($type, $parent)
    {
        try {
            parent::postFlight($type, $parent);

            // All other integrity checks and fixes
            $this->checkParamStructure();
            $this->cleanLegacyInstalls();
            $this->checkAndCreateDefaultCategory();
            $this->moveCurrentUploadedFiles();
            $this->legacyDatabaseUpdates();
            $this->fixOrderingParamForMenus();
            $this->fixDownloadsViewParams();
            $this->fixItemViewParams();

        } catch (Exception $e) {
            $this->setMessage($e->getMessage(), 'error');

        } catch (Throwable $e) {
            $this->setMessage($e->getMessage(), 'error');
        }

        // To catch any new messages that may have been queued
        $this->showMessages();
    }

    /**
     * Legacy configuration checks and warnings
     */
    protected function cleanLegacyInstalls()
    {
        $db = JFactory::getDBO();

        // Check if mod_osdownloads is installed to show the warning of deprecated
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from('#__extensions')
            ->where($db->qn('type') . '=' . $db->q('module'))
            ->where($db->qn('element') . '=' . $db->q('mod_osdownloads'));
        $db->setQuery($query);
        $this->deprecatedModOSDownloadsIsInstalled = (int)$db->loadResult();

        // Remove the old pkg_osdownloads, if existent
        $query = $db->getQuery(true)
            ->delete('#__extensions')
            ->where(
                array(
                    $db->quoteName('type') . '=' . $db->quote('package'),
                    $db->quoteName('element') . '=' . $db->quote('pkg_osdownloads')
                )
            );
        $db->setQuery($query)->execute();
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
                    $a       = JFile::move($current, $new);
                }
            }

            // Try to remove the old folder, if it is empty
            $files                 = JFolder::files($oldUploadPath);
            $oldUploadRelativePath = str_replace(JPATH_SITE . DIRECTORY_SEPARATOR, '', $oldUploadPath);
            $newUploadRelativePath = str_replace(JPATH_SITE . DIRECTORY_SEPARATOR, '', $newUploadPath);
            if (empty($files)) {
                JFolder::delete($oldUploadPath);
                $this->setMessage(
                    JText::sprintf(
                        'COM_OSDOWNLOADS_INSTALL_REMOVED_FOLDER',
                        $oldUploadRelativePath,
                        $newUploadRelativePath
                    )
                );

            } else {
                $this->setMessage(
                    JText::sprintf('COM_OSDOWNLOADS_INSTALL_COULD_NOT_REMOVE_FOLDER', $oldUploadRelativePath)
                );
            }
        }
    }

    /**
     * Check and fix various legacy database versions
     *
     * @return void
     */
    protected function legacyDatabaseUpdates()
    {
        $db = JFactory::getDbo();

        $db->setQuery("SHOW COLUMNS FROM #__osdownloads_documents");
        $rows       = $db->loadObjectList();
        $db_version = "1.0.0";

        $has_show_email    = false;
        $has_description_3 = false;
        $has_direct_field  = false;
        $has_file_url      = false;

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
            $db->setQuery(
                file_get_contents(
                    JPATH_ADMINISTRATOR . '/components/com_osdownloads/sql/update_1.0.0_1.0.3.mysql.utf8.sql'
                )
            )->execute();
        }

        if ($db_version == "1.0.1") {
            echo("<div>Migrate database from version 1.0.1 to 1.0.3</div>");
            $db->setQuery(
                file_get_contents(
                    JPATH_ADMINISTRATOR . '/components/com_osdownloads/sql/update_1.0.1_1.0.3.mysql.utf8.sql'
                )
            )->execute();
        }

        if ($db_version == "1.0.2") {
            echo("<div>Migrate database from version 1.0.2 to 1.0.3</div>");
            $db->setQuery(
                file_get_contents(
                    JPATH_ADMINISTRATOR . '/components/com_osdownloads/sql/update_1.0.2_1.0.3.mysql.utf8.sql'
                )
            )->execute();
        }
        if (!$has_description_3) {
            // Fix database if upgrade from version 1.0.2 (new install). If upgrade from 1.0.x to 1.0.2 it still correct
            $db->setQuery("SHOW COLUMNS FROM #__osdownloads_documents");
            $rows              = $db->loadObjectList();
            $has_description_3 = false;
            foreach ($rows as $row) {
                if ($row->Field == "description_3") {
                    $has_description_3 = true;
                }
            }
            if (!$has_description_3) {
                echo("<div>Apply patch for database version 1.0.2</div>");
                $db->setQuery(
                    sprintf(
                        'ALTER TABLE %s ADD %s TEXT NOT NULL AFTER %s',
                        $db->quoteName('#__osdownloads_documents'),
                        $db->quoteName('description_3'),
                        $db->quoteName('description_2')
                    )
                )->execute();
            }
        }

        if (!$has_file_url) {
            echo("<div>Migrate database for remote files</div>");
            $sql = file_get_contents(JPATH_ADMINISTRATOR . "/components/com_osdownloads/sql/updates/mysql/1.0.17.sql");
            $db->setQuery($sql)->execute();
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

        if ($has_show_email) {
            $this->removeDeprecatedFieldShowEmail();
        }

        $db->setQuery(
            sprintf(
                'ALTER TABLE %1$s CHANGE %2$s %2$s BIGINT(20) NOT NULL  AUTO_INCREMENT',
                $db->quoteName('#__osdownloads_emails'),
                $db->quoteName('id')
            )
        )->execute();
    }

    /**
     * @return void
     */
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
        $total = (int)$db->loadResult();

        if ($total === 0) {
            /** @var JTableCategory $row */
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
                $row->rebuildPath();
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
                        sprintf(
                            '(%1$s IS NULL OR %1$s = %2$s)',
                            $db->quoteName('language'),
                            $db->quote('')
                        )
                    )
                );
            $db->setQuery($query)->execute();

            // Rebuild paths where needed
            $query = $db->getQuery(true)
                ->select('id')
                ->from('#__categories')
                ->where(
                    array(
                        $db->quoteName('extension') . '=' . $db->quote('com_osdownloads'),
                        $db->quoteName('path') . '=' . $db->quote('')
                    )
                );

            if ($ids = $db->setQuery($query)->loadColumn()) {
                /** @var JTableCategory $category */
                $category = JTable::getInstance('Category');

                foreach ($ids as $id) {
                    $category->rebuildPath($id);
                }
            }
        }
    }

    /**
     * @return void
     */
    protected function removeDeprecatedFieldShowEmail()
    {
        $db = JFactory::getDBO();

        // Fix the current data
        $query = $db->getQuery(true)
            ->update('#__osdownloads_documents')
            ->set('require_email = 2')
            ->where('show_email = 1')
            ->where('require_email = 0');
        $db->setQuery($query)->execute();

        // Drop the show_email column
        $db->setQuery(
            sprintf(
                'ALTER TABLE %s DROP COLUMN %s',
                $db->quoteName('#__osdownloads_documents'),
                $db->quoteName('show_email')
            )
        )->execute();
    }

    /**
     * Fix old values for the ordering param in menus, adding the table prefix.
     *
     * @return void
     */
    protected function fixOrderingParamForMenus()
    {
        require JPATH_SITE . '/administrator/components/com_osdownloads/include.php';

        $db = JFactory::getDbo();

        $query = $db->getQuery(true)
            ->select('link')
            ->select('params')
            ->select('id')
            ->from('#__menu')
            ->where('link = ' . $db->quote('index.php?option=com_osdownloads&view=downloads'));
        $db->setQuery($query);
        $menus = $db->loadObjectList();

        if (!empty($menus)) {
            foreach ($menus as $menu) {
                $params          = @json_decode($menu->params);
                $legacyOrderings = array(
                    'ordering',
                    'name',
                    'downloaded',
                    'created_time',
                    'modified_time'
                );

                if (isset($params->ordering) && in_array($params->ordering, $legacyOrderings)) {
                    $params->ordering = 'doc.' . $params->ordering;
                    $params           = json_encode($params);

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

    /**
     * Detect legacy settings and fix the downloads view params. If legacy data
     * is found, warn the user. We call legacy data, the param category_id with
     * multiple values, in the downloads view. Represented issues for SEF
     * URLs, so we refactored allowing only one category.
     *
     * @return void
     */
    protected function fixDownloadsViewParams()
    {
        $db = JFactory::getDbo();

        // Look for menu items for Category view
        $query    = $db->getQuery(true)
            ->select(
                array(
                    'id',
                    'params',
                )
            )
            ->from('#__menu')
            ->where('link = ' . $db->quote('index.php?option=com_osdownloads&view=downloads'));
        $menuList = $db->setQuery($query)->loadObjectList();

        if (!empty($menuList)) {
            foreach ($menuList as $menu) {
                $params = json_decode($menu->params);

                // Does it have the old param and multiple categories selected?
                if (isset($params->category_id) && is_array($params->category_id) && !empty($params->category_id)) {
                    // Get the first category for the new param. If empty, use 0, the root category
                    $id = (int)$params->category_id[0];

                    unset($params->category_id);

                    // Update the link adding the selected category
                    $link = 'index.php?option=com_osdownloads&view=downloads&id=' . $id;

                    $query = $db->getQuery(true)
                        ->update('#__menu')
                        ->where('id = ' . (int)$menu->id)
                        ->set('link = ' . $db->quote($link))
                        ->set('params = ' . $db->quote(json_encode($params)));
                    $db->setQuery($query)->execute();

                    try {
                        JFactory::getApplication()->enqueueMessage(
                            sprintf(
                                JText::_('Only one category is allowed for the OSDOwnloads Category Files view. The params for menu item %s were upgraded.'),
                                $menu->id
                            ),
                            'warning'
                        );

                    } catch (Exception $e) {
                        // Fail silently
                    }
                }
            }
        }
    }

    /**
     * Detect legacy settings and fix the item view params
     *
     * @return void
     */
    protected function fixItemViewParams()
    {
        $db = JFactory::getDbo();

        // Look for menu items for Item view
        $query    = $db->getQuery(true)
            ->select(
                array(
                    'id',
                    'params',
                )
            )
            ->from('#__menu')
            ->where('link = ' . $db->quote('index.php?option=com_osdownloads&view=item'));
        $menuList = $db->setQuery($query)->loadObjectList();

        if (!empty($menuList)) {
            foreach ($menuList as $menu) {
                $params = json_decode($menu->params);

                // Does it have the old param?
                if (isset($params->document_id) && !empty($params->document_id)) {
                    $id = (int)$params->document_id;

                    unset($params->document_id);

                    // Update the link adding the selected category
                    $link = 'index.php?option=com_osdownloads&view=item&id=' . $id;

                    $query = $db->getQuery(true)
                        ->update('#__menu')
                        ->where('id = ' . (int)$menu->id)
                        ->set('link = ' . $db->quote($link))
                        ->set('params = ' . $db->quote(json_encode($params)));
                    $db->setQuery($query)->execute();
                }
            }
        }
    }

    /**
     * Adjust component parameters as needed
     * @TODO: Move to AbstractInstaller script
     */
    protected function checkParamStructure()
    {
        /** @var JTableExtension $table */
        $table = JTable::getInstance('Extension');
        $table->load(array('element' => 'com_osdownloads', 'type' => 'component'));

        $data = json_decode($table->params);
        $params = new Registry($data);

        $parameterMap = $this->getParameterChangeMap();
        foreach ($parameterMap as $oldKey => $newKey) {
            if ($value = $params->get($oldKey)) {
                $params->set($newKey, $value);
                $params->set($oldKey, null);
            }
        }

        if ($data != $params->toObject()) {
            $table->params = $params->toString();
            $table->store();
        }
    }

    /**
     * Return array mapping old Parameter kesy to new
     *
     * @return array
     */
    protected function getParameterChangeMap()
    {
        $parameterMap = array(
            'connect_mailchimp'       => 'mailinglist.mailchimp.enable',
            'mailchimp_api'           => 'mailinglist.mailchimp.api',
            'list_id'                 => 'mailinglist.mailchimp.list_id'
        );

        return $parameterMap;
    }
}
