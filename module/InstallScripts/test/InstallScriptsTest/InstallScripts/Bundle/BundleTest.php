<?php

namespace InstallScriptsTest\Locator;

use PHPUnit_Framework_TestCase;
use \InstallScriptsTest\InstallScripts\Bundle\BundleMock;


class BundleTest extends PHPUnit_Framework_TestCase
{
    /** @var \InstallScripts\Locator\Locator */
    protected $bundle;


    /**
     * @param null|\Zend\Db\Adapter\Adapter $adapter
     * @return \InstallScriptsTest\InstallScripts\Bundle\BundleMock
     */
    public function getBundle($adapter = null)
    {
        if (null === $this->bundle) {
            $this->bundle = new BundleMock($adapter);
        }
        return $this->bundle;
    }

    public function testGetBundleName()
    {
        $name = $this->getBundle()->getName();
        $this->assertEquals('InstallScriptsTest\InstallScripts\Bundle\BundleMock', $name);
    }

    /**
     * @expectedException        InstallScripts\Exception\BundleException
     * @expectedExceptionMessage Bundle version not set for "InstallScriptsTest\InstallScripts\Bundle\BundleMock"
     */
    public function testGetVersionsDontExist()
    {
        $this->getBundle()->getVersions();
    }

    public function testSetGetVersion()
    {
        $versions = array(
            '0.0.1' => 'Test1',
            '0.0.2' => 'Test2'
        );

        $bundle = $this->getBundle();
        $bundle->setVersions($versions);
        $this->assertTrue(is_array($bundle->getVersions()));
        $this->assertEquals($versions, $bundle->getVersions());
    }

    protected function shuffle_assoc($array)
    {
        uksort($array, function() { return rand() > rand(); });
        return $array;
    }

    public function testSetGetVersionsSorted()
    {
        $versions = array(
            '0'     => '0',
            '0.0.1' => '1',
            '0.0.4' => '2',
            '0.1'   => '3',
            '0.4.2' => '4',
            '0.9.2' => '5',
            '1.0.2' => '6',
            '2'     => '7',
            '2.0.1' => '8',
            '2.0.2' => '9',
            '2.1.0' => '10',
            '2.9.0' => '11',
            '2.9.1' => '12',
            '3'     => '13',
            '4.0'   => '14',
        );

        for ($k=0; $k < 10; $k++) {
            $shuffled = $this->shuffle_assoc($versions);

            $bundle = $this->getBundle();
            $bundle->setVersions($shuffled);
            $sorted = $bundle->getVersionsSorted();

            $i = 0;
            foreach ($sorted AS $version => $j) {
                $this->assertEquals($i, $j, '$version='.$version);
                $i++;
            }
        }
    }

    public function providerGetMaxVersion()
    {
        return array(
            array(array(
                    '0.3'   => '0',
                    '1.1.0' => '1',
                ), '1.1.0'),
            array(array(
                    '1.9.99' => '1',
                    '2.0.0'  => '0',
                ), '2.0.0'),
            array(array(
                    '3'     => '1',
                    '2.0.0' => '0',
                ), '3'),
            array(array(
                    '0.1.1' => '1',
                    '0.1.0' => '0',
                    '0.2.0' => '1',
                ), '0.2.0'),
            array(array(
                    '0.3'   => '1',
                    '0.0.1' => '1',
                ), '0.3'),
        );
    }

    /**
     * @dataProvider providerGetMaxVersion
     */
    public function testSetGetMaxVersion($versions, $expected)
    {
        $shuffled = $this->shuffle_assoc($versions);
        $max = $this->getBundle()->setVersions($shuffled)->getMaxVersion();
        $this->assertEquals($expected, $max);
    }

    public function testSetGetMaxVersionNotFound()
    {
        $versions = array();
        $bundle = $this->getBundle();
        $bundle->setVersions($versions);

        try {
            $bundle->getMaxVersion();
            $this->assertTrue(false);
        } catch (\InstallScripts\Exception\BundleException $exc) {
            $this->assertTrue(true);
        }
    }

    public function testSetException()
    {
        $this->getBundle()->setException('EXCEPTION1');
        $exceptions = $this->getBundle()->getExceptions();

        $this->assertTrue(is_array($exceptions));
        $this->assertGreaterThan(0, count($exceptions));
        $this->assertEquals('EXCEPTION1', $exceptions[0]);
    }
}