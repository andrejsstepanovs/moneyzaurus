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
            '0.0.3' => __NAMESPACE__.'\Install',
            '0.0.1' => __NAMESPACE__.'\Install',
            '0.1.0' => __NAMESPACE__.'\Install',
            '0.0.2' => __NAMESPACE__.'\Install',
        );
    }

    public function Install()
    {

    }

}
