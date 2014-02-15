<?php

namespace Application\Db;

use \Db\ActiveRecord;

/**
 * Class Currency
 *
 * @package Application\Db
 *
 * @method Currency setCurrencyId(string $currencyId)
 * @method Currency setName(string $name)
 * @method Currency setHtml(string $name)
 * @method Currency setDateCreated(string $dateCreated)
 * @method string getCurrencyId()
 * @method string getName()
 * @method string getHtml()
 * @method string getDateCreated()
 */
class Currency extends ActiveRecord
{
    /**
     * @param string|null                   $tableName
     * @param \Zend\Db\Adapter\Adapter|null $adapter
     * @param string|null                   $schema
     */
    public function __construct($tableName = 'currency', $adapter = null, $schema = null)
    {
        parent::__construct($tableName, $adapter, $schema);
    }
}
