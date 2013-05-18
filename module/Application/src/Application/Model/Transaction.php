<?php
namespace Moneyzaurus\Entity;

use Varient\Database\Entity\AbstractEntity;

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
class Transaction extends AbstractEntity
{

}