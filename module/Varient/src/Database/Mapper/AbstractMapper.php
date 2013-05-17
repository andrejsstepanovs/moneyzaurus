<?php

namespace Varient\Database\Mapper;

use ZfcBase\Mapper\AbstractDbMapper;

class AbstractMapper extends AbstractDbMapper
{
    public function setTableName($tableName)
    {
        $this->tableName = $tableName;
    }

}
