<?php
/**
 * @copyright   (c) 2014, Vrok
 * @license     http://customlicense CustomLicense
 * @author      Jakob Schumann <schumann@vrok.de>
 */

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

    /**
     * Lists all configured caches and their status information.
     *
     * @return ViewModel
     */
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

    /**
     * Allows to flush the given cache.
     *
     * @return ViewModel|Response
     */
    public function flushCacheAction()
    {
        $name = $this->params('name');
        $config = $this->getServiceLocator()->get('config');
        if (!isset($config['caches'][$name])) {
            $this->flashMessenger()
                ->addErrorMessage('message.cache.notFound');
            return $this->redirect()->toRoute('admin/caches');
        }

        $cache = $this->getServiceLocator()->get($name);
        if (! $cache instanceof \Zend\Cache\Storage\FlushableInterface) {
            $this->flashMessenger()
                ->addErrorMessage('message.cache.notFlushable');
            return $this->redirect()->toRoute('admin/caches');
        }

        $form = $this->getServiceLocator()->get('FormElementManager')
                ->get('Vrok\Form\ConfirmationForm');
        $form->setConfirmationMessage(
            ['message.cache.confirmFlush', ['name' => $name]]
        );

        $viewModel = [
            'name' => $name,
            'form' => $form,
        ];

        if (!$this->request->isPost()) {
            return $viewModel;
        }

        $isValid = $form->setData($this->request->getPost())->isValid();
        if (!$isValid) {
            return $viewModel;
        }

        $cache->flush();
        $this->flashMessenger()
            ->addSuccessMessage('message.cache.flushed');
        return $this->redirect()->toRoute('admin/caches');
    }

    /**
     * Just output the phpinfo(), do not use the layout, it would be corrupted.
     */
    public function phpinfoAction()
    {
        phpinfo();
        exit;
    }
}
