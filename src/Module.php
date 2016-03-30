<?php

/**
 * @copyright   (c) 2014-16, Vrok
 * @license     http://customlicense CustomLicense
 * @author      Jakob Schumann <schumann@vrok.de>
 */

namespace AppBase;

use Zend\EventManager\EventInterface;
use Zend\ModuleManager\Feature\BootstrapListenerInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ControllerProviderInterface;
use Zend\ModuleManager\Feature\FormElementProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;
use Zend\ModuleManager\Feature\ViewHelperProviderInterface;
use Zend\Mvc\ApplicationInterface;
use Zend\Session\Config\SessionConfig;
use Zend\Session\Container;
use Zend\Session\SessionManager;

/**
 * Module bootstrapping.
 */
class Module implements
    BootstrapListenerInterface,
    ConfigProviderInterface,
    ControllerProviderInterface,
    FormElementProviderInterface,
    ServiceProviderInterface,
    ViewHelperProviderInterface
{
    /**
     * Returns the modules default configuration.
     *
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__.'/../config/module.config.php';
    }

    /**
     * Return additional serviceManager config with closures that should not be
     * in the config files to allow caching of the complete configuration.
     *
     * @return array
     */
    public function getControllerConfig()
    {
        return [
            'factories' => [
                'AppBase\Controller\Account' => function ($sm) {
                    return new Controller\AccountController($sm);
                },
                'AppBase\Controller\Admin' => function ($sm) {
                    return new Controller\AdminController($sm);
                },
                'AppBase\Controller\Cron' => function ($sm) {
                    return new Controller\CronController($sm);
                },
                'AppBase\Controller\Group' => function ($sm) {
                    return new Controller\GroupController($sm);
                },
                'AppBase\Controller\SlmQueue' => function ($sm) {
                    return new Controller\SlmQueueController($sm);
                },
                'AppBase\Controller\User' => function ($sm) {
                    return new Controller\UserController($sm);
                },
                'AppBase\Controller\Validation' => function ($sm) {
                    return new Controller\ValidationController($sm);
                },
            ],
        ];
    }

    /**
     * Return additional serviceManager config with closures that should not be in the
     * config files to allow caching of the complete configuration.
     *
     * @return array
     */
    public function getFormElementConfig()
    {
        return [
            'factories' => [
                'AppBase\Form\User\DisplayNameChange' => function ($sm) {
                    $form = new Form\User\DisplayNameChange();
                    $form->setEntityManager($sm->getServiceLocator()->get('Doctrine\ORM\EntityManager'));
                    $form->setTranslator($sm->getServiceLocator()->get('MvcTranslator'));
                    return $form;
                },
                'AppBase\Form\User\Group' => function ($sm) {
                    $form = new Form\User\Group();
                    $form->setEntityManager($sm->getServiceLocator()->get('Doctrine\ORM\EntityManager'));
                    $form->setTranslator($sm->getServiceLocator()->get('MvcTranslator'));
                    return $form;
                },
                'AppBase\Form\User\GroupFieldset' => function ($sm) {
                    $form = new Form\User\GroupFieldset();
                    $form->setEntityManager($sm->getServiceLocator()->get('Doctrine\ORM\EntityManager'));
                    $form->setTranslator($sm->getServiceLocator()->get('MvcTranslator'));
                    return $form;
                },
                'AppBase\Form\User\Login' => function ($sm) {
                    $form = new Form\User\Login();
                    $form->setEntityManager($sm->getServiceLocator()->get('Doctrine\ORM\EntityManager'));
                    $form->setTranslator($sm->getServiceLocator()->get('MvcTranslator'));
                    return $form;
                },
                'AppBase\Form\User\PasswordChange' => function ($sm) {
                    $form = new Form\User\PasswordChange();
                    $form->setEntityManager($sm->getServiceLocator()->get('Doctrine\ORM\EntityManager'));
                    $form->setTranslator($sm->getServiceLocator()->get('MvcTranslator'));
                    $form->setUserManager($sm->getServiceLocator()->get('Vrok\Service\UserManager'));
                    return $form;
                },
                'AppBase\Form\User\PasswordRequest' => function ($sm) {
                    $form = new Form\User\PasswordRequest();
                    $form->setEntityManager($sm->getServiceLocator()->get('Doctrine\ORM\EntityManager'));
                    $form->setTranslator($sm->getServiceLocator()->get('MvcTranslator'));
                    return $form;
                },
                'AppBase\Form\User\PasswordReset' => function ($sm) {
                    $form = new Form\User\PasswordReset();
                    $form->setEntityManager($sm->getServiceLocator()->get('Doctrine\ORM\EntityManager'));
                    $form->setTranslator($sm->getServiceLocator()->get('MvcTranslator'));
                    $form->setUserManager($sm->getServiceLocator()->get('Vrok\Service\UserManager'));
                    return $form;
                },
                'AppBase\Form\User\UserCreate' => function ($sm) {
                    $form = new Form\User\UserCreate();
                    $form->setEntityManager($sm->getServiceLocator()->get('Doctrine\ORM\EntityManager'));
                    $form->setTranslator($sm->getServiceLocator()->get('MvcTranslator'));
                    return $form;
                },
                'AppBase\Form\User\UserEdit' => function ($sm) {
                    $form = new Form\User\UserEdit();
                    $form->setEntityManager($sm->getServiceLocator()->get('Doctrine\ORM\EntityManager'));
                    $form->setTranslator($sm->getServiceLocator()->get('MvcTranslator'));
                    return $form;
                },
                'AppBase\Form\User\UserFieldset' => function ($sm) {
                    $form = new Form\User\UserFieldset();
                    $form->setEntityManager($sm->getServiceLocator()->get('Doctrine\ORM\EntityManager'));
                    $form->setTranslator($sm->getServiceLocator()->get('MvcTranslator'));
                    return $form;
                },
                'AppBase\Form\User\UserFilter' => function ($sm) {
                    $form = new Form\User\UserFilter();
                    $form->setEntityManager($sm->getServiceLocator()->get('Doctrine\ORM\EntityManager'));
                    $form->setTranslator($sm->getServiceLocator()->get('MvcTranslator'));
                    return $form;
                },
                'AppBase\Form\Validation\ConfirmationForm' => function ($sm) {
                    $form = new Form\Validation\ConfirmationForm();
                    $form->setEntityManager($sm->getServiceLocator()->get('Doctrine\ORM\EntityManager'));
                    $form->setTranslator($sm->getServiceLocator()->get('MvcTranslator'));
                    return $form;
                },
                'AppBase\SlmQueue\RecoverForm' => function ($sm) {
                    $form = new SlmQueue\RecoverForm();
                    $form->setEntityManager($sm->getServiceLocator()->get('Doctrine\ORM\EntityManager'));
                    $form->setTranslator($sm->getServiceLocator()->get('MvcTranslator'));
                    return $form;
                },
            ],
        ];
    }

    /**
     * Return additional serviceManager config with closures that should not be in the
     * config files to allow caching of the complete configuration.
     *
     * @return array
     */
    public function getServiceConfig()
    {
        return [
            'factories' => [
                'AppBase\Notification\AdminNotifications' => function ($sm) {
                    $service = new Notification\AdminNotifications();
                    $service->setEmailService($sm->get('Vrok\Service\Email'));
                    $service->setUserManager($sm->get('Vrok\Service\UserManager'));
                    return $service;
                },
                'doctrine.cache.zend_storage' => function ($sm) {
                    return new \DoctrineModule\Cache\ZendStorageCache(
                            $sm->get('defaultCache'));
                },

                'ZendLog' => function ($sm) {
                    $filename = 'log_'.date('F').'.txt';
                    $log = new \Zend\Log\Logger();
                    $writer = new \Zend\Log\Writer\Stream('./data/logs/'.$filename);
                    $log->addWriter($writer);

                    return $log;
                },

                'Zend\Mail\Transport' => function ($sm) {
                    $spec = [];
                    $config = $sm->get('Config');
                    if (!empty($config['email_service']['transport'])) {
                        $spec = $config['email_service']['transport'];
                    }

                    return \Zend\Mail\Transport\Factory::create($spec);
                },

                'Zend\Authentication\AuthenticationService' => function ($sm) {
                    return new \Zend\Authentication\AuthenticationService(
                        // stores the user ID in the session and retrieves the object
                        // from the DB
                        $sm->get('Vrok\Authentication\Storage\Doctrine'),

                        // checks for username or email as identity, checks if the
                        // user is active & validated
                        $sm->get('Vrok\Authentication\Adapter\Doctrine')
                    );
                },
            ],
        ];
    }

    /**
     * Retrieve additional view helpers using factories that are not set in the config.
     *
     * @return array
     */
    public function getViewHelperConfig()
    {
        return [
            'factories' => [
                'navigation' => function ($sm) {
                    $auth = $sm->getServiceLocator()->get('BjyAuthorize\Service\Authorize');
                    $role = $auth->getIdentity();

                    $navigation = $sm->get('Zend\View\Helper\Navigation');
                    $navigation->setAcl($auth->getAcl())->setRole($role);

                    return $navigation;
                },
            ],
        ];
    }

    /**
     * Attach some listeners to the shared eventmanager.
     *
     * @param EventInterface $e
     */
    public function onBootstrap(EventInterface $e)
    {
        $application = $e->getApplication();
        /* @var $application ApplicationInterface */
        $eventManager = $application->getEventManager();
        $config       = $application->getServiceManager()->get('config');

        $this->initSession($config);

        // Allow caching of assertions by not using dependencies via constructor
        // but retrieving them from the static helper.
        $sm = $application->getServiceManager();
        \Vrok\Acl\Assertion\AssertionHelper::setServiceLocator($sm);

        // @todo konfigurierbar machen
        // ist ausserdem auch nur die default-timezone für anzeigen
        date_default_timezone_set('Europe/Berlin');

        // @todo accept-header und locale des eingeloggten users auswerten
        $metaService   = $sm->get('Vrok\Service\Meta');
        $defaultLocale = $metaService->getValue('defaultLocale') ?: 'de_DE';
        \Locale::setDefault($defaultLocale);
        $sm->get('MvcTranslator')->setLocale($defaultLocale);

        $sharedEvents = $eventManager->getSharedManager();

        // Listen to the CRON events, they are rare, don't instantiate any objects yet
        $sharedEvents->attach('AppBase\Controller\CronController', 'cronDaily', function ($e) {
            return \Vrok\SlmQueue\Job\PurgeValidations::onCronDaily($e);
        });
        $sharedEvents->attach('AppBase\Controller\CronController', 'cronDaily', function ($e) {
            return \Vrok\SlmQueue\Job\CheckTodos::onCronDaily($e);
        });
    }

    /**
     * Starts the session with the configuration from the application config.
     *
     * @param array $config
     */
    protected function initSession($config)
    {
        $sessionConfig = new SessionConfig();
        if (isset($config['session'])) {
            $sessionConfig->setOptions($config['session']);
        }
        $sessionManager = new SessionManager($sessionConfig);
        $sessionManager->start();
        Container::setDefaultManager($sessionManager);
    }
}
