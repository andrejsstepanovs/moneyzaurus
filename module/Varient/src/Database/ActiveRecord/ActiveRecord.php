<?php

namespace Varient\Database\ActiveRecord;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\AdapterAwareInterface;
use Zend\Db\Sql\TableIdentifier;
use Zend\Db\ResultSet\HydratingResultSet;
use Zend\Stdlib\Hydrator\HydratorInterface;
use Varient\Database\Table\AbstractTable;
use Varient\Database\Model\AbstractModel;


/**
 * Active Record
 */
class ActiveRecord extends AbstractModel implements AdapterAwareInterface
{
    /** @var string */
    protected $tableName;

    /** @var \Varient\Database\Table\AbstractTable */
    protected $table;

    /** @var \Varient\Database\Model\AbstractModel */
    protected $model;

    /** @var \Zend\Db\Adapter\Adapter */
    protected $adapter;

    /** @var \Zend\Stdlib\Hydrator\HydratorInterface|null */
    protected $hydrator;


    /**
     * @param string $tableName
     */
    public function setTableName($tableName)
    {
        $this->tableName = $tableName;
        return $this;
    }

    /**
     * @param \Zend\Db\Adapter\Adapter $adapter
     * @return \Varient\Database\ActiveRecord\ActiveRecord
     */
    public function setDbAdapter(Adapter $adapter)
    {
        $this->adapter = $adapter;
        return $this;
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * @return \Zend\Db\Adapter\Adapter
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * @param \Varient\Database\Table\AbstractTable $table
     * @return \Varient\Database\ActiveRecord\ActiveRecord
     */
    public function setTable($table)
    {
        $this->table = $table;
        return $this;
    }

    /**
     * @param \Zend\Stdlib\Hydrator\HydratorInterface $hydrator
     */
    public function setHydrator(HydratorInterface $hydrator)
    {
        $this->hydrator = $hydrator;
        return $this;
    }

    /**
     * @return \Zend\Stdlib\Hydrator\HydratorInterface|null
     */
    public function getHydrator()
    {
        return $this->hydrator;
    }

    /**
     * @return \Varient\Database\Table\AbstractTable
     */
    public function getTable()
    {
        if (null === $this->table) {
            $this->setTable(new AbstractTable(
                $this->getAdapter(),
                new HydratingResultSet($this->getHydrator(), $this),
                new TableIdentifier($this->getTableName())
            ));
        }
        return $this->table;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $primaries = $this->getTable()->getPrimary();
        foreach ($primaries AS $primary) {
            $this->setData($primary, $id);
        }
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        $primaries = $this->getTable()->getPrimary();
        foreach ($primaries AS $primary) {
            return $this->getData($primary);
        }
        return null;
    }

    /**
     * @param mixed $id
     * @return \Varient\Database\ActiveRecord\ActiveRecord
     */
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

    /**
     * @return \Varient\Database\ActiveRecord\ActiveRecord
     */
    public function save()
    {
        $this->getTable()->saveEntity($this);
        return $this->load();
    }

    /**
     * @return integer AffectedRows
     */
    public function delete()
    {
        return $this->getTable()->deleteEntity($this);
    }

}
