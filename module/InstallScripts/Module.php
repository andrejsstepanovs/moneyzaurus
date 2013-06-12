<?php
namespace InstallScripts;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface;
use Zend\Console\Adapter\AdapterInterface as Console;


class Module implements
    AutoloaderProviderInterface,
    ConfigProviderInterface,
    ConsoleUsageProviderInterface
{
    /** @var array */
    protected $config;


    /**
     * @return array
     */
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    /**
     * @return array
     */
    public function getConfig($key = null)
    {
        if (null === $this->config) {
            $this->config = array_merge(
                include __DIR__ . '/config/router.config.php',
                include __DIR__ . '/config/controllers.config.php'
            );
        }

        if (!empty($key)) {
            return $this->config[$key];
        }

        return $this->config;
    }

    /**
     * @return array
     */
    public function getConsoleUsage(Console $console)
    {
        return array(
            'install-scripts list'                       => 'show install scripts list',
            'install-scripts versions [<script>]'        => 'show script versions',
            'install-scripts config'                     => 'shows configuration',
            'install-scripts set-latest'                 => 'set all scripts to latest version',
            'install-scripts set <script> <version>'     => 'set version without executing scripts',
            'install-scripts install <script> <version>' => 'install specific version',
            'install-scripts update [<script>]'          => 'install scripts incrementally to latest version',

            // parameters
            array('script',        'Install script name'          ),
            array('version',       'Install script version number'),
            array('<parameter>',   'Mandatory'),
            array('[<parameter>]', 'Optional'),
        );
    }

}
