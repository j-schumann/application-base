<?php

/**
 * Application-Base config.
 */
return [
// <editor-fold defaultstate="collapsed" desc="asset_manager">
    'asset_manager' => [
        'resolver_configs' => [
            'paths' => [
                __DIR__.'/../public',
            ],
            'view_scripts' => [
                'app-base.js' => 'app-base/partials/app-base.js',
            ],
        ],
    ],
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="bjyauthorize">
    'bjyauthorize' => [
        'default_role'      => 'guest',
        'identity_provider' => 'BjyAuthorize\Provider\Identity\AuthenticationIdentityProvider',
        'role_providers'    => [
            'BjyAuthorize\Provider\Role\Config' => [
                'user' => [
                    'children' => ['admin'],
                ],
                'guest' => [],
            ],
            // all other roles inherit from the base roles and are loaded from the
            // user groups tabel
            'BjyAuthorize\Provider\Role\ObjectRepositoryProvider' => [
                'role_entity_class' => 'Vrok\Entity\Group',
                'object_manager'    => 'Doctrine\ORM\EntityManager',
            ],
        ],
        'resource_providers' => [
            'BjyAuthorize\Provider\Resource\Config' => [
                'admin' => [],
                'guest' => [],
                'user'  => [],
            ],
        ],
        'rule_providers' => [
            'BjyAuthorize\Provider\Rule\Config' => [
                'allow' => [
                    ['admin', 'admin'],
                    ['guest', 'guest'],
                    ['user', 'user'],
                ],
            ],
        ],

        // Add guards here for other modules that don't use BjyAuthorize
        // themself as they would deny access by default without a guard
        'guards' => [
            'BjyAuthorize\Guard\Controller' => [
                [
                    'controller' => 'AppBase\Controller\Account',
                    'action'     => ['login', 'index', 'request-password', 'reset-password'],
                    'roles'      => ['guest', 'user'],
                ],
                [
                    'controller' => 'AppBase\Controller\Account',
                    'action'     => ['change-displayname', 'change-password', 'logout', 'delete'],
                    'roles'      => ['user'],
                ],
                [
                    'controller' => 'AppBase\Controller\Admin',
                    'roles'      => ['admin'],
                ],
                [
                    'controller' => 'AppBase\Controller\Cron',
                    'roles'      => ['guest', 'user'],
                ],
                [
                    'controller' => 'AppBase\Controller\Group',
                    'roles'      => ['userAdmin'],
                ],
                [
                    'controller' => 'AppBase\Controller\SlmQueue',
                    'roles'      => ['queueAdmin'],
                ],
                [
                    'controller' => 'AppBase\Controller\SlmQueue',
                    'action'     => 'check-jobs',
                    'roles'      => ['guest', 'queueAdmin'],
                ],
                [
                    'controller' => 'AppBase\Controller\User',
                    'roles'      => ['userAdmin'],
                ],
                [
                    'controller' => 'AppBase\Controller\User',
                    'action'     => 'password-strength',
                    'roles'      => ['guest', 'user'],
                ],
                [
                    'controller' => 'AppBase\Controller\Validation',
                    'roles'      => ['guest', 'user'],
                ],

                // guards for the SlmQueue module
                [
                    'controller' => 'SlmQueueDoctrine\Controller\DoctrineWorkerController',
                    'roles'      => ['guest', 'user'],
                ],

                // guards for the translation module
                [
                    'controller' => 'TranslationModule\Controller\Index',
                    'roles'      => ['translationAdmin'],
                ],
                [
                    'controller' => 'TranslationModule\Controller\Entry',
                    'roles'      => ['translationAdmin'],
                ],
                [
                    'controller' => 'TranslationModule\Controller\Module',
                    'roles'      => ['translationAdmin'],
                ],
                [
                    'controller' => 'TranslationModule\Controller\Language',
                    'roles'      => ['translationAdmin'],
                ],
                [
                    'controller' => 'TranslationModule\Controller\Management',
                    'roles'      => ['translationAdmin'],
                ],

                // guards for supervisor-control
                [
                    'controller' => 'SupervisorControl\Controller\Supervisor',
                    'roles'      => ['supervisorAdmin'],
                ],
                [
                       'controller' => 'SupervisorControl\Controller\Console',
                    'roles'         => ['guest', 'supervisorAdmin'],
                ],
            ],
        ],
        'unauthorized_strategy' => 'Vrok\Mvc\View\Http\AuthorizeRedirectStrategy',
    ],
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="console">
    'console' => [
        'router' => [
            'routes' => [
                'cron-hourly' => [
                    'options' => [
                        'route'    => 'cron-hourly',
                        'defaults' => [
                            'controller' => 'AppBase\Controller\Cron',
                            'action'     => 'cron-hourly',
                        ],
                    ],
                ],
                'cron-daily' => [
                    'options' => [
                        'route'    => 'cron-daily',
                        'defaults' => [
                            'controller' => 'AppBase\Controller\Cron',
                            'action'     => 'cron-daily',
                        ],
                    ],
                ],
                'cron-monthly' => [
                    'options' => [
                        'route'    => 'cron-monthly',
                        'defaults' => [
                            'controller' => 'AppBase\Controller\Cron',
                            'action'     => 'cron-monthly',
                        ],
                    ],
                ],
                'purge-validations' => [
                    'options' => [
                        'route'    => 'purge-validations',
                        'defaults' => [
                            'controller' => 'AppBase\Controller\Validation',
                            'action'     => 'purge',
                        ],
                    ],
                ],
                'check-jobs' => [
                    'options' => [
                        'route'    => 'check-jobs',
                        'defaults' => [
                            'controller' => 'AppBase\Controller\SlmQueue',
                            'action'     => 'check-jobs',
                        ],
                    ],
                ],
            ],
        ],
    ],
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="doctrine">
    'doctrine' => [
        'configuration' => [
            'orm_default' => [
                'metadata_cache'  => 'zend_storage',
                'query_cache'     => 'zend_storage',
                'result_cache'    => 'zend_storage',
                'hydration_cache' => 'zend_storage',

                // Generate proxies automatically (turn off for production)
                'generate_proxies' => true,

                // directory where proxies will be stored.
                'proxy_dir' => __DIR__.'/../../../../data/DoctrineORMModule/Proxy',

                // namespace for generated proxy classes
                'proxy_namespace' => 'DoctrineORMModule\Proxy',

                'types' => [
                    'datetime' => 'Vrok\Doctrine\DBAL\Types\UTCDateTimeType',
                ],
            ],
        ],
        'driver' => [
            'queue_entities' => [
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'zend_storage',
                'paths' => [__DIR__.'/../../../slm/queue-doctrine/data'],
            ],

            // include the DoctrineQueue
            'orm_default' => [
                'drivers' => [
                    'Application\Entity' => 'queue_entities',
                ],
            ],

            // the libraries don't configure caches themself
            'translation_entities' => [
                'cache' => 'zend_storage',
            ],
            'vrok_entities' => [
                'cache' => 'zend_storage',
            ],
        ],
    ],
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="listeners">
    'listeners' => [
        'AppBase\Notification\AdminNotifications',
    ],
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="navigation">
    /*
     * Bjy-Authorize uses resource names like
     * controller/{ControllerServiceName}:{action} when the guards are defined with
     * one or more actions instead of defining the actions as privileges on the controllers
     * When no action is set we must only use controller/{ControllerServiceName} as
     * there is no resource controller/{ControllerServiceName}:index for them.
     */
    'navigation' => [
        'default' => [
            'account' => [
                'label' => 'navigation.account',
                'route' => 'account',
                'order' => -100,
                'pages' => [
                    [
                        'label'    => 'navigation.account.login',
                        'route'    => 'account/login',
                        'resource' => 'guest',
                    ],
                    [
                        'label'    => 'navigation.account.index',
                        'route'    => 'account',
                        'resource' => 'user',
                    ],
                    'account/logout' => [
                        'label'    => 'navigation.account.logout',
                        'route'    => 'account/logout',
                        'resource' => 'user',
                    ],
                    [
                        'label'   => 'navigation.account.changeDisplayname',
                        'route'   => 'account/change-displayname',
                        'visible' => false,
                    ],
                    [
                        'label'   => 'navigation.account.changePassword',
                        'route'   => 'account/change-password',
                        'visible' => false,
                    ],
                    [
                        'label'   => 'navigation.account.requestPassword',
                        'route'   => 'account/request-password',
                        'visible' => false,
                    ],
                    [
                        'label'   => 'navigation.account.delete',
                        'route'   => 'account/delete',
                        'visible' => false,
                    ],
                ],
            ],
            'administration' => [
                'label'    => 'navigation.administration', // default label or none is rendered
                'route'    => 'admin', // we need either a route or an URI to avoid fatal error
                'resource' => 'admin',
                'order'    => 1000,
                'pages'    => [
                    [
                        'label'    => 'navigation.user',
                        'route'    => 'user',
                        'resource' => 'controller/AppBase\Controller\User',
                        'pages'    => [
                            [
                                'label' => 'navigation.user.create',
                                'route' => 'user/create',
                            ],
                            [
                                'label'   => 'navigation.user.edit',
                                'route'   => 'user/edit',
                                'visible' => false,
                            ],
                            [
                                'label'   => 'navigation.user.delete',
                                'route'   => 'user/delete',
                                'visible' => false,
                            ],
                        ],
                    ],
                    [
                        'label'    => 'navigation.user.group',
                        'route'    => 'user/group',
                        'resource' => 'controller/AppBase\Controller\Group',
                        'pages'    => [
                            [
                                'label' => 'navigation.user.group.create',
                                'route' => 'user/group/create',
                            ],
                            [
                                'label'   => 'navigation.user.group.edit',
                                'route'   => 'user/group/edit',
                                'visible' => false,
                            ],
                            [
                                'label'   => 'navigation.user.group.delete',
                                'route'   => 'user/group/delete',
                                'visible' => false,
                            ],
                        ],
                    ],
                    'server' => [
                        'label'    => 'navigation.administration.server', // default label or none is rendered
                        'route'    => 'admin', // we need either a route or an URI to avoid fatal error
                        'resource' => 'controller/AppBase\Controller\Admin',
                        'order'    => 1000,
                        'pages'    => [
                            [
                                'label'     => 'navigation.slmQueue',
                                'route'     => 'slm-queue',
                                'resource'  => 'controller/AppBase\Controller\SlmQueue',
                                'privilege' => 'index',
                                'order'     => 1000,
                                'pages'     => [
                                    [
                                        'label'   => 'navigation.slmQueue.recover',
                                        'route'   => 'slm-queue/recover',
                                        'visible' => false,
                                    ],
                                    [
                                        'label'   => 'navigation.slmQueue.listBuried',
                                        'route'   => 'slm-queue/list-buried',
                                        'visible' => false,
                                    ],
                                    [
                                        'label'   => 'navigation.slmQueue.listRunning',
                                        'route'   => 'slm-queue/list-running',
                                        'visible' => false,
                                    ],
                                    [
                                        'label'   => 'navigation.slmQueue.delete',
                                        'route'   => 'slm-queue/delete',
                                        'visible' => false,
                                    ],
                                    [
                                        'label'   => 'navigation.slmQueue.release',
                                        'route'   => 'slm-queue/release',
                                        'visible' => false,
                                    ],
                                    [
                                        'label'   => 'navigation.slmQueue.unbury',
                                        'route'   => 'slm-queue/unbury',
                                        'visible' => false,
                                    ],
                                ],
                            ],
                            [
                                'label'     => 'navigation.caches',
                                'route'     => 'admin/caches',
                                'resource'  => 'controller/AppBase\Controller\Admin',
                                'privilege' => 'caches',
                                'order'     => 1000,
                                'pages'     => [
                                    [
                                        'label'   => 'navigation.flushCache',
                                        'route'   => 'admin/flush-cache',
                                        'visible' => false,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="router">
    'router' => [
        'routes' => [
            'account' => [
                'type'    => 'Literal',
                'options' => [
                    'route'    => '/account/',
                    'defaults' => [
                        'controller' => 'AppBase\Controller\Account',
                        'action'     => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes'  => [
                    'login' => [
                        'type'    => 'Segment',
                        'options' => [
                            'route'    => 'login[/]',
                            'defaults' => [
                                'action' => 'login',
                            ],
                        ],
                    ],
                    'logout' => [
                        'type'    => 'Segment',
                        'options' => [
                            'route'    => 'logout[/]',
                            'defaults' => [
                                'action' => 'logout',
                            ],
                        ],
                    ],
                    'change-password' => [
                        'type'    => 'Segment',
                        'options' => [
                            'route'    => 'change-password[/]',
                            'defaults' => [
                                'action' => 'change-password',
                            ],
                        ],
                    ],
                    'change-displayname' => [
                        'type'    => 'Segment',
                        'options' => [
                            'route'    => 'change-displayname[/]',
                            'defaults' => [
                                'action' => 'change-displayname',
                            ],
                        ],
                    ],
                    'request-password' => [
                        'type'    => 'Segment',
                        'options' => [
                            'route'    => 'request-password[/]',
                            'defaults' => [
                                'action' => 'request-password',
                            ],
                        ],
                    ],
                    'reset-password' => [
                        'type'    => 'Segment',
                        'options' => [
                            'route'    => 'reset-password[/]',
                            'defaults' => [
                                'action' => 'reset-password',
                            ],
                        ],
                    ],
                    'delete' => [
                        'type'    => 'Segment',
                        'options' => [
                            'route'    => 'delete[/]',
                            'defaults' => [
                                'action' => 'delete',
                            ],
                        ],
                    ],
                ],
            ],
            'admin' => [
                'type'    => 'Segment',
                'options' => [
                    'route'    => '/admin[/]',
                    'defaults' => [
                        'controller' => 'AppBase\Controller\Admin',
                        'action'     => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes'  => [
                    'caches' => [
                        'type'    => 'Segment',
                        'options' => [
                            'route'    => 'caches[/]',
                            'defaults' => [
                                'action' => 'caches',
                            ],
                        ],
                    ],
                    'flush-cache' => [
                        'type'    => 'Segment',
                        'options' => [
                            'route'       => 'flush-cache/[:name][/]',
                            'constraints' => [
                                'name' => '[a-zA-Z0-9_-]+',
                            ],
                            'defaults' => [
                                'action' => 'flush-cache',
                            ],
                        ],
                    ],
                    'phpinfo' => [
                        'type'    => 'Segment',
                        'options' => [
                            'route'    => 'phpinfo[/]',
                            'defaults' => [
                                'action' => 'phpinfo',
                            ],
                        ],
                    ],
                ],
            ],
            'slm-queue' => [
                'type'    => 'Literal',
                'options' => [
                    'route'    => '/slm-queue/',
                    'defaults' => [
                        'controller' => 'AppBase\Controller\SlmQueue',
                        'action'     => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes'  => [
                    'recover' => [
                        'type'    => 'Segment',
                        'options' => [
                            'route'       => 'recover/[:name][/]',
                            'constraints' => [
                                'name' => '[a-zA-Z0-9_-]+',
                            ],
                            'defaults' => [
                                'action' => 'recover',
                            ],
                        ],
                    ],
                    'list-buried' => [
                        'type'    => 'Segment',
                        'options' => [
                            'route'       => 'list-buried/[:name][/]',
                            'constraints' => [
                                'name' => '[a-zA-Z0-9_-]+',
                                ],
                            'defaults' => [
                                'action' => 'list-buried',
                            ],
                        ],
                    ],
                    'list-running' => [
                        'type'    => 'Segment',
                        'options' => [
                            'route'       => 'list-running/[:name][/]',
                            'constraints' => [
                                'name' => '[a-zA-Z0-9_-]+',
                            ],
                            'defaults' => [
                                'action' => 'list-running',
                            ],
                        ],
                    ],
                    'delete' => [
                        'type'    => 'Segment',
                        'options' => [
                            'route'       => 'delete/[:name]/[:id][/]',
                            'constraints' => [
                                'name' => '[a-zA-Z0-9_-]+',
                                'id'   => '[0-9]+',
                            ],
                            'defaults' => [
                                'action' => 'delete',
                            ],
                        ],
                    ],
                    'release' => [
                        'type'    => 'Segment',
                        'options' => [
                            'route'       => 'release/[:name]/[:id][/]',
                            'constraints' => [
                                'name' => '[a-zA-Z0-9_-]+',
                                'id'   => '[0-9]+',
                            ],
                            'defaults' => [
                                'action' => 'release',
                            ],
                        ],
                    ],
                    'unbury' => [
                        'type'    => 'Segment',
                        'options' => [
                            'route'       => 'unbury/[:name]/[:id][/]',
                            'constraints' => [
                                'name' => '[a-zA-Z0-9_-]+',
                                'id'   => '[0-9]+',
                            ],
                            'defaults' => [
                                'action' => 'unbury',
                            ],
                        ],
                    ],
                ],
            ],
            'user' => [
                'type'    => 'Literal',
                'options' => [
                    'route'    => '/user/',
                    'defaults' => [
                        'controller' => 'AppBase\Controller\User',
                        'action'     => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes'  => [
                    'create' => [
                        'type'    => 'Segment',
                        'options' => [
                            'route'    => 'create[/]',
                            'defaults' => [
                                'action' => 'create',
                            ],
                        ],
                    ],
                    'edit' => [
                        'type'    => 'segment',
                        'options' => [
                            'route'       => 'edit/[:id][/]',
                            'constraints' => [
                                'id' => '[0-9]+',
                            ],
                            'defaults' => [
                                'action' => 'edit',
                            ],
                        ],
                    ],
                    'delete' => [
                        'type'    => 'segment',
                        'options' => [
                            'route'       => 'delete/[:id][/]',
                            'constraints' => [
                                'id' => '[0-9]+',
                            ],
                            'defaults' => [
                                'action' => 'delete',
                            ],
                        ],
                    ],
                    'password-strength' => [
                        'type'    => 'segment',
                        'options' => [
                            'route'    => 'password-strength[/]',
                            'defaults' => [
                                'action' => 'password-strength',
                            ],
                        ],
                    ],
                    'search' => [
                        'type'    => 'Segment',
                        'options' => [
                            'route'    => 'search[/]',
                            'defaults' => [
                                'action' => 'search',
                            ],
                        ],
                    ],
                    'group' => [
                        'type'    => 'Literal',
                        'options' => [
                            'route'    => 'group/',
                            'defaults' => [
                                'controller' => 'AppBase\Controller\Group',
                                'action'     => 'index',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes'  => [
                            'create' => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'    => 'create[/]',
                                    'defaults' => [
                                        'action' => 'create',
                                    ],
                                ],
                            ],
                            'edit' => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'       => 'edit/[:id][/]',
                                    'constraints' => [
                                        'id' => '[0-9]+',
                                    ],
                                    'defaults' => [
                                        'action' => 'edit',
                                    ],
                                ],
                            ],
                            'delete' => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'       => 'delete/[:id][/]',
                                    'constraints' => [
                                        'id' => '[0-9]+',
                                    ],
                                    'defaults' => [
                                        'action' => 'delete',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'validation' => [
                'type'    => 'Literal',
                'options' => [
                    'route'    => '/validation/',
                    'defaults' => [
                        'controller' => 'AppBase\Controller\Validation',
                        'action'     => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes'  => [
                    'confirm' => [
                        'type'    => 'Segment',
                        'options' => [
                            'route'    => 'confirm/',
                            'defaults' => [
                                'action' => 'confirm',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes'  => [
                            'params' => [
                                'type'    => 'Segment',
                                'options' => [
                                    'route'       => '[:id][/:token][/]',
                                    'constraints' => [
                                        'id'    => '[0-9]+',
                                        'token' => '[a-zA-Z0-9]+',
                                    ],
                                    'defaults' => [
                                        'action' => 'confirm',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="service_manager">
    'service_manager' => [
        'allow_override' => true,

        'abstract_factories' => [
            'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
            'Zend\Log\LoggerAbstractServiceFactory',
        ],

        'aliases' => [
            // BjyAuthorize only searches for zfcuser_user_service -> point to our
            // own service
            'zfcuser_user_service' => 'Vrok\Service\UserManager',
        ],

        'factories' => [
            'Navigation' => 'Zend\Navigation\Service\DefaultNavigationFactory',

            // replace the default translator with our custom extension
            'Zend\I18n\Translator\TranslatorInterface' => 'Vrok\I18n\Translator\TranslatorServiceFactory',
        ],

        'lazy_services' => [
            'class_map' => [
            ],

            'proxies_target_dir' =>  __DIR__.'/../../../../data/LazyServices/',
            'write_proxy_files'  => true,
        ],
    ],
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="session">
    'session' => [
        'remember_me_seconds' => 2419200,
        'use_cookies'         => true,
        'cookie_httponly'     => true,
        'cookie_secure'       => true,
    ],
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="slm_queue">
    'slm_queue' => [
        'queues' => [
            'jobs' => [
                'deleted_lifetime' => 30,

                // keep failed jobs in the queue forever, they need to be processed later
                'buried_lifetime' => -1, // DoctrineQueue::LIFETIME_UNLIMITED
            ],
        ],
        'queue_manager' => [
            'factories' => [
                'jobs' => 'SlmQueueDoctrine\Factory\DoctrineQueueFactory',
            ],
        ],
        'worker_strategies' => [
            'queues' => [
                'jobs' => [
                    'SlmQueue\Strategy\MaxRunsStrategy' => ['max_runs' => 100],

                    // restart if memory usage reaches 200 MB
                    'SlmQueue\Strategy\MaxMemoryStrategy' => ['max_memory' => 200 * 1024 * 1024],

                    // look for new jobs every 10 seconds
                    'SlmQueueDoctrine\Strategy\IdleNapStrategy' => ['nap_duration' => 10],

                    // This actually starts the job processing
                    'SlmQueue\Strategy\ProcessQueueStrategy',
                ],
            ],
        ],

        // after how many seconds are jobs reported as long running by
        // SlmQueueController::checkJobsAction?
        'runtime_threshold' => 3600, // 60 * 60
    ],
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="translator">
    'translator' => [
        // this sets the primary and the fallback locale
        // the primary locale is overwritten in the module bootstrap according
        // to the systems defaultLocale stored in the metaData and eventually
        // detected by the users profile language or accept-language headers.
        'locale' => ['de_DE', 'de_DE'],
    ],
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="validation_manager">
    'validation_manager' => [
        'timeouts' => [
            'confirmPasswordRequest' => 86400, //24*60*60
            'confirmEmailChange'     => 172800, //48*60*60
            'validateUser'           => 172800, //48*60*60
        ],
    ],
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="view_manager">
    'view_manager' => [
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'XHTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map'             => [
            'error/403'   => __DIR__.'/../view/error/403.phtml',
            'error/404'   => __DIR__.'/../view/error/404.phtml',
            'error/index' => __DIR__.'/../view/error/index.phtml',
           ],
        'template_path_stack' => [
            __DIR__.'/../view',
        ],
        'strategies' => [
            // done by [bjyautorize][unauthorized_strategy]
            //'Vrok\Mvc\View\Http\AuthorizeRedirectStrategy',

            'Vrok\Mvc\View\Http\ErrorLoggingStrategy',
        ],
    ],
// </editor-fold>
];
