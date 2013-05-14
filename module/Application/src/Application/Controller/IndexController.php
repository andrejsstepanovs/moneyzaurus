<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Session\Container;

class IndexController extends AbstractActionController
{
    protected $transactionTable;

    public function indexAction()
    {

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
