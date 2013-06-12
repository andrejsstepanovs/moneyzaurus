<?php

namespace InstallScripts\Bundle;

use Zend\Mvc\MvcEvent;


interface BundleInterface
{
    /**
     * Get available bundle versions
     *
     * @return array
     */
    public function getVersions();

    /**
     * Get available bundle versions sorted
     *
     * @return array
     */
    public function getVersionsSorted();

    /**
     * Get max bundle version
     *
     * @return string
     */
    public function getMaxVersion();

    /**
     * Set MvcEvent
     *
     * @param \Zend\Mvc\MvcEvent $mvcEvent
     * @return BundleInterface
     */
    public function setMvcEvent(MvcEvent $mvcEvent);

    /**
     * Get MvcEvent
     *
     * @return \Zend\Mvc\MvcEvent
     */
    public function getMvcEvent();
}