<?php
namespace Codeception\Module;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class AcceptanceHelper extends \Codeception\Module
{
    protected $builtPackagePath;

    protected $builtPackageFile;

    public function createJoomlaSiteForTests($siteName = 'tests_osdownloads', $version = '3.3.6')
    {
        $output = shell_exec("joomla site:delete {$siteName}") . "\n";
        $output .= shell_exec("joomla site:create --joomla={$version} {$siteName}");

        $this->debug($output);
        $this->debug("Joomla site created: {$siteName}}");

        // Install database with dummy data
        // shell_exec('mysql -u root -proot sites_tests_osdownloads < tests/_data/sites_tests_osdownloads.sql');
    }

    public function buildProjectUsingPhing($task = 'build')
    {
        $output = shell_exec('phing ' . $task);
        $this->debug("Builder output:\n" . $output);
        preg_match(
            '/Building zip\: (\/home\/vagrant\/Projects\/[a-z0-9_\-]*\/packages\/([a-z0-9_\-\.]*))/i',
            $output,
            $matches
        );

        if (!empty($matches)) {
            $this->builtPackagePath = $matches[1];
            $this->builtPackageFile = $matches[2];

            $this->debug('Built file name: ' . $this->builtPackageFile);
            $this->debug('Built file path: ' . $this->builtPackagePath);

            // Move the file to the _data dir
            // $dataPath = 'tests/_data/' . $this->builtPackageFile;
            // $copied = copy($this->builtPackagePath, $dataPath);
            // $this->debug('Copied package to: ' . $dataPath);
        }

        return $output;
    }

    public function getBuiltFilePath()
    {
        return $this->builtPackagePath;
    }

    public function getBuiltFile()
    {
        return $this->builtPackageFile;
    }

    public function unzipBuiltPackageToTmp()
    {
        $tmpPath = '/var/www/tests_osdownloads/tmp/test_' . uniqid();

        shell_exec("unzip {$this->builtPackagePath} -d {$tmpPath}");
        $this->debug('Unzipping package to: ' . $tmpPath);

        return $tmpPath;
    }
}
