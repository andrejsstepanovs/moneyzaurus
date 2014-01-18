<?php
namespace Application\Controller;

use Application\Controller\AbstractActionController;
use Application\Form\Form\Login as LoginForm;


class IndexController extends AbstractActionController
{
    /** @var \Application\Form\Form\Login */
    protected $loginForm;

    /**
     * @return \Application\Form\Form\Login
     */
    protected function getLoginForm()
    {
        if (null === $this->loginForm) {
            $this->loginForm = new LoginForm();
            $this->loginForm->setAttribute('data-ajax', 'false');
        }

        return $this->loginForm;
    }

    public function indexAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            return $this->redirect()->toRoute('transaction');
        }

        $this->getViewHelperPlugin('inlineScript')->appendScript('
            $(document).ready(function() {
                $("#login-username").focus();
            });
        ');

        return array(
            'form' => $this->getLoginForm()
        );
    }

}
