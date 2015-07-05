<?php
/**
 * @copyright   (c) 2014, Vrok
 * @license     http://customlicense CustomLicense
 * @author      Jakob Schumann <schumann@vrok.de>
 */

namespace AppBase\SlmQueue\Job;

use Vrok\Service\Email;
use Vrok\Service\UserManager;
use Vrok\Service\ValidationManager;
use Vrok\SlmQueue\Job\AbstractJob;

/**
 * Executed after a user registered, sends a validation link to the users email
 * to confirm it is valid.
 */
class SendValidationMail extends AbstractJob
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $payload = $this->getContent();
        $userManager = $this->getServiceLocator()->get(UserManager::class);
        $user = $userManager->getUserRepository()->find($payload['userId']);
        if (!$user) {
            throw new \RuntimeException('User '.$payload['userId'].' not found!');
        }

        $vm = $this->getServiceLocator()->get(ValidationManager::class);
        $validation = $vm->createValidation(
            UserManager::VALIDATION_USER,
            $user
        );

        // flush here, we need the validation->id for the confirmation URL
        $this->getEntityManager()->flush();

        $partial = $this->getServiceLocator()->get('viewhelpermanager')->get('partial');
        $html = $partial('app-base/partials/mail/userValidation', [
            'user'              => $user,
            'validation'        => $validation,
            'confirmationUrl'   => $vm->getConfirmationUrl($validation),
            'confirmationBase'  => $vm->getConfirmationUrl(),
            'validationTimeout' => $vm->getTimeout(UserManager::VALIDATION_USER),
        ]);
        $text = $partial('app-base/partials/mail/userValidationText', [
            'user'              => $user,
            'validation'        => $validation,
            'confirmationUrl'   => $vm->getConfirmationUrl($validation),
            'confirmationBase'  => $vm->getConfirmationUrl(),
            'validationTimeout' => $vm->getTimeout(UserManager::VALIDATION_USER),
        ]);

        $emailService = $this->getServiceLocator()->get(Email::class);
        $mail = $emailService->createMail();
        $mail->addTo($user->getEmail());
        $mail->setSubject('mail.userValidation.subject');

        $htmlPart = $mail->getHtmlPart($html, false, false);
        $textPart = $mail->getTextPart($text, false, true);
        $mail->setAlternativeBody($textPart, $htmlPart);

        $emailService->sendMail($mail);
    }
}
