<?php

namespace Application\Install;

use InstallScripts\Script;
use Db\ActiveRecord;
use \Db\Exception\ModelNotFoundException;

/**
 * Class Transactions
 *
 * @package Application\Install
 */
class Transactions extends Script
{
    /** @var array */
    protected $activeRecords = array();

    /**
     * init
     */
    public function __construct()
    {
        set_time_limit(0);
    }

    /**
     * @return array
     */
    public function getVersions()
    {
        return array(
            '0.0.1' => 'MoveDatabase',
        );
    }

    /**
     * Move old db values to new db structure
     */
    public function MoveDatabase()
    {
        return true;
    }
}
