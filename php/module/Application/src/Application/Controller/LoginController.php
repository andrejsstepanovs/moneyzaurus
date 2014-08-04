<?php
namespace Application\Controller;

use Application\Form\Form\Login as LoginForm;
use Application\Form\Validator\Login as LoginValidator;
use Db\Exception\ModelNotFoundException;
use Zend\Json\Json;

/**
 * Class LoginController
 *
 * @package Application\Controller
 */
class LoginController extends AbstractActionController
{
    /** @var \Application\Form\Form\Login */
    protected $loginForm;

    /** @var \Application\Form\Validator\Login */
    protected $loginValidator;

    /** @var \Application\Form\Validator\User */
    protected $userValidator;

    /**
     * @return \Application\Form\Form\Login
     */
    protected function getLoginForm()
    {
        if (null === $this->loginForm) {
            $this->loginForm = new LoginForm();
            //$this->loginForm->setAttribute('data-ajax', 'false');
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
            return $this->redirect()->toRoute('transaction');
        }

        return array(
            'form' => $this->getLoginForm()
        );
    }

    public function authenticateAction()
    {
        $data = array(
            'success' => 1,
            'url'     => null
        );

        $form = $this->getLoginForm();

        /** @var \Zend\Http\PhpEnvironment\Request $request */
        $request = $this->getRequest();

        if ($request->isPost()) {

            $form->setInputFilter($this->getLoginValidator()->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {

                try {
                    $user = $this->authenticate();
                    $data['message'] = 'Welcome ' . $user->getEmail() . '!';
                    $data['url'] = $this->url()->fromRoute('transaction') . '/index';
                } catch (\InvalidArgumentException $exc) {
                    $data['message'] = $exc->getMessage();
                }

            } else {
                $data['message'] = 'Invalid username or password.';
            }
        }

        $response = $this->getResponse();
        $response->setContent(Json::encode($data));

        return $response;
    }

    /**
     * @return \Application\Db\User
     * @throws \InvalidArgumentException
     */
    protected function authenticate()
    {
        $request = $this->getRequest();

        /** @var \Zend\Authentication\AuthenticationService $authService */
        $authService = $this->getAuthService();

        /** @var \Zend\Authentication\Adapter\DbTable\CredentialTreatmentAdapter $authAdapter */
        $authAdapter = $authService->getAdapter();

        $authAdapter->setIdentity($request->getPost('email'))
                    ->setCredential($request->getPost('password'));

        /** @var \Zend\Authentication\Result $result */
        $result = $authAdapter->authenticate();

        if (!$result->isValid()) {
            throw new \InvalidArgumentException(implode(' | ', $result->getMessages()));
        }

        try {
            /** @var \Application\Db\User $user */
            $user = $this->getAbstractHelper()->getModel('user');
            $userData = $user->setEmail($result->getIdentity())
                 ->load()
                 ->unsPassword()
                 ->toArray();

        } catch (ModelNotFoundException $exc) {
            throw new \InvalidArgumentException('Invalid username or password.');
        }

        $authService->getStorage()->write($userData);

        return $user;
    }
}
