<?php

namespace InstallScripts\Bundle;

use Zend\Db\Adapter\AdapterAwareInterface;
use Zend\Db\Adapter\Adapter;
use InstallScripts\Exception;


class Bundle implements AdapterAwareInterface
{
    /** @var \Zend\Db\Adapter\Adapter */
    private $adapter;

    /** @var \Exception */
    private $exceptions = array();

    /** @var array */
    protected $activeRecords;

    /** @var array */
    protected $versions;


    /**
     * @param null|\Zend\Db\Adapter\Adapter $adapter
     */
    public function __construct($adapter = null)
    {
        if ($adapter) {
            $this->setDbAdapter($adapter);
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
     * Set db adapter
     *
     * @param \Zend\Db\Adapter\Adapter $adapter
     * @return AdapterAwareInterface
     */
    public function setDbAdapter(Adapter $adapter)
    {
        $this->adapter = $adapter;
        return $this;
    }

    /**
     * @return \Zend\Db\Adapter\Adapter
     */
    protected function getDbAdapter()
    {
        return $this->adapter;
    }

    /**
     * @param string $sqlQuery
     * @return boolean
     */
    public function executeQuery($sqlQuery)
    {
        try {
            $this->adapter->query($sqlQuery)->execute();
            return true;

        } catch (\Zend\Db\Adapter\Exception\InvalidQueryException $exc) {
            $this->setException($exc);
        } catch (Exception $exc) {
            $this->setException($exc);
        }

        return false;
    }

    /**
     * @param mixed $exc
     * @return \InstallScripts\Bundle\Bundle
     */
    public function setException($exc)
    {
        $this->exceptions[] = $exc;
        return $this;
    }

    /**
     * @return array
     */
    public function getExceptions()
    {
        return $this->exceptions;
    }

}
