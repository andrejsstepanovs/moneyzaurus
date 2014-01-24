<?php
namespace Db;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\AdapterAwareInterface;
use Zend\Db\Sql\TableIdentifier;
use Zend\Db\ResultSet\HydratingResultSet;
use Zend\Stdlib\Hydrator\HydratorInterface;

/**
 * Active Record
 */
class ActiveRecord extends AbstractModel implements AdapterAwareInterface
{
    /** @var string */
    protected $tableName;

    /** @var string */
    protected $schema;

    /** @var \Db\AbstractTable */
    protected $table;

    /** @var \Db\AbstractModel */
    protected $model;

    /** @var \Zend\Db\Adapter\Adapter */
    protected $adapter;

    /** @var \Zend\Stdlib\Hydrator\HydratorInterface|null */
    protected $hydrator;

    /**
     * @param string|null                   $tableName
     * @param \Zend\Db\Adapter\Adapter|null $adapter
     * @param string|null                   $shema
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
     * @param  \Zend\Db\Adapter\Adapter $adapter
     * @return \Db\ActiveRecord
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
     * @param  \Db\AbstractTable $table
     * @return \Db\ActiveRecord
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
     * @param \Db\AbstractModel $model
     */
    public function setModel(AbstractModel $model)
    {
        $this->model = $model;
    }

    /**
     * @return \Db\AbstractModel
     */
    public function getModel()
    {
        if (null === $this->model) {
            return $this;
        }

        return $this->model;
    }

    /**
     * @return \Db\AbstractTable
     */
    public function getTable()
    {
        if (null === $this->table) {
            if ($this->getTableName()) {
                $tableIdentifier = new TableIdentifier(
                    $this->getTableName(),
                    $this->getSchema()
                );
            } else {
                $tableIdentifier = null;
            }

            $this->setTable(
                new AbstractTable(
                    $this->getAdapter(),
                    new HydratingResultSet($this->getHydrator(), $this->getModel()),
                    $tableIdentifier
                )
            );
        }

        return $this->table;
    }

    /**
     * @param mixed $idValue
     */
    public function setId($idValue)
    {
        $primaries = $this->getTable()->getPrimary();
        foreach ($primaries as $primary) {
            $this->setData($primary, $idValue);
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        $primaries = $this->getTable()->getPrimary();
        foreach ($primaries as $primary) {
            return $this->getData($primary);
        }

        return null;
    }

    /**
     * @param mixed $idValue
     *
     * @return \Db\ActiveRecord
     * @throws \Db\Exception\ModelNotFoundException
     */
    public function load($idValue = null)
    {
        if ($idValue) {
            $this->setId($idValue);
        }

        $results = $this->getTable()->fetchByModel($this);

        if ($results->count()) {
            $this->data = $results->current()->getData();

            return $this;
        }

        throw new \Db\Exception\ModelNotFoundException(
            '"'.$this->getTableName() . '" not found for primary "' . $this->getId() . '"'
        );
    }

    /**
     * @return \Db\ActiveRecord
     */
    public function save()
    {
        $this->getTable()->saveEntity($this);

        if (!$this->getId()) {
            $idValue = $this->getTable()->getLastInsertValue();
            if ($idValue) {
                $this->clear()->setId($idValue);
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
