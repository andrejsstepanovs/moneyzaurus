<?php

namespace InstallScripts\Model;

use \InstallScripts\Exception;


class Storage
{
    protected $config;
    protected $data;
    protected $storage;


    /**
     * @param null|array $config
     * @param null|array $data
     */
    public function __construct($config = null, $data = null)
    {
        if ($config) {
            $this->setConfig($config);
        }

        if ($data) {
            $this->setData($data);
        }
    }

    /**
     * @param array $config
     * @return \InstallScripts\Model\Storage
     */
    public function setConfig($config)
    {
        $this->config = $config;
        return $this;
    }

    /**
     * @param null|string $key
     * @return mixed
     */
    protected function getConfig($key = null)
    {
        if ($key) {
            return $this->config[$key];
        }

        return $this->config;
    }

    /**
     * @param array $data
     * @return \InstallScripts\Model\Storage
     */
    public function setData(array $data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return |InstallScripts\Storage\StorageInterface
     * @throws \InstallScripts\Exception\UnknownStorageException
     */
    protected function getStorage()
    {
        if (null === $this->storage) {
            $storage = $this->getConfig('storage');
            $storageClassName = $storage['adapter'];

            if (!class_exists($storageClassName)) {
                throw new Exception\UnknownStorageException(
                    'Storage "'.$storageClassName.'" was not found. ' .
                    'Check "storage" parameter'
                );
            }

            $this->storage = new $storageClassName();

            if (array_key_exists('options', $storage)) {
                $this->storage->setOptions($storage['options']);
            }
        }

        return $this->storage;
    }

    /**
     * @return boolean
     */
    public function save()
    {
        $data = $this->getData();
        return $this->getStorage()->save($data);
    }

    /**
     * @return array
     */
    public function load()
    {
        $data = $this->getStorage()->load();
        return $this->setData($data)->getData();
    }

}