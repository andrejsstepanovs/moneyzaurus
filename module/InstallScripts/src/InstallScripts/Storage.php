<?php

namespace InstallScripts;

use InstallScripts\Exception\StorageAdapterException;
use InstallScripts\StorageAdapter\StorageAdapterInterface;


class Storage
{
    /** @var array */
    protected $config;

    /** @var |InstallScripts\StorageAdapter\StorageAdapterInterface */
    protected $storageAdapter;


    /**
     * @param array $config
     * @return \InstallScripts\Storage
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
    public function getConfig($key = null)
    {
        if ($key) {
            if (!array_key_exists($key, $this->config)) {
                throw new StorageAdapterException(
                    'StorageAdapter config key "' . $key . '" is missing'
                );
            }

            return $this->config[$key];
        }

        return $this->config;
    }

    /**
     * @param array $storageConfig
     * @return string
     * @throws \InstallScripts\Exception\StorageAdapterException
     */
    protected function getAdapterName(array $storageConfig)
    {
        if (!array_key_exists('Adapter', $storageConfig)) {
            throw new StorageAdapterException(
                'StorageAdapter Adapter config is not set'
            );
        }

        return $storageConfig['Adapter'];
    }

    /**
     * @return |InstallScripts\StorageAdapter\StorageAdapterInterface
     * @throws \InstallScripts\Exception\StorageAdapterException
     */
    public function getAdapter()
    {
        if (null === $this->storageAdapter) {
            $storageConfig = $this->getConfig('StorageAdapter');

            $storageAdapterClassName = $this->getAdapterName($storageConfig);

            if (!class_exists($storageAdapterClassName)) {
                throw new StorageAdapterException(
                    'StorageAdapter "' . $storageAdapterClassName . '" '
                    . 'not found'
                );
            }


            $storageAdapter = new $storageAdapterClassName();

            if (!$storageAdapter instanceof StorageAdapterInterface) {
                throw new StorageAdapterException(
                    'StorageAdapter "' . $storageAdapterClassName . '" '
                    . 'not instance of StorageAdapterInterface'
                );
            }

            if (array_key_exists('Options', $storageConfig)) {
                $storageAdapter->setOptions($storageConfig['Options']);
            }

            $this->storageAdapter = $storageAdapter;
        }

        return $this->storageAdapter;
    }

}
