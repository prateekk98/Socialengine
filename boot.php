<?php

/**
 * @package     Engine_Boot
 * @version     $Id: boot.php 2018-06-20 00:04:31Z $
 * @copyright   Copyright (c) 2006-2020 Webligo Developments
 * @license     http://www.socialengine.com/license/
 */
class Engine_Boot
{
    protected $_customBoots = [];
    protected $_bootFileName = 'index.php';
    protected $_bootDir = 'application';
    protected $_customBootsDir = 'boot';
    protected $_rootPath;

    public function __construct($rootPath)
    {
        $this->_rootPath = $rootPath;
        $this->_loadCustomBoot();
    }

    public function setRootBootFileName($bootFileName)
    {
        $this->_bootFileName = $bootFileName;
        return $this;
    }

    public function setRootBootDir($rootBootDir)
    {
        $this->_bootDir = $rootBootDir;
        return $this;
    }

    public function boot()
    {
        foreach ($this->_customBoots as $customBoot) {
            $customBoot->beforeBoot();
        }

        define('_ENGINE_R_REL', $this->_bootDir);
        define('_ENGINE_R_TARG', $this->_bootFileName);

        include $this->_rootPath
            . DIRECTORY_SEPARATOR
            . _ENGINE_R_REL . DIRECTORY_SEPARATOR
            . _ENGINE_R_TARG;
    }

    protected function _loadCustomBoot()
    {
        $dirPath = $this->_rootPath
            . DIRECTORY_SEPARATOR . $this->_customBootsDir;
        include_once $dirPath . DIRECTORY_SEPARATOR . 'Abstract.php';
        foreach (new DirectoryIterator($dirPath) as $file) {
            if (!$file->isFile() || in_array($file->getFilename(), ['Abstract.php'])) {
                continue;
            }
            include_once $file->getPathname();
            $className = 'Engine_Boot_' . $file->getBasename('.php');
            if (!class_exists($className)) {
                continue;
            }
            $boot = new $className($this);
            if ($boot instanceof Engine_Boot_Abstract) {
                $this->_customBoots[] = $boot;
            }
        }
    }
}
