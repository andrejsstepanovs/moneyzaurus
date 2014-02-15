<?php
namespace Application\Controller;

use \Db\Exception\ModelNotFoundException;
use Application\Form\Form\Connection as ConnectionForm;
use Application\Form\Validator\Connection as ConnectionValidator;
use Application\Helper\Mail\Helper as MailHelper;
use Application\Exception\ConnectionExistsException;
use Application\Exception\UserNotFoundException;

/**
 * Class ConnectionController
 *
 * @package Application\Controller
 */
class ConnectionController extends AbstractActionController
{
    /** @var ConnectionForm */
    protected $_connectionForm;

    /** @var ConnectionValidator */
    protected $_connectionValidator;

    /** @var MailHelper */
    protected $_mailHelper;

    /**
     * @return ConnectionForm
     */
    protected function getForm()
    {
        if (null === $this->_connectionForm) {
            $this->_connectionForm = new ConnectionForm;
        }

        return $this->_connectionForm;
    }

    /**
     * @return MailHelper
     */
    protected function getMailHelper()
    {
        if (null === $this->_mailHelper) {
            $this->_mailHelper = new MailHelper;
        }

        return $this->_mailHelper;
    }

    /**
     * @return ConnectionValidator
     */
    protected function getValidator()
    {
        if (null === $this->_connectionValidator) {
            $this->_connectionValidator = new ConnectionValidator();
        }

        return $this->_connectionValidator;
    }

    public function indexAction()
    {
        $form = $this->getForm();

        $request = $this->getRequest();

        if ($request->isPost()) {
            $form->setInputFilter($this->getValidator()->getInputFilter());
            $form->setData($request->getPost());

            $email   = $request->getPost()->get('email');
            $message = $request->getPost()->get('message');

            if ($form->isValid()) {
                try {
                    $user = $this->findUser($email);
                    $this->saveConnection($user);
                    $this->sendEmail($user, $message);

                } catch (\Exception $exc) {
                    $this->showMessage($exc->getMessage());
                }
            }
        }

        return array(
            'form' => $form
        );
    }

    public function failAction()
    {
    }

    public function acceptAction()
    {
        $id = $this->getParam('id');

        /** @var /Application\Db\Connection $connection */
        $connection = $this->getTable('connection');
        $connection->setConnectionId($id)->setIdUserParent($this->getUserId())->load();
        $connection->setState(\Application\Db\Connection::STATE_ACCEPTED)->save();

        return $this->redirect()->toRoute('user'); //#connection
    }

    public function rejectAction()
    {
        $id = $this->getParam('id');

        /** @var /Application\Db\Connection $connection */
        $connection = $this->getTable('connection');
        $connection->setConnectionId($id)->setIdUser($this->getUserId())->load();
        $connection->setState(\Application\Db\Connection::STATE_REJECTED)->save();

        return $this->redirect()->toRoute('user'); //#connection
    }

    /**
     * @param \Application\Db\User $user
     * @param string               $message
     */
    public function sendEmail(\Application\Db\User $user, $message)
    {
        /** @var \Zend\Mail\Transport\Sendmail $transport */
        /** @var \Zend\I18n\Translator\Translator $translator */
        $transport  = $this->getServiceLocator()->get('MailTransport');
        $translator = $this->getServiceLocator()->get('Translator');
        $config     = $this->getServiceLocator()->get('Config');
        try {
            $parentUser = clone $this->getTable('user');
            $parentUser->clear();
            $parentUser->load($this->getUserId());

            /** @var \Zend\View\Helper\Partial $partial */
            $partial  = $this->getViewHelperPlugin('partial');
            $htmlBody = $partial->__invoke(
                'application/connection/email',
                array(
                    'user'    => $user,
                    'friend'  => $parentUser,
                    'message' => $message
                )
            );

            $subject = $translator->translate('Invite to share moneyzaurus account');
            $fromEmail = $config['mail']['email'];

            $message = $this->getMailHelper()->getMailMessage($user->getEmail(), $htmlBody, $subject, $fromEmail);
            $transport->send($message);

        } catch (\Exception $exc) {
            return $this->redirect()->toRoute('connection', array('action' => 'fail'));
        }

        return true;
    }

    /**
     * @param \Application\Db\User $user
     *
     * @return \Application\Db\Connection
     */
    protected function saveConnection(\Application\Db\User $user)
    {
        /** @var \Application\Db\Connection $connection */
        $connection = $this->getTable('connection');
        $connection->setIdUser($user->getUserId())
            ->setIdUserParent($this->getUserId());

        try {
            $connection->load();
            if ($connection->getId()) {
                throw new ConnectionExistsException('Connection already exist.');
            }
        } catch (ModelNotFoundException $exc) {
            $connection->save();
        }

        return $connection;
    }

    /**
     * @param string $email
     *
     * @return \Application\Db\User
     */
    protected function findUser($email)
    {
        /** @var \Application\Db\User $user */
        $user = $this->getTable('user');
        $user->setEmail($email);

        try {
            $user->load();
        } catch (ModelNotFoundException $exc) {
            throw new UserNotFoundException('User ' . $email . ' not found');
        }

        return $user;
    }
}
