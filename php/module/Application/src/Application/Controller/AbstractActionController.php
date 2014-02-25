<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController as ZendAbstractActionController;
use Application\Helper\AbstractHelper;
use Application\Exception\UserNotFoundException;
use Zend\Mvc\Exception;
use Zend\Mvc\MvcEvent;
use Zend\Escaper\Escaper;
use Zend\Http\PhpEnvironment\Request;

/**
 * Class AbstractActionController
 *
 * @package Application\Controller
 * @method  Request getRequest()
 */
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

    /** @var Escaper */
    protected $escaper;

    /** @var array */
    protected $viewHelperPlugin = array();

    /** @var array */
    protected $paramCache = array();

    /**
     * Execute the request
     *
     * @param  MvcEvent                  $mvcEvent
     * @return mixed
     * @throws Exception\DomainException
     */
    public function onDispatch(MvcEvent $mvcEvent)
    {
        $actionResponse = parent::onDispatch($mvcEvent);

        $messages = $this->flashmessenger()->getMessages();
        if (!empty($messages)) {
            $this->showMessages($messages);
        }

        return $actionResponse;
    }

    /**
     * @param  string                   $message
     * @return AbstractActionController
     */
    public function showMessage($message)
    {
        return $this->showMessages(array($message));
    }

    /**
     * @param  array                    $messages
     * @return AbstractActionController
     */
    public function showMessages($messages)
    {
        if (!empty($messages)) {
            foreach ($messages as $message) {
                $this->getViewHelperPlugin('inlineScript')->appendScript(
                    '$(document).ready(function () {
                        $(document).on("pageshow", function () {
                            var message = "'.str_replace('"', "'", $message).'";
                            site.popupMessage(message);
                        });
                    });'
                );
            }
        }

        return $this;
    }

    /**
     * @return AbstractHelper
     */
    protected function getAbstractHelper()
    {
        if (null === $this->helper) {
            $this->helper = new AbstractHelper();
            $this->helper->setServiceLocator($this->getServiceLocator());

            if ($this->getAuthService()->hasIdentity()) {
                $this->helper->setUserId($this->getUserId());
            }
        }

        return $this->helper;
    }

    /**
     * @return integer
     *
     * @throws UserNotFoundException
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
                throw new UserNotFoundException(
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
        $cacheManager = $this->getAbstractHelper()->getCacheManager();

        $cacheNamespaces = array('currency');
        $cacheKey = 'currency_value_options';
        $valueOptions = $cacheManager->data($cacheNamespaces, $cacheKey);
        if (!$valueOptions) {
            $currency = $this->getAbstractHelper()->getTable('currency');
            $currencies = $currency->getTable()->fetchAll();

            $valueOptions = array();
            /** @var \Application\Db\Currency $currency */
            foreach ($currencies as $currency) {
                $valueOptions[$currency->getId()] = $currency->getName();
            }

            $cacheManager->data($cacheNamespaces, $cacheKey, $valueOptions);
        }

        return $valueOptions;
    }

    /**
     * @return string
     */
    public function getDefaultUserCurrency()
    {
        return 'EUR';
    }

    /**
     * @return \Zend\Authentication\AuthenticationService
     */
    public function getAuthService()
    {
        if (null === $this->authService) {
            /** @var \Zend\Authentication\AuthenticationService $authService */
            $authService = $this->getServiceLocator()->get('AuthService');

            $this->authService = $authService;
        }

        return $this->authService;
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
     *
     * @return \Zend\View\Helper\InlineScript
     */
    protected function getViewHelperPlugin($pluginName)
    {
        $pluginName = strtolower($pluginName);
        if (!array_key_exists($pluginName, $this->viewHelperPlugin)) {
            $this->viewHelperPlugin[$pluginName] = $this->getViewHelper()->get($pluginName);
        }

        return $this->viewHelperPlugin[$pluginName];
    }

    /**
     * @return Escaper
     */
    protected function getEscaper()
    {
        if (null === $this->escaper) {
            $this->escaper = new Escaper();
        }

        return $this->escaper;
    }

    /**
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    protected function getParam($key, $default = null)
    {
        if (!array_key_exists($key, $this->paramCache)) {
            /** @var \Zend\Http\PhpEnvironment\Request $request */
            $request = $this->getRequest();
            $this->paramCache[$key] = $request->getQuery()->get($key, $default);
        }

        return $this->paramCache[$key];
    }

    /**
     * @return array
     */
    protected function getParams()
    {
        /** @var \Zend\Http\PhpEnvironment\Request $request */
        $request = $this->getRequest();
        $this->paramCache = $request->getQuery()->toArray();

        return $this->paramCache;
    }

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return $this
     */
    protected function setParam($key, $value)
    {
        $this->paramCache[$key] = $value;

        return $this;
    }
}
