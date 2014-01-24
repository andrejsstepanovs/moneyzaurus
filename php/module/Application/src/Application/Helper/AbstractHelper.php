<?php

namespace Application\Helper;

use Db\AbstractModel;
use Db\ActiveRecord;

/**
 * Class AbstractHelper
 *
 * @package Application\Helper
 */
class AbstractHelper extends AbstractModel
{
    /**
     * @var array
     */
    protected $activeRecords;

    /**
     * @param  string           $table
     * @return \Db\ActiveRecord
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
