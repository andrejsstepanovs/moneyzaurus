<?php
namespace Application\Controller;

use Db\Db\ActiveRecord;
use Application\Form\Form\Login as LoginForm;
use Application\Form\Form\User as UserForm;
use Application\Form\Validator\Login as LoginValidator;
use Application\Form\Validator\User as UserValidator;
use Zend\Authentication\Storage\Session;
use Application\Controller\AbstractActionController;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;


class LoginController extends AbstractActionController
{
    /** @var \Application\Form\Form\Login */
    protected $loginForm;

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
     * @return \Application\Form\Form\Login
     */
    public function getLoginForm()
    {
        if (null === $this->loginForm) {
            $this->loginForm = new LoginForm();
        }

        return $this->loginForm;
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
     * Shows user profile.
     *
     * @return array
     */
    public function indexAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            return $this->redirect()->toRoute('moneyzaurus');
        }

        $form = $this->getLoginForm();

        /** @var \Zend\Http\PhpEnvironment\Request $request */
        $request = $this->getRequest();

        if ($request->isPost()) {

            if ($this->getAuthService()->hasIdentity()){
                $this->flashmessenger()->addMessage('Already logged in');
                return $this->redirect()->toRoute('moneyzaurus');
            }

            $form->setInputFilter($this->getLoginValidator()->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {

                $response = $this->authenticate();
                if ($response) {
                    return $response;
                }

            } else {
                $this->flashmessenger()->addMessage('Wrong data');
            }
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

}
