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
        $predict = $params->fromRoute('predict');
        return $predict;
    }

    /**
     * @return string
     */
    public function getItem()
    {
        $params = $this->getParams();
        $item = $params->fromRoute('item');
        return $item;
    }

    /**
     * @return string
     */
    public function getGroup()
    {
        $params = $this->getParams();
        $group = $params->fromRoute('group');
        return $group;
    }

}