<?php

namespace Application\Helper\Transaction\Predict;

use Application\Helper\AbstractHelper;

/**
 * Class Predict
 *
 * @package Application\Helper\Transaction
 *
 * @method Price setTransactions(array $transactions)
 */
class Price extends AbstractHelper
{
    /** @var array */
    protected $predictions = array();

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
        /** @var \Db\ActiveRecord $transaction */
        foreach ($this->getTransactions() as $transaction) {
            $this->addPrediction($transaction->getData('price'));
        }

        return $this->formatResults();
    }

    /**
     * @return array
     */
    protected function formatResults()
    {
        $prices = array_map(
            function ($price) {
                return sprintf("%01.2f", $price);
            },
            $this->predictions
        );

        return $prices;
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
