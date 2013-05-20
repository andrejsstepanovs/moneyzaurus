<?php

namespace Application\EventManager;

use Zend\Mvc\MvcEvent;
use Zend\Permissions\Acl\Acl as ZendAcl;
use Zend\Permissions\Acl\Role\GenericRole as ZendRole;
use Zend\Permissions\Acl\Resource\GenericResource as ZendResource;


class Acl //implements ServiceManagerAwareInterface//, EventManagerAwareInterface
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
     * @param \Zend\Mvc\MvcEvent $eventManager
     * @return \Application\EventManager\Acl
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
     * @param array $config
     * @return \Application\EventManager\Acl
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
        return $this->config;
    }

    /**
     * @param \Zend\Permissions\Acl\Acl $acl
     * @return \Application\EventManager\Acl
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


    public function checkAcl()
    {
        $acl = $this->getAcl();

        $routeParams = $this->getMvcEvent()->getRouteMatch()->getParams();
        $controller = $routeParams['controller'];
        //$action = $routeParams['action'];

        $userId = 1;

        $user = new \Varient\Database\ActiveRecord\ActiveRecord('user');
        try {
            $role = $user->setId($userId)->load()->getRole();
        } catch (Exception $exc) {
            $role = 'guest';
        }

//        \DEBUG::dump($controller);

        $allowed = $acl->isAllowed($role, $controller);
        if (!$allowed) {

        }

//        $this->getMvcEvent()->
    }

}