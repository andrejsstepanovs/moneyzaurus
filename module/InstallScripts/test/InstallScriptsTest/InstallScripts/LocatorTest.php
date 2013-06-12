<?php

namespace InstallScriptsTest\Locator;

use PHPUnit_Framework_TestCase;
use InstallScripts\Locator as InstallScriptsLocator;

/**
 * @group Locator
 */
class LocatorTest extends PHPUnit_Framework_TestCase
{
    /** @var \InstallScripts\Locator */
    protected $locator;


    /**
     * @param null|array $config
     * @return \InstallScripts\Locator
     */
    public function getLocator($config = null)
    {
        if (null === $this->locator) {
            $this->locator = new InstallScriptsLocator($config);
        }
        return $this->locator;
    }

    public function testSetConfig()
    {
        $locator = $this->getLocator();

        $data = array('banana' => 'apple');

        $locator->setConfig($data);

        $dataOut = $locator->getConfig();

        $this->assertArrayHasKey('banana', $dataOut);
        $this->assertEquals($data['banana'], $dataOut['banana']);
    }

    public function testSetConfigReturnSelf()
    {
        $locator = $this->getLocator();

        $data = array('banana' => 'apple');
        $return = $locator->setConfig($data);
        $this->assertEquals(get_class($locator), get_class($return));
    }

    public function testGetScripts()
    {
        $config = array(
            'Scripts' => array(
                'InstallScriptsTest' => array(
                    'InstallScripts\Script\ScriptMock',
                )
            )
        );

        $locator = $this->getLocator()->setConfig($config);
        $scripts = $locator->getScripts();

        $this->assertTrue(is_array($scripts));
        $this->assertEquals(1, count($scripts));

        $this->assertEquals('InstallScriptsTest\InstallScripts\Script\ScriptMock', get_class($scripts[0]));
        $this->assertEquals('InstallScriptsTest\InstallScripts\Script\ScriptMock', $scripts[0]->getName());
    }

    /**
     * @expectedException        InstallScripts\Exception\LocatorException
     * @expectedExceptionMessage InstallScript "InstallScriptsTest\Unknown\Unknown" not found
     */
    public function testGetScriptNotFound()
    {
        $config = array(
            'Scripts' => array(
                'InstallScriptsTest' => array(
                    'Unknown\Unknown',
                )
            )
        );
        $this->getLocator()->setConfig($config)->getScripts();
    }

    /**
     * @expectedException        InstallScripts\Exception\ScriptException
     * @expectedExceptionMessage Script versions not set for "InstallScriptsTest\InstallScripts\Script\ScriptMock"
     */
    public function testGetBundleNoConfig()
    {
        $config = array(
            'Scripts' => array(
                'InstallScriptsTest' => array(
                    'InstallScripts\Script\ScriptMock',
                )
            )
        );
        $bundles = $this->getLocator()->setConfig($config)->getScripts();
        foreach ($bundles as $bundle) {
            $bundle->getVersions();
        }
    }


    /**
     * @expectedException        InstallScripts\Exception\LocatorException
     * @expectedExceptionMessage Script "InstallScriptsTest\Locator\LocatorTest" not instance of ScriptInterface
     */
    public function testGetScriptNoInstanceOfScript()
    {
        $config = array(
            'Scripts' => array(
                'InstallScriptsTest' => array(
                    'Locator\LocatorTest',
                )
            )
        );
        $this->getLocator()->setConfig($config)->getScripts();
    }

}