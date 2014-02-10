<?php

namespace Application;

use Zend\Db\TableGateway\Feature;
use Zend\Mvc\MvcEvent;
use Zend\Authentication\Adapter\DbTable\CredentialTreatmentAdapter;
use Zend\ServiceManager\ServiceManager;
use Zend\Mail\Transport\Smtp as MailTransport;
use Zend\Mail\Transport\SmtpOptions;

/**
 * Class Module
 *
 * @package Application
 */
class Module
{
    /** @var array */
    protected $config;

    /**
     * @param \Zend\Mvc\MvcEvent $mvcEvent
     */
    public function onBootstrap(MvcEvent $mvcEvent)
    {
        /** @var $application \Zend\Mvc\Application */
        $application = $mvcEvent->getApplication();

        /** @var $serviceManager \Zend\ServiceManager\ServiceManager */
        $serviceManager = $application->getServiceManager();

        Feature\GlobalAdapterFeature::setStaticAdapter(
            $serviceManager->get('Zend\Db\Adapter\Adapter')
        );

        /** @var $acl \Application\Acl\Acl */
        $acl = $serviceManager->get('Application\Acl\Acl');

        $eventManager = $application->getEventManager();
        $eventManager->attach(
            \Zend\Mvc\MvcEvent::EVENT_ROUTE,
            array($acl, 'checkAcl'),
            -100
        );
    }

    /**
     * @return array
     */
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    /**
     * @return array
     */
    public function getConfig($key = null)
    {
        if (null === $this->config) {
            $this->config = array_merge(
                include __DIR__ . '/config/router.config.php',
                include __DIR__ . '/config/navigation.config.php',
                include __DIR__ . '/config/view.config.php',
                include __DIR__ . '/config/translator.config.php',
                include __DIR__ . '/config/controllers.config.php',
                include __DIR__ . '/config/service_manager.config.php',
                include __DIR__ . '/config/di.config.php',
                include __DIR__ . '/config/acl.config.php'
            );
        }

        if (!empty($key)) {
            return $this->config[$key];
        }

        return $this->config;
    }

    /**
     * @return array
     */
    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'AuthService' => function (ServiceManager $serviceManager) {
                    /** @var \Zend\Db\Adapter\Adapter $dbAdapter */
                    $dbAdapter = $serviceManager->get('Zend\Db\Adapter\Adapter');
                    $tableName           = 'user';
                    $identityColumn      = 'email';
                    $credentialColumn    = 'password';
                    $credentialTreatment = \Application\Controller\AbstractActionController::CREDENTIAL_TREATMENT;
                    $dbTableAuthAdapter  = new CredentialTreatmentAdapter(
                        $dbAdapter,
                        $tableName,
                        $identityColumn,
                        $credentialColumn,
                        $credentialTreatment
                    );

                    /** @var \Zend\Authentication\AuthenticationService $authService */
                    $authService = $serviceManager->get('Zend\Authentication\AuthenticationService');
                    $authService->setAdapter($dbTableAuthAdapter);
                    return $authService;
                },
                'Application\Acl\Acl' => function ($serviceManager) {
                    return new Acl\Acl($serviceManager);
                },
                'MailTransport' => function (ServiceManager $serviceManager) {
                    $transport = new MailTransport();
                    $config = $serviceManager->get('Config');
                    $transport->setOptions(new SmtpOptions($config['mail']['transport']['options']));

                    return $transport;
                },
            )
        );
    }
}
