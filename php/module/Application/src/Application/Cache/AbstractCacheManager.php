<?php

namespace Application\Cache;

use Application\Helper\AbstractHelper;
use Zend\Cache\Storage\Adapter\Apc as CacheStorage;

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
    /**
     * @param string $key
     *
     * @return string
     */
    protected function getKey($key)
    {
        return $this->getUserId() . '-' . $key;
    }

    /**
     * @param string   $key
     * @param mixed   $value
     * @param null|int $lifetime
     *
     * @return bool
     */
    protected function save($key, $value, $lifetime = null)
    {
        if ($lifetime) {
            $this->setLifetime($lifetime);
        }
        return $this->getCacheStorage()->addItem($key, $value);
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    protected function get($key)
    {
        return $this->getCacheStorage()->getItem($key);
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    protected function exist($key)
    {
        return $this->getCacheStorage()->hasItem($key);
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    protected function remove($key)
    {
        return $this->getCacheStorage()->removeItem($key);
    }

    /**
     * @param int $lifetime
     */
    protected function setLifetime($lifetime)
    {
        /** @var \Zend\Cache\Storage\Adapter\ApcOptions $options */
        $options = $this->getCacheStorage()->getOptions();
        if ($options->getTtl() != $lifetime) {
            $options->setTtl($lifetime);
        }
    }
}
