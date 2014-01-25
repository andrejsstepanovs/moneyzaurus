<?php
namespace Application\Controller;

use Application\Form\Form\ResendPassword as ResendPasswordForm;
use Application\Form\Validator\ResendPassword as ResendPasswordValicator;
use Db\ActiveRecord;
use Application\Helper\ResendPassword\Helper;
use Zend\Db\Sql\Expression as Expression;

/**
 * Class ResendPasswordController
 *
 * @package Application\Controller
 *
 * @method \Application\Helper\ResendPassword\Helper getHelper()
 */
class ResendPasswordController extends AbstractActionController
{
    /** @var \Application\Form\Form\ResendPassword */
    protected $resendPasswordForm;

    /** @var \Application\Form\Validator\ResendPassword */
    protected $resendPassValidator;

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
     * @return void
     */
    protected function init()
    {
        $helper = new Helper();
        $this->setHelper($helper);
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

                $newPassword = $this->getHelper()->getNewPassword();
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

    }

    public function failAction()
    {

    }

    /**
     * @param string $newPassword
     * @return null|\Zend\Http\PhpEnvironment\Response
     */
    private function resendPassword($newPassword)
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
            $transport->send($this->getHelper()->getMailMessage($user));
            $password = new Expression(
                AbstractActionController::CREDENTIAL_TREATMENT,
                $user->getPassword()
            );
            $user->setPassword($password)->save();
        } catch (\Exception $exc) {
            return $this->redirect()->toRoute('resend-password', array('action' => 'fail'));
        }

        return $this->redirect()->toRoute('resend-password', array('action' => 'success'));
    }
}