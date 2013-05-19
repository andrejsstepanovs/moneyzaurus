<?php

namespace Application\Model;

use Varient\Database\Model\AbstractModel;

/**
 * @method Item setItemId(integer $itemId)
 * @method integer getItemId()
 * @method Item setIdUser(integer $idUser)
 * @method integer getIdUser()
 * @method Item setName(integer $name)
 * @method integer getName()
 * @method Item setDateCreated(datetime $dateCreated)
 * @method datetime getDateCreated()
 */
class Item extends AbstractModel
{
    /**
     * @return integer
     */
    public function getId()
    {
        return $this->getItemId();
    }

    /**
     * @param integer $id
     * @return \Application\Model\Item
     */
    public function setId($id)
    {
        return $this->setItemId($id);
    }
}
