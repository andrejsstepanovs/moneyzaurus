<?php

namespace Application\Helper\Transaction;

use Application\Helper\AbstractHelper;

/**
 * Class Predict
 *
 * @package Application\Helper\Transaction
 *
 * @method \Application\Helper\Transaction\Predict setTransactions(array $transactions)
 * @method array getTransactions()
 */
class Predict extends AbstractHelper
{
    const BY_COUNT = 0;
    const BY_DAY   = 1;

    public function getPrices()
    {
        $data = array(self::BY_COUNT => array(), self::BY_DAY => array());

        /** @var \Db\ActiveRecord $transaction */
        foreach ($this->getTransactions() as $transaction) {
            $day = $transaction->getData('day_of_the_week');
            $price = $transaction->getData('price');

            if (empty($data[self::BY_COUNT][$price])) {
                $data[self::BY_COUNT][$price] = 1;
            } else {
                $data[self::BY_COUNT][$price] = $data[self::BY_COUNT][$price] + 1;
            }

            if (empty($data[$day][$price])) {
                $data[self::BY_DAY][$day][$price] = 1;
            } else {
                $data[self::BY_DAY][$day][$price] = $data[$day][$price] + 1;
            }
        }

        $allPrices = array_keys($data[self::BY_COUNT]);

        asort($data[self::BY_COUNT]);

        $prices = array();
        $allSortedPrices = array_keys($data[self::BY_COUNT]);
        $prices[] = end($allSortedPrices); // most popular

        $currentDay = date('w') + 1;
        if (array_key_exists($currentDay, $data[self::BY_DAY])) {
            $pricesInThisDay = array_keys($data[self::BY_DAY][$currentDay]);
            $prices[] = reset($pricesInThisDay); // last used in this day
            $prices[] = next($pricesInThisDay);  // next last used

            sort($pricesInThisDay);
            $prices[] = end($pricesInThisDay); // most popular in this day
        }

        $allSortedPrices = array_keys($data[self::BY_COUNT]);
        if (count($allSortedPrices) > 3) {
            $prices[] = next($allSortedPrices); // next most popular
            $prices[] = next($allSortedPrices); // next most popular
            $prices[] = max($allPrices);
        }

        $prices = array_filter(
            $prices,
            function ($val) {
                return empty($val) ? false : true;
            }
        );

        sort($prices);
        $prices = array_unique($prices);

        return $prices;
    }


}
