<?php

namespace InstallScripts\Bundle;


interface BundleInterface
{
    /**
     * Get available bundle versions
     *
     * @return array
     */
    public function getVersions();
}