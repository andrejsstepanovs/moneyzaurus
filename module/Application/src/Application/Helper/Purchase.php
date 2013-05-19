<?php

namespace Application\Helper;

use Application\Model\Currency as CurrencyModel;
use Application\Model\Group as GroupModel;
use Application\Model\Item as ItemModel;
use Application\Model\User as UserModel;
use Application\Model\Purchase as PurchaseModel;

use Zend\ServiceManager\ServiceManager;

//use Application\Table\Group

class Purchase
{
    /** @var \Zend\ServiceManager\ServiceManager */
    protected $serviceManager;

    /** @var \Application\Table\Group */
    protected $groupTable;

    /** @var \Application\Table\Item */
    protected $itemTable;

    /** @var \Application\Table\Currency */
    protected $currencyTable;

    /** @var \Application\Table\User */
    protected $userTable;

    /** @var \Application\Table\Purchase */
    protected $purchaseTable;

    /** @var \Application\Model\Group */
    protected $groupModel;

    /** @var \Application\Model\Item */
    protected $itemModel;

    /** @var \Application\Model\Currency */
    protected $currencyModel;

    /** @var \Application\Model\User */
    protected $userModel;

    /** @var \Application\Model\Purchase */
    protected $purchaseModel;


    public function __construct(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }

    /**
     * @return \Zend\ServiceManager\ServiceManager
     */
    protected function getServiceManager()
    {
        return $this->serviceManager;
    }

    /**
     * @return \Varient\Database\Table\AbstractTable
     */
    protected function getServiceManagerTable($table)
    {
        return $this->getServiceManager()->get('Application\Table\\'.$table);
    }

    /**
     * @return \Application\Table\Group
     */
    public function getGroupTable()
    {
        if ($this->groupTable === null) {
            $this->groupTable = $this->getServiceManagerTable('Group');
        }
        return $this->groupTable;
    }

    /**
     * @return \Application\Table\Purchase
     */
    public function getPurchaseTable()
    {
        if ($this->purchaseTable === null) {
            $this->purchaseTable = $this->getServiceManagerTable('Purchase');
        }
        return $this->purchaseTable;
    }

    /**
     * @return \Application\Table\Item
     */
    public function getItemTable()
    {
        if ($this->itemTable === null) {
            $this->itemTable = $this->getServiceManagerTable('Item');
        }
        return $this->itemTable;
    }

    /**
     * @return \Application\Table\Currency
     */
    public function getCurrencyTable()
    {
        if ($this->currencyTable === null) {
            $this->currencyTable = $this->getServiceManagerTable('Currency');
        }
        return $this->currencyTable;
    }

    /**
     * @return \Application\Table\User
     */
    public function getUserTable()
    {
        if ($this->userTable === null) {
            $this->userTable = $this->getServiceManagerTable('User');
        }
        return $this->userTable;
    }


    /**
     * @return \Application\Model\User
     */
    public function getUserModel()
    {
        if ($this->userModel === null) {
            $this->userModel = new UserModel();
        }
        return $this->userModel;
    }

    /**
     * @return \Application\Model\Currency
     */
    public function getCurrencyModel()
    {
        if ($this->currencyModel === null) {
            $this->currencyModel = new CurrencyModel();
        }
        return $this->currencyModel;
    }

    /**
     * @return \Application\Model\Item
     */
    public function getItemModel()
    {
        if ($this->itemModel === null) {
            $this->itemModel = new ItemModel();
        }
        return $this->itemModel;
    }

    /**
     * @return \Application\Model\Group
     */
    public function getGroupModel()
    {
        if ($this->groupModel === null) {
            $this->groupModel = new GroupModel();
        }
        return $this->groupModel;
    }

    /**
     * @return \Application\Model\Purchase
     */
    public function getPurchaseModel()
    {
        if ($this->purchaseModel === null) {
            $this->purchaseModel = new PurchaseModel();
        }
        return $this->purchaseModel;
    }

