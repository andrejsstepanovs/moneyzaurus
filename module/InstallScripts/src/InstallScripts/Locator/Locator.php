<?php

namespace InstallScripts\Locator;

use InstallScripts\Exception;
use InstallScripts\Bundle\BundleInterface;


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
    public function getConfig($key = null)
    {
        if (!empty($key)) {
            if (!array_key_exists($key, $this->config)) {
                throw new Exception\ConfigException(
                    'InstallScript config key "' . $key . '" is missing'
                );
            }

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

                    if (!$bundle instanceof BundleInterface) {
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