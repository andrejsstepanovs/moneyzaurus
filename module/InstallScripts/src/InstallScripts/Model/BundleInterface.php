<?php

namespace InstallScripts\Model;


interface BundleInterface
{
    /**
     * Get available bundle versions
     *
     * @return array
     */
    public function getVersions();
}