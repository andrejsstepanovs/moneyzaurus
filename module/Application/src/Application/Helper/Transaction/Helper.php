<?php

namespace Application\Helper\Transaction;

use Application\Helper\AbstractHelper;
use Zend\Db\Sql\Select;
use Zend\Http\PhpEnvironment\Request;
use Zend\Mvc\Controller\Plugin\Params;


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
    public function getPredict()
    {
        $params = $this->getParams();
        $predict = $params->fromQuery('predict');
        return $predict;
    }

    /**
     * @return string
     */
    public function getItem()
    {
        $params = $this->getParams();
        $item = $params->fromQuery('item');
        return $item;
    }

    /**
     * @return string
     */
    public function getGroup()
    {
        $params = $this->getParams();
        $group = $params->fromQuery('group');
        return $group;
    }

    /**
     * @return string
     */
    public function getOrderBy()
    {
        return 'transaction_id';
    }

    /**
     * @return string
     */
    public function getOrder()
    {
        return \Zend\Db\Sql\Select::ORDER_DESCENDING;
    }
}