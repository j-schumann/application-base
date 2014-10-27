<?php
/**
 * Application-Base config
 */
return array(
// <editor-fold defaultstate="collapsed" desc="bjyauthorize">
    'bjyauthorize' => array(
        'default_role' => 'guest',
        'identity_provider' => 'BjyAuthorize\Provider\Identity\AuthenticationIdentityProvider',
        'role_providers' => array(
            // basic roles are configured and have no DB entry
            'BjyAuthorize\Provider\Role\Config' => array(
                'user' => array(
                    'children' => array('admin'),
                ),
                'guest' => array(),
            ),
            // all other roles inherit from the base roles and are loaded from the
            // user groups tabel
            'BjyAuthorize\Provider\Role\ObjectRepositoryProvider' => array(
                'role_entity_class' => 'Vrok\Entity\Group',
                'object_manager' => 'Doctrine\ORM\EntityManager',
            ),
        ),
        'resource_providers' => array(
            'BjyAuthorize\Provider\Resource\Config' => array(
                // primitive resources for the navigation where a controller
                // cannot be used as it is allowed for everyone
                'admin' => [],
                'guest' => [],
                'user'  => [],
            ),
        ),
        'rule_providers' => array(
            'BjyAuthorize\Provider\Rule\Config' => array(
                'allow' => array(
                    ['admin', 'admin'],
                    ['guest', 'guest'],
                    ['user', 'user'],
                ),
            ),
        ),

        // Add guards here for other modules that don't use BjyAuthorize
        // themself as they would deny access by default without a guard
        'guards' => array(
            'BjyAuthorize\Guard\Controller' => array(
                // guards for application-base
                array(
                    'controller' => 'AppBase\Controller\Account',
                    'action' => array('login', 'index', 'request-password'),
                    'roles' => array('guest', 'user',),
                ),
                array(
                    'controller' => 'AppBase\Controller\Account',
                    'action' => array('change-password', 'logout'),
                    'roles' => array('user'),
                ),
                array(
                    'controller' => 'AppBase\Controller\Admin',
                    'roles' => array('admin'),
                ),
                array(
                    // console route
                    'controller' => 'AppBase\Controller\Cron',
                    'roles' => array('guest', 'user'),
                ),
                array(
                    'controller' => 'AppBase\Controller\Group',
                    'roles' => array('userAdmin'),
                ),
                array(
                    'controller' => 'AppBase\Controller\SlmQueue',
                    'roles' => array('queueAdmin'),
                ),
                array(
                    // console route
                    'controller' => 'AppBase\Controller\SlmQueue',
                    'action' => 'check-jobs',
                    'roles' => array('guest', 'queueAdmin'),
                ),
                array(
                    'controller' => 'AppBase\Controller\User',
                    'roles' => array('userAdmin',),
                ),
                array(
                    // console route
                    'controller' => 'AppBase\Controller\Validation',
                    'roles' => array('guest', 'user'),
                ),

                // guards for the SlmQueue module
                array(
                    // console route
                    'controller' => 'SlmQueueDoctrine\Controller\DoctrineWorkerController',
                    'roles' => array('guest', 'user'),
                ),

                // guards for the translation module
                array(
                    'controller' => 'TranslationModule\Controller\Index',
                    'roles' => array('translationAdmin'),
                ),
                array(
                    'controller' => 'TranslationModule\Controller\String',
                    'roles' => array('translationAdmin'),
                ),
                array(
                    'controller' => 'TranslationModule\Controller\Module',
                    'roles' => array('translationAdmin'),
                ),
                array(
                    'controller' => 'TranslationModule\Controller\Language',
                    'roles' => array('translationAdmin'),
                ),
                array(
                    'controller' => 'TranslationModule\Controller\Management',
                    'roles' => array('translationAdmin'),
                ),

                // guards for supervisor-control
                array(
                    'controller' => 'SupervisorControl\Controller\Supervisor',
                    'roles' => array('supervisorAdmin'),
                ),
                array(
                    // console route
                    'controller' => 'SupervisorControl\Controller\Console',
                    'roles' => array('guest', 'supervisorAdmin'),
                ),
            ),
        ),
        'unauthorized_strategy' => 'AuthorizeRedirectStrategy',
    ),
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="console">
    'console' => array(
        'router' => array(
            'routes' => array(
                'cron-hourly' => array(
                    'options' => array(
                        'route' => 'cron-hourly',
                        'defaults' => array(
                            'controller' => 'AppBase\Controller\Cron',
                            'action'     => 'cron-hourly',
                        ),
                    ),
                ),
                'cron-daily' => array(
                    'options' => array(
                        'route' => 'cron-daily',
                        'defaults' => array(
                            'controller' => 'AppBase\Controller\Cron',
                            'action'     => 'cron-daily',
                        ),
                    ),
                ),
                'cron-monthly' => array(
                    'options' => array(
                        'route' => 'cron-monthly',
                        'defaults' => array(
                            'controller' => 'AppBase\Controller\Cron',
                            'action'     => 'cron-monthly',
                        ),
                    ),
                ),
                'purge-validations' => array(
                    'options' => array(
                        'route' => 'purge-validations',
                        'defaults' => array(
                            'controller' => 'AppBase\Controller\Validation',
                            'action'     => 'purge',
                        ),
                    ),
                ),
                'check-jobs' => array(
                    'options' => array(
                        'route' => 'check-jobs',
                        'defaults' => array(
                            'controller' => 'AppBase\Controller\SlmQueue',
                            'action'     => 'check-jobs',
                        ),
                    ),
                ),
            ),
        ),
    ),
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="controllers">
    'controllers' => array(
        'invokables' => array(
            'AppBase\Controller\Account' => 'AppBase\Controller\AccountController',
            'AppBase\Controller\Admin' => 'AppBase\Controller\AdminController',
            'AppBase\Controller\Cron' => 'AppBase\Controller\CronController',
            'AppBase\Controller\Group' => 'AppBase\Controller\GroupController',
            'AppBase\Controller\SlmQueue' => 'AppBase\Controller\SlmQueueController',
            'AppBase\Controller\User' => 'AppBase\Controller\UserController',
            'AppBase\Controller\Validation' => 'AppBase\Controller\ValidationController',
        ),
    ),
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="doctrine">
    'doctrine' => array(
        'configuration' => array(
            'orm_default' => array(
                'metadata_cache'  => 'zend_storage',
                'query_cache'     => 'zend_storage',
                'result_cache'    => 'zend_storage',
                'hydration_cache' => 'zend_storage',

                // Generate proxies automatically (turn off for production)
                'generate_proxies' => true,

                // directory where proxies will be stored.
                'proxy_dir' => __DIR__ . '/../../../../data/DoctrineORMModule/Proxy',

                // namespace for generated proxy classes
                'proxy_namespace' => 'DoctrineORMModule\Proxy',

                'types' => array(
                    // this overwrites the default DateTime column type for the whole
                    // application to use UTC times.
                    'datetime' => 'Vrok\Doctrine\DBAL\Types\UTCDateTimeType',
                ),
            ),
        ),
        'driver' => array(
            // load the DoctrineQueue entity from the slm-queue folder so it
            // is automatically created by doctrine orm:schema-tool:update
            'queue_entities' => array(
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'zend_storage',
                'paths' => array(__DIR__ . '/../../../slm/queue-doctrine/data')
            ),

            // include the DoctrineQueue
            'orm_default' => array(
                'drivers' => array(
                    'Application\Entity' => 'queue_entities'
                ),
            ),

            // the libraries don't configure caches themself
            'translation_entities' => array(
                'cache' => 'zend_storage',
            ),
            'vrok_entities' => array(
                'cache' => 'zend_storage',
            ),
        ),
    ),
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="listeners">
    'listeners' => array(
        'AppBase\Notification\AdminNotifications',
    ),
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="navigation">
    /**
     * Bjy-Authorize uses resource names like
     * controller/{ControllerServiceName}:{action} when the guards are defined with
     * one or more actions instead of defining the actions as privileges on the controllers
     * When no action is set we must only use controller/{ControllerServiceName} as
     * there is no resource controller/{ControllerServiceName}:index for them.
     */
    'navigation' => array(
        'default' => array(
            'account' => array(
                'label'    => 'navigation.account',
                'route'    => 'account',
                'order'    => -100,
                'pages'    => array(
                    array(
                        'label'    => 'navigation.account.login',
                        'route'    => 'account/login',
                        'resource' => 'guest',
                    ),
                    array(
                        'label'    => 'navigation.account.index',
                        'route'    => 'account',
                        'resource' => 'user',
                    ),
                    array(
                        'label'    => 'navigation.account.logout',
                        'route'    => 'account/logout',
                        'resource' => 'user',
                    ),
                    array(
                        'label'   => 'navigation.account.changePassword',
                        'route'   => 'account/change-password',
                        'visible' => false,
                    ),
                    array(
                        'label'   => 'navigation.account.requestPassword',
                        'route'   => 'account/request-password',
                        'visible' => false,
                    ),
                ),
            ),
            'administration' => array(
                'label'    => 'navigation.administration', // default label or none is rendered
                'route'    => 'admin', // we need either a route or an URI to avoid fatal error
                'resource' => 'admin',
                'order'    => 1000,
                'pages'    => array(
                    array(
                        'label'    => 'navigation.user',
                        'route'    => 'user',
                        'resource' => 'controller/AppBase\Controller\User',
                        'pages'    => array(
                            array(
                                'label' => 'navigation.user.create',
                                'route' => 'user/create',
                            ),
                            array(
                                'label'   => 'navigation.user.edit',
                                'route'   => 'user/edit',
                                'visible' => false,
                            ),
                            array(
                                'label'   => 'navigation.user.delete',
                                'route'   => 'user/delete',
                                'visible' => false,
                            ),
                        ),
                    ),
                    array(
                        'label'    => 'navigation.user.group',
                        'route'    => 'user/group',
                        'resource' => 'controller/AppBase\Controller\Group',
                        'pages'    => array(
                            array(
                                'label' => 'navigation.user.group.create',
                                'route' => 'user/group/create',
                            ),
                            array(
                                'label'     => 'navigation.user.group.edit',
                                'route'   => 'user/group/edit',
                                'visible' => false,
                            ),
                            array(
                                'label'   => 'navigation.user.group.delete',
                                'route'   => 'user/group/delete',
                                'visible' => false,
                            ),
                        ),
                    ),
                    'server' => array(
                        'label'    => 'navigation.administration.server', // default label or none is rendered
                        'route'    => 'admin', // we need either a route or an URI to avoid fatal error
                        'resource' => 'controller/AppBase\Controller\Admin',
                        'order'    => 1000,
                        'pages'    => array(
                            array(
                                'label'     => 'navigation.slmQueue',
                                'route'     => 'slm-queue',
                                'resource'  => 'controller/AppBase\Controller\SlmQueue',
                                'privilege' => 'index',
                                'order'     => 1000,
                                'pages'     => array(
                                    array(
                                        'label'   => 'navigation.slmQueue.recover',
                                        'route'   => 'slm-queue/recover',
                                        'visible' => false,
                                    ),
                                    array(
                                        'label'   => 'navigation.slmQueue.listBuried',
                                        'route'   => 'slm-queue/list-buried',
                                        'visible' => false,
                                    ),
                                    array(
                                        'label'   => 'navigation.slmQueue.listRunning',
                                        'route'   => 'slm-queue/list-running',
                                        'visible' => false,
                                    ),
                                    array(
                                        'label'   => 'navigation.slmQueue.delete',
                                        'route'   => 'slm-queue/delete',
                                        'visible' => false,
                                    ),
                                    array(
                                        'label'   => 'navigation.slmQueue.release',
                                        'route'   => 'slm-queue/release',
                                        'visible' => false,
                                    ),
                                    array(
                                        'label'   => 'navigation.slmQueue.unbury',
                                        'route'   => 'slm-queue/unbury',
                                        'visible' => false,
                                    ),
                                ),
                            ),
                            array(
                                'label'     => 'navigation.caches',
                                'route'     => 'admin/caches',
                                'resource'  => 'controller/AppBase\Controller\Admin',
                                'privilege' => 'caches',
                                'order'     => 1000,
                                'pages'     => array(
                                    array(
                                        'label'   => 'navigation.flushCache',
                                        'route'   => 'admin/flush-cache',
                                        'visible' => false,
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="router">
    'router' => array(
        'routes' => array(
            'account' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/account/',
                    'defaults' => array(
                        'controller'    => 'AppBase\Controller\Account',
                        'action'        => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes'  => array(
                    'login' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => 'login[/]',
                            'defaults' => array(
                                'action' => 'login',
                            ),
                        ),
                    ),
                    'logout' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => 'logout[/]',
                            'defaults' => array(
                                'action' => 'logout',
                            ),
                        ),
                    ),
                    'change-password' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => 'change-password[/]',
                            'defaults' => array(
                                'action' => 'change-password',
                            ),
                        ),
                    ),
                    'request-password' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => 'request-password[/]',
                            'defaults' => array(
                                'action' => 'request-password',
                            ),
                        ),
                    ),
                ),
            ),
            'admin' => array(
                'type'    => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/admin[/]',
                    'defaults' => array(
                        'controller' => 'AppBase\Controller\Admin',
                        'action'     => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'caches' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => 'caches[/]',
                            'defaults' => array(
                                'action' => 'caches',
                            ),
                        ),
                    ),
                    'flush-cache' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => 'flush-cache/[:name][/]',
                            'constraints' => array(
                                'name' => '[a-zA-Z0-9_-]+',
                            ),
                            'defaults' => array(
                                'action' => 'flush-cache',
                            ),
                        ),
                    ),
                    'phpinfo' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => 'phpinfo[/]',
                            'defaults' => array(
                                'action' => 'phpinfo',
                            ),
                        ),
                    ),
                ),
            ),
            'slm-queue' => array(
                'type' => 'Literal',
                'options' => array(
                    'route'    => '/slm-queue/',
                    'defaults' => array(
                        'controller' => 'AppBase\Controller\SlmQueue',
                        'action'     => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'recover' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'       => 'recover/[:name][/]',
                            'constraints' => array(
                                'name' => '[a-zA-Z0-9_-]+',
                            ),
                            'defaults' => array(
                                'action' => 'recover',
                            ),
                        ),
                    ),
                    'list-buried' => array(
                        'type'     => 'Segment',
                        'options' => array(
                            'route'       => 'list-buried/[:name][/]',
                            'constraints' => array(
                                'name' => '[a-zA-Z0-9_-]+',
                            ),
                            'defaults' => array(
                                'action' => 'list-buried',
                            ),
                        ),
                    ),
                    'list-running' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route'       => 'list-running/[:name][/]',
                            'constraints' => array(
                                'name' => '[a-zA-Z0-9_-]+',
                            ),
                            'defaults' => array(
                                'action' => 'list-running',
                            ),
                        ),
                    ),
                    'delete' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => 'delete/[:name]/[:id][/]',
                            'constraints' => array(
                                'name' => '[a-zA-Z0-9_-]+',
                                'id' => '[0-9]+',
                            ),
                            'defaults' => array(
                                'action' => 'delete',
                            ),
                        ),
                    ),
                    'release' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => 'release/[:name]/[:id][/]',
                            'constraints' => array(
                                'name' => '[a-zA-Z0-9_-]+',
                                'id' => '[0-9]+',
                            ),
                            'defaults' => array(
                                'action' => 'release',
                            ),
                        ),
                    ),
                    'unbury' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => 'unbury/[:name]/[:id][/]',
                            'constraints' => array(
                                'name' => '[a-zA-Z0-9_-]+',
                                'id' => '[0-9]+',
                            ),
                            'defaults' => array(
                                'action' => 'unbury',
                            ),
                        ),
                    ),
                ),
            ),
            'user' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/user/',
                    'defaults' => array(
                        'controller' => 'AppBase\Controller\User',
                        'action' => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'create' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => 'create[/]',
                            'defaults' => array(
                                'action' => 'create',
                            ),
                        ),
                    ),
                    'edit' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => 'edit/[:id][/]',
                            'constraints' => array(
                                'id' => '[0-9]+'
                            ),
                            'defaults' => array(
                                'action' => 'edit'
                            ),
                        ),
                    ),
                    'delete' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => 'delete/[:id][/]',
                            'constraints' => array(
                                'id' => '[0-9]+'
                            ),
                            'defaults' => array(
                                'action' => 'delete'
                            ),
                        ),
                    ),
                    'search' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => 'search[/]',
                            'defaults' => array(
                                'action' => 'search'
                            ),
                        ),
                    ),
                    'group' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => 'group/',
                            'defaults' => array(
                                'controller' => 'AppBase\Controller\Group',
                                'action' => 'index',
                            ),
                        ),
                        'may_terminate' => true,
                        'child_routes' => array(
                            'create' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => 'create[/]',
                                    'defaults' => array(
                                        'action' => 'create',
                                    ),
                                ),
                            ),
                            'edit' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => 'edit/[:id][/]',
                                    'constraints' => array(
                                        'id' => '[0-9]+'
                                    ),
                                    'defaults' => array(
                                        'action' => 'edit'
                                    ),
                                ),
                            ),
                            'delete' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => 'delete/[:id][/]',
                                    'constraints' => array(
                                        'id' => '[0-9]+'
                                    ),
                                    'defaults' => array(
                                        'action' => 'delete'
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
            'validation' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/validation/',
                    'defaults' => array(
                        'controller' => 'AppBase\Controller\Validation',
                        'action' => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'confirm' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => 'confirm/',
                            'defaults' => array(
                                'action' => 'confirm',
                            ),
                        ),
                        'may_terminate' => true,
                        'child_routes' => array(
                            'params' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '[:id][/:token][/]',
                                    'constraints' => array(
                                        'id' => '[0-9]+',
                                        'token' => '[a-zA-Z0-9]+',
                                    ),
                                    'defaults' => array(
                                        'action' => 'confirm',
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="service_manager">
    'service_manager' => array(
        // required to overwrite existing services with an alias etc.
        'allow_override' => true,

        'abstract_factories' => array(
            // uses the 'caches' key to automatically create cache services
            'Zend\Cache\Service\StorageCacheAbstractServiceFactory',

            'Zend\Log\LoggerAbstractServiceFactory',
        ),

        // add some short names that hopefully don't conflict
        'aliases' => array(
            'AuthorizeRedirectStrategy' => 'Vrok\Mvc\View\Http\AuthorizeRedirectStrategy',
            'ClientInfo'                => 'Vrok\Client\Info',
            'EmailService'              => 'Vrok\Service\Email',
            'MetaService'               => 'Vrok\Service\Meta',
            'OwnerService'              => 'Vrok\Service\Owner',
            'UserManager'               => 'Vrok\Service\UserManager',
            'ValidationManager'         => 'Vrok\Service\ValidationManager',
            'AuthenticationService'     => 'Zend\Authentication\AuthenticationService',

            // @todo warum ist das notwendig?
            'translator' => 'MvcTranslator',

            // BjyAuthorize only searches for zfcuser_user_service -> point to our
            // own service
            'zfcuser_user_service' => 'Vrok\Service\UserManager',
        ),

        'factories' => array(
            // replace the default translator with our custom extension
            'Zend\I18n\Translator\TranslatorInterface'
                    => 'Vrok\I18n\Translator\TranslatorServiceFactory',
        ),

        'invokables' => array(
            'AppBase\Notification\AdminNotifications' => 'AppBase\Notification\AdminNotifications',
        ),
    ),
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="session">
    'session' => array(
        'remember_me_seconds' => 2419200,
        'use_cookies' => true,
        'cookie_httponly' => true,
    ),
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="slm_queue">
    'slm_queue' => array(
        'queues' => array(
            'jobs' => array(
                // keep processed jobs in the queue for 30min
                'deleted_lifetime' => 30,

                // keep failed jobs in the queue forever, they need to be processed later
                'buried_lifetime' => -1, // DoctrineQueue::LIFETIME_UNLIMITED
            ),
        ),
        'queue_manager' => array(
            'factories' => array(
                'jobs' => 'SlmQueueDoctrine\Factory\DoctrineQueueFactory',
            ),
        ),
        'worker_strategies' => array(
            'queues' => array(
                'jobs' => array(
                    // restart after 100 processed jobs
                    'SlmQueue\Strategy\MaxRunsStrategy' => array('max_runs' => 100),

                    // restart if memory usage reaches 200 MB
                    'SlmQueue\Strategy\MaxMemoryStrategy' => array('max_memory' => 200 * 1024 * 1024),

                    // look for new jobs every 10 seconds
                    'SlmQueueDoctrine\Strategy\IdleNapStrategy' => array('nap_duration' => 10),

                    // This actually starts the job processing
                    'SlmQueue\Strategy\ProcessQueueStrategy',
                ),
            ),
        ),

        // after how many seconds are jobs reported as long running by
        // SlmQueueController::checkJobsAction?
        'runtime_threshold' => 3600, // 60 * 60
    ),
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="translator">
    'translator' => array(
        // @todo replace with locale detection and user settings
        'locale' => 'de_DE',
    ),
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="validation_manager">
    'validation_manager' => array(
        'timeouts' => array(
            'password' => 172800, //48*60*60
        ),
    ),
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="view_manager">
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'XHTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map'             => array(
            'error/403' => __DIR__ . '/../view/error/403.phtml',
            'error/404' => __DIR__ . '/../view/error/404.phtml',
            'error/index' => __DIR__ . '/../view/error/index.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
        'strategies' => array(
            // done by [bjyautorize][unauthorized_strategy]
            //'AuthorizeRedirectStrategy',
        ),
    ),
// </editor-fold>
);
