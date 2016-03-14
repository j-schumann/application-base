<?php

/**
 * @copyright   (c) 2014, Vrok
 * @license     http://customlicense CustomLicense
 * @author      Jakob Schumann <schumann@vrok.de>
 */

namespace AppBase\Controller;

use Vrok\Mvc\Controller\AbstractActionController;
use Zend\Http\Response;

/**
 * Holds validation management functions.
 */
class ValidationController extends AbstractActionController
{
    /**
     * Entry point for users to confirm a validation request. Accessed by opening
     * the link sent via email, either without parameters (no direct validation)
     * or with parameters in the URL (direct validation).
     * Redirects the user to the page the validation event returned if the
     * confirmation was successful.
     *
     * @return ViewModel|Response
     */
    public function confirmAction()
    {
        $form = $this->getServiceLocator()->get('FormElementManager')
            ->get(\AppBase\Form\Validation\ConfirmationForm::class);
        $viewModel = ['form' => $form];

        // the Form was posted, ignore the URL parameters
        if ($this->request->isPost()) {
            $isValid = $form->setData($this->request->getPost())->isValid();
            if (!$isValid) {
                return $viewModel;
            }

            $id    = $form->get('id')->getValue();
            $token = $form->get('token')->getValue();
        } else {
            $id    = $this->params('id');
            $token = $this->params('token');
            $form->setData(['id' => $id, 'token' => $token]);

            if (!$id || !$token) {
                if ($id xor $token) {
                    $this->flashMessenger()
                            ->addErrorMessage('message.validation.paramMissing');
                }

                return $viewModel;
            }
        }

        $validationManager = $this->getServiceLocator()->get('Vrok\Service\ValidationManager');
        /* @var $validationManager \Vrok\Service\ValidationManager */

        $result = $validationManager->confirmValidation($id, $token);
        if ($result instanceof Response) {
            return $result;
        }

        return $viewModel;
    }

    /**
     * Console route to allow purging via CLI or cron job.
     */
    public function purgeAction()
    {
        $vm = $this->getServiceLocator()->get('Vrok\Service\ValidationManager');
        $vm->purgeValidations();
    }
}
