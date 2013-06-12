<?php

namespace InstallScripts;

use InstallScripts\Exception\LocatorException;
use InstallScripts\ScriptInterface;


class Locator
{
    /** @var array */
    protected $config;

    /** @var array */
    protected $scripts;


    /**
     * @param array $config
     * @return \InstallScripts\Locator
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
                throw new LocatorException(
                    'InstallScript config key "' . $key . '" is missing'
                );
            }

            return $this->config[$key];
        }

        return $this->config;
    }

    /**
     * @param string $namespace
     * @param string $scriptName
     * @return string
     * @throws LocatorException
     */
    protected function getScriptClassName($namespace, $scriptName)
    {
        $scriptClassName = $namespace . '\\' . $scriptName;

        if (!class_exists($scriptClassName)) {
            throw new LocatorException(
                'InstallScript "' . $scriptClassName . '" not found'
            );
        }

        return $scriptClassName;
    }

    /**
     * @return array
     */
    public function getScripts()
    {
        if (null === $this->scripts) {

            $scriptsConfig = $this->getConfig('Scripts');

            foreach ($scriptsConfig as $namespace => $scripts) {

                foreach ($scripts as $scriptName) {

                    $scriptClassName = $this->getScriptClassName(
                        $namespace,
                        $scriptName
                    );

                    $installScript = new $scriptClassName();

                    if (!$installScript instanceof ScriptInterface) {
                        throw new LocatorException(
                            'Script "' . $scriptClassName . '" '
                            . 'not instance of ScriptInterface'
                        );
                    }

                    $this->scripts[] = $installScript;
                }
            }
        }

        return $this->scripts;
    }

}