<?php
/**
 * @copyright   (c) 2014, Vrok
 * @license     http://customlicense CustomLicense
 * @author      Jakob Schumann <schumann@vrok.de>
 */

namespace AppBase\Controller;

use Vrok\Mvc\Controller\AbstractActionController;

/**
 * Holds validation management functions.
 */
class ValidationController extends AbstractActionController
{
    public function confirmAction()
    {
        $form = $this->getServiceLocator()->get('FormElementManager')
            ->get('AppBase\Form\Validation\ConfirmationForm');
        $viewModel = $this->createViewModel(array(
            'form' => $form,
        ));

        // the Form was posted, ignore the URL parameters
        if ($this->request->isPost()) {
            $isValid = $form->setData($this->request->getPost())->isValid();
            if (!$isValid) {
                return $viewModel;
            }

            $id = $form->get('id')->getValue();
            $token = $form->get('token')->getValue();
        }
        else {
            $id = $this->params('id');
            $token = $this->params('token');
            $form->setData(array('id' => $id, 'token' => $token));

            if (!$id || !$token) {
                if ($id xor $token) {
                    $this->flashMessenger()
                            ->addErrorMessage('message.validation.paramMissing');
                }
                return $viewModel;
            }
        }

        $validationManager = $this->getServiceLocator()->get('ValidationManager');
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
        $vm = $this->getServiceLocator()->get('ValidationManager');
        $vm->purgeValidations();
    }
}
