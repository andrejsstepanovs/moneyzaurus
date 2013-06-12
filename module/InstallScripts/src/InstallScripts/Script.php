<?php

namespace InstallScripts;

use Zend\Mvc\MvcEvent;
use InstallScripts\Exception\ScriptException;


class Script implements ScriptInterface
{
    /** @var array */
    protected $versions;

    /** @var \Zend\Mvc\MvcEvent */
    protected $mvcEvent;


    /**
     * @param string $versions
     * @return \InstallScripts\Script
     */
    public function setVersions($versions)
    {
        $this->versions = $versions;
        return $this;
    }

    /**
     * @return array
     * @throws Exception\ScriptException
     */
    public function getVersions()
    {
        if ($this->versions) {
            return $this->versions;
        }

        throw new ScriptException(
            'Script versions not set for "' . $this->getName() . '"'
        );
    }

    /**
     * @return array
     */
    public function getVersionsSorted()
    {
        $versions = $this->getVersions();

        if (!is_array($versions)) {
            throw new ScriptException('Script versions not set');
        }

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
        foreach (array_keys($versions) AS $version) {
            $i++;
            if ($count == $i) {
                return $version;
            }
        }

        throw new ScriptException('Max version not found');
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
     * @return \InstallScripts\Script
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
