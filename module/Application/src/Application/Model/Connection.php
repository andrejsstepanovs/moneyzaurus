<?php
namespace Application\Model;

use Varient\Database\Model\AbstractModel;

/**
 * @method Connection setConnectionId(integer $connectionId)
 * @method integer getConnectionId()
 * @method Connection setIdUser(integer $idUser)
 * @method integer getIdUser()
 * @method Connection setIdUserParent(integer $idUserParent)
 * @method integer getIdUserParent()
 * @method Connection setDateCreated(datetime $dateCreated)
 * @method datetime getDateCreated()
 */
class Connection extends AbstractModel
{
    /**
     * @return integer
     */
    public function getId()
    {
        return $this->getConnectionId();
    }

    /**
     * @param integer $id
     * @return \Application\Model\Connection
     */
    public function setId($id)
    {
        return $this->setConnectionId($id);
    }
}
