<?php

namespace AppBase\Controller;

use Vrok\Entity\User;
use Vrok\Mvc\Controller\AbstractActionController;

/**
 * Handles the basic actions for the users account like login/logout/password cahnge etc.
 */
class AccountController extends AbstractActionController
{
    /**
     * Account overview for the logged in User
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        if (!$this->currentUser()) {
            return $this->redirect()->toRoute('account/login');
        }
    }

    /**
     * Shows a login form.
     *
     * @return ViewModel
     */
    public function loginAction()
    {
        if ($this->currentUser()) {
            return $this->redirect()->toRoute('account');
        }

        $form = $this->getServiceLocator()->get('FormElementManager')
                ->get('AppBase\Form\User\Login');
        $form->setData($this->request->getPost());
        $viewModel = ['form' => $form];

        if (!$this->request->isPost() || !$form->isValid()) {
            return $this->createViewModel($viewModel);
        }

        $data = $form->getData();
        $um = $this->getServiceLocator()->get('UserManager');

        // we do not use the Zend\Authentication\Validator directly in the form
        // as this would lead to a successful login even when the CSRF failed
        $result = $um->login($data['username'], $data['password']);
        if (! $result instanceof User) {
            $form->get('password')->setMessages($result);
            return $this->createViewModel($viewModel);
        }

        return $this->loginRedirector()->goBack($um->getPostLoginRoute());
    }

    /**
     * Shows a logout form.
     * Use a form instead of simply logging out to prevent CSRF.
     *
     * @return ViewModel
     */
    public function logoutAction()
    {
        if (!$this->currentUser()) {
            return $this->redirect()->toRoute('home');
        }

        // directly logout without confirmation if the session id is given
        // to protect against simple CSRF
        if ($this->params()->fromQuery('s') !== session_id()) {
            $form = $this->getServiceLocator()->get('FormElementManager')
                ->get('Vrok\Form\ConfirmationForm');
            $form->setData($this->request->getPost());
            if (!$this->request->isPost() || !$form->isValid()) {
                return $this->createViewModel(['form' => $form]);
            }
        }

        $userService = $this->getServiceLocator()->get('UserManager');
        $userService->logout();

        return $this->redirect()->toRoute('home');
    }

    /**
     * Allows the logged in user to change his displayName.
     *
     * @return ViewModel|Response
     */
    public function changeDisplaynameAction()
    {
        $form = $this->getServiceLocator()->get('FormElementManager')
                ->get('AppBase\Form\User\DisplayNameChange');

        $um = $this->getServiceLocator()->get('UserManager');
        $user = $this->identity();

        $form->setData($um->getUserRepository()->getInstanceData($user));
        $form->setData($this->request->getPost());
        $viewModel = $this->createViewModel(['form' => $form]);

        if (!$this->request->isPost() || !$form->isValid()) {
            return $viewModel;
        }

        $data = $form->getData();
        $user->setDisplayName($data['displayName']);
        $um->getEntityManager()->flush();

        $this->flashMessenger()
                ->addSuccessMessage('message.user.displayNameChanged');
        return $this->redirect()->toRoute('account');
    }

    /**
     * Allows the logged in user to change his password.
     * BjyGuarded: users only
     *
     * @return ViewModel
     */
    public function changePasswordAction()
    {
        $form = $this->getServiceLocator()->get('FormElementManager')
                ->get('AppBase\Form\User\PasswordChange');
        $form->setData($this->request->getPost());
        $viewModel = $this->createViewModel(['form' => $form]);

        if (!$this->request->isPost() || !$form->isValid()) {
            return $viewModel;
        }

        $data = $form->getData();
        $userManager = $this->getServiceLocator()->get('UserManager');
        $user = $userManager->getAuthService()->getIdentity();

        if (!$user->checkPassword($data['password'])) {
            $form->setElementMessage('password', 'validate.user.wrongPassword');
            return $viewModel;
        }

        $user->setPassword($data['newPassword']);
        $userManager->getEntityManager()->flush();

        $this->flashMessenger()
                ->addSuccessMessage('message.user.passwordChanged');
        return $this->redirect()->toRoute('account');
    }

    /**
     * Allows the user to request an email with a new random password.
     *
     * @return ViewModel|Response
     */
    public function requestPasswordAction()
    {
        $form = $this->getServiceLocator()->get('FormElementManager')
                ->get('AppBase\Form\User\PasswordRequest');
        $form->setData($this->request->getPost());
        $viewModel = $this->createViewModel(['form' => $form]);

        if (!$this->request->isPost() || !$form->isValid()) {
            return $viewModel;
        }

        $data = $form->getData();
        $userManager = $this->getServiceLocator()->get('UserManager');

        $user = $userManager->getUserByIdentity($data['username']);
        if (!$user) {
            $form->setElementMessage('username', 'validate.user.identityNotFound');
            return $viewModel;
        }

        // @todo do not directly change the password but send a validation and then
        // show a form for the new password (+confirmation).
        //$validationManager = $this->getServiceLocator()->get('ValidationManager');
        //$validations = $validationManager->getValidations($user, 'confirmPasswordRequest');

        $userManager->sendRandomPassword($user);
        $this->flashMessenger()
                ->addSuccessMessage('message.user.passwordRequested');

        return $this->redirect()->toRoute('account/login');
    }

    /**
     * Allows the user to delete his account.
     */
    public function deleteAction()
    {
        $form = $this->getServiceLocator()->get('FormElementManager')
            ->get('Vrok\Form\ConfirmationForm');

        $form->setData($this->request->getPost());
        if (!$this->request->isPost() || !$form->isValid()) {
            return $this->createViewModel(['form' => $form]);
        }

        $userManager = $this->getServiceLocator()->get('UserManager');
        $results = $userManager->deleteAccount();
        foreach($results as $message) {
            if (is_string($message)) {
                $this->flashMessenger()
                    ->addInfoMessage($message);
            }
        }

        // still logged in -> delete failed, show the messages
        if ($this->identity()) {
            return $this->createViewModel([]);
        }

        $this->flashMessenger()->addSuccessMessage('message.account.deleted');

        // we don't want an extra "deleted" page but want a page where flash
        // messages are shown
        return $this->redirect()->toRoute('account/login');
    }
}
