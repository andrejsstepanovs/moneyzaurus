<?php

namespace Moneyzaurus\Entity;

use Varient\Database\Entity\AbstractEntity;

class Purchase extends Transaction
{
    public function setCurrency(AbstractEntity $currency)
    {
        $this->setIdCurrency($currency);
    }

}
