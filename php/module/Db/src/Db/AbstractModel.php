<?php

namespace Db;

use \Zend\Db\Sql\Expression as Expression;

/**
 * Abstract Db Model class.
 *
 * @uses ArrayAccess This class will allow get/set data using the [] operator
 *
 * @method \Db\AbstractModel setId(int)
 * @method int               getId()
 */
class AbstractModel implements \ArrayAccess
{

    /**
     * Setter/Getter underscore transformation cache
     *
     * @var array
     */
    protected $underscoreCache = array();

    /**
     * Model data
     *
     * @var array
     */
    protected $data = array();

    /**
     * Exchange internal values from provided array
     *
     * @param  array $array
     * @return void
     */
    public function exchangeArray(array $data)
    {
        foreach ($data as $name => $value) {
            $this->setData($name, $value);
        }
    }

    /**
     * Return an array representation of the object
     *
     * @return array
     */
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }

    /*
     * for key-name = value use setKeyName(value); getKeyName(); unsKeyName(); hasKeyName();
     *
     * @param  string $method
     * @param  array  $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        switch (substr($method, 0, 3)) {
            case 'get':
                $key = $this->underscore(substr($method, 3));

                return $this->getData($key);

            case 'set':
                $key = $this->underscore(substr($method, 3));
                $value = isset($args[0]) ? $args[0] : null;

                return $this->setData($key, $value);

            case 'uns':
                $key = $this->underscore(substr($method, 3));

                return $this->unsetData($key);

            case 'has':
                $key = $this->underscore(substr($method, 3));

                return $this->hasData($key);
        }

        throw new \Db\Exception\UnknownCallableException(
            'Method "'.$method.'" dose not exist'
        );
    }

    /**
     * @return \Db\AbstractModel
     */
    public function clear()
    {
        $this->data = array();

        return $this;
    }

    /**
     * @param string $key
     */
    public function unsetData($key)
    {
        $this->data[$key] = null;

        return $this;
    }

    /**
     * @param string|array $key
     * @param mixed        $value
     */
    public function setData($key, $value = null)
    {
        if (is_array($key)) {
            $this->data = $key;
        } else {
            $this->data[$key] = $value;
        }

        return $this;
    }

    /**
     * @param  string $key
     * @return mixed
     */
    public function getData($key = null)
    {
        if (null === $key) {
            return $this->data;
        }

        return isset($this->data[$key]) ? $this->data[$key] : null;
    }

    /**
     * @return array
     */
    public function toArray($noObjects = true)
    {
        $array = $this->getData();
        if (!$noObjects) {
            return $array;
        }

        array_walk(
            $array,
            function ($val, $key) use (&$array) {
                if (is_object($val) && !$val instanceof Expression) {
                    unset($array[$key]);
                }
            }
        );

        return $array;
    }

    /**
     * @param  string  $key
     * @return boolean
     */
    public function hasData($key)
    {
        return array_key_exists($key, $this->data);
    }

    /**
     * @param  string $name
     * @return string
     */
    protected function underscore($name)
    {
        if (isset($this->underscoreCache[$name])) {
            return $this->underscoreCache[$name];
        }

        $result = strtolower(preg_replace('/(.)([A-Z])/', "$1_$2", $name));
        $this->underscoreCache[$name] = $result;

        return $result;
    }

    /**
     * ArrayAccess Interface
     * @param mixed $offset
     */
    public function offsetExists($offset)
    {
        return $this->hasData($offset);
    }

    /**
     * ArrayAccess Interface
     * @param mixed $offset
     */
    public function offsetGet($offset)
    {
        return $this->getData($offset);
    }

    /**
     * ArrayAccess Interface
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        return $this->setData($offset, $value);
    }

    /**
     * ArrayAccess Interface
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        return $this->unsetData($offset);
    }
}
