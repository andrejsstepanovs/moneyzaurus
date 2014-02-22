<?php

namespace Application\Cache;

/**
 * Class Manager
 *
 * @package Application\AbstractCacheManager
 */
class Manager extends AbstractCacheManager
{
    /**
     * Saves cache value if $data is provided.
     * Reads data from cache if $data is not provided.
     *
     * @param array|string $namespace
     * @param string       $key
     * @param mixed|null   $data
     *
     * @return mixed|bool
     */
    public function data($namespace, $key, $data = null)
    {
        $namespaces = is_array($namespace) ? $namespace : array($namespace);

        foreach ($namespaces as $namespace) {
            if ($data === null) {
                $value = $this->get($key, $namespace);
                if ($value) {
                    return $value;
                }
            } else {
                $this->save($namespace, $key, $data);
            }
        }

        return false;
    }

    /**
     * Invalidate namespace cache
     *
     * @param string|array $namespace
     * @param string|null  $action    null will remove all
     *
     * @throws \InvalidArgumentException
     * @return $this
     */
    public function trigger($namespace, $action = null)
    {
        $namespaces = is_array($namespace) ? $namespace : array($namespace);

        foreach ($namespaces as $namespace) {
            switch ($action) {
                case self::ACTION_UPDATE:
                case self::ACTION_DELETE:
                case self::ACTION_INSERT:
                case null:
                    $this->removeNamespace($namespace);
                    break;
                default:
                    throw new \InvalidArgumentException(
                        'Action "' . $action . '" not found'
                    );
                    break;
            }
        }

        return $this;
    }
}
