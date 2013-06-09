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
    public function getStorage($config = null)
    {
        if (null === $this->storage) {
            $this->storage = new InstallScriptsStorage($config);
        }
        return $this->storage;
    }

    public function testSetConfig()
    {
        $storage = $this->getStorage();

        $data = array('banana' => 'apple');
        $storage->setConfig($data);

        $dataOut = $storage->getConfig();
        $this->assertArrayHasKey('banana', $dataOut);
        $this->assertEquals($data['banana'], $dataOut['banana']);
    }

    public function testSetConfigConstructor()
    {
        $data = array('banana' => 'apple');
        $storage = $this->getStorage($data);

        $dataOut = $storage->getConfig();
        $this->assertArrayHasKey('banana', $dataOut);
        $this->assertEquals($data['banana'], $dataOut['banana']);
    }

    public function testGetConfig()
    {
        $storage = $this->getStorage();

        $data = array('banana' => 'apple');
        $storage->setConfig($data);

        $value = $storage->getConfig('banana');
        $this->assertEquals($data['banana'], $value);
    }

    public function testGetAdapter()
    {
        $storage = $this->getStorage();

        $data = array(
            'storage'   => array(
                'adapter' => 'InstallScripts\Storage\FileAdapter',
            )
        );
        $storage->setConfig($data);

        $adapter = $storage->getAdapter();
        $this->assertInstanceOf('InstallScripts\Storage\FileAdapter', $adapter);
    }

    public function testGetAdapterWithOptions()
    {
        $storage = $this->getStorage();

        $data = array(
            'storage'   => array(
                'adapter' => 'InstallScripts\Storage\FileAdapter',
                'options' => array(
                    'file' => 'filename',
                )
            )
        );
        $storage->setConfig($data);

        $adapter = $storage->getAdapter();
        $this->assertInstanceOf('InstallScripts\Storage\FileAdapter', $adapter);

        $adapterOptions = $adapter->getOptions();

        $this->assertTrue(is_array($adapterOptions));
        $this->assertEquals($data['storage']['options']['file'], $adapterOptions['file']);
    }

    /**
     * @expectedException        InstallScripts\Exception\UnknownStorageException
     * @expectedExceptionMessage Storage "InstallScripts\Storage\UnknownAdapter" was not found. Check "storage" parameter
     */
    public function testGetAdapterDontExist()
    {
        $storage = $this->getStorage();

        $data = array(
            'storage'   => array(
                'adapter' => 'InstallScripts\Storage\UnknownAdapter',
            )
        );
        $storage->setConfig($data);

        $adapter = $storage->getAdapter();
        $this->assertInstanceOf('InstallScripts\Storage\FileAdapter', $adapter);
    }

}