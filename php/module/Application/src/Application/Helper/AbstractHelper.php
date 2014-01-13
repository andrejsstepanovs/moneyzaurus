<?php

namespace Application\Helper;

use Db\Db\AbstractModel;
use Db\Db\ActiveRecord;

class AbstractHelper extends AbstractModel
{
    /**
     * @var array
     */
    protected $activeRecords;

    /**
     * @param string $table
     * @return \Db\Db\ActiveRecord
     */
    public function getTable($table = null)
    {
        $key = !$table ? 'null' : $table;
        if (!isset($this->activeRecords[$table])) {
            $this->activeRecords[$key] = new ActiveRecord($table);
        }

        return $this->activeRecords[$key];
    }
}