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

        $form = $this->getServiceLocator()->get('FormElementManager')
                ->get('Vrok\Form\ConfirmationForm');
        $form->setData($this->request->getPost());
        if (!$this->request->isPost() || !$form->isValid()) {
            return $this->createViewModel(array('form' => $form));
        }

        $userService = $this->getServiceLocator()->get('UserManager');
        $userService->logout();

        return $this->redirect()->toRoute('account/login');
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
        $viewModel = $this->createViewModel(array('form' => $form));

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
     * @return ViewModel
     */
    public function requestPasswordAction()
    {
        $form = $this->getServiceLocator()->get('FormElementManager')
                ->get('AppBase\Form\User\PasswordRequest');
        $form->setData($this->request->getPost());
        $viewModel = $this->createViewModel(array('form' => $form));

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
        //$validations = $validationManager->getValidations($user, 'password');

        $userManager->sendRandomPassword($user);
        $this->flashMessenger()
                ->addSuccessMessage('message.user.passwordRequested');

        return $this->redirect()->toRoute('account/login');
    }
}
