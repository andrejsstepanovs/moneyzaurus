<?php
namespace Moneyzaurus\Entity;

use Varient\Entity\Entity as VarientEntity;

/**
 * @method Transaction setId(integer $id)
 * @method integer getId()
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
class Transaction extends VarientEntity
{
    protected $id;
    protected $id_user;
    protected $id_group;
    protected $id_item;
    protected $price;
    protected $id_currency;
    protected $date;
    protected $date_created;
}