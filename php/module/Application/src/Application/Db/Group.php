<?php

namespace Application\Db;

use \Db\ActiveRecord;

/**
 * Class Group
 *
 * @package Application\Db
 *
 * @method \Application\Db\Group setGroupId(int $GroupId)
 * @method \Application\Db\Group setIdUser(int $idUser)
 * @method \Application\Db\Group setName(string $name)
 * @method \Application\Db\Group setDateCreated(string $dateCreated)
 * @method int    getGroupId()
 * @method int    getIdUser()
 * @method string getName()
 * @method string getDateCreated()
 */
class Group extends ActiveRecord
{
    /**
     * @param string|null                   $tableName
     * @param \Zend\Db\Adapter\Adapter|null $adapter
     * @param string|null                   $shema
     */
    public function __construct($tableName = 'group', $adapter = null, $schema = null)
    {
        parent::__construct($tableName, $adapter, $schema);
    }
}
