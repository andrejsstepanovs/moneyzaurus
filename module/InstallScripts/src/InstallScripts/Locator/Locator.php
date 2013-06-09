<?php

namespace InstallScripts\Locator;

use InstallScripts\Exception;
use InstallScripts\Model\Bundle;


class Locator
{
    /** @var array */
    protected $config;

    /** @var array */
    protected $bundles;


    /**
     * @param null|array $config
     */
    public function __construct($config = null)
    {
        if ($config) {
            $this->setConfig($config);
        }
    }

    /**
     * @param array $config
     * @return \InstallScripts\Model\Locator
     */
    public function setConfig($config)
    {
        $this->config = $config;
        return $this;
    }

    /**
     * @param null|string $key
     * @return mixed
     */
    protected function getConfig($key = null)
    {
        if ($key) {
            return $this->config[$key];
        }

        return $this->config;
    }

    /**
     * @return array
     */
    public function getBundles()
    {
        if (null === $this->bundles) {

            $namespaces = $this->getConfig('modules');

            foreach ($namespaces as $namespace => $bundlesData) {
                foreach ($bundlesData as $bundleName) {
                    $className = $namespace . '\\' . $bundleName;

                    if (!class_exists($className)) {
                        throw new Exception\BundleException(
                            'Bundle "' . $className . '" not found'
                        );
                    }

                    $bundle = new $className();

                    if (!$bundle instanceof Bundle) {
                        throw new Exception\BundleException(
                            'Bundle "' . $className . '" not instance of Bundle'
                        );
                    }

                    $this->bundles[] = $bundle;
                }
            }
        }

        return $this->bundles;
    }

}