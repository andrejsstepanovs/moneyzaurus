<?php

namespace Varient\Database\Table;

use Varient\Database\Exception;
use Varient\Database\Model\AbstractModel;
use Zend\Db\Metadata\Metadata;
use Zend\Db\Sql\TableIdentifier;
use Zend\Db\TableGateway\Feature;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Sql\Select;

/**
 * Abstract Table class
 */
class AbstractTable extends AbstractTableGateway
{
    /** @var array */
    protected $primary;

    /** @var array */
    protected $uniqe;

    /** @var string */
    protected $tableName;

    /** @var \Zend\Db\Metadata\Metadata */
    protected $metadata;

    /** @var \Zend\Db\Metadata\Object\TableObject */
    protected $metadataTableObject;


    /**
     *
     * @param \Zend\Db\Adapter\Adapter|null $adapter
     * @param |Zend\Db\ResultSet\HydratingResultSet|\Varient\Database\Model\AbstractModel $resultSetPrototype
     * @param \Zend\Db\Sql\TableIdentifier $table
     */
    public function __construct($adapter, $resultSetPrototype, TableIdentifier $table = null)
    {
        if (empty($table) && empty($this->table)) {
            $classname = get_class($this);
            $tablename = strtolower(substr($classname, strrpos($classname, '\\') + 1));
            $table = new TableIdentifier($tablename);

        } elseif (is_string($this->table)) {
            $table = new TableIdentifier($this->table);
        }

        $this->table = $table;

        if (null === $adapter) {
            $this->featureSet = new Feature\FeatureSet();
            $this->featureSet->addFeature(new Feature\GlobalAdapterFeature());
        } else {
            $this->adapter = $adapter;
        }

        $this->resultSetPrototype = $resultSetPrototype;
        $this->initialize();
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        if (null === $this->tableName) {
            $this->tableName = $this->getTable()->getTable();
        }

        return $this->tableName;
    }

    /**
     * @return Zend\Db\Metadata\Metadata
     */
    protected function getMetadata()
    {
        if (null == $this->metadata) {
            $this->metadata = new Metadata($this->adapter);
        }

        return $this->metadata;
    }

    /**
     * @return Zend\Db\Metadata\Object\TableObject
     */
    protected function getMetadataTableObject()
    {
        if (null == $this->metadataTableObject) {
            $this->metadataTableObject = $this->getMetadata()
                                              ->getTable($this->getTableName());
        }

        return $this->metadataTableObject;
    }

    /**
     * @return array
     */
    public function getPrimary()
    {
        if (null === $this->primary) {
            /** @var $constraint Zend\Db\Metadata\Object\ConstraintObject */
            $constraints = $this->getMetadataTableObject()->getConstraints();
            foreach ($constraints AS $constraint) {
                if ($constraint->isPrimaryKey()) {
                    $primaries = $constraint->getColumns();
                    foreach ($primaries AS $primary) {
                        $this->primary[] = $primary;
                    }
                }
            }
            if (empty($this->primary)) {
                $uniqe = $this->getUniqe();
                if (!empty($uniqe)) {
                    foreach ($uniqe AS $column) {
                        $this->primary[] = $column;
                    }
                } else {
                    throw new Exception\TablePrimaryNotFoundException(
                            'Primary not found in table "'.$this->getTableName().'"'
                    );
                }
            }
            $this->primary = array_unique($this->primary);
        }

        return $this->primary;
    }

    /**
     * @return array
     */
    public function getUniqe()
    {
        if (null === $this->uniqe) {
            /** @var $constraint Zend\Db\Metadata\Object\ConstraintObject */
            $constraints = $this->getMetadataTableObject()->getConstraints();
            foreach ($constraints AS $constraint) {
                if ($constraint->isPrimaryKey() || $constraint->isUnique()) {
                    $primaries = $constraint->getColumns();
                    foreach ($primaries AS $primary) {
                        $this->uniqe[] = $primary;
                    }
                }
            }
            $this->uniqe = array_unique($this->uniqe);
        }

        return $this->uniqe;
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        if (empty($this->columns)) {
            /** @var $column Zend\Db\Metadata\Object\ColumnObject */
            $columnObjects = $this->getMetadataTableObject()->getColumns();
            foreach ($columnObjects AS $column) {
                $columns[] = $column->getName();
            }
            $this->columns = $columns;
        }

        return $this->columns;
    }

    /**
     * @return \Zend\Db\ResultSet\ResultSet
     * @throws Exception\WrongModuleException
     */
    public function fetchAll()
    {
        return $this->select();
    }

    /**
     * @return \Zend\Db\ResultSet\ResultSet
     * @throws Exception\WrongModuleException
     */
    public function fetchUniqeColum($column, $where = null)
    {
        return $this->select(function (Select $select) use ($column, $where) {
            $select->quantifier(Select::QUANTIFIER_DISTINCT);
            $select->columns(array($column));
            if ($where) {
                $select->where($where);
            }
        });
    }

    /**
     * @param \Varient\Database\Model\AbstractModel $model
     * @return \Zend\Db\ResultSet\HydratingResultSet
     */
    public function fetchByModel(AbstractModel $model)
    {
        $where = array();
        foreach ($model->getData() AS $key => $val) {
            $where[$key] = $val;
        }

        /** @var $select Zend\Db\Sql\Select */
        $select = $this->getSql()->select()->where($where);

        /** @var $resultSet Zend\Db\ResultSet\HydratingResultSet */
        $resultSet = $this->executeSelect($select);
        return $resultSet;
    }

    /**
     * @param \Varient\Database\Model\AbstractModel $model
     * @return array
     */
    protected function getPrimaryValue(AbstractModel $model)
    {
        $where = array();
        foreach ($this->getPrimary() AS $key) {
            if ($model->hasData($key)) {
                $where[$key] = $model->getData($key);
            }
        }

        return $where;
    }

    /**
     * @param \Varient\Database\Model\AbstractModel $model
     * @return array
     */
    protected function getUniqeValue(AbstractModel $model)
    {
        $where = array();
        foreach ($this->getUniqe() AS $key) {
            if ($model->hasData($key)) {
                $where[$key] = $model->getData($key);
            }
        }

        return $where;
    }

    /**
     * @param \Varient\Database\Model\AbstractModel $model
     * @return integer
     */
    public function deleteEntity(AbstractModel $model)
    {
        return $this->delete($model->toArray());
    }

    /**
     * @param \Varient\Database\Model\AbstractModel $model
     * @return integer
     */
    public function insertEntity(AbstractModel $model)
    {
        return $this->insert($model->toArray());
    }

    /**
     * @param \Varient\Database\Model\AbstractModel $model
     * @return integer
     */
    public function updateEntity(AbstractModel $model)
    {
        $where = $this->getPrimaryValue($model);
        return $this->update($model->toArray(), $where);
    }

    /**
     * @param \Varient\Database\Model\AbstractModel $model
     * @return integer
     */
    public function saveEntity(AbstractModel $model)
    {
        $where = $this->getPrimaryValue($model);
        if (empty($where)) {
            return $this->insertEntity($model);
        }
        return $this->updateEntity($model);
    }

}
