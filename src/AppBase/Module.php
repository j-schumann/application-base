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

/**
 * Module bootstrapping.
 */
class Module implements
    BootstrapListenerInterface,
    ConfigProviderInterface,
    ServiceProviderInterface
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
     * Attach some listeners to the shared eventmanager.
     *
     * @param EventInterface $e
     */
    public function onBootstrap(EventInterface $e)
    {
        /* @var $e \Zend\Mvc\MvcEvent */
        $application = $e->getApplication();
        $sharedEvents = $application->getEventManager()->getSharedManager();

        // Listen to the CRON events, they are rare, don't instantiate any objects yet
        $sharedEvents->attach('AppBase\Controller\CronController', 'cronDaily', function($e) {
	    return \Vrok\SlmQueue\Job\PurgeValidations::onCronDaily($e);
        });
        $sharedEvents->attach('AppBase\Controller\CronController', 'cronDaily', function($e) {
            return \Vrok\SlmQueue\Job\CheckTodos::onCronDaily($e);
        });
    }
}
