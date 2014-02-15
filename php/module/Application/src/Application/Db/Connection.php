<?php

namespace Application\Db;

use \Db\ActiveRecord;

/**
 * Class Currency
 *
 * @package Application\Db
 *
 * @method Connection setConnectionId(int $connectionId)
 * @method Connection setIdUser(int $idUser)
 * @method Connection setIdUserParent(int $idUserParent)
 * @method Connection setDateCreated(string $dateCreated)
 * @method Connection setState(int $state)
 * @method int    getConnectionId()
 * @method int    getIdUser()
 * @method int    getIdUserParent()
 * @method string getDateCreated()
 * @method int    getState()
 */
class Connection extends ActiveRecord
{
    /** connection is accepted */
    const STATE_ACCEPTED = 'accepted';

    /** connection is rejected */
    const STATE_REJECTED = 'rejected';

    /**
     * @param string|null                   $tableName
     * @param \Zend\Db\Adapter\Adapter|null $adapter
     * @param string|null                   $schema
     */
    public function __construct($tableName = 'connection', $adapter = null, $schema = null)
    {
        parent::__construct($tableName, $adapter, $schema);
    }
}
