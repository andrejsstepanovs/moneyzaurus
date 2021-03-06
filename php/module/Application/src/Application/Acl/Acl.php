<?php

namespace Application\Acl;

use Zend\Mvc\MvcEvent;
use Zend\ServiceManager\ServiceManager;
use Zend\Permissions\Acl\Acl as ZendAcl;
use Zend\Permissions\Acl\Role\GenericRole as ZendRole;
use Zend\Permissions\Acl\Resource\GenericResource as ZendResource;
use Application\Exception\AclResourceNotAllowedException;
use Zend\Mvc\Application as ZendApplication;
use Zend\Http\Headers as HttpHeaders;
use Zend\Console\Request as ConsoleRequest;

/**
 * Class Acl
 *
 * @package Application\Acl
 */
class Acl
{
    /** @var ServiceManager */
    protected $serviceManager;

    /** @var ZendAcl */
    protected $acl;

    /** @var MvcEvent */
    protected $mvcEvent;

    /** @var array */
    protected $config;

    /** @var \Zend\EventManager\EventManager */
    protected $eventManager;

    /**
     * @param null|ServiceManager $serviceManager
     */
    public function __construct($serviceManager = null)
    {
        if ($serviceManager) {
            $this->setServiceManager($serviceManager);
        }
    }

    /**
     * @param  MvcEvent $eventManager
     * @return $this
     */
    public function setMvcEvent(MvcEvent $eventManager)
    {
        $this->mvcEvent = $eventManager;

        return $this;
    }

    /**
     * @return MvcEvent
     */
    public function getMvcEvent()
    {
        if (null === $this->mvcEvent) {
            /** @var $mvcEvent MvcEvent */
            $mvcEvent = $this->getServiceManager()->get('application')->getMvcEvent();
            $this->setMvcEvent($mvcEvent);
        }

        return $this->mvcEvent;
    }

    /**
     * @return ServiceManager
     */
    protected function getServiceManager()
    {
        if (null === $this->serviceManager) {
            $this->serviceManager = $this->getMvcEvent()
                                         ->getApplication()
                                         ->getServiceManager();
        }

        return $this->serviceManager;
    }

    /**
     * @param  ServiceManager $serviceManager
     * @return $this
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;

        return $this;
    }

    /**
     * @return \Zend\EventManager\EventManager
     */
    protected function getEventManager()
    {
        if (null === $this->eventManager) {
            $this->eventManager = $this->getServiceManager()->get('EventManager');
        }

        return $this->eventManager;
    }

    /**
     * @param  array $config
     * @return $this
     */
    public function setAclConfig(array $config)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * @return array
     */
    public function getAclConfig()
    {
        if (null === $this->config) {
            $config = $this->getServiceManager()->get('Config');
            if (array_key_exists('acl', $config)) {
                $this->setAclConfig($config['acl']);
            }
        }

        return $this->config;
    }

    /**
     * @param  ZendAcl $acl
     * @return $this
     */
    public function setAcl(ZendAcl $acl)
    {
        $this->acl = $acl;

        return $this;
    }

    /**
     * @return ZendAcl
     */
    public function getAcl()
    {
        if (null === $this->acl) {

            $acl = new ZendAcl();
            $allResources = array();

            foreach ($this->getAclConfig() as $role => $resources) {

                $role = new ZendRole($role);
                $acl->addRole($role);

                $allResources = array_merge($resources, $allResources);

                foreach ($resources as $resource) {
                    $acl->addResource(new ZendResource($resource));
                }

                foreach ($allResources as $resource) {
                    $acl->allow($role, $resource);
                }
            }

            $this->setAcl($acl);
        }

        return $this->acl;
    }

    /**
     * @param MvcEvent|null $mvcEvent
     *
     * @return $this
     */
    public function checkAcl(MvcEvent $mvcEvent = null)
    {
        if ($mvcEvent === null) {
            $mvcEvent = $this->getMvcEvent();
        }

        $request = $mvcEvent->getRequest();
        if ($request instanceof ConsoleRequest) {
            return true;
        }

        $routeMatch = $mvcEvent->getRouteMatch();
        $routeParams = $routeMatch->getParams();

        $controller = $routeParams['controller'];
        //$action = $routeParams['action'];

        /** @var \Zend\Authentication\AuthenticationService $auth */
        $auth = $this->getServiceManager()->get('AuthService');

        $userRole = 'guest';
        if ($auth->hasIdentity()) {
            $identity = $auth->getIdentity();
            if (!$identity || empty($identity['role'])) {
                // something is wrong.
                $auth->clearIdentity();
            } else {
                $userRole = $identity['role'];
            }
        }

        $allowed = $this->getAcl()->isAllowed($userRole, $controller);
        if (!$allowed) {
            $message = 'Controller "' . $routeMatch->getMatchedRouteName() . '" not allowed for "' . $userRole . '".';
            $exception = new AclResourceNotAllowedException($message, 600);

            $mvcEvent
                ->setError(ZendApplication::ERROR_EXCEPTION)
                ->setParam('exception', $exception)
                ->getApplication()
                ->getEventManager()
                ->trigger(MvcEvent::EVENT_DISPATCH_ERROR, $mvcEvent);
        }

        return $this;
    }

    /**
     * @param MvcEvent $mvcEvent
     * @param string   $controllerName
     */
    protected function redirect(MvcEvent $mvcEvent, $controllerName)
    {
        $url = $mvcEvent->getRouter()->assemble(
            array(),
            array('name' => $controllerName)
        );

        /** @var \Zend\Http\PhpEnvironment\Response $response */
        $response = $mvcEvent->getResponse();

        $header = new HttpHeaders();
        $header->addHeaderLine('Location', $url);

        $response->setHeaders($header);
        $response->setStatusCode(302);
        $response->sendHeaders();
    }
}
