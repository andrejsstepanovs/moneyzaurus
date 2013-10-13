<?php

namespace Application\Acl;

use Zend\Mvc\MvcEvent;
use Zend\ServiceManager\ServiceManager;
use Zend\Permissions\Acl\Acl as ZendAcl;
use Zend\Permissions\Acl\Role\GenericRole as ZendRole;
use Zend\Permissions\Acl\Resource\GenericResource as ZendResource;
use Application\Exception;
use Zend\Console\Request as ConsoleRequest;


class Acl
{
    /** @var \Zend\ServiceManager\ServiceManager */
    protected $serviceManager;

    /** @var \Zend\Permissions\Acl\Acl */
    protected $acl;

    /** @var \Zend\Mvc\MvcEvent */
    protected $mvcEvent;

    /** @var array */
    protected $config;


    /**
     * @param null|\Zend\ServiceManager\ServiceManager $serviceManager
     */
    public function __construct($serviceManager = null)
    {
        if ($serviceManager) {
            $this->setServiceManager($serviceManager);
        }
    }

    /**
     * @param \Zend\Mvc\MvcEvent $eventManager
     * @return $this
     */
    public function setMvcEvent(MvcEvent $eventManager)
    {
        $this->mvcEvent = $eventManager;
        return $this;
    }

    /**
     * @return \Zend\Mvc\MvcEvent
     */
    public function getMvcEvent()
    {
        if (null === $this->mvcEvent) {
            /** @var $mvcEvent \Zend\Mvc\MvcEvent */
            $mvcEvent = $this->getServiceManager()->get('application')->getMvcEvent();
            $this->setMvcEvent($mvcEvent);
        }
        return $this->mvcEvent;
    }

    /**
     * @return \Zend\ServiceManager\ServiceManager
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
     * @param \Zend\ServiceManager\ServiceManager $serviceManager
     * @return $this
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
        return $this;
    }

    /**
     * @param array $config
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
     * @param \Zend\Permissions\Acl\Acl $acl
     * @return $this
     */
    public function setAcl(ZendAcl $acl)
    {
        $this->acl = $acl;
        return $this;
    }

    /**
     * @return \Zend\Permissions\Acl\Acl
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
     * @throws Exception\AclResourceNotAllowedException
     */
    public function checkAcl()
    {
        $mvcEvent = $this->getMvcEvent();

        $request = $mvcEvent->getRequest();
        if ($request instanceof ConsoleRequest) {
            return true;
        }

        $routeParams = $mvcEvent->getRouteMatch()->getParams();


        $controller = $routeParams['controller'];
        //$action = $routeParams['action'];

        $auth = $this->getServiceManager()->get('AuthService');

        $userRole = 'guest';
        if ($auth->hasIdentity()) {
            $identity = $auth->getIdentity();
            if (!array_key_exists('role', $identity)) {
                // something is wrong.
                $auth->clearIdentity();
            } else {
                $userRole = $identity['role'];
            }
        }

        $allowed = $this->getAcl()->isAllowed($userRole, $controller);
        if (!$allowed) {
            throw new Exception\AclResourceNotAllowedException(
                'Resource "' . $controller . '" is not allowed '
                . 'for role "'.$userRole.'"'
            );
        }
    }

}