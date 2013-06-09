<?php

namespace Application\Install;

use InstallScripts\Model\Bundle;

class Transactions extends Bundle
{

    /**
     * @return array
     */
    public function getVersions()
    {
        return array(
            '0.0.3' => 'Install',
            '0.0.1' => 'Install',
            '0.1.0' => 'Install',
            '0.0.2' => 'Install',
        );
    }

    public function Install()
    {
        return true;
    }

}
