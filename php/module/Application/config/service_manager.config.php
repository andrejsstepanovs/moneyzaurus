<?php

return array(
    'service_manager' => array(
        'factories' => array(
            'Navigation'  => 'Zend\Navigation\Service\DefaultNavigationFactory',
            'Translator' => 'Zend\I18n\Translator\TranslatorServiceFactory'
        ),
    ),
);
