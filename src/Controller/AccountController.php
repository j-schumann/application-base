<?php

/**
 * @copyright   (c) 2014-16, Vrok
 * @license     MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @author      Jakob Schumann <schumann@vrok.de>
 */

namespace AppBase\Controller;

use Vrok\Entity\User;
use Vrok\Service\UserManager;
use Vrok\Mvc\Controller\AbstractActionController;
use Zend\Session\Container as SessionContainer;

/**
 * Handles the basic actions for the users account like login/logout/password cahnge etc.
 */
class AccountController extends AbstractActionController
{
    /**
     * Retrieve the userManager instance.
     *
     * @return UserManager
     */
    protected function getUserManager()
    {
        return $this->getServiceLocator()->get(UserManager::class);
    }

    /**
     * Account overview for the logged in User.
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        if (!$this->identity()) {
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
        $um   = $this->getUserManager();

        if ($this->identity()) {
            return $this->redirect()->toRoute($um->getPostLoginRoute());
        }

        $form = $this->getServiceLocator()->get('FormElementManager')
                ->get(\AppBase\Form\User\Login::class);
        $form->setData($this->request->getPost());
        $viewModel = ['form' => $form];

        if (!$this->request->isPost() || !$form->isValid()) {
            return $this->createViewModel($viewModel);
        }

        $data = $form->getData();

        // we do not use the Zend\Authentication\Validator directly in the form
        // as this would lead to a successful login even when the CSRF failed
        $result = $um->login($data['username'], $data['password']);
        if (!$result instanceof User) {
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
        if (!$this->identity()) {
            return $this->redirect()->toRoute('home');
        }

        // directly logout without confirmation if the session id is given
        // to protect against simple CSRF
        if ($this->params()->fromQuery('s') !== session_id()) {
            $form = $this->getServiceLocator()->get('FormElementManager')
                ->get(\Vrok\Form\ConfirmationForm::class);
            $form->setData($this->request->getPost());
            if (!$this->request->isPost() || !$form->isValid()) {
                return $this->createViewModel(['form' => $form]);
            }
        }

        $userService = $this->getUserManager();
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
                ->get(\AppBase\Form\User\DisplayNameChange::class);

        $um   = $this->getUserManager();
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
     * BjyGuarded: users only.
     *
     * @return ViewModel
     */
    public function changePasswordAction()
    {
        $form = $this->getServiceLocator()->get('FormElementManager')
                ->get(\AppBase\Form\User\PasswordChange::class);
        $form->setData($this->request->getPost());
        $viewModel = $this->createViewModel(['form' => $form]);

        if (!$this->request->isPost() || !$form->isValid()) {
            return $viewModel;
        }

        $data        = $form->getData();
        $userManager = $this->getUserManager();
        $user        = $this->identity();

        if (!$user->checkPassword($data['password'])) {
            $form->setElementMessage('password', 'validate.user.wrongPassword');

            return $viewModel;
        }

        $user->setPassword($data['newPassword']);
        $userManager->getEntityManager()->flush();

        // password changed -> logout on other devices
        // @todo via Event lösen, changePassword.post oder user.change etc
        $userManager->clearUserLoginKeys($user);

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
                ->get(\AppBase\Form\User\PasswordRequest::class);
        $form->setData($this->request->getPost());
        $viewModel = $this->createViewModel(['form' => $form]);

        if (!$this->request->isPost() || !$form->isValid()) {
            return $viewModel;
        }

        $data        = $form->getData();
        $userManager = $this->getUserManager();

        $user = $userManager->getUserByIdentity($data['username']);
        if (!$user) {
            $form->setElementMessage('username', 'validate.user.identityNotFound');

            return $viewModel;
        }

        // do not directly change the password but send a validation and then
        // show a form for the new password (+confirmation).
        $this->queue('jobs')->push('AppBase\SlmQueue\Job\SendPasswordRequest', [
            'userId' => $user->getId(),
        ]);

        $this->flashMessenger()
                ->addSuccessMessage('message.user.passwordRequested');

        return $this->redirect()->toRoute('account/login');
    }

    /**
     * Allows the user to set a new password after confirming a pw request.
     *
     * @return ViewModel|Response
     */
    public function resetPasswordAction()
    {
        if ($this->identity()) {
            return $this->redirect()->toRoute('account');
        }

        $form = $this->getServiceLocator()->get('FormElementManager')
                ->get(\AppBase\Form\User\PasswordReset::class);
        $form->setData($this->request->getPost());
        $viewModel = $this->createViewModel(['form' => $form]);

        $sessionContainer = new SessionContainer(UserManager::class);
        if (!$sessionContainer['passwordRequestIdentity']) {
            return $this->redirect()->toRoute('account/login');
        }

        $userManager = $this->getServiceLocator()->get(UserManager::class);

        /* @var $user User */
        $user = $userManager->getUserByIdentity($sessionContainer['passwordRequestIdentity']);
        if (!$user) {
            $this->flashMessenger()
                    ->addErrorMessage('validate.user.identityNotFound');

            return $this->redirect()->toRoute('account/login');
        }

        if (!$this->request->isPost() || !$form->isValid()) {
            return $viewModel;
        }

        $data = $form->getData();
        $user->setPassword($data['newPassword']);
        $userManager->getEntityManager()->flush();
        $sessionContainer['passwordRequestIdentity'] = null;

        $this->flashMessenger()
                ->addSuccessMessage('message.user.passwordChanged');

        return $this->redirect()->toRoute('account/login');
    }

    /**
     * Allows the user to delete his account.
     */
    public function deleteAction()
    {
        $form = $this->getServiceLocator()->get('FormElementManager')
            ->get(\Vrok\Form\ConfirmationForm::class);

        $form->setData($this->request->getPost());
        if (!$this->request->isPost() || !$form->isValid()) {
            return $this->createViewModel(['form' => $form]);
        }

        $userManager = $this->getServiceLocator()->get(UserManager::class);
        $results     = $userManager->deleteAccount();
        foreach ($results as $message) {
            if (is_string($message)) {
                $this->flashMessenger()
                    ->addInfoMessage($message);
            }
        }

        // still logged in -> delete failed, show the messages
        if ($this->identity()) {
            return $this->createViewModel(['form' => $form]);
        }

        $this->flashMessenger()->addSuccessMessage('message.account.deleted');

        // we don't want an extra "deleted" page but want a page where flash
        // messages are shown
        return $this->redirect()->toRoute('account/login');
    }

    /**
     * Allows the user to make some general settings.
     *
     * @return ViewModel
     */
    public function settingsAction()
    {
        $user = $this->identity();
        /* @var $user \Vrok\Entity\User */

        $form = $this->getServiceLocator()->get('FormElementManager')
                ->get('AppBase\Form\User\Settings');
        $form->bind($this->identity());

        $viewModel = $this->createViewModel([
            'form' => $form,
        ]);

        if (!$this->request->isPost()) {
            return $viewModel;
        }

        $isValid = $form->setData($this->request->getPost())->isValid();
        if (!$isValid) {
            return $viewModel;
        }

        // we can not use a callback validator, it won't be called when
        // allowEmpty == true
        if (empty($user->getHttpNotificationUser())
                xor empty($user->getHttpNotificationPw())
        ) {
            $form->get('user')->setElementMessage('httpNotificationUser',
                        'validate.user.httpNotificationAuth.incomplete');
            return $viewModel;
        }

        $this->getUserManager()->getEntityManager()->flush();
        $this->flashMessenger()
                ->addSuccessMessage('message.account.settings.edited');

        return $this->redirect()->toRoute('account');
    }
}
