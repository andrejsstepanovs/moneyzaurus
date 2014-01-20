<?php

namespace Application\Helper\Lister;

use Application\Helper\AbstractHelper;

/**
 * @method \Zend\Http\PhpEnvironment\Request getRequest()
 * @method Helper setParams(\Zend\Mvc\Controller\Plugin\Params $params)
 * @method \Zend\Mvc\Controller\Plugin\Params getParams()
 */
class Helper extends AbstractHelper
{
    /**
     * @return string
     */
    public function getOrderBy()
    {
        $params = $this->getParams();
        $orderBy = $params->fromRoute('order_by') ? $params->fromRoute('order_by') : 'transaction_id';
        return $orderBy;
    }

    /**
     * @return string
     */
    public function getOrder()
    {
        $params = $this->getParams();
        $order  = $params->fromRoute('order') ? $params->fromRoute('order') : \Zend\Db\Sql\Select::ORDER_DESCENDING;
        return $order;
    }

    /**
     * @return string
     */
    public function getItemsPerPage()
    {
        return 100;
    }

    /**
     * @return null|string
     */
    public function getItem()
    {
        $params = $this->getParams();
        $item   = $params->fromQuery('item');

        return $item;
    }

    /**
     * @return null|string
     */
    public function getGroup()
    {
        $params = $this->getParams();
        $group   = $params->fromQuery('group');

        return $group;
    }

    /**
     * @return null|string
     */
    public function getPrice()
    {
        $params = $this->getParams();
        $price  = $params->fromQuery('price');

        return $price;
    }

    /**
     * @return null|int
     */
    public function getTransactionId()
    {
        $params = $this->getParams();
        $transactionId  = $params->fromQuery('transaction_id');

        return $transactionId;
    }

    /**
     * @return null|string
     */
    public function getDate()
    {
        $params = $this->getParams();
        $date   = $params->fromQuery('date');

        return $date;
    }

    /**
     * @return null|string
     */
    public function getCurrencyId()
    {
        $params = $this->getParams();
        $currency = $params->fromQuery('currency');

        return $currency;
    }

    /**
     * @return int
     */
    public function getIdUser()
    {
        $params = $this->getParams();
        $idUser = $params->fromQuery('id_user');

        return (int)$idUser;
    }
}
