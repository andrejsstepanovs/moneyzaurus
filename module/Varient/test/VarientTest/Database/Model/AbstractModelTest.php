<?php

namespace VarientTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Varient\Database\Model\AbstractModel;

class AbstractModelTest extends AbstractHttpControllerTestCase
{
    protected $model;

    /**
     * @return \Varient\Database\Model\AbstractModel
     */
    public function getModel()
    {
        if (null === $this->model) {
            $this->model = new AbstractModel();
        }
        return $this->model;
    }

    public function providerArrayData()
    {
        $data = array();
        for ($i = 0; $i < 100; $i++) {
            $val = array();
            $k = rand(5,10);
            for ($j = 0; $j < $k; $j++) {
                $val[uniqid()] = uniqid();
            }
            $data[] = array($val);
        }

        return $data;
    }

    public function providerKeyValData()
    {
        $data = array();
        for ($i = 0; $i < 10; $i++) {
            $k = rand(5,10);
            for ($j = 0; $j < $k; $j++) {
                $data[] = array(uniqid(), uniqid());
            }
        }

        return $data;
    }

    /**
     * @dataProvider providerArrayData
     */
    public function testSetFullData($originalData)
    {
        $this->getModel()->setData($originalData);

        $this->assertEmpty(array_diff($originalData, $this->getModel()->getData()));
    }

    /**
     * @dataProvider providerKeyValData
     */
    public function testSetKeyData($key, $val)
    {
        $this->getModel()->setData($key, $val);

        $this->assertEquals($val, $this->getModel()->getData($key));
    }

    /**
     * @dataProvider providerKeyValData
     */
    public function testUpdateKeyData($key, $val)
    {
        $this->getModel()->setData($key, 'somedata');
        $this->getModel()->setData($key, $val);

        $this->assertEquals($val, $this->getModel()->getData($key));
    }

    /**
     * @dataProvider providerArrayData
     */
    public function testClearData($originalData)
    {
        $this->getModel()->setData($originalData);
        $this->getModel()->clear();

        $this->assertEmpty($this->getModel()->getData());
    }

    /**
     * @dataProvider providerKeyValData
     */
    public function testUnsetData($key, $val)
    {
        $this->getModel()->setData($key, $val);
        $this->getModel()->unsetData($key);

        $this->assertNull($this->getModel()->getData($key));
    }

    /**
     * @dataProvider providerKeyValData
     */
    public function testHasDataFalseAfterClear($key, $val)
    {
        $this->getModel()->setData($key, $val);
        $this->getModel()->clear();

        $this->assertFalse($this->getModel()->hasData($key));
    }

    /**
     * @dataProvider providerKeyValData
     */
    public function testHasDataFalse($key, $val)
    {
        $this->assertFalse($this->getModel()->hasData($key));
    }

    public function testSetDataWithSet()
    {
        $val = uniqid();

        $this->getModel()->setKey($val);
        $this->assertEquals($val, $this->getModel()->getData('key'));

        $this->getModel()->setKeyName($val);
        $this->assertEquals($val, $this->getModel()->getData('key_name'));

        $this->getModel()->setKeyNameValue($val);
        $this->assertEquals($val, $this->getModel()->getData('key_name_value'));
    }

    public function testGetDataWithGet()
    {
        $val = uniqid();

        $this->getModel()->setKey($val);
        $this->assertEquals($val, $this->getModel()->getKey());

        $this->getModel()->setKeyName($val);
        $this->assertEquals($val, $this->getModel()->getKeyName());

        $this->getModel()->setKeyNameValue($val);
        $this->assertEquals($val, $this->getModel()->getKeyNameValue());
    }

    public function testGetUnknownKey()
    {
        $this->assertNull($this->getModel()->getKey());
        $this->assertNull($this->getModel()->getData('somekey'));
    }

    public function testUnsetKey()
    {
        $val = uniqid();
        $this->getModel()->setKeyName($val);
        $this->getModel()->unsKeyName();

        $this->assertNull($this->getModel()->getData('key_name'));
    }

    public function testHasKey()
    {
        $val = uniqid();
        $this->getModel()->setKeyName($val);

        $this->assertTrue($this->getModel()->hasKeyName());
        $this->assertFalse($this->getModel()->hasUnknownKeyName());
    }

    public function testSetReturnsObject()
    {
        $model = $this->getModel()->setKeyName('value');

        $this->assertEquals(get_class($this->getModel()), get_class($model));
    }

    /**
     * @dataProvider providerArrayData
     */
    public function testToArray($data)
    {
        $this->getModel()->setData($data);

        $this->assertEmpty(array_diff($data, $this->getModel()->toArray()));
    }

    public function testToArrayWithObjects()
    {
        $obj = new \stdClass();
        $obj->apple = uniqid();

        $data = array(
            'lemon'  => 'yellow',
            'object' => $obj
        );
        $this->getModel()->setData($data);

        $array = $this->getModel()->toArray(false);
        $this->assertEquals($obj->apple, $array['object']->apple);
    }

    public function testToArrayWithObjectsAsArray()
    {
        $obj = new \stdClass();
        $obj->apple = uniqid();

        $data = array(
            'lemon'  => 'yellow',
            'object' => $obj
        );
        $this->getModel()->setData($data);

        $array = $this->getModel()->toArray();

        $this->assertArrayNotHasKey('object', $array);
    }

    /**
     * @dataProvider providerArrayData
     */
    public function testArrayAccessSet($data)
    {
        $model = $this->getModel();
        foreach ($data AS $key => $val) {
            $model[$key] = $val;
        }

        $this->assertEmpty(array_diff($data, $this->getModel()->getData()));
    }

    /**
     * @dataProvider providerArrayData
     */
    public function testArrayAccessGet($data)
    {
        $model = $this->getModel()->setData($data);

        foreach ($data AS $key => $val) {
            $this->assertEquals($val, $model[$key]);
        }
    }

    /**
     * @dataProvider providerKeyValData
     */
    public function testArrayAccessUnset($key, $val)
    {
        $model = $this->getModel()->setData($key, $val);

        unset($model[$key]);

        $this->assertNull($this->getModel()->getData($key));
    }

    /**
     * @dataProvider providerKeyValData
     */
    public function testArrayAccessOffset($key, $val)
    {
        $model = $this->getModel()->setData($key, $val);

        $this->assertTrue(isset($model[$key]));

        $empty = empty($model[$key]);
        $this->assertFalse($empty);
    }

    /**
     * @dataProvider providerKeyValData
     */
    public function testGetArrayCopy($key, $val)
    {
        $this->getModel()->setData($key, $val);

        $array = $this->getModel()->getArrayCopy();

        $this->assertEmpty(array_diff($array['data'], $this->getModel()->getData()));
    }

    /**
     * @expectedException Varient\Database\Exception\UnknownCallableException
     */
    public function testUnknownCallable()
    {
        $this->getModel()->unknwoncallable();
    }

    /**
     * @expectedException Varient\Database\Exception\UnknownCallableException
     */
    public function testUnknownCallableWithParams()
    {
        $this->getModel()->unknwoncallable(1, 2);
    }

    /**
     * @dataProvider providerArrayData
     */
    public function testExchangeArray($data)
    {
        $this->getModel()->exchangeArray($data);
        $this->assertEmpty(array_diff($data, $this->getModel()->getData()));
    }

}