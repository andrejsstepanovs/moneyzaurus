<?php
namespace Application\Controller;

use Db\ActiveRecord;
use Application\Form\Form\ResendPassword as ResendPasswordForm;
use Application\Form\Validator\ResendPassword as ResendPasswordValicator;
use Zend\Authentication\Storage\Session;
use Zend\Mail\Message;
use Zend\Db\Sql\Expression as Expression;

/**
 * Class ResendPasswordController
 *
 * @package Application\Controller
 */
class ResendPasswordController extends AbstractActionController
{
    /** @var \Application\Form\Form\ResendPassword */
    protected $resendPasswordForm;

    /** @var \Application\Form\Validator\ResendPassword */
    protected $resendPassValidator;

    /** @var \Zend\Authentication\Storage\Session */
    protected $storage;

    /** @var \Db\ActiveRecord */
    protected $user;

    /**
     * @return \Db\ActiveRecord
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
     * @return \Application\Form\Form\ResendPassword
     */
    protected function getResendPasswordForm()
    {
        if (null === $this->resendPasswordForm) {
            $this->resendPasswordForm = new ResendPasswordForm();
            $this->resendPasswordForm->setAttribute('data-ajax', 'false');
        }

        return $this->resendPasswordForm;
    }

    /**
     * @return \Application\Form\Validator\ResendPassword
     */
    public function getResendPasswordValidator()
    {
        if (null === $this->resendPassValidator) {
            $this->resendPassValidator = new ResendPasswordValicator();
        }

        return $this->resendPassValidator;
    }

    /**
     * @return array
     */
    public function indexAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            return $this->redirect()->toRoute('moneyzaurus');
        }

        $form = $this->getResendPasswordForm();

        /** @var \Zend\Http\PhpEnvironment\Request $request */
        $request = $this->getRequest();

        if ($request->isPost()) {

            $form->setInputFilter($this->getResendPasswordValidator()->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {

                $newPassword = $this->getNewPassword();
                $response = $this->resendPassword($newPassword);
                if ($response) {
                    return $response;
                }

            } else {
                $this->flashmessenger()->addMessage('Wrong data');
            }
        }

        return array(
            'form' => $this->getResendPasswordForm()
        );
    }

    /**
     * @param string $newPassword
     * @return null|\Zend\Http\PhpEnvironment\Response
     */
    protected function resendPassword($newPassword)
    {
        try {
            $request = $this->getRequest();
            $user = $this->getUser()->setEmail($request->getPost('email'))->load();
            $user->setPassword($newPassword);
        } catch (\Db\Exception\ModelNotFoundException $exc) {
            return $this->redirect()->toRoute('resend-password', array('action' => 'fail'));
        }

        /** @var \Zend\Mail\Transport\Sendmail $transport */
        $transport = $this->getServiceLocator()->get('MailTransport');
        try {
            $transport->send($this->getMailMessage($user));
            $passwordExpression = new Expression(
                AbstractActionController::CREDENTIAL_TREATMENT,
                $user->getPassword()
            );
            $user->setPassword($passwordExpression)->save();
        } catch (\Exception $exc) {
            return $this->redirect()->toRoute('resend-password', array('action' => 'fail'));
        }

        return $this->redirect()->toRoute('resend-password', array('action' => 'success'));
    }

    /**
     * @return Message
     */
    private function getMailMessage($user)
    {
        $message = array();
        $message[] ='Hi! New password is ' . $user->getPassword() . '';

        $htmlPart = new \Zend\Mime\Part(implode('', $message));
        $htmlPart->type = 'text/html';

        $textPart = new \Zend\Mime\Part(implode('\r\n', $message));
        $textPart->type = 'text/plain';

        $body = new \Zend\Mime\Message();
        $body->setParts(array($htmlPart, $textPart));


        $mail = new Message();
        $mail->addTo($user->getEmail());
        $mail->setEncoding('UTF-8');
        $mail->setSubject('moneyzaurus.com email reset');
        $mail->setFrom('service@moneyzaurus.com');
        $mail->setBody($body);

        return $mail;
    }

    /**
     * @return string
     */
    private function getNewPassword()
    {
        return uniqid();
    }

    public function successAction()
    {

    }

    public function failAction()
    {

    }
}
