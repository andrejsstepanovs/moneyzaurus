<?php

namespace InstallScripts\Storage;

use InstallScripts\Storage\AdapterInterface;
use Zend\Json\Json;
use InstallScripts\Exception;


class FileAdapter implements AdapterInterface
{
    /** @var array */
    protected $data;

    /** @var array */
    protected $options;


    /**
     * @param array $data
     * @return \InstallScripts\Storage\File
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
     * @return \InstallScripts\Storage\File
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
                throw new Exception\MissingStorageOptionsException(
                    'Storage adapter option "' . $key . '" is missing'
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
            throw new Exception\StorageSaveException(
                'Failed save to "' . $filePath . '"'
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
        $filePath = realpath($file);

        if (!file_exists($file)) {
            throw new Exception\StorageLoadException(
                'File dont exist "' . $filePath . '"'
            );
        }

        $stream = fopen($filePath, 'r');
        if ($stream === false) {
            throw new Exception\StorageLoadException(
                'Failed read file "' . $filePath . '"'
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
     * @param string $bundleName
     * @return string version
     */
    public function getBundleVersion($bundleName)
    {
        $version = 0;
        try {
            $storageData = $this->load();
        } catch (Exception\StorageLoadException $exc) {
            $storageData = null;
        }

        if (!$storageData
            || !array_key_exists('bundles', $storageData)
            || !array_key_exists($bundleName, $storageData['bundles'])
            || !array_key_exists('version', $storageData['bundles'][$bundleName])
        ) {
            return $version;
        }

        return $storageData['bundles'][$bundleName]['version'];
    }

    /**
     * @param string $bundleName
     * @param string version
     * @return \InstallScripts\Storage\File
     */
    public function setBundleVersion($bundleName, $version)
    {
        $storageData = $this->getData();
        $storageData['bundles'][$bundleName]['version'] = $version;
        return $this->setData($storageData);
    }
}