<?php

/**
 * @copyright   (c) 2014-16, Vrok
 * @license     MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @author      Jakob Schumann <schumann@vrok.de>
 */

namespace AppBase\Controller;

use Vrok\Entity\Group;
use Vrok\Mvc\Controller\AbstractActionController;

/**
 * Allows userAdmins to list and CRUD goups.
 */
class GroupController extends AbstractActionController
{
    /**
     * Lists all existing user groups.
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        $em         = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $repository = $em->getRepository('Vrok\Entity\Group');
        $groups     = $repository->findAll();

        return $this->createViewModel(['groups' => $groups]);
    }

    /**
     * Allows the userAdmin to create a new group.
     *
     * @return ViewModel|Response
     */
    public function createAction()
    {
        $form = $this->getServiceLocator()->get('FormElementManager')
                ->get('AppBase\Form\User\Group');

        $viewModel = [
            'form' => $form,
        ];

        if (! $this->request->isPost()) {
            return $viewModel;
        }

        $isValid = $form->setData($this->request->getPost())->isValid();
        if (! $isValid) {
            return $viewModel;
        }

        $data        = $form->getData();
        $userService = $this->getServiceLocator()->get(\Vrok\Service\UserManager::class);
        $group       = $userService->createGroup($data['group']);

        $this->flashMessenger()
                ->addSuccessMessage('message.user.group.created');

        return $this->redirect()->toRoute('user/group');
    }

    /**
     * Allows the userAdmin to edit the selected Group.
     *
     * @return ViewModel|Response
     */
    public function editAction()
    {
        $group = $this->getEntityFromParam('Vrok\Entity\Group');
        if (! $group instanceof Group) {
            $this->getResponse()->setStatusCode(404);

            return $this->createViewModel(['message' => $group]);
        }

        $em         = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $repository = $em->getRepository('Vrok\Entity\Group');

        $form = $this->getServiceLocator()->get('FormElementManager')
                ->get('AppBase\Form\User\Group');
        $form->setData(['group' => $repository->getInstanceData($group)]);

        $viewModel = [
            'form'  => $form,
            'group' => $group,
        ];

        if (! $this->request->isPost()) {
            return $viewModel;
        }

        $isValid = $form->setData($this->request->getPost())->isValid();
        if (! $isValid) {
            return $viewModel;
        }

        $data = $form->getData();
        $repository->updateInstance($group, $data['group']);
        $em->flush();

        $this->flashMessenger()
                ->addSuccessMessage('message.user.group.edited');

        return $this->redirect()->toRoute('user/group');
    }

    /**
     * Allows the userAdmin to delete the selected group.
     *
     * @return ViewModel|Response
     */
    public function deleteAction()
    {
        $group = $this->getEntityFromParam('Vrok\Entity\Group');
        if (! $group instanceof Group) {
            $this->getResponse()->setStatusCode(404);

            return $this->createViewModel(['message' => $group]);
        }

        if ($group->getChildren()->count()) {
            $this->flashMessenger()
                ->addErrorMessage('message.user.group.cannotDeleteWithChildren');

            return $this->redirect()->toRoute('user/group');
        }

        $form = $this->getServiceLocator()->get('FormElementManager')
                ->get('Vrok\Form\ConfirmationForm');
        $form->setConfirmationMessage(['message.user.group.confirmDelete',
            $group->getName(), ]);

        $viewModel = [
            'form'  => $form,
            'group' => $group,
        ];

        if (! $this->request->isPost()) {
            return $viewModel;
        }

        $isValid = $form->setData($this->request->getPost())->isValid();
        if (! $isValid) {
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
