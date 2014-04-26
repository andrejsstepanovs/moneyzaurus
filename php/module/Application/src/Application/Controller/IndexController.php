<?php
namespace Application\Controller;

use Application\Form\Form\Login as LoginForm;
use Application\Form\Form\Register as RegisterForm;
use Zend\Json\Json;

/**
 * Class IndexController
 *
 * @package Application\Controller
 */
class IndexController extends AbstractActionController
{
    /** @var \Application\Form\Form\Login */
    protected $loginForm;

    /** @var \Application\Form\Form\Register */
    protected $registerForm;

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

    public function authenticatedAction()
    {
        $responseData = array(
            'success'       => true,
            'url'           => null,
            'authenticated' => $this->getAuthService()->hasIdentity()
        );
        if (!$responseData['authenticated']) {
            $responseData['url'] = $this->url()->fromRoute('moneyzaurus');
        }

        $response = $this->getResponse();
        $response->setContent(Json::encode($responseData));

        return $response;
    }

    public function indexAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            return $this->redirect()->toRoute('transaction');
        }

        $this->getViewHelperPlugin('inlineScript')->appendScript(
            '$(document).ready(function () {
                $("#login-username").focus();
            });'
        );

        return array(
            'loginForm'    => $this->getLoginForm(),
            'registerForm' => $this->getRegisterForm()
        );
    }

}