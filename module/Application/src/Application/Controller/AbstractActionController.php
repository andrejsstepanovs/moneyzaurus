<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController AS ZendAbstractActionController;
use Db\Db\ActiveRecord;
use Application\Helper\AbstractHelper;


class AbstractActionController extends ZendAbstractActionController
{
    /** @var \Zend\Authentication\AuthenticationService */
    protected $authService;

    /** @var integer */
    protected $userId;

    /** @var \Application\Helper\AbstractHelper */
    protected $helper;

    /** @var array */
    protected $activeRecords;

    /** @var \Zend\View\HelperPluginManager */
    protected $viewHelper;

    /** @var array */
    protected $viewHelperPlugin = array();


    /**
     * Execute the request
     *
     * @param  MvcEvent $e
     * @return mixed
     * @throws Exception\DomainException
     */
    public function onDispatch(\Zend\Mvc\MvcEvent $e)
    {
        $this->init();

        $actionResponse = parent::onDispatch($e);

        $messages = $this->flashmessenger()->getMessages();
        if (!empty($messages)) {
            $this->showMessages($messages);
        }

        return $actionResponse;
    }

    protected function init()
    {
        return;
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
                $this->getViewHelperPlugin('inlineScript')->appendScript('
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
     * @param AbstractHelper $helper
     *
     * @return $this
     */
    protected function setHelper(AbstractHelper $helper)
    {
        $this->helper = $helper;

        return $this;
    }

    /**
     * @return AbstractHelper|int
     */
    protected function getHelper()
    {
        if (null === $this->helper) {
            $this->helper = new AbstractHelper();
        }

        return $this->helper;
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
     * @return array
     */
    public function getCurrencyValueOptions()
    {
        $currency = $this->getTable('currency');
        $currencies = $currency->getTable()->fetchAll();

        $valueOptions = array();
        foreach ($currencies AS $currency) {
            $valueOptions[$currency->getId()] = $currency->getName();
        }

        return $valueOptions;
    }

    /**
     * @return \Zend\Authentication\AuthenticationService
     */
    public function getAuthService()
    {
        if (null === $this->authService) {
            $this->authService = $this->getServiceLocator()
                                      ->get('AuthService');
        }

        return $this->authService;
    }

    /**
     * @param string $table
     * @return \Db\Db\ActiveRecord
     */
    protected function getTable($table = null)
    {
        return $this->getHelper()->getTable($table);
    }

    /**
     * @return \Zend\View\HelperPluginManager
     */
    protected function getViewHelper()
    {
        if (null === $this->viewHelper) {
            $this->viewHelper = $this->getServiceLocator()->get('viewhelpermanager');
        }

        return $this->viewHelper;
    }

    /**
     * @param string $pluginName
     */
    protected function getViewHelperPlugin($plugin)
    {
        $plugin = strtolower($plugin);
        if (!array_key_exists($plugin, $this->viewHelperPlugin)) {
            $this->viewHelperPlugin[$plugin] = $this->getViewHelper()
                                                    ->get($plugin);
        }

        return $this->viewHelperPlugin[$plugin];
    }

}
