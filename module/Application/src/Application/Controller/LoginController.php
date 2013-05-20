<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class LoginController extends AbstractActionController
{

    public function indexAction()
    {

    }

    public function authAction()
    {
        \DEBUG::dump(__METHOD__);
    }

}
