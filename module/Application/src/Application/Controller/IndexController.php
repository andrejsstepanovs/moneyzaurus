<?php
namespace Application\Controller;

use Application\Controller\AbstractActionController;


class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            return $this->redirect()->toRoute('transaction');
        }
    }

}
