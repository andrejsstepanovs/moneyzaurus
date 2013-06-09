<?php

namespace InstallScriptsTest\Storage;

use PHPUnit_Framework_TestCase;
use InstallScripts\Storage\Storage as InstallScriptsStorage;


class StorageTest extends PHPUnit_Framework_TestCase
{
    /** @var \InstallScripts\Locator\Locator */
    protected $storage;


    /**
     * @param null|array $config
     * @return \InstallScripts\Storage\Storage
     */
    public function getLocator($config = null)
    {
        if (null === $this->storage) {
            $this->storage = new InstallScriptsStorage($config);
        }
        return $this->storage;
    }

    public function testSetConfig()
    {
        
    }
}