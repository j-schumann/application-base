<?php
/**
 * @copyright   (c) 2014, Vrok
 * @license     http://customlicense CustomLicense
 * @author      Jakob Schumann <schumann@vrok.de>
 */

namespace AppBase;

use Zend\EventManager\EventInterface;
use Zend\ModuleManager\Feature\BootstrapListenerInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
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
        return include __DIR__ . '/../../config/module.config.php';
    }

    /**
     * Return additional serviceManager config with closures that should not be in the
     * config files to allow caching of the complete configuration.
     *
     * @return array
     */
    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'doctrine.cache.zend_storage' => function($sm) {
                    return new \DoctrineModule\Cache\ZendStorageCache(
                            $sm->get('defaultCache'));
                },

                'ZendLog' => function ($sm) {
                    $filename = 'log_' . date('F') . '.txt';
                    $log = new \Zend\Log\Logger();
                    $writer = new \Zend\Log\Writer\Stream('./data/logs/' . $filename);
                    $log->addWriter($writer);

                    return $log;
                },

                'Zend\Mail\Transport' => function($sm) {
                    $spec = array();
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
            ),
        );
    }

    /**
     * Retrieve additional view helpers using factories that are not set in the config.
     *
     * @return array
     */
    public function getViewHelperConfig()
    {
        return array(
            'factories' => array(
                'navigation' => function($sm) {
                    $auth = $sm->getServiceLocator()->get('BjyAuthorize\Service\Authorize');
                    $role = $auth->getIdentity();

                    $navigation = $sm->get('Zend\View\Helper\Navigation');
                    $navigation->setAcl($auth->getAcl())->setRole($role);

                    return $navigation;
                },
            ),
        );
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
        $config = $application->getServiceManager()->get('config');

        $this->initSession($config);

        // Allow caching of assertions by not using dependencies via constructor
        // or ServiceLocatorAware but retrieving them from the static helper.
        $sm = $application->getServiceManager();
        \Vrok\Acl\Assertion\AssertionHelper::setServiceLocator($sm);

        // @todo konfigurierbar machen
        // ist ausserdem auch nur die default-timezone für anzeigen
        date_default_timezone_set('Europe/Berlin');

        // @todo ersetzen mit locale detection aktueller User + Translator setzen
        \Locale::setDefault('de_DE');

        $sharedEvents = $eventManager->getSharedManager();

        // Listen to the CRON events, they are rare, don't instantiate any objects yet
        $sharedEvents->attach('AppBase\Controller\CronController', 'cronDaily', function($e) {
	    return \Vrok\SlmQueue\Job\PurgeValidations::onCronDaily($e);
        });
        $sharedEvents->attach('AppBase\Controller\CronController', 'cronDaily', function($e) {
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

    /**
     * @todo necessary?
     *
     * @param ApplicationInterface $application
     * @return type
     */
    protected function prepareTheme(ApplicationInterface $application)
    {
        $sm = $application->getServiceManager();

        $config = $sm->get('Config');
        if (!empty($config['theme'])) {
            return;
        }

        if (isset($config['theme']['template_map'])) {
            $map = $sm->get('ViewTemplateMapResolver');
            $map->merge($config['theme']['template_map']);
        }

        if (isset($config['theme']['template_path_stack'])) {
            $stack = $sm->get('ViewTemplatePathStack');
            $stack->addPaths($config['theme']['template_path_stack']);
        }
    }
}
