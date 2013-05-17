<?php
namespace Application\Entity;

use Varient\Database\Entity\AbstractEntity;

/**
 * @method Connection setId(integer $id)
 * @method integer getId()
 * @method Connection setIdUser(integer $idUser)
 * @method integer getIdUser()
 * @method Connection setIdUserParent(integer $idUserParent)
 * @method integer getIdUserParent()
 * @method Connection setDateCreated(datetime $dateCreated)
 * @method datetime getDateCreated()
 */
class Connection extends AbstractEntity
{
    protected $id;
    protected $id_user;
    protected $id_user_parent;
    protected $date_created;
}