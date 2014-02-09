<?php

namespace Application\Db;

use \Db\ActiveRecord;

/**
 * Class Currency
 *
 * @package Application\Db
 *
 * @method \Application\Db\Currency setCurrencyId(string $currencyId)
 * @method \Application\Db\Currency setName(string $name)
 * @method \Application\Db\Currency setHtml(string $name)
 * @method \Application\Db\Currency setDateCreated(string $dateCreated)
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
     * @param string|null                   $shema
     */
    public function __construct($tableName = 'Currency', $adapter = null, $schema = null)
    {
        parent::__construct($tableName, $adapter, $schema);
    }
}
