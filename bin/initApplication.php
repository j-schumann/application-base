<?php

use Zend\Mvc\Application;

ini_set('display_errors', true);
chdir(__DIR__);

$previousDir = '.';

while (!file_exists('config/application.config.php')) {
    $dir = dirname(getcwd());

    if ($previousDir === $dir) {
        throw new RuntimeException(
            'Unable to locate "config/application.config.php"'
        );
    }

    $previousDir = $dir;
    chdir($dir);
}

if (is_readable('init_autoloader.php')) {
    include_once 'init_autoloader.php';
} elseif (!(@include_once __DIR__.'/../vendor/autoload.php')
    && !(@include_once __DIR__.'/../../../autoload.php')
) {
    throw new RuntimeException('Error: vendor/autoload.php could not be found. Did you run php composer.phar install?');
}

$application = Application::init(include 'config/application.config.php');
