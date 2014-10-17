<?php

namespace AppBase\Controller;

use Vrok\Mvc\Controller\AbstractActionController;
use Zend\Session\Container as SessionContainer;

/**
 * Allows the administrator to edit/create/delete users.
 */
class UserController extends AbstractActionController
{
    /**
     * Lists all registered users.
     */
    public function indexAction()
    {
        $sessionContainer = new SessionContainer(__CLASS__);
        if (!$sessionContainer['orderBy']) {
            $sessionContainer['orderBy'] = 'username';
        }
        if (!$sessionContainer['order']) {
            $sessionContainer['order'] = 'asc';
        }
        $orderBy = $this->params()->fromQuery('orderBy');
        if (in_array($orderBy, array('username', 'email'))) {
            $sessionContainer['orderBy'] = $orderBy;
        }
        $order = $this->params()->fromQuery('order');
        if (in_array($order, array('asc', 'desc'))) {
            $sessionContainer['order'] = $order;
        }

        $userManager = $this->getServiceLocator()->get('UserManager');
        $form = $this->getServiceLocator()->get('FormElementManager')
                ->get('AppBase\Form\User\UserFilter');
        if ($sessionContainer['userFilter']) {
            $form->setData(array(
                'userFilter' => $sessionContainer['userFilter']
            ));
        }

        if ($this->params()->fromQuery('group')) {
            $group = $userManager->getGroupRepository()
                    ->findOneBy(array('name' => $this->params()->fromQuery('group')));

            if ($group) {
                $form->get('userFilter')->get('groupFilter')->setValue($group->getId());
                $sessionContainer['userFilter']['groupFilter'] = $group->getId();
            }
        }

        if ($this->request->isPost()) {
            $isValid = $form->setData($this->request->getPost())->isValid();
            if ($isValid) {
                $data = $form->getData();
                $sessionContainer['userFilter'] = $data['userFilter'];
            }
        }

        $filter = $userManager->getUserFilter()->areNotDeleted();

        if ($sessionContainer['userFilter']
            && !empty($sessionContainer['userFilter']['nameSearch'])
        ) {
            $filter->byName($sessionContainer['userFilter']['nameSearch']);
        }

        if ($sessionContainer['userFilter']
            && !empty($sessionContainer['userFilter']['groupFilter'])
        ) {
            $filter->joinGroups()->byGroupId($sessionContainer['userFilter']['groupFilter']);
        }

        $filter->orderByField($sessionContainer['orderBy'], $sessionContainer['order']);

        $paginator = $filter->getPaginator();
        $paginator->setItemCountPerPage(15);
        $paginator->setCurrentPageNumber((int)$this->params()->fromQuery('page', 1));

        return $this->createViewModel(array(
            'form'      => $form,
            'paginator' => $paginator,
            'orderBy'   => $sessionContainer['orderBy'],
            'order'     => $sessionContainer['order'],
        ));
    }

    /**
     * Allows userAdmins to create new users.
     */
    public function createAction()
    {
        $form = $this->getServiceLocator()->get('FormElementManager')
                ->get('AppBase\Form\User\UserCreate');
        $form->setData($this->request->getPost());
        $viewModel = $this->createViewModel(array('form' => $form));

        if (!$this->request->isPost() || !$form->isValid()) {
            return $viewModel;
        }

        $data = $form->getData();

        $setRandomPassword = (bool)$data['user']['setRandomPassword'];
        unset($data['user']['setRandomPassword']);

        if (!$setRandomPassword && !$data['user']['password']) {
            $form->get('user')
                    ->setElementMessage('password', 'validate.user.password.notSet');
            return $viewModel;
        }

        $userManager = $this->getServiceLocator()->get('UserManager');
        $user = $userManager->createUser($data['user']);
        if (!$user instanceof \Vrok\Entity\User) {
            $form->get('user')->setUntranslatedMessages($user);
            return $viewModel;
        }

        if ($setRandomPassword) {
            $userManager->sendRandomPassword($user);
        }

        $this->getServiceLocator()->get('Doctrine\ORM\EntityManager')->flush();
        $this->flashMessenger()->addSuccessMessage('message.user.created');
        return $this->redirect()->toRoute('user');
    }

    /**
     * Allows userAdmins to edit an user record.
     *
     * @return ViewModel
     */
    public function editAction()
    {
        $user = $this->getEntityFromParam('Vrok\Entity\User');
        if (!$user instanceof \Vrok\Entity\User) {
            $this->getResponse()->setStatusCode(404);
            return $this->createViewModel(array('message' => $user));
        }

        $em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $repository = $em->getRepository('Vrok\Entity\User');

        $form = $this->getServiceLocator()->get('FormElementManager')
                ->get('AppBase\Form\User\UserEdit');
        $userData = $repository->getInstanceData($user);
        unset($userData['password']);
        $form->setData(array('user' => $userData));

        $viewModel = $this->createViewModel(array(
            'form' => $form,
            'user' => $user,
        ));

        if (!$this->request->isPost()) {
            return $viewModel;
        }

        $isValid = $form->setData($this->request->getPost())->isValid();
        if (!$isValid) {
            return $viewModel;
        }

        $data = $form->getData();
        $setRandomPassword = (bool)$data['user']['setRandomPassword'];
        unset($data['user']['setRandomPassword']);

        // dont set an empty password, leave the current one
        if (empty($data['user']['password'])) {
            unset($data['user']['password']);
        }

        $repository->updateInstance($user, $data['user']);

        // send the email after updating the record, maybe we set a new email...
        if ($setRandomPassword) {
            $userManager = $this->getServiceLocator()->get('UserManager');
            $userManager->sendRandomPassword($user);
        }

        $em->flush();

        $this->flashMessenger()
                ->addSuccessMessage('message.user.edited');
        return $this->redirect()->toRoute('user');
    }

    /**
     *
     * @return ViewModel
     */
    public function deleteAction()
    {
        $user = $this->getEntityFromParam('Vrok\Entity\User');
        if (!$user instanceof \Vrok\Entity\User) {
            $this->getResponse()->setStatusCode(404);
            return $this->createViewModel(array('message' => $user));
        }

        $form = $this->getServiceLocator()->get('FormElementManager')
                ->get('Vrok\Form\ConfirmationForm');
        $form->setConfirmationMessage(array('message.user.confirmDelete',
            array('displayName' => $user->getDisplayName(), 'email' => $user->getEmail())));

        $viewModel = $this->createViewModel(array(
            'form' => $form,
            'user' => $user,
        ));

        if (!$this->request->isPost()) {
            return $viewModel;
        }

        $isValid = $form->setData($this->request->getPost())->isValid();
        if (!$isValid) {
            return $viewModel;
        }

        $em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $em->remove($user);
        try {
            $em->flush();
        }
        catch (\Doctrine\DBAL\DBALException $e) {
            $this->flashMessenger()
                ->addErrorMessage('message.user.cannotDeleteReferenced');
            return $this->redirect()->toRoute('user/edit', array('id' => $user->getId()));
        }

        $this->flashMessenger()
                ->addSuccessMessage('message.user.deleted');
        return $this->redirect()->toRoute('user');
    }
}
