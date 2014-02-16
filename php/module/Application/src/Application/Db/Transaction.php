<?php

namespace Application\Db;

use Db\ActiveRecord;

/**
 * Class Transaction
 *
 * @package Application\Db
 *
 * @method Transaction setTransactionId(int $transactionId)
 * @method Transaction setIdUser(int $idUser)
 * @method Transaction setIdGroup(int $idGroup)
 * @method Transaction setIdItem(int $idItem)
 * @method Transaction setPrice(float $idPrice)
 * @method Transaction setIdCurrency(string $idCurrency)
 * @method Transaction setDate(string $data)
 * @method Transaction setDateCreated(string $dataCreated)
 * @method int    getTransactionId()
 * @method int    getIdUser()
 * @method int    getIdGroup()
 * @method int    getIdItem()
 * @method float  getPrice()
 * @method string getIdCurrency()
 * @method string getDate()
 * @method string getDateCreated()
 */
class Transaction extends ActiveRecord
{
    /**
     * @param string|null                   $tableName
     * @param \Zend\Db\Adapter\Adapter|null $adapter
     * @param string|null                   $schema
     */
    public function __construct($tableName = 'transaction', $adapter = null, $schema = null)
    {
        parent::__construct($tableName, $adapter, $schema);
    }
}
