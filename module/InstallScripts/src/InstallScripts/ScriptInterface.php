<?php

namespace InstallScripts;

use Zend\Mvc\MvcEvent;


interface ScriptInterface
{
    /**
     * Get available script versions
     *
     * @return array
     */
    public function getVersions();

    /**
     * Get available script versions sorted
     *
     * @return array
     */
    public function getVersionsSorted();

    /**
     * Get max script version
     *
     * @return string
     */
    public function getMaxVersion();

    /**
     * Set MvcEvent
     *
     * @param \Zend\Mvc\MvcEvent $mvcEvent
     * @return \InstallScripts\ScriptInterface
     */
    public function setMvcEvent(MvcEvent $mvcEvent);

    /**
     * Get MvcEvent
     *
     * @return \Zend\Mvc\MvcEvent
     */
    public function getMvcEvent();

}
