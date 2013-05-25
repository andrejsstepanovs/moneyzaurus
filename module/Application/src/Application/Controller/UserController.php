<?php
namespace Application\Controller;

use Varient\Database\ActiveRecord\ActiveRecord;
use Application\Form\Form\Login as LoginForm;
use Application\Form\Validator\Login as LoginValidator;
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

    protected $validator;


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
     * @return \Application\Form\Validator\Login
     */
    public function getValidator()
    {
        if (null === $this->validator) {
            $this->validator = new LoginValidator();
        }

        return $this->validator;
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

        $response = $this->authenticate();
        if ($response) {
            return $response;
        }

        return array(
            'form' => $this->getForm()
        );
    }

    /**
     * @return null|\Zend\Http\PhpEnvironment\Response
     */
    protected function authenticate()
    {
        $request = $this->getRequest();

        if (!$request->isPost()) {
            return null;
        }

        $form = $this->getForm();
        $form->setInputFilter($this->getValidator()->getInputFilter());
        $form->setData($request->getPost());

        if (!$form->isValid()) {
            return null;
        }

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

            return null;
        }

        $user = $this->getUser()
                     ->setEmail($request->getPost('email'))
                     ->load()
                     ->unsPassword()
                     ->toArray();

        $authService->setStorage($this->getSessionStorage());
        $authService->getStorage()->write($user);

        return $this->redirect()->toRoute('moneyzaurus');
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
