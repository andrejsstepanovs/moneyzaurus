<?php

namespace Application\Model;

use Varient\Database\Model\AbstractModel;

/**
 * @method Group setGroupId(integer $groupId)
 * @method integer getGroupId()
 * @method Group setIdUser(integer $idUser)
 * @method integer getIdUser()
 * @method Group setName(integer $name)
 * @method integer getName()
 * @method Group setDateCreated(datetime $dateCreated)
 * @method datetime getDateCreated()
 */
class Group extends AbstractModel
{
    /**
     * @return integer
     */
    public function getId()
    {
        return $this->getGroupId();
    }

    /**
     * @param integer $id
     * @return \Application\Model\Group
     */
    public function setId($id)
    {
        return $this->setGroupId($id);
    }
}
