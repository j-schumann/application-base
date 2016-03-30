<?php

/**
 * @copyright   (c) 2014-16, Vrok
 * @license     MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @author      Jakob Schumann <schumann@vrok.de>
 */

namespace AppBase\SlmQueue\Job;

use Vrok\Service\Email;
use Vrok\Service\UserManager;
use Vrok\Service\ValidationManager;
use Vrok\SlmQueue\Job\AbstractJob;

/**
 * ends a validation link to the users email to confirm the setting of a new
 * password.
 */
class SendPasswordRequest extends AbstractJob
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $payload     = $this->getContent();
        $userManager = $this->getServiceLocator()->get(UserManager::class);
        $user        = $userManager->getUserRepository()->find($payload['userId']);
        if (!$user) {
            throw new \RuntimeException('User '.$payload['userId'].' not found!');
        }

        /* @var $vm ValidationManager */
        $vm = $this->getServiceLocator()->get(ValidationManager::class);
        $em = $this->getEntityManager();

        // a user can only have one active password request at a time, else
        // brute force would get easier
        $old = $vm->getValidations($user, UserManager::VALIDATION_PASSWORD);
        foreach($old as $oldValidation) {
            $em->remove($oldValidation);
        }

        $validation = $vm->createValidation(
            UserManager::VALIDATION_PASSWORD,
            $user
        );

        // flush here, we need the validation->id for the confirmation URL
        $em->flush();

        $partial = $this->getServiceLocator()->get('viewhelpermanager')->get('partial');
        $html    = $partial('app-base/partials/mail/passwordRequest', [
            'user'              => $user,
            'validation'        => $validation,
            'confirmationUrl'   => $vm->getConfirmationUrl($validation),
            'confirmationBase'  => $vm->getConfirmationUrl(),
            'validationTimeout' => $vm->getTimeout(UserManager::VALIDATION_PASSWORD),
        ]);
        $text = $partial('app-base/partials/mail/passwordRequestText', [
            'user'              => $user,
            'validation'        => $validation,
            'confirmationUrl'   => $vm->getConfirmationUrl($validation),
            'confirmationBase'  => $vm->getConfirmationUrl(),
            'validationTimeout' => $vm->getTimeout(UserManager::VALIDATION_PASSWORD),
        ]);

        $emailService = $this->getServiceLocator()->get(Email::class);
        $mail         = $emailService->createMail();
        $mail->addTo($user->getEmail());
        $mail->setSubject('mail.passwordRequest.subject');

        $htmlPart = $mail->getHtmlPart($html, false, false);
        $textPart = $mail->getTextPart($text, false, true);
        $mail->setAlternativeBody($textPart, $htmlPart);

        $emailService->sendMail($mail);
    }
}
