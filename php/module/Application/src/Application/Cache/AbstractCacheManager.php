<?php

namespace Application\Cache;

use Application\Helper\AbstractHelper;
use Zend\Cache\Storage\Adapter\Filesystem as CacheStorage;
use Zend\Cache\Storage\Adapter\ApcOptions as CacheOptions;
use Zend\Serializer\Serializer;
use Zend\Serializer\Adapter\PhpSerialize;

/**
 * Class AbstractCacheManager
 *
 * @package Application\Cache
 *
 * @method AbstractCacheManager setCacheStorage(CacheStorage $cache)
 * @method CacheStorage         getCacheStorage()
 * @method CacheStorage         setUserId(int $userId)
 * @method int                  getUserId()
 */
abstract class AbstractCacheManager extends AbstractHelper
{
    /** update action */
    const ACTION_INSERT = 'insert';

    /** update action */
    const ACTION_UPDATE = 'update';

    /** delete action */
    const ACTION_DELETE = 'delete';

    /** @var CacheOptions */
    protected $options;

    /** @var Serializer */
    protected $serializer;

    /**
     * @param string $namespace
     *
     * @return string
     */
    protected function getNamespace($namespace)
    {
        return 'user' . $this->getUserId() . '' . $namespace;
    }

    /**
     * @param string   $namespace
     * @param string   $key
     * @param mixed    $value
     * @param null|int $lifetime
     *
     * @return bool
     */
    protected function save($namespace, $key, $value, $lifetime = null)
    {
        $this->setNamespace($namespace);
        if ($lifetime) {
            $this->setLifetime($lifetime);
        }

        $serialized = $this->serialize($value);

        return $this->getCacheStorage()->setItem($key, $serialized);
    }

    /**
     * @param string      $key
     * @param null|string $namespace
     *
     * @return mixed
     */
    protected function get($key, $namespace = null)
    {
        if ($namespace) {
            $this->setNamespace($namespace);
        }

        $serialized = $this->getCacheStorage()->getItem($key);
        $data = $this->unserialize($serialized);

        return $data;
    }

    /**
     * @param string      $key
     * @param null|string $namespace
     *
     * @return bool
     */
    protected function exist($key, $namespace = null)
    {
        if ($namespace) {
            $this->setNamespace($namespace);
        }

        return $this->getCacheStorage()->hasItem($key);
    }

    /**
     * @param string      $key
     * @param null|string $namespace
     *
     * @return bool
     */
    public function removeByKey($key, $namespace = null)
    {
        if ($namespace) {
            $this->setNamespace($namespace);
        }

        return $this->getCacheStorage()->removeItem($key);
    }

    /**
     * @param string $namespace
     *
     * @return bool
     */
    protected function removeNamespace($namespace)
    {
        $namespaceValue = $this->setNamespace($namespace)->getNamespace($namespace);

        return $this->getCacheStorage()->clearByNamespace($namespaceValue);
    }

    /**
     * @param string $namespace
     *
     * @return $this
     */
    protected function setNamespace($namespace)
    {
        $namespaceValue = $this->getNamespace($namespace);
        $this->getOptions()->setNamespace($namespaceValue);

        return $this;
    }

    /**
     * @param int $lifetime
     */
    public function setLifetime($lifetime)
    {
        if ($this->getOptions()->getTtl() != $lifetime) {
            $this->getOptions()->setTtl($lifetime);
        }
    }

    /**
     * @return CacheOptions
     */
    private function getOptions()
    {
        if ($this->options === null) {
            $this->options = $this->getCacheStorage()->getOptions();
        }

        return $this->options;
    }

    /**
     * @param mixed $data
     *
     * @return string
     */
    private function serialize($data)
    {
        return $this->getSerializer()->serialize($data);
    }

    /**
     * @param string $data
     *
     * @return mixed
     */
    private function unserialize($data)
    {
        return $this->getSerializer()->unserialize($data);
    }

    /**
     * @return PhpSerialize
     */
    private function getSerializer()
    {
        if (null === $this->serializer) {
            $this->serializer = Serializer::factory('PhpSerialize');
        }

        return $this->serializer;
    }
}
