<?php
namespace Application\Controller;

use Application\Db\User;
use Application\Form\Form\Login as LoginForm;
use Application\Form\Form\Register as RegisterForm;
use Application\Form\Form\ResendPassword as ResendPasswordForm;
use Application\Form\Validator\ResendPassword as ResendPasswordValicator;
use Application\Helper\ResendPassword\Helper as ResendPasswordHelper;
use Zend\Db\Sql\Expression as Expression;
use Db\Exception\ModelNotFoundException;
use Application\Module;

/**
 * Class ResendPasswordController
 *
 * @package Application\Controller
 *
 * @method ResendPasswordHelper getAbstractHelper()
 */
class ResendPasswordController extends AbstractActionController
{
    /** @var \Application\Form\Form\Login */
    protected $loginForm;

    /** @var \Application\Form\Form\Register */
    protected $registerForm;

    /** @var \Application\Form\Form\ResendPassword */
    protected $resendPasswordForm;

    /** @var \Application\Form\Validator\ResendPassword */
    protected $resendPassValidator;

    /** @var ResendPasswordHelper */
    protected $resendPasswordHelper;

    /** @var \Db\ActiveRecord */
    protected $user;

    /**
     * @return \Application\Form\Form\Login
     */
    protected function getLoginForm()
    {
        if (null === $this->loginForm) {
            $this->loginForm = new LoginForm();
            $this->loginForm->setAttribute('data-ajax', 'false');
        }

        return $this->loginForm;
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
     * @return \Application\Db\User
     */
    public function getUser()
    {
        if (null === $this->user) {
            $this->user = new User();
        }

        return $this->user;
    }

    /**
     * @return ResendPasswordHelper
     */
    protected function getResendPasswordHelper()
    {
        if (null === $this->resendPasswordHelper) {
            $this->resendPasswordHelper = new ResendPasswordHelper;
        }

        return $this->resendPasswordHelper;
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

                $newPassword = $this->getResendPasswordHelper()->getNewPassword();
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

    public function successAction()
    {
        return array(
            'loginForm' => $this->getLoginForm()
        );
    }

    public function failAction()
    {
        return array(
            'registerForm' => $this->getRegisterForm()
        );
    }

    /**
     * @param  string                                  $newPassword
     * @return null|\Zend\Http\PhpEnvironment\Response
     */
    private function resendPassword($newPassword)
    {
        try {
            $request = $this->getRequest();
            /** @var User $user */
            $user = $this->getUser()->setEmail($request->getPost('email'))->load();
            $user->setPassword($newPassword);
        } catch (ModelNotFoundException $exc) {
            return $this->redirect()->toRoute('resend-password', array('action' => 'fail'));
        }

        /** @var \Zend\Mail\Transport\Sendmail $transport */
        /** @var \Zend\I18n\Translator\Translator $translator */
        $transport  = $this->getServiceLocator()->get('MailTransport');
        $translator = $this->getServiceLocator()->get('Translator');
        $config     = $this->getServiceLocator()->get('Config');
        try {
            /** @var \Zend\View\Helper\Partial $partial */
            $partial  = $this->getViewHelperPlugin('partial');
            $htmlBody = $partial->__invoke('application/resend-password/email', array('user' => $user));

            $subject = $translator->translate('New moneyzaurus.com password');
            $fromEmail = $config['mail']['email'];

            $message = $this
                ->getResendPasswordHelper()
                ->getMailMessage($user->getEmail(), $htmlBody, $subject, $fromEmail);

            $transport->send($message);

            $passwordExpression = new Expression(
                Module::CREDENTIAL_TREATMENT,
                $user->getPassword()
            );
            $user->setPassword($passwordExpression)->save();
        } catch (\Exception $exc) {
            return $this->redirect()->toRoute('resend-password', array('action' => 'fail'));
        }

        return $this->redirect()->toRoute('resend-password', array('action' => 'success'));
    }
}
