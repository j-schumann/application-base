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
            array('message.cache.confirmFlush', array('name' => $name))
        );

        $viewModel = $this->createViewModel(array(
            'name' => $name,
            'form' => $form,
        ));

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
}
