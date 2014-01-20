<?php
namespace Application\Controller;

/**
 * Class LogoutController
 *
 * @package Application\Controller
 */
class LogoutController extends AbstractActionController
{
    /**
     * Clear user identity.
     *
     * @return \Zend\Http\PhpEnvironment\Response
     */
    public function indexAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            $this->getAuthService()->clearIdentity();
            $this->flashmessenger()->addMessage('Logged out');
        }

        return $this->redirect()->toRoute('moneyzaurus');
    }
}
