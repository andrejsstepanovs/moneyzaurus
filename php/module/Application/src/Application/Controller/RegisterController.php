<?php
namespace Application\Controller;

use Db\Db\ActiveRecord;
use Application\Form\Form\Register as RegisterForm;
use Application\Form\Validator\Register as RegisterValidator;
use Application\Controller\AbstractActionController;
use Zend\Db\TableGateway\Exception\RuntimeException;
use \Zend\Db\Sql\Expression as Expression;


class RegisterController extends AbstractActionController
{
    /** @var \Application\Form\Form\Register */
    protected $registerForm;

    /** @var \Application\Form\Validator\Register */
    protected $registerValidator;

    /** @var \Db\Db\ActiveRecord */
    protected $user;


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
     * @return \Application\Form\Form\Register
     */
    protected function getRegisterForm()
    {
        if (null === $this->registerForm) {
            $this->registerForm = new RegisterForm();
            $this->registerForm->setAttribute('data-ajax', 'false');
        }

        return $this->registerForm;
    }

    /**
     * @return \Application\Form\Validator\Register
     */
    public function getRegisterValidator()
    {
        if (null === $this->registerValidator) {
            $this->registerValidator = new RegisterValidator();
        }

        return $this->registerValidator;
    }

    /**
     * @return array
     */
    public function indexAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            return $this->redirect()->toRoute('moneyzaurus');
        }

        $form = $this->getRegisterForm();

        /** @var \Zend\Http\PhpEnvironment\Request $request */
        $request = $this->getRequest();

        if ($request->isPost()) {

            $form->setInputFilter($this->getRegisterValidator()->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {

                $response = $this->register();
                if ($response) {
                    return $response;
                }

            } else {
                $this->flashmessenger()->addMessage('Wrong data');
            }
        }

        return array(
            'form' => $this->getRegisterForm()
        );
    }

    /**
     * @return null|\Zend\Http\PhpEnvironment\Response
     */
    protected function register()
    {
        $request = $this->getRequest();

        $user = $this->getUser();
        $user->setEmail($request->getPost('email'));

        try {
            $user->load();
            if ($user->getId()) {
                throw new RuntimeException('User already exists.');
            }

        } catch (\Db\Db\Exception\ModelNotFoundException $exc) {
            $passwordExpression = new Expression(
                AbstractActionController::CREDENTIAL_TREATMENT,
                $request->getPost('password')
            );

            $user->setPassword($passwordExpression);
            $user->save();

            $this->flashmessenger()->addMessage('Success.');
        }

        return $this->redirect()->toRoute('moneyzaurus');
    }

}