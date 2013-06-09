<?php

namespace InstallScripts\Storage;

interface StorageInterface
{
    /**
     * Save data to storage
     *
     * @param array $data
     * @return boolean
     */
    public function save(array $data);

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
}