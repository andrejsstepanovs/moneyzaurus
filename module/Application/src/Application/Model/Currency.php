<?php

namespace Application\Model;

use Varient\Database\Model\AbstractModel;

/**
 * @method Currency setCurrencyId(integer $currencyId)
 * @method integer getCurrencyId()
 * @method Currency setName(string $name)
 * @method integer getName()
 * @method Currency setCode(string $code)
 * @method integer getCode()
 * @method Currency setHtml(string $html)
 * @method integer getHtml()
 * @method Currency setDateCreated(datetime $dateCreated)
 * @method datetime getDateCreated()
 */
class Currency extends AbstractModel
{
    /**
     * @return integer
     */
    public function getId()
    {
        return $this->getCurrencyId();
    }

    /**
     * @param integer $id
     * @return \Varient\Database\Model\AbstractModel
     */
    public function setId($id)
    {
        return $this->setCurrencyId($id);
    }
}
