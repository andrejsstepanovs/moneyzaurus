<?php

namespace Application\Cache;

/**
 * Class Manager
 *
 * @package Application\AbstractCacheManager
 */
class Manager extends AbstractCacheManager
{
    const KEY_TRANSACTION_LIST = 'transaction_list';

    /**
     * @param array|null $data
     *
     * @return array|bool
     */
    public function transactionList(array $data = null)
    {
        $this->setLifetime(600);
        $key = $this->getKey(self::KEY_TRANSACTION_LIST);
        if (null === $data) {
            return $this->get($key);
        }
        return $this->save($key, $data);
    }
}
