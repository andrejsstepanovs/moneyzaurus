<?php

namespace Varient\Database\ActiveRecord;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\AdapterAwareInterface;
use Zend\Db\Sql\TableIdentifier;
use Zend\Db\ResultSet\HydratingResultSet;
use Varient\Database\Table\AbstractTable;
use Varient\Database\Model\AbstractModel;

class ActiveRecord extends AbstractModel implements AdapterAwareInterface
{
    protected $tableName;
    protected $table;
    protected $model;
    protected $adapter;

    public function setTableName($tableName)
    {
        $this->tableName = $tableName;
    }

    public function setDbAdapter(Adapter $adapter)
    {
        $this->adapter = $adapter;
        return $this;
    }

    public function getTableName()
    {
        return $this->tableName;
    }

    public function getAdapter()
    {
        return $this->adapter;
    }

    public function getTable()
    {
        if (null === $this->table) {
            $this->table = new AbstractTable(
                $this->getAdapter(),
                new HydratingResultSet(null, $this),
                new TableIdentifier($this->getTableName())
            );
        }
        return $this->table;
    }

    public function setId($id)
    {
        $primaries = $this->getTable()->getPrimary();
        foreach ($primaries AS $primary) {
            $this->setData($primary, $id);
        }
    }

    public function load($id = null)
    {
        if ($id) {
            $this->setId($id);
        }

        $results = $this->getTable()->fetchByModel($this);

        if ($results->count()) {
            $this->data = $results->current()->getData();
        }

        return $this;
    }

    public function save()
    {
        $this->getTable()->saveEntity($this);
        return $this->load();
    }

    public function delete()
    {
        return $this->getTable()->deleteEntity($this);
    }
}
