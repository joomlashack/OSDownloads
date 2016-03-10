<?php
/**
 * @package   AllediaInstaller
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2015 Alledia.com, All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace Alledia\Installer\Extension;

defined('_JEXEC') or die();

use Alledia\Installer\AutoLoader;

/**
 * Licensed class, for extensions with Free and Pro versions
 */
class Licensed extends Generic
{
    /**
     * License type: free or pro
     *
     * @var string
     */
    protected $license;

    /**
     * The path for the pro library
     *
     * @var string
     */
    protected $proLibraryPath;

    /**
     * The path for the free library
     *
     * @var string
     */
    protected $libraryPath;

    /**
     * Class constructor, set the extension type.
     *
     * @param string $namespace The element of the extension
     * @param string $type      The type of extension
     * @param string $folder    The folder for plugins (only)
     */
    public function __construct($namespace, $type, $folder = '', $basePath = JPATH_SITE)
    {
        parent::__construct($namespace, $type, $folder, $basePath);

        $this->license = strtolower($this->manifest->alledia->license);

        $this->getLibraryPath();
        $this->getProLibraryPath();
    }

    /**
     * Check if the license is pro
     *
     * @return boolean True for pro license
     */
    public function isPro()
    {
        return $this->license === 'pro';
    }

    /**
     * Check if the license is free
     *
     * @return boolean True for free license
     */
    public function isFree()
    {
        return ! $this->isPro();
    }

    /**
     * Get the include path for the include on the free library, based on the extension type
     *
     * @return string The path for pro
     */
    public function getLibraryPath()
    {
        if (empty($this->libraryPath)) {
            $basePath = $this->getExtensionPath();

            $this->libraryPath = $basePath . '/library';
        }

        return $this->libraryPath;
    }

    /**
     * Get the include path for the include on the pro library, based on the extension type
     *
     * @return string The path for pro
     */
    public function getProLibraryPath()
    {
        if (empty($this->proLibraryPath)) {
            $basePath = $this->getLibraryPath();

            $this->proLibraryPath = $basePath . '/Pro';
        }

        return $this->proLibraryPath;
    }

    /**
     * Loads the library, if existent (including the Pro Library)
     *
     * @return bool
     */
    public function loadLibrary()
    {
        $libraryPath = $this->getLibraryPath();

        // If we have a library path, lets load it
        if (file_exists($libraryPath)) {

            if ($this->isPro()) {
                // Check if the pro library exists
                if (!file_exists($this->getProLibraryPath())) {
                    throw new \Exception("Pro library not found: {$this->extension->type}, {$this->extension->element}");
                }
            }
            // Setup autoloaded libraries
            AutoLoader::register('Alledia\\' . $this->namespace, $libraryPath);

            return true;
        }

        return false;
    }
}
