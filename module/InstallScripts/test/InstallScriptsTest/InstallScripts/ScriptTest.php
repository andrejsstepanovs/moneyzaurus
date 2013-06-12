<?php

namespace InstallScriptsTest\Locator;

use PHPUnit_Framework_TestCase;
use \InstallScriptsTest\InstallScripts\Script\ScriptMock;
use Zend\Mvc\MvcEvent;


/**
 * @group Script
 */
class ScriptTest extends PHPUnit_Framework_TestCase
{
    /** @var \InstallScriptsTest\InstallScripts\Script\ScriptMock */
    protected $script;


    /**
     * @param null|\Zend\Mvc\MvcEvent $mvcEvent
     * @return \InstallScriptsTest\InstallScripts\Script\ScriptMock
     */
    public function getScript($mvcEvent = null)
    {
        if (null === $this->script) {
            $this->script = new ScriptMock($mvcEvent);
        }
        return $this->script;
    }

    public function testGetScriptName()
    {
        $name = $this->getScript()->getName();
        $this->assertEquals('InstallScriptsTest\InstallScripts\Script\ScriptMock', $name);
    }

    /**
     * @expectedException        InstallScripts\Exception\ScriptException
     * @expectedExceptionMessage Script versions not set for "InstallScriptsTest\InstallScripts\Script\ScriptMock"
     */
    public function testGetVersionsDontExist()
    {
        $this->getScript()->getVersions();
    }

    public function testSetGetVersion()
    {
        $versions = array(
            '0.0.1' => 'Test1',
            '0.0.2' => 'Test2'
        );

        $script = $this->getScript();
        $script->setVersions($versions);
        $this->assertTrue(is_array($script->getVersions()));
        $this->assertEquals($versions, $script->getVersions());
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

            $script = $this->getScript();
            $script->setVersions($shuffled);
            $sorted = $script->getVersionsSorted();

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
        $max = $this->getScript()->setVersions($shuffled)->getMaxVersion();
        $this->assertEquals($expected, $max);
    }

    public function testSetGetMaxVersionNotFound()
    {
        $versions = array();
        $script = $this->getScript();
        $script->setVersions($versions);

        try {
            $script->getMaxVersion();
            $this->assertTrue(false);
        } catch (\InstallScripts\Exception\ScriptException $exc) {
            $this->assertTrue(true);
        }
    }

    public function testSetMvcEvent()
    {
        $mvcEventIn = new MvcEvent();
        $mvcEventOut = $this->getScript()->setMvcEvent($mvcEventIn)->getMvcEvent();

        $this->assertEquals(get_class($mvcEventIn), get_class($mvcEventOut));
    }

}
