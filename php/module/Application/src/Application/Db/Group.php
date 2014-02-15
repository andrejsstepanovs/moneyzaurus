<?php

namespace Application\Db;

use \Db\ActiveRecord;

/**
 * Class Group
 *
 * @package Application\Db
 *
 * @method Group setGroupId(int $GroupId)
 * @method Group setIdUser(int $idUser)
 * @method Group setName(string $name)
 * @method Group setDateCreated(string $dateCreated)
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
     * @param string|null                   $schema
     */
    public function __construct($tableName = 'group', $adapter = null, $schema = null)
    {
        parent::__construct($tableName, $adapter, $schema);
    }
}
