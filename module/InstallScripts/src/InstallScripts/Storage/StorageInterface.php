<?php

namespace InstallScripts\Storage;

interface StorageInterface
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
     * Get current bundle version
     *
     * @param string $bundleName
     * @return array
     */
    public function getBundleVersion($bundleName);
}