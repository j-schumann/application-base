<?php

namespace AppBase\Controller;

use Vrok\Mvc\Controller\AbstractActionController;

/**
 * Placeholder, entry point in the administrator navigation.
 */
class AdminController extends AbstractActionController
{
    /**
     * Can show administrative data about the system.
     */
    public function indexAction()
    {
    }

    public function cachesAction()
    {
        $config = $this->getServiceLocator()->get('config');
        $cacheNames = isset($config['caches'])
            ? array_keys($config['caches'])
            : [];

        $caches = [];
        foreach($cacheNames as $name) {
            $caches[$name] = $this->getServiceLocator()->get($name);
        }
        return $this->createViewModel(['caches' => $caches]);
    }
}