    /**
     * @param integer $idUser
     * @param string $itemValue
     * @param string $groupValue
     * @param string $priceValue
     * @param string $dateValue
     * @return \Application\Model\Purchase
     */
    public function save($idUser, $itemValue, $groupValue, $priceValue, $currencyCode, $dateValue)
    {
        $purchaseModel = $this->getPurchaseModel();
        $purchaseModel->setPrice((float) $priceValue)
                      ->setItem($this->findItemModel($idUser, $itemValue))
                      ->setGroup($this->findGroupModel($idUser, $groupValue))
                      ->setCurrency($this->findCurrencyModel($currencyCode))
                      ->setUser($this->findUserModel($idUser))
                      ->setDate($dateValue);

        $purchaseTable = $this->getPurchaseTable();
        $purchaseTable->insertEntity($purchaseModel);

        return $purchaseModel->setTransactionId($purchaseTable->getLastInsertValue());
    }

    /**
     * @param integer $idUser
     * @param string $itemValue
     * @return \Application\Model\Item
     */
    protected function findItemModel($idUser, $itemValue)
    {
        /** @var $itemModel \Application\Model\Item */
        $itemModel = $this->getItemModel()
                          ->setName($itemValue)
                          ->setIdUser($idUser);

        /** @var $itemTable \Application\Table\Item */
        $itemTable = $this->getItemTable();

        /** @var $itemResult \Zend\Db\ResultSet\HydratingResultSet */
        $itemResult = $itemTable->fetchByModel($itemModel);
        if ($itemResult->count()) {
            $itemModel = $itemResult->current();
        } else {
            $itemTable->insertEntity($itemModel);
            $itemModel->setItemId($itemTable->getLastInsertValue());
        }

        return $itemModel;
    }

    /**
     * @param integer $idUser
     * @param string $groupValue
     * @return \Application\Model\Group
     */
    protected function findGroupModel($idUser, $groupValue)
    {
        /** @var $groupModel \Application\Model\Group */
        $groupModel = $this->getGroupModel()
                           ->setName($groupValue)
                           ->setIdUser($idUser);

        /** @var $itemTable \Application\Table\Group */
        $groupTable = $this->getGroupTable();

        /** @var $groupResult \Zend\Db\ResultSet\HydratingResultSet */
        $groupResult = $groupTable->fetchByModel($groupModel);
        if ($groupResult->count()) {
            $groupModel = $groupResult->current();
        } else {
            $groupTable->insertEntity($groupModel);
            $groupModel->setGroupId($groupTable->getLastInsertValue());
        }

        return $groupModel;
    }

    /**
     * @param string $currencyCode
     * @return \Application\Model\Group
     */
    protected function findCurrencyModel($currencyCode)
    {
        /** @var $groupModel \Application\Model\Currency */
        $currencyModel = $this->getCurrencyModel()
                              ->setCurrencyId($currencyCode);

        /** @var $itemTable \Application\Table\Currency */
        $currencyTable = $this->getCurrencyTable();

        /** @var $currencyResult \Zend\Db\ResultSet\HydratingResultSet */
        $currencyResult = $currencyTable->fetchByModel($currencyModel);
        if ($currencyResult->count()) {
            $currencyModel = $currencyResult->current();
        } else {
            throw new \Application\Exception\CurrencyNotFoundException(
                'Currency "'.$currencyCode.'" is not supported.'
            );
        }

        return $currencyModel;
    }

    /**
     * @param integer $idUser
     * @return \Application\Model\User
     */
    protected function findUserModel($idUser)
    {
        /** @var $userModel \Application\Model\User */
        $userModel = $this->getUserModel()
                          ->setUserId($idUser);

        /** @var $userTable \Application\Table\User */
        $userTable = $this->getUserTable();

        /** @var $userResult \Zend\Db\ResultSet\HydratingResultSet */
        $userResult = $userTable->fetchByModel($userModel);
        if ($userResult->count()) {
            $userModel = $userResult->current();
        } else {
            throw new \Application\Exception\UserNotFoundException(
                'User "'.$idUser.'" is not found.'
            );
        }

        return $userModel;
    }
}