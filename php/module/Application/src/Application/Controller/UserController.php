<?php
namespace Application\Controller;

use Application\Form\Form\User as UserForm;
use Application\Form\Validator\User as UserValidator;
use Application\Helper\Connection\Helper as ConnectionHelper;
use Zend\Authentication\Storage\Session;
use Zend\Db\Sql\Expression as SqlExpression;
use Application\Module;

/**
 * Class UserController
 *
 * @package Application\Controller
 */
class UserController extends AbstractActionController
{
    /** @var \Application\Form\Form\User */
    protected $userForm;

    /** @var \Zend\Authentication\Storage\Session */
    protected $storage;

    /** @var \Application\Form\Validator\Login */
    protected $loginValidator;

    /** @var \Application\Form\Validator\User */
    protected $userValidator;

    /** @var ConnectionHelper */
    protected $connectionHelper;

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
     * @return ConnectionHelper
     */
    public function getConnectionHelper()
    {
        if (null === $this->connectionHelper) {
            $this->connectionHelper = new ConnectionHelper;
            $this->connectionHelper->setAbstractHelper($this->getAbstractHelper());
        }

        return $this->connectionHelper;
    }

    /**
     * @return \Application\Form\Form\User
     */
    public function getUserForm()
    {
        if (null === $this->userForm) {
            $this->userForm = new UserForm();
        }

        /** @var \Zend\I18n\Translator\Translator $translator */
        $translator = $this->getServiceLocator()->get('Translator');

        /** @var \Application\Db\User $user */
        $user = $this->getAbstractHelper()->getTable('user');
        $user->setUserId($this->getUserId())->load();

        /** @var \Zend\Form\Element\Select[] $formElements */
        $formElements = $this->userForm->getElements();

        $formElements['email']->setValue($user->getEmail());
        $formElements['email']->setAttribute('disabled', 'disabled');

        $formElements['password']->setAttribute('placeholder', $translator->translate('New password'));

        $formElements['submit']->setValue($translator->translate('Save'));

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
        $userForm = $form;

        /** @var \Zend\Http\PhpEnvironment\Request $request */
        $request = $this->getRequest();

        if ($request->isPost()) {

            $inputFilter = $this->getUserValidator()->getInputFilter();
            $inputFilter->remove('email');

            $form->remove('email');
            $form->setInputFilter($inputFilter);
            $form->setData($request->getPost());

            if ($form->isValid()) {

                /** @var \Application\Db\User $user */
                $user = $this->getAbstractHelper()->getTable('user');
                $user->setUserId($this->getUserId())->load();

                $passwordExpression = new SqlExpression(
                    Module::CREDENTIAL_TREATMENT,
                    $request->getPost('password')
                );

                $user->setPassword($passwordExpression);
                $user->save();

            } else {
                $this->flashmessenger()->addMessage('Wrong data');
            }
        }

        $connections = $this->getConnectionHelper()->getUserConnections($this->getUserId());
        return array(
            'form'        => $userForm,
            'connections' => $connections,
            'userId'      => $this->getUserId()
        );
    }
}
