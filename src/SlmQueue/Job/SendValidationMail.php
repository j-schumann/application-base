<?php

/**
 * @copyright   (c) 2014-16, Vrok
 * @license     MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @author      Jakob Schumann <schumann@vrok.de>
 */

namespace AppBase\SlmQueue\Job;

use SlmQueue\Job\AbstractJob;
use Vrok\Service\Email;
use Vrok\Service\UserManager;
use Vrok\Service\ValidationManager;
use Zend\View\Helper\Partial;

/**
 * Executed after a user registered, sends a validation link to the users email
 * to confirm it is valid.
 */
class SendValidationMail extends AbstractJob
{
    /**
     * @var ValidationManager
     */
    protected $validationManager = null;

    /**
     * @var Email
     */
    protected $emailService = null;

    /**
     * @var UserManager
     */
    protected $userManager = null;

    /**
     * @var Partial
     */
    protected $partialHelper = null;

    /**
     * Class constructor - stores the dependencies.
     *
     * @param ValidationManager $validationManager
     * @param Email $emailService
     * @param UserManager $userManager
     * @param Partial $partialHelper
     */
    public function __construct(
        ValidationManager $validationManager,
        Email $emailService,
        UserManager $userManager,
        Partial $partialHelper
    ) {
        $this->validationManager = $validationManager;
        $this->emailService = $emailService;
        $this->userManager = $userManager;
        $this->partialHelper = $partialHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $payload = $this->getContent();
        $user = $this->userManager->getUserRepository()->find($payload['userId']);
        if (!$user) {
            throw new \RuntimeException('User '.$payload['userId'].' not found!');
        }

        $validation = $this->validationManager->createValidation(
            UserManager::VALIDATION_USER,
            $user
        );

        // flush here, we need the validation->id for the confirmation URL
        $this->userManager->getEntityManager()->flush();

        // the helper isn't __invoked but $this is __called -> "dereference"
        $partial = $this->partialHelper;
        $html    = $partial('app-base/partials/mail/userValidation', [
            'user'              => $user,
            'validation'        => $validation,
            'confirmationUrl'   => $this->validationManager->getConfirmationUrl($validation),
            'confirmationBase'  => $this->validationManager->getConfirmationUrl(),
            'validationTimeout' => $this->validationManager->getTimeout(UserManager::VALIDATION_USER),
        ]);
        $text = $partial('app-base/partials/mail/userValidationText', [
            'user'              => $user,
            'validation'        => $validation,
            'confirmationUrl'   => $this->validationManager->getConfirmationUrl($validation),
            'confirmationBase'  => $this->validationManager->getConfirmationUrl(),
            'validationTimeout' => $this->validationManager->getTimeout(UserManager::VALIDATION_USER),
        ]);

        $mail         = $this->emailService->createMail();
        $mail->addTo($user->getEmail());
        $mail->setSubject('mail.userValidation.subject');

        $htmlPart = $mail->getHtmlPart($html, false, false);
        $textPart = $mail->getTextPart($text, false, true);
        $mail->setAlternativeBody($textPart, $htmlPart);

        $this->emailService->sendMail($mail);
    }
}
