<?php

namespace InstallScriptsTest\Storage;

use PHPUnit_Framework_TestCase;
use InstallScripts\Storage as InstallScriptsStorage;

/**
 * @group Storage
 */
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
            'StorageAdapter'   => array(
                'Adapter' => 'InstallScripts\StorageAdapter\FileStorageAdapter',
            )
        );
        $storage->setConfig($data);

        $adapter = $storage->getAdapter();
        $this->assertInstanceOf('InstallScripts\StorageAdapter\FileStorageAdapter', $adapter);
    }

    public function testGetAdapterWithOptions()
    {
        $storage = $this->getStorage();

        $data = array(
            'StorageAdapter'   => array(
                'Adapter' => 'InstallScripts\StorageAdapter\FileStorageAdapter',
                'Options' => array(
                    'file' => 'filename',
                )
            )
        );
        $storage->setConfig($data);

        $adapter = $storage->getAdapter();
        $this->assertInstanceOf('InstallScripts\StorageAdapter\FileStorageAdapter', $adapter);

        $adapterOptions = $adapter->getOptions();

        $this->assertTrue(is_array($adapterOptions));
        $this->assertEquals($data['StorageAdapter']['Options']['file'], $adapterOptions['file']);
    }

    /**
     * @expectedException        InstallScripts\Exception\StorageAdapterException
     * @expectedExceptionMessage StorageAdapter "InstallScripts\Storage\UnknownAdapter" not found
     */
    public function testGetAdapterDontExist()
    {
        $storage = $this->getStorage();

        $data = array(
            'StorageAdapter'   => array(
                'Adapter' => 'InstallScripts\Storage\UnknownAdapter',
            )
        );
        $storage->setConfig($data);

        $adapter = $storage->getAdapter();
        $this->assertInstanceOf('InstallScripts\StorageAdapter\FileStorageAdapter', $adapter);
    }

}