<?php

namespace Application\Helper\Transaction\Predict;

use Application\Helper\AbstractHelper;

/**
 * Class Predict
 *
 * @package Application\Helper\Transaction
 *
 * @method \Application\Helper\Transaction\Predict\Price setTransactions(array $transactions)
 * @method \Application\Helper\Transaction\Predict\Price setCurrentDay(string $currentDay)
 */
class Price extends AbstractHelper
{
    /** results grouped by usage count */
    const BY_COUNT = 0;

    /** results grouped by day */
    const BY_DAY   = 1;

    /** @var array */
    protected $predictions = array();

    /**
     * @return int
     */
    protected function getCurrentDay()
    {
        $currentDay = $this->getData('current_day');
        if (!$currentDay) {
            $currentDay = date('w') + 1;
            $this->setCurrentDay($currentDay);
        }

        return $currentDay;
    }

    /**
     * @param $price
     *
     * @return $this
     */
    protected function addPrediction($price)
    {
        $this->predictions[] = $price;
        return $this;
    }

    /**
     * @return array
     */
    public function getPredictions()
    {
        return $this->init()->filterResults();
    }

    /**
     * @return array
     */
    protected function filterResults()
    {
        // remove empty
        $prices = array_filter(
            $this->predictions,
            function ($val) {
                return empty($val) ? false : true;
            }
        );

        // sort
        sort($prices);

        // remove duplicates
        $prices = array_unique($prices);

        // format
        $prices = array_map(
            function ($price) {
                return sprintf("%01.2f", $price);
            },
            $prices
        );

        return $prices;
    }

    /**
     * @return $this
     */
    protected function init()
    {
        $data = $this->getGroupedPriceData();

        $allPricesByCount = $data[self::BY_COUNT];

        asort($allPricesByCount);
        $allPricesSortedByCount = array_keys($allPricesByCount);

        // add most popular prices by usage count
        $this->addPrediction(array_pop($allPricesSortedByCount));
        $this->addPrediction(array_pop($allPricesSortedByCount));


        $allPricesByDay = $data[self::BY_DAY];
        $currentDay     = $this->getCurrentDay();

        if (!empty($allPricesByDay[$currentDay])) {
            $allPricesInThisDay = $allPricesByDay[$currentDay];

            $sortedPricesInThisDay = array_keys($allPricesInThisDay);
            $this->addPrediction(array_pop($sortedPricesInThisDay)); // last used in this day
            $this->addPrediction(array_pop($sortedPricesInThisDay)); // next last used

            sort($allPricesInThisDay);
            $sortedPricesInThisDay = array_keys($allPricesInThisDay);
            $this->addPrediction(array_pop($sortedPricesInThisDay)); // most popular in this day
        }

        return $this;
    }

    /**
     * @return array
     */
    protected function getGroupedPriceData()
    {
        $data = array(
            self::BY_COUNT => array(),
            self::BY_DAY   => array()
        );
        $currentDay = $this->getCurrentDay();

        /** @var \Db\ActiveRecord $transaction */
        foreach ($this->getTransactions() as $transaction) {
            $day   = $transaction->getData('day_of_the_week');
            $price = $transaction->getData('price');

            if (empty($data[self::BY_COUNT][$price])) {
                $data[self::BY_COUNT][$price] = 1;
            } else {
                $data[self::BY_COUNT][$price] = $data[self::BY_COUNT][$price] + 1;
            }

            if ($currentDay != $day) {
                continue;
            }

            if (empty($data[$day][$price])) {
                $data[self::BY_DAY][$day][$price] = 1;
            } else {
                $data[self::BY_DAY][$day][$price] = $data[$day][$price] + 1;
            }
        }

        return $data;
    }

    /**
     * @return array
     */
    protected function getTransactions()
    {
        $transactions = $this->getData('transactions');
        return $transactions ? $transactions : array();
    }

}
