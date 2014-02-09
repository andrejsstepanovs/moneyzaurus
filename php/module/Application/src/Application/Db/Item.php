<?php

namespace Application\Db;

use \Db\ActiveRecord;

/**
 * Class Item
 *
 * @package Application\Db
 *
 * @method \Application\Db\Item setItemId(int $itemId)
 * @method \Application\Db\Item setIdUser(int $idUser)
 * @method \Application\Db\Item setName(string $name)
 * @method \Application\Db\Item setDateCreated(string $dateCreated)
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
     * @param string|null                   $shema
     */
    public function __construct($tableName = 'item', $adapter = null, $schema = null)
    {
        parent::__construct($tableName, $adapter, $schema);
    }
}
