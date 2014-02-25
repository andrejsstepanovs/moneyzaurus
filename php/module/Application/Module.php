<?php

namespace Application;

use Zend\Db\TableGateway\Feature;
use Zend\Mvc\MvcEvent;
use Zend\Authentication\Adapter\DbTable\CredentialTreatmentAdapter;
use Zend\ServiceManager\ServiceManager;
use Zend\Mail\Transport\Smtp as MailTransport;
use Zend\Mail\Transport\SmtpOptions;
use Zend\Session\Container as SessionContainer;
use Zend\Authentication\Storage\Session as AuthenticationSessionStorage;
use Zend\Session\SessionManager;
use Zend\Session\Config\StandardConfig as SessionConfig;
use Application\Exception\AclResourceNotAllowedException;
use Zend\Cache\StorageFactory as CacheStorageFactory;
use Application\Cache\Manager as CacheManager;

/**
 * Class Module
 *
 * @package Application
 */
class Module
{
    /** password treatment */
    const CREDENTIAL_TREATMENT = 'MD5(?)';

    /** @var array */
    protected $config;

    /**
     * @param \Zend\Mvc\MvcEvent $mvcEvent
     */
    public function onBootstrap(MvcEvent $mvcEvent)
    {
        /** @var $application \Zend\Mvc\Application */
        $application = $mvcEvent->getApplication();

        /** @var $serviceManager ServiceManager */
        $serviceManager = $application->getServiceManager();

        Feature\GlobalAdapterFeature::setStaticAdapter(
            $serviceManager->get('Zend\Db\Adapter\Adapter')
        );

        /** @var \Zend\Session\SessionManager $sessionManager */
        $sessionManager = $serviceManager->get('SessionManager');
        $sessionManager->start();
        SessionContainer::setDefaultManager($sessionManager);

        /** @var $acl \Application\Acl\Acl */
        $acl = $serviceManager->get('Application\Acl\Acl');

        /** @var \Zend\EventManager\EventManager $eventManager */
        $eventManager = $application->getEventManager();
        $eventManager->attach(
            MvcEvent::EVENT_ROUTE,
            array($acl, 'checkAcl'),
            -100
        );

        $eventManager->attach(
            MvcEvent::EVENT_DISPATCH_ERROR,
            function (MvcEvent $mvcEvent) use ($serviceManager) {
                $exception = $mvcEvent->getParam('exception');
                if ($exception) {
                    $serviceManager->get('Zend\Log\Logger')->crit($mvcEvent->getParam('exception'));
                    if ($exception instanceof AclResourceNotAllowedException) {
                        /** @var \Zend\Http\PhpEnvironment\Response $response */
                        $url = $mvcEvent->getRouter()->assemble(array(), array('name' => 'moneyzaurus'));
                        $response = $mvcEvent->getResponse();
                        $response->getHeaders()->addHeaderLine('Location', $url);
                        $response->setStatusCode(302)->sendHeaders();
                    }
                }
            },
            -999
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
     * @param string $key
     *
     * @return array
     */
    public function getConfig($key = null)
    {
        if (null === $this->config) {
            $this->config = array_merge(
                include __DIR__ . '/config/cache.config.php',
                include __DIR__ . '/config/session.config.php',
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
                    $credentialTreatment = Module::CREDENTIAL_TREATMENT;
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
                    $authService->setStorage($serviceManager->get('AuthStorage'));

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
                'AuthStorage' => function (ServiceManager $serviceManager) {
                    $session = new AuthenticationSessionStorage(
                        null,
                        null,
                        $serviceManager->get('SessionManager')
                    );

                    return $session;
                },
                'SessionManager' => function (ServiceManager $serviceManager) {
                    $config = $serviceManager->get('Configuration');
                    $sessionData = $config['session'];

                    $sessionConfig = new SessionConfig();
                    $sessionConfig->setOptions($sessionData['config']);

                    $sessionManager = new SessionManager($sessionConfig);
                    $sessionManager->setName($sessionData['config']['name']);
                    $sessionManager->rememberMe();

                    /** @var \Zend\Session\ValidatorChain $validationChain */
                    $validationChain = $sessionManager->getValidatorChain();

                    foreach ($sessionData['validator'] as $i => $validator) {
                        $validator = new $validator();
                        $validationChain->attach('session.validate', array($validator, 'isValid'));
                    }

                    return $sessionManager;
                },
                'CacheStorageAdapter' => function (ServiceManager $serviceManager) {
                    $config = $serviceManager->get('Config');
                    $dataCache = $config['data_cache'];

                    /** @var \Zend\Cache\Storage\Adapter\Filesystem $cache */
                    $cache = CacheStorageFactory::factory($dataCache);

                    return $cache;
                },
                'CacheManager' => function (ServiceManager $serviceManager) {

                    /** @var \Zend\Cache\Storage\Adapter\Filesystem $cacheStorage */
                    $cacheStorage = $serviceManager->get('CacheStorageAdapter');

                    $cacheManager = new CacheManager();
                    $cacheManager->setCacheStorage($cacheStorage)->setLifetime(600);

                    return $cacheManager;
                },
            )
        );
    }
}
