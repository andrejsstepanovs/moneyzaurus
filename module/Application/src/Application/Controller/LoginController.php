<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Varient\Database\ActiveRecord\ActiveRecord;
use Application\Form\Login as LoginForm;
use Zend\Authentication\Storage\Session;


class LoginController extends AbstractActionController
{
    /** @var \Application\Form\Login */
    protected $form;

    /** @var \Zend\Authentication\Storage\Session */
    protected $storage;

    /** @var \Varient\Database\ActiveRecord\ActiveRecord */
    protected $user;


    /**
     * @return \Varient\Database\ActiveRecord\ActiveRecord
     */
    public function getUser()
    {
        if (null === $this->user) {
            $this->user = new ActiveRecord('user');
        }
        return $this->user;
    }

    /**
     * @return \Zend\Authentication\Storage\Session
     */
    public function getSessionStorage()
    {
        if (null === $this->storage) {
            $this->storage = new Session();
        }

        return $this->storage;
    }

    /**
     * @return \Application\Form\Login
     */
    public function getForm()
    {
        if (null === $this->form) {
            $this->form = new LoginForm();
        }

        return $this->form;
    }

    public function indexAction()
    {
        if ($this->getAuthService()->hasIdentity()){
            $this->flashmessenger()->addMessage('Already logged in');
            return $this->redirect()->toRoute('moneyzaurus');
        }

        $form = $this->getForm();

        return array(
            'form' => $form,
        );
    }

    public function submitAction()
    {
        $form     = $this->getForm();
        $redirect = 'login';

        $request = $this->getRequest();

        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
                /** @var $authService \Zend\Authentication\AuthenticationService */
                $authService = $this->getAuthService();
                $authService->getAdapter()
                            ->setIdentity($request->getPost('email'))
                            ->setCredential($request->getPost('password'));

                $result = $authService->authenticate();
                foreach ($result->getMessages() as $message) {
                    $this->flashmessenger()->addMessage($message);
                }

                if ($result->isValid()) {
                    $redirect = 'moneyzaurus';

                    //check if it has rememberMe :
//                    if ($request->getPost('rememberme') == 1 ) {
//                        $this->getSessionStorage()
//                             ->setRememberMe(1);
//                        //set storage again
//                        $authService->setStorage($this->getSessionStorage());
//                    }

                    $userData = $this->getUser()
                                     ->setEmail($request->getPost('email'))
                                     ->load()
                                     ->toArray();

                    $authService->setStorage($this->getSessionStorage());
                    $authService->getStorage()->write($userData);
//                    $authService->getStorage()->write($request->getPost('email'));
                }
            }
        }

        return $this->redirect()->toRoute($redirect);
    }


    public function logoutAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            $this->getSessionStorage()->forgetMe();
            $this->getAuthService()->clearIdentity();
            $this->flashmessenger()->addMessage("You've been logged out");
        }

        return $this->redirect()->toRoute('login');
    }

}
