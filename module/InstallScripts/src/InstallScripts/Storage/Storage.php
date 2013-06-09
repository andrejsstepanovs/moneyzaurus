<?php

namespace InstallScripts\Storage;

use InstallScripts\Exception;


class Storage
{
    /** @var array */
    protected $config;

    /** @var |InstallScripts\Storage\StorageInterface */
    protected $storage;


    /**
     * @param null|array $config
     */
    public function __construct($config = null)
    {
        if ($config) {
            $this->setConfig($config);
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
     * @return |InstallScripts\Storage\StorageInterface
     * @throws \InstallScripts\Exception\UnknownStorageException
     */
    public function getAdapter()
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

}