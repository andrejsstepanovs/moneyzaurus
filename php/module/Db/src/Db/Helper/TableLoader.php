<?php

namespace Db\Helper;

use Zend\Db\ResultSet\HydratingResultSet;
use Zend\Db\Adapter\Adapter;

/**
 * Class TableLoader
 *
 * @package Db\Helper
 */
class TableLoader
{
    /** @var string */
    protected $tableNamespace;

    /** @var string */
    protected $modelNamespace;

    /** @var \Zend\Db\Adapter\Adapter */
    protected $dbAdapter;

    /**
     * @param \Zend\Db\Adapter\Adapter $dbAdapter
     * @param string                   $tableNamespace
     * @param string                   $modelNamespace
     */
    public function __construct($dbAdapter = null, $tableNamespace = null, $modelNamespace = null)
    {
        if ($dbAdapter) {
            $this->setDbAdapter($dbAdapter);
        }

        if ($tableNamespace) {
            $this->setTableNamespace($tableNamespace);
        }

        if ($modelNamespace) {
            $this->setModelNamespace($modelNamespace);
        }
    }

    /**
     * @param  string            $tableName
     * @param  string            $modelName
     * @return \Db\AbstractTable
     */
    public function getTable($tableName, $modelName = null)
    {
        if (null === $modelName) {
            $modelName = $tableName;
        }

        $tableClassname = $this->getTableNamespace().'\\'.$tableName;

        $table = new $tableClassname($this->getDbAdapter(),
                new HydratingResultSet(null, $this->getModel($modelName))
        );

        return $table;
    }

    /**
     * @param  string            $modelName
     * @return \Db\AbstractModel
     */
    protected function getModel($modelName)
    {
        $modelClass = $this->getModelNamespace().'\\'.$modelName;

        return new $modelClass();
    }

    /**
     * @param  string                 $tableNamespace
     * @return \Db\Helper\TableLoader
     */
    public function setTableNamespace($tableNamespace)
    {
        $this->tableNamespace = $tableNamespace;

        return $this;
    }

    /**
     * @param  string                 $modelNamespace
     * @return \Db\Helper\TableLoader
     */
    public function setModelNamespace($modelNamespace)
    {
        $this->modelNamespace = $modelNamespace;

        return $this;
    }

    /**
     * @param  \Zend\Db\Adapter\Adapter $dbAdapter
     * @return \Db\Helper\TableLoader
     */
    public function setDbAdapter(Adapter $dbAdapter)
    {
        $this->dbAdapter = $dbAdapter;

        return $this;
    }

    /**
     * @return string
     */
    protected function getTableNamespace()
    {
        return $this->tableNamespace;
    }

    /**
     * @return string
     */
    protected function getModelNamespace()
    {
        return $this->modelNamespace;
    }

    /**
     * @return \Zend\Db\Adapter\Adapter
     */
    protected function getDbAdapter()
    {
        return $this->dbAdapter;
    }
}
