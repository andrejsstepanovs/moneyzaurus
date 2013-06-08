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

    /** @var string */
    protected $schema;

    /** @var \Varient\Database\Table\AbstractTable */
    protected $table;

    /** @var \Varient\Database\Model\AbstractModel */
    protected $model;

    /** @var \Zend\Db\Adapter\Adapter */
    protected $adapter;

    /** @var \Zend\Stdlib\Hydrator\HydratorInterface|null */
    protected $hydrator;


    /**
     * @param strings|null $tableName
     * @param \Zend\Db\Adapter\Adapter|null $adapter
     * @param strings|null $shema
     */
    public function __construct($tableName = null, $adapter = null, $schema = null)
    {
        if ($adapter) {
            $this->setDbAdapter($adapter);
        }

        if ($tableName) {
            $this->setTableName($tableName);
        }

        if ($schema) {
            $this->setShema($schema);
        }
    }

    /**
     * @param string $tableName
     */
    public function setTableName($tableName)
    {
        $this->tableName = $tableName;
        return $this;
    }

    /**
     * @param string $tableName
     */
    public function setShema($schema)
    {
        $this->schema = $schema;
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
     * @return string
     */
    public function getSchema()
    {
        return $this->schema;
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
     * @param \Varient\Database\Model\AbstractModel $model
     */
    public function setModel(AbstractModel $model)
    {
        $this->model = $model;
    }

    /**
     * @return \Varient\Database\Model\AbstractModel
     */
    public function getModel()
    {
        if (null === $this->model) {
            return $this;
        }

        return $this->model;
    }

    /**
     * @return \Varient\Database\Table\AbstractTable
     */
    public function getTable()
    {
        if (null === $this->table) {
            if ( $this->getTableName()) {
                $tableIdentifier = new TableIdentifier(
                    $this->getTableName(),
                    $this->getSchema()
                );
            } else {
                $tableIdentifier = null;
            }

            $this->setTable(new AbstractTable(
                $this->getAdapter(),
                new HydratingResultSet($this->getHydrator(), $this->getModel()),
                $tableIdentifier
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

        return $this;
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
     * @throws \Varient\Database\Exception\ModelNotFoundException
     */
    public function load($id = null)
    {
        if ($id) {
            $this->setId($id);
        }

        $results = $this->getTable()->fetchByModel($this);

        if ($results->count()) {
            $this->data = $results->current()->getData();
            return $this;
        }

        throw new \Varient\Database\Exception\ModelNotFoundException(
            '"'.$this->getTableName() . '" not found for primary "' . $this->getId() . '"'
        );
    }

    /**
     * @return \Varient\Database\ActiveRecord\ActiveRecord
     */
    public function save()
    {
        $this->getTable()->saveEntity($this);

        if (!$this->getId()) {
            $id = $this->getTable()->getLastInsertValue();
            if ($id) {
                $this->clear()->setId($id);
            }
        }

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
