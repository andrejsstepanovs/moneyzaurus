<?php
namespace Moneyzaurus\Model;

use Varient\Database\Model\AbstractModel;

/**
 * @method Transaction setTransactionId(integer $id)
 * @method integer getTransactionId()
 * @method Transaction setIdUser(integer $idUser)
 * @method integer getIdUser()
 * @method Transaction setIdGroup(integer $idGroup)
 * @method integer getIdGroup()
 * @method Transaction setIdItem(integer $idItem)
 * @method integer getIdItem()
 * @method Transaction setPrice(float $price)
 * @method float getPrice()
 * @method Transaction setIdCurrency(integer $idCurrency)
 * @method integer getIdCurrency()
 * @method Transaction setDate(date $date)
 * @method date getDate(date $date)
 * @method Transaction setDateCreated(datetime $dateCreated)
 * @method datetime getDateCreated()
 */
class Transaction extends AbstractModel
{
    /**
     * @return integer
     */
    public function getId()
    {
        return $this->getTransactionId();
    }

    /**
     * @param integer $id
     * @return \Application\Model\Transaction
     */
    public function setId($id)
    {
        return $this->setTransactionId($id);
    }
}