<?php

namespace Application\Db;

use Db\ActiveRecord;

/**
 * Class Item
 *
 * @package Application\Db
 *
 * @method Item setItemId(int $itemId)
 * @method Item setIdUser(int $idUser)
 * @method Item setName(string $name)
 * @method Item setDateCreated(string $dateCreated)
 * @method string getItemId()
 * @method string getIdUser()
 * @method string getName()
 * @method string getDateCreated()
 */
class Item extends ActiveRecord
{
    /**
     * @param string|null                   $tableName
     * @param \Zend\Db\Adapter\Adapter|null $adapter
     * @param string|null                   $schema
     */
    public function __construct($tableName = 'item', $adapter = null, $schema = null)
    {
        parent::__construct($tableName, $adapter, $schema);
    }
}
