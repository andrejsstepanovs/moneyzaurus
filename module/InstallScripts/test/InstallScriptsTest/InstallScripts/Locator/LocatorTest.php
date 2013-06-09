<?php

namespace InstallScriptsTest\Locator;

use PHPUnit_Framework_TestCase;
use InstallScripts\Locator\Locator as InstallScriptsLocator;


class LocatorTest extends PHPUnit_Framework_TestCase
{
    /** @var \InstallScripts\Locator\Locator */
    protected $locator;


    /**
     * @param null|array $config
     * @return \InstallScripts\Locator\Locator
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

    public function testSetConfigInController()
    {
        $data = array('banana' => uniqid());
        $dataOut = $this->getLocator($data)->getConfig();

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

    public function testGetBundles()
    {
        $config = array(
            'modules' => array(
                'InstallScriptsTest' => array(
                    'InstallScripts\Bundle\BundleMock',
                )
            )
        );

        $locator = $this->getLocator($config);
        $bundles = $locator->getBundles();

        $this->assertTrue(is_array($bundles));
        $this->assertEquals(1, count($bundles));

        $this->assertEquals('InstallScriptsTest\InstallScripts\Bundle\BundleMock', get_class($bundles[0]));
        $this->assertEquals('InstallScriptsTest\InstallScripts\Bundle\BundleMock', $bundles[0]->getName());
    }

    /**
     * @expectedException        InstallScripts\Exception\BundleException
     * @expectedExceptionMessage Bundle "InstallScriptsTest\Unknown\Unknown" not found
     */
    public function testGetBundleNotFound()
    {
        $config = array(
            'modules' => array(
                'InstallScriptsTest' => array(
                    'Unknown\Unknown',
                )
            )
        );
        $this->getLocator($config)->getBundles();
    }

    /**
     * @expectedException        InstallScripts\Exception\BundleException
     * @expectedExceptionMessage Bundle version not set for "InstallScriptsTest\InstallScripts\Bundle\BundleMock"
     */
    public function testGetBundleNoConfig()
    {
        $config = array(
            'modules' => array(
                'InstallScriptsTest' => array(
                    'InstallScripts\Bundle\BundleMock',
                )
            )
        );
        $bundles = $this->getLocator($config)->getBundles();
        foreach ($bundles as $bundle) {
            $bundle->getVersions();
        }
    }


    /**
     * @expectedException        InstallScripts\Exception\BundleException
     * @expectedExceptionMessage Bundle "InstallScriptsTest\Locator\LocatorTest" not instance of Bundle
     */
    public function testGetBundleNoInstanceOfBundle()
    {
        $config = array(
            'modules' => array(
                'InstallScriptsTest' => array(
                    'Locator\LocatorTest',
                )
            )
        );
        $this->getLocator($config)->getBundles();
    }

}