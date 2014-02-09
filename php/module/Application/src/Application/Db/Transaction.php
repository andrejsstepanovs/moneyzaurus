<?php

namespace Application\Db;

use \Db\ActiveRecord;

/**
 * Class Transaction
 *
 * @package Application\Db
 *
 * @method \Application\Db\Transaction setTransactionId(int $transactionId)
 * @method \Application\Db\Transaction setIdUser(int $idUser)
 * @method \Application\Db\Transaction setIdGroup(int $idGroup)
 * @method \Application\Db\Transaction setIdItem(int $idItem)
 * @method \Application\Db\Transaction setPrice(float $idPrice)
 * @method \Application\Db\Transaction setIdCurrency(string $idCurrency)
 * @method \Application\Db\Transaction setDate(string $data)
 * @method \Application\Db\Transaction setDateCreated(string $dataCreated)
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
     * @param string|null                   $shema
     */
    public function __construct($tableName = 'transaction', $adapter = null, $schema = null)
    {
        parent::__construct($tableName, $adapter, $schema);
    }
}
