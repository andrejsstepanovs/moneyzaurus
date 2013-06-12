<?php

namespace InstallScripts\StorageAdapter;


interface StorageAdapterInterface
{
    /**
     * Save data to storage
     *
     * @return boolean
     */
    public function save();

    /**
     * Load data to storage
     *
     * @return array
     */
    public function load();

    /**
     * Set options
     *
     * @return array
     */
    public function setOptions(array $options);

    /**
     * Get current install script version
     *
     * @param string $scriptName
     * @return string
     */
    public function getScriptVersion($scriptName);

    /**
     * Set script version
     *
     * @param string $scriptName
     * @param string $version
     * @return InstallScripts\Storage\StorageAdapterInterface
     */
    public function setScriptVersion($scriptName, $version);
}