<?php
require_once 'phing/Task.php';

/**
 * SetversionTask
 *
 * Increments a three-part version number from a given Joomla Manifest file
 * and writes it back to the file.
 * Incrementing is based on given releasetype, which can be one
 * of Major, Minor and Bugfix.
 * Resulting version number is also published under supplied property.
 * Based on original VersionTask.php
 *
 * @author      Bill Tomczak <bill@ostraining.com>
 * @version
 * @package     phing.tasks.ext
 */
class SetversionTask extends Task
{
    /**
     * @var string $releasetype
     */
    protected $releasetype;

    /**
     * @var PhingFile file
     */
    protected $file;

    /**
     * @var string $property
     */
    protected $property;

    /* Regex to match for version number */
    const REGEX = '#(<version>\s*)(\d*)\.?(\d*)\.?(\d*)([^<]*)(</version>)#m';

    /* Allowed Releastypes */
    const RELEASETYPE_MAJOR  = 'MAJOR';
    const RELEASETYPE_MINOR  = 'MINOR';
    const RELEASETYPE_BUGFIX = 'BUGFIX';

    /**
     * Set Property for Releasetype (Minor, Major, Bugfix)
     *
     * @param string $releasetype
     *
     * @return void
     */
    public function setReleasetype($releasetype)
    {
        $this->releasetype = strtoupper($releasetype);
    }

    /**
     * Set Property for File containing versioninformation
     *
     * @param PhingFile $file
     *
     * @return void
     */
    public function setFile($file)
    {
        $this->file = $file;
    }

    /**
     * Set name of property to be set
     *
     * @param $property
     *
     * @return void
     */
    public function setProperty($property)
    {
        $this->property = $property;
    }

    /**
     * Main-Method for the Task
     *
     * @return  void
     * @throws  BuildException
     */
    public function main()
    {
        // check supplied attributes
        $this->checkReleasetype();
        $this->checkFile();
        $this->checkProperty();

        // read file
        $filecontent = trim(file_get_contents($this->file));

        // get new version
        $newVersion = $this->getVersion($filecontent);

        // Update the file
        $this->updateFile($newVersion);

        // publish new version number as property
        $this->project->setProperty($this->property, $newVersion);

    }


    /**
     * Returns new version number corresponding to Release type
     *
     * @param string $filecontent
     *
     * @return string
     */
    protected function getVersion($filecontent)
    {
        // init
        $newVersion = '';

        // Extract version
        preg_match(self::REGEX, $filecontent, $match);
        list(,,$major, $minor, $bugfix, $add) = $match;

        // Return new version number
        switch ($this->releasetype) {
            case self::RELEASETYPE_MAJOR:
                $newVersion = sprintf(
                    "%d.%d.%d",
                    ++$major,
                    0,
                    0
                );
                break;

            case self::RELEASETYPE_MINOR:
                $newVersion = sprintf(
                    "%d.%d.%d",
                    $major,
                    ++$minor,
                    0
                );
                break;

            case self::RELEASETYPE_BUGFIX:
                $newVersion = sprintf(
                    "%d.%d.%d",
                    $major,
                    $minor,
                    ++$bugfix
                );
                break;
        }

        return $newVersion . $add;
    }


    /**
     * checks releasetype attribute
     * @return void
     * @throws BuildException
     */
    protected function checkReleasetype()
    {
        // check Releasetype
        if (is_null($this->releasetype)) {
            throw new BuildException('releasetype attribute is required', $this->location);
        }
        // known releasetypes
        $releaseTypes = array(
            self::RELEASETYPE_MAJOR,
            self::RELEASETYPE_MINOR,
            self::RELEASETYPE_BUGFIX
        );

        if (!in_array($this->releasetype, $releaseTypes)) {
            throw new BuildException(sprintf(
                'Unknown Releasetype %s..Must be one of Major, Minor or Bugfix',
                $this->releasetype
            ), $this->location);
        }
    }

    /**
     * checks file attribute
     * @return void
     * @throws BuildException
     */
    protected function checkFile()
    {
        // check File
        if ($this->file === null ||
            strlen($this->file) == 0
        ) {
            throw new BuildException('You must specify a Joomla manifest file', $this->location);
        }

        $content = file_get_contents($this->file);
        if (strlen($content) == 0) {
            throw new BuildException(sprintf('Supplied file %s is empty', $this->file), $this->location);
        }

        // check for xml version tag
        if (!preg_match(self::REGEX, $content)) {
            throw new BuildException('Unable to find version tag', $this->location);
        }

    }

    /**
     * checks property attribute
     * @return void
     * @throws BuildException
     */
    protected function checkProperty()
    {
        if (is_null($this->property) ||
            strlen($this->property) === 0
        ) {
            throw new BuildException('Property for publishing version number is not set', $this->location);
        }
    }

    protected function updateFile($newVersion)
    {
        $content = file_get_contents($this->file);

        if (preg_match(self::REGEX, $content, $match)) {
            $source  = array_shift($match);
            $head    = array_shift($match);
            $tail    = array_pop($match);
            $replace = $head . $newVersion . $tail;

            $content = str_replace($source, $replace, $content);
            file_put_contents($this->file, $content);

            $this->log('Updated ' . basename($this->file) . ' to new version: ' . $newVersion);
        }

    }
}
