<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController AS ZendAbstractActionController;
use Db\Db\ActiveRecord;


class AbstractActionController extends ZendAbstractActionController
{
    /** @var \Zend\Authentication\AuthenticationService */
    protected $authservice;

    /** @var integer */
    protected $userId;

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
        $actionResponse = parent::onDispatch($e);

        $messages = $this->flashmessenger()->getMessages();
        if (!empty($messages)) {
            $this->showMessages($messages);
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
        if (null === $this->authservice) {
            $this->authservice = $this->getServiceLocator()
                                      ->get('AuthService');
        }

        return $this->authservice;
    }


    /**
     * @param string $table
     * @return \Db\Db\ActiveRecord
     */
    protected function getTable($table = null)
    {
        $key = !$table ? 'null' : $table;
        if (!isset($this->activeRecords[$table])) {
            $this->activeRecords[$key] = new ActiveRecord($table);
        }

        return $this->activeRecords[$key];
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
