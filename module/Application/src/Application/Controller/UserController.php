<?php
namespace Application\Controller;

use Varient\Database\ActiveRecord\ActiveRecord;
use Application\Form\Login as LoginForm;
use Zend\Authentication\Storage\Session;
use Varient\Controller\AbstractActionController;

class UserController extends AbstractActionController
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

    /**
     * Shows user profile.
     *
     * @return array
     */
    public function indexAction()
    {
        if (!$this->getAuthService()->hasIdentity()) {
            return $this->redirect()->toRoute('user', array('action' => 'login'));
        }
    }

    /**
     * Shows login form.
     *
     * @return array
     */
    public function loginAction()
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

    /**
     * Make authentification.
     *
     * @return \Zend\Http\PhpEnvironment\Response
     */
    protected function authenticateAction()
    {
        $form     = $this->getForm();
        $redirect = 'user';

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
                if (!$result->isValid()) {
                    foreach ($result->getMessages() as $message) {
                        $this->flashmessenger()->addMessage($message);
                    }
                } else {
                    $redirect = 'moneyzaurus';

                    $user = $this->getUser()
                                 ->setEmail($request->getPost('email'))
                                 ->load()
                                 ->unsPassword()
                                 ->toArray();

                    $authService->setStorage($this->getSessionStorage());
                    $authService->getStorage()->write($user);
                }
            }
        }

        return $this->redirect()->toRoute($redirect);
    }

    /**
     * Clear user identity.
     *
     * @return \Zend\Http\PhpEnvironment\Response
     */
    public function logoutAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            $this->getAuthService()->clearIdentity();
            $this->flashmessenger()->addMessage('Logged out');
        }

        return $this->redirect()->toRoute('moneyzaurus');
    }

}
