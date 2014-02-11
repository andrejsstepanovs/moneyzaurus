<?php

namespace Application\Db;

use \Db\ActiveRecord;

/**
 * Class Currency
 *
 * @package Application\Db
 *
 * @method \Application\Db\Connection setConnectionId(int $connectionId)
 * @method \Application\Db\Connection setIdUser(int $idUser)
 * @method \Application\Db\Connection setIdUserParent(int $idUserParent)
 * @method \Application\Db\Connection setDateCreated(string $dateCreated)
 * @method int    getConnectionId()
 * @method int    getIdUser()
 * @method int    getIdUserParent()
 * @method string getDateCreated()
 */
class Connection extends ActiveRecord
{
    /**
     * @param string|null                   $tableName
     * @param \Zend\Db\Adapter\Adapter|null $adapter
     * @param string|null                   $shema
     */
    public function __construct($tableName = 'connection', $adapter = null, $schema = null)
    {
        parent::__construct($tableName, $adapter, $schema);
    }
}
