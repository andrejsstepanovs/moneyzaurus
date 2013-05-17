<?php

namespace Varient\Database\Entity;

/**
 * 
 */
class AbstractEntity {

    /**
     * Setter/Getter underscore transformation cache
     *
     * @var array
     */
    protected static $underscoreCache = array();

    /**
     * Set/Get attribute wrapper
     *
     * @param   string $method
     * @param   array $args
     * @return  mixed
     */
    public function __call($method, $args)
    {
        switch (substr($method, 0, 3)) {
            case 'get' :
                $key = $this->underscore(substr($method, 3));
                return $this->$key;

            case 'set' :
                $key = $this->underscore(substr($method,3));
                $this->$key = isset($args[0]) ? $args[0] : null;
                return $this;

            case 'uns' :
                $key = $this->underscore(substr($method,3));
                $this->$key = null;
                return $this;

            case 'has' :
                $key = $this->underscore(substr($method,3));
                return isset($this->$key);
        }
    }

    /**
     * Converts field names for setters and geters
     *
     * $this->setMyField($value) === $this->setData('my_field', $value)
     * Uses cache to eliminate unneccessary preg_replace
     *
     * @param string $name
     * @return string
     */
    protected function underscore($name)
    {
        if (isset(self::$underscoreCache[$name])) {
            return self::$underscoreCache[$name];
        }

        $result = strtolower(preg_replace('/(.)([A-Z])/', "$1_$2", $name));
        self::$underscoreCache[$name] = $result;
        return $result;
    }
}

?>
