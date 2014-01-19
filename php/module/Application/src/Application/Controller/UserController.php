<?php
namespace Application\Controller;

use Db\Db\ActiveRecord;
use Application\Form\Form\User as UserForm;
use Application\Form\Validator\User as UserValidator;
use Zend\Authentication\Storage\Session;
use Application\Controller\AbstractActionController;


class UserController extends AbstractActionController
{
    /** @var \Application\Form\Form\User */
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
     * @return \Application\Form\Form\User
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
        $form = $this->getUserForm();

        /** @var \Zend\Http\PhpEnvironment\Request $request */
        $request = $this->getRequest();

        if ($request->isPost()) {

            $form->setInputFilter($this->getUserValidator()->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {

                $keys = array('month_start_date', 'default_currency');

                $user = $this->getUser()->load($this->getUserId());

                foreach ($keys as $key) {
                    $user->setData($key, $request->getPost($key));
                }

                try {
                    $user->save();
                } catch (\Exception $exc) {
                    //$exc->getMessage();
                }

            } else {
                $this->flashmessenger()->addMessage('Wrong data');
            }
        }

        return array(
            'form'     => $this->getUserForm()
        );
    }
}
