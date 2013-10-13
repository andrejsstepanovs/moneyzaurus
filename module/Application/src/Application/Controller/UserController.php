<?php
namespace Application\Controller;

use Db\Db\ActiveRecord;
use Application\Form\Form\Login as LoginForm;
use Application\Form\Form\User as UserForm;
use Application\Form\Validator\Login as LoginValidator;
use Application\Form\Validator\User as UserValidator;
use Zend\Authentication\Storage\Session;
use Application\Controller\AbstractActionController;


class UserController extends AbstractActionController
{
    /** @var \Application\Form\Login */
    protected $loginForm;

    /** @var \Application\Form\User */
    protected $userForm;

    /** @var \Zend\Authentication\Storage\Session */
    protected $storage;

    /** @var \Db\Db\ActiveRecord */
    protected $user;

    /** @var \Application\Form\Validator\Login */
    protected $loginValidator;

    /** @var \Application\Form\Validator\User */
    protected $userValidator;


    /**
     * @return \Db\Db\ActiveRecord
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
    public function getLoginForm()
    {
        if (null === $this->loginForm) {
            $this->loginForm = new LoginForm();
        }

        return $this->loginForm;
    }

    /**
     * @return \Application\Form\User
     */
    public function getUserForm()
    {
        if (null === $this->userForm) {
            $this->userForm = new UserForm();
        }

        $user = $this->getUser()->load($this->getUserId());


        $formElements = $this->userForm->getElements();

        $formElements['month_start_date']->setValue($user->getMonthStartDate());

        $formElements['default_currency']->setValueOptions($this->getCurrencyValueOptions())
                                         ->setValue($user->getDefaultCurrency());

        $formElements['email']->setValue($user->getEmail());

        return $this->userForm;
    }

    /**
     * @return \Application\Form\Validator\Login
     */
    public function getLoginValidator()
    {
        if (null === $this->loginValidator) {
            $this->loginValidator = new LoginValidator();
        }

        return $this->loginValidator;
    }

    /**
     * @return \Application\Form\Validator\User
     */
    public function getUserValidator()
    {
        if (null === $this->userValidator) {
            $this->userValidator = new UserValidator();
        }

        return $this->userValidator;
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

        $form = $this->getUserForm();

        $request = $this->getRequest();

        if ($request->isPost()) {

            $form->setInputFilter($this->getUserValidator()->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {

                $keys = array('month_start_date', 'default_currency');

                $user = $this->getUser()->load($this->getUserId());

                foreach ($keys AS $key) {
                    $user->setData($key, $request->getPost($key));
                }

                $user->save();

            } else {
                $this->flashmessenger()->addMessage('Wrong data');
            }
        }

        return array(
            'form'     => $this->getUserForm()
        );
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
            'form' => $this->getLoginForm()
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

        $form = $this->getLoginForm();
        $form->setInputFilter($this->getLoginValidator()->getInputFilter());
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
