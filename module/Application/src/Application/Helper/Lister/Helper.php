<?php

namespace Application\Helper\Lister;

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
    public function getOrderBy()
    {
        $params = $this->getParams();
        $order_by = $params->fromRoute('order_by') ? $params->fromRoute('order_by') : 'transaction_id';
        return $order_by;
    }

    /**
     * @return string
     */
    public function getOrder()
    {
        $params = $this->getParams();
        $order    = $params->fromRoute('order') ? $params->fromRoute('order') : \Zend\Db\Sql\Select::ORDER_ASCENDING;
        return $order;
    }

    /**
     * @return string
     */
    public function getPage()
    {
        $params = $this->getParams();
        $page     = $params->fromRoute('page') ? (int) $params->fromRoute('page') : 1;
        return $page;
    }

    /**
     * @return string
     */
    public function getItemsPerPage()
    {
        return 20;
    }

}