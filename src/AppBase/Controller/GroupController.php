<?php

namespace AppBase\Controller;

use Vrok\Mvc\Controller\AbstractActionController;

class GroupController extends AbstractActionController
{
    public function indexAction()
    {
        $em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $repository = $em->getRepository('Vrok\Entity\Group');
        $groups = $repository->findAll();

        return $this->createViewModel(array('groups' => $groups));
    }

    public function createAction()
    {
        $form = $this->getServiceLocator()->get('FormElementManager')
                ->get('AppBase\Form\User\Group');

        $viewModel = $this->createViewModel(array(
            'form' => $form,
        ));

        if (!$this->request->isPost()) {
            return $viewModel;
        }

        $isValid = $form->setData($this->request->getPost())->isValid();
        if (!$isValid) {
            return $viewModel;
        }

        $data = $form->getData();
        $userService = $this->getServiceLocator()->get('UserManager');
        $group = $userService->createGroup($data['group']);

        $this->flashMessenger()
                ->addSuccessMessage('message.user.group.created');
        return $this->redirect()->toRoute('user/group');
    }

    public function editAction()
    {
        $group = $this->getEntityFromParam('Vrok\Entity\Group');
        if (!$group instanceof \Vrok\Entity\Group) {
            $this->getResponse()->setStatusCode(404);
            return $this->createViewModel(array('message' => $group));
        }

        $em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $repository = $em->getRepository('Vrok\Entity\Group');

        $form = $this->getServiceLocator()->get('FormElementManager')
                ->get('AppBase\Form\User\Group');
        $form->setData(array('group' => $repository->getInstanceData($group)));

        $viewModel = $this->createViewModel(array(
            'form'  => $form,
            'group' => $group,
        ));

        if (!$this->request->isPost()) {
            return $viewModel;
        }

        $isValid = $form->setData($this->request->getPost())->isValid();
        if (!$isValid) {
            return $viewModel;
        }

        $data = $form->getData();
        $repository->updateInstance($group, $data['group']);
        $em->flush();

        $this->flashMessenger()
                ->addSuccessMessage('message.user.group.edited');
        return $this->redirect()->toRoute('user/group');
    }

    public function deleteAction()
    {
        $group = $this->getEntityFromParam('Vrok\Entity\Group');
        if (!$group instanceof \Vrok\Entity\Group) {
            $this->getResponse()->setStatusCode(404);
            return $this->createViewModel(array('message' => $group));
        }

        if ($group->getChildren()->count()) {
            $this->flashMessenger()
                ->addErrorMessage('message.user.group.cannotDeleteWithChildren');
            return $this->redirect()->toRoute('user/group');
        }

        $form = $this->getServiceLocator()->get('FormElementManager')
                ->get('Vrok\Form\ConfirmationForm');
        $form->setConfirmationMessage(array('message.user.group.confirmDelete',
            $group->getName()));

        $viewModel = $this->createViewModel(array(
            'form'  => $form,
            'group' => $group,
        ));

        if (!$this->request->isPost()) {
            return $viewModel;
        }

        $isValid = $form->setData($this->request->getPost())->isValid();
        if (!$isValid) {
            return $viewModel;
        }

        $em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $em->remove($group);
        $em->flush();

        $this->flashMessenger()
                ->addSuccessMessage('message.user.group.deleted');
        return $this->redirect()->toRoute('user/group');
    }
}
