<?php

namespace InstallScripts\Bundle;

use Zend\Mvc\MvcEvent;
use InstallScripts\Exception;


class Bundle
{
    /** @var array */
    protected $versions;

    /** @var \Zend\Mvc\MvcEvent */
    protected $mvcEvent;


    /**
     * @param null|\Zend\Mvc\MvcEvent $mvcEvent
     */
    public function __construct($mvcEvent = null)
    {
        if ($mvcEvent) {
            $this->setMvcEvent($mvcEvent);
        }
    }

    public function setVersions($versions)
    {
        $this->versions = $versions;
        return $this;
    }

    /**
     * @return array
     * @throws Exception\BundleException
     */
    public function getVersions()
    {
        if ($this->versions) {
            return $this->versions;
        }

        throw new Exception\BundleException(
            'Bundle version not set for "' . $this->getName() . '"'
        );
    }

    /**
     * @return array
     */
    public function getVersionsSorted()
    {
        $versions = $this->getVersions();
        uksort($versions, 'version_compare');
        return $versions;
    }

    /**
     * @return string version number
     */
    public function getMaxVersion()
    {
        $versions = $this->getVersionsSorted();

        $i = 0;
        $count = count($versions);
        foreach ($versions AS $version => $val) {
            $i++;
            if ($count == $i) {
                return $version;
            }
        }

        throw new Exception\BundleException(
            'Max version not found'
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return get_class($this);
    }

    /**
     * @param \Zend\Mvc\MvcEvent $mvcEvent
     * @return \InstallScripts\Model\Locator
     */
    public function setMvcEvent(MvcEvent $mvcEvent)
    {
        $this->mvcEvent = $mvcEvent;
        return $this;
    }

    /**
     * @return \Zend\Mvc\MvcEvent
     */
    public function getMvcEvent()
    {
        return $this->mvcEvent;
    }

}
