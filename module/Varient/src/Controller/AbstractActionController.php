<?php

namespace Varient\Controller;

use Zend\Mvc\Controller\AbstractActionController AS ZendAbstractActionController;


class AbstractActionController extends ZendAbstractActionController
{
    /** @var \Zend\Authentication\AuthenticationService */
    protected $authservice;

    /** @var integer */
    protected $userId;


    /**
     * Execute the request
     *
     * @param  MvcEvent $e
     * @return mixed
     * @throws Exception\DomainException
     */
    public function onDispatch(\Zend\Mvc\MvcEvent $e)
    {
        $actionResponse = parent::onDispatch($e);

        $messages = $this->flashmessenger()->getMessages();
        if (!empty($messages)) {
            $this->showMessages();
        }

        return $actionResponse;
    }

    /**
     * @param string $message
     * @return \Varient\Controller\AbstractActionController
     */
    public function showMessage($message)
    {
        return $this->showMessages(array($message));
    }

    /**
     * @param array $messages
     * @return \Varient\Controller\AbstractActionController
     */
    public function showMessages($messages)
    {
        if (!empty($messages)) {
            foreach ($messages AS $message) {
                $inlineScript = $this->getServiceLocator()->get('viewhelpermanager')->get('inlineScript');
                $inlineScript->appendScript('
                    $(document).ready(function() {
                        var message = "'.str_replace('"', "'", $message).'";
                        $.mobile.showPageLoadingMsg("b", message, true);
                        setTimeout(function() {
                            $.mobile.hidePageLoadingMsg();
                        }, 1500);
                    });
                ');
            }
        }
        return $this;
    }

    /**
     * @return integer
     */
    protected function getUserId()
    {
        if (null === $this->userId) {
            $auth = $this->getAuthService();
            if ($auth->hasIdentity()) {
                $identity = $auth->getIdentity();
                $this->userId = $identity['user_id'];
            }

            if (empty($this->userId)) {
                throw new Exception\UserNotFoundException(
                    'User not found'
                );
            }
        }

        return $this->userId;
    }


    /**
     * @return \Zend\Authentication\AuthenticationService
     */
    public function getAuthService()
    {
        if (null === $this->authservice) {
            $this->authservice = $this->getServiceLocator()
                                      ->get('AuthService');
        }

        return $this->authservice;
    }
}
