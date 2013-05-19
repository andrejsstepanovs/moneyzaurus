<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Session\Container;

class IndexController extends AbstractActionController
{
    protected $transactionTable;

    public function indexAction()
    {
        $purchaseTable = $this->getServiceLocator()->get('Application\Table\Purchase');
//        $purchaseTable = $this->getServiceLocator()->get('Application\Table\Transaction');


        \DEBUG::dump($purchaseTable);

        return array(
            'message' => 'Hello world',
//            'transactions' => $transactions,
        );
    }
}
