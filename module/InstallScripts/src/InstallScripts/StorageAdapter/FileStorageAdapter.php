<?php

namespace InstallScripts\StorageAdapter;

use InstallScripts\Exception\StorageAdapterException;
use Zend\Json\Json;
use InstallScripts\StorageAdapter\StorageAdapterInterface;


class FileStorageAdapter implements StorageAdapterInterface
{
    /** @var array */
    protected $data;

    /** @var array */
    protected $options;


    /**
     * @param array $data
     * @return \InstallScripts\StorageAdapter\FileStorageAdapter
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
     * @param array $options
     * @return \InstallScripts\StorageAdapter\FileStorageAdapter
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * @param null|string $key
     * @return array
     */
    public function getOptions($key = null)
    {
        if ($key) {
            if (!array_key_exists($key, $this->options)) {
                throw new StorageAdapterException(
                    'FileStorageAdapter option "' . $key . '" is missing'
                );
            }

            return $this->options[$key];
        }

        return $this->options;
    }

    /**
     * @param array $data
     * @return boolean
     * @throws Exception\StorageSaveException
     */
    public function save()
    {
        $stringData = $this->getDataAsString();

        $filePath = $this->getOptions('file');

        $stream = fopen($filePath, 'w');

        if ($stream === false) {
            throw new StorageAdapterException(
                'Failed to open "' . $filePath . '" for saving'
            );
        }

        fwrite($stream, $stringData);
        return fclose($stream);
    }

    /**
     * @return array
     * @throws Exception\StorageLoadException
     */
    public function load()
    {
        $file = $this->getOptions('file');

        if (!file_exists($file)) {
            throw new StorageAdapterException(
                'File dont exist "' . $file . '"'
            );
        }

        $filePath = realpath($file);

        $stream = fopen($filePath, 'r');
        if ($stream === false) {
            throw new StorageAdapterException(
                'Failed to open file "' . $filePath . '" for loading'
            );
        }

        $stringData = fread($stream, filesize($filePath));
        fclose($stream);

        $data = $this->getDataAsArray($stringData);
        $this->setData($data);

        return $this->getData();
    }

    /**
     * @return array
     */
    protected function getDataAsString()
    {
        return Json::encode($this->getData());
    }

    /**
     * @param string $stringData
     * @return array
     */
    protected function getDataAsArray($stringData)
    {
        return Json::decode($stringData, Json::TYPE_ARRAY);
    }

    /**
     * @param string $scriptName
     * @return string version
     */
    public function getScriptVersion($scriptName)
    {
        $version = 0;

        try {
            $storageData = $this->load();
        } catch (StorageAdapterException $exc) {
            $storageData = null;
        }

        if (!isset($storageData['scripts'][$scriptName]['version'])) {
            return $version;
        }

        return $storageData['scripts'][$scriptName]['version'];
    }

    /**
     * @param string $scriptName
     * @param string version
     * @return \InstallScripts\StorageAdapter\FileStorageAdapter
     */
    public function setScriptVersion($scriptName, $version)
    {
        $storageData = $this->getData();
        $storageData['scripts'][$scriptName]['version'] = $version;
        return $this->setData($storageData);
    }

}
