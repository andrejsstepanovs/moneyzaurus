<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Session\Container;

class IndexController extends AbstractActionController
{
    protected $transactionTable;

    public function indexAction()
    {
        $itemModel = new \Application\Model\Item();
        $itemModel->setName('UPDATED');
        $itemModel->setIdUser(1);
//        $itemModel->setItemId(4);


//        $table = $this->getServiceLocator()->get('Varient\Database\Helper\TableLoader');
//        \DEBUG::dump($table->getTable('Item'));


//        $itemTable = $this->getServiceLocator()->get('Application\Table\Item');
//
//        $result = $itemTable->saveEntity($itemModel);
//        \DEBUG::dump($result);

//        foreach($itemTable->fetchAll() AS $model){
//
////            \DEBUG::dump($model);
//
//            $result = $item->deleteEntity($model);
//
//            \DEBUG::dump($result);
//        }
//
//        \DEBUG::dump($item->fetchAll());


//        \DEBUG::dump($this->zfcUserIdentity());


//        $session = new Container('Zend_Auth');

//        \DEBUG::dump($session);

//        $session->name='aaaaa';
//        \DEBUG::dump($session);

        return array(
            'message' => 'Hello world',
//            'transactions' => $transactions,
        );
    }
}
