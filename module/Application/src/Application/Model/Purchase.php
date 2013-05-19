<?php

namespace Application\Model;

use Varient\Database\Model\AbstractModel;

class Purchase extends Transaction
{
    public function setCurrency(AbstractModel $currency)
    {
        $this->setIdCurrency($currency);
    }

}
