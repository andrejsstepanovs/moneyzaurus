<?php

namespace Application\Model;


class Purchase extends Transaction
{
    /**
     * @param \Application\Model\User $user
     * @return \Application\Model\Purchase
     */
    public function setUser(User $user)
    {
        parent::setUser($user);
        return $this->setIdUser($user->getId());
    }

    /**
     * @param \Application\Model\Currency $currency
     * @return \Application\Model\Purchase
     */
    public function setCurrency(Currency $currency)
    {
        parent::setCurrency($currency);
        return $this->setIdCurrency($currency->getId());
    }

    /**
     * @param \Application\Model\Group $group
     * @return \Application\Model\Purchase
     */
    public function setGroup(Group $group)
    {
        parent::setGroup($group);
        return $this->setIdGroup($group->getId());
    }

    /**
     * @param \Application\Model\Item $item
     * @return \Application\Model\Purchase
     */
    public function setItem(Item $item)
    {
        parent::setItem($item);
        return $this->setIdItem($item->getId());
    }


    /**
     * @param date $date
     * @return \Application\Model\Purchase
     */
    public function setDate($date)
    {
        parent::setDate(date('Y-m-d', strtotime($date)));
    }

}
