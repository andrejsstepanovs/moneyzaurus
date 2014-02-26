<?php

namespace Application\Helper\Lister;

use Application\Helper\AbstractHelper;
use Zend\Http\PhpEnvironment\Request;
use Zend\Mvc\Controller\Plugin\Params as PluginParams;
use Zend\Db\Sql\Select;

/**
 * @method Request getRequest()
 * @method Helper setParams(PluginParams $params)
 * @method PluginParams getParams()
 */
class Helper extends AbstractHelper
{
    /**
     * @return string
     */
    public function getOrderBy()
    {
        $params = $this->getParams();
        $orderBy = $params->fromPost('order_by') ? $params->fromPost('order_by') : 'transaction_id';

        return $orderBy;
    }

    /**
     * @return string
     */
    public function getOrder()
    {
        $params = $this->getParams();
        $order  = $params->fromPost('order') ? $params->fromPost('order') : Select::ORDER_DESCENDING;

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
        $item   = $params->fromPost('item');

        return $item;
    }

    /**
     * @return null|string
     */
    public function getGroup()
    {
        $params = $this->getParams();
        $group   = $params->fromPost('group');

        return $group;
    }

    /**
     * @return null|string
     */
    public function getPrice()
    {
        $params = $this->getParams();
        $price  = $params->fromPost('price');

        return $price;
    }

    /**
     * @return null|int
     */
    public function getTransactionId()
    {
        $params = $this->getParams();
        $transactionId  = $params->fromPost('transaction_id');

        return $transactionId;
    }

    /**
     * @return null|string
     */
    public function getDate()
    {
        $params = $this->getParams();
        $date   = $params->fromPost('date');

        return $date;
    }

    /**
     * @return null|string
     */
    public function getCurrencyId()
    {
        $params = $this->getParams();
        $currency = $params->fromPost('currency');

        return $currency;
    }

    /**
     * @return int
     */
    public function getIdUser()
    {
        $params = $this->getParams();
        $idUser = $params->fromPost('id_user');

        return (int) $idUser;
    }
}
