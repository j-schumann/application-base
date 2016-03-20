<?php

/**
 * @copyright   (c) 2014, Vrok
 * @license     http://customlicense CustomLicense
 * @author      Jakob Schumann <schumann@vrok.de>
 */

namespace AppBase\Form\User;

use Vrok\Form\Form;
use Vrok\Validator\PasswordStrength;
use Zend\InputFilter\InputFilterProviderInterface;

/**
 * Asks the user to enter his current password, the new password and repeat it to avoid typos.
 */
class PasswordChange extends Form implements InputFilterProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function init()
    {
        // csrf first so error message appears above the form
        $this->addCsrfElement('csrfPasswordChange');

        $ur = $this->getEntityManager()
                ->getRepository('Vrok\Entity\User');

        $passwordDefinition                     = $ur->getFormElementDefinition('password');
        $passwordDefinition['options']['label'] = 'form.user.currentPassword.label';
        $this->add($passwordDefinition);

        $passwordDefinition['name']                = 'newPassword';
        $passwordDefinition['options']['label']    = 'form.user.newPassword.label';
        $passwordDefinition['attributes']['class'] = 'rate-password';
        $this->add($passwordDefinition);

        unset($passwordDefinition['attributes']['class']);
        $passwordDefinition['name']             = 'passwordRepeat';
        $passwordDefinition['options']['label'] = 'form.user.passwordRepeat.label';
        $this->add($passwordDefinition);

        $this->add([
            'name'       => 'confirm',
            'attributes' => [
                'type'  => 'submit',
                'value' => 'form.submit',
            ],
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function getInputFilterSpecification()
    {
        $ur = $this->getEntityManager()
                ->getRepository('Vrok\Entity\User');

        $oldPasswordSpec = $ur->getInputSpecification('password');

        $userManager = $this->getServiceLocator()->getServiceLocator()
                ->get(\Vrok\Service\UserManager::class);
        $thresholds = $userManager->getPasswordStrengthThresholds();

        $newPasswordSpec                                   = $oldPasswordSpec;
        $newPasswordSpec['name']                           = 'newPassword';
        $newPasswordSpec['validators']['passwordStrength'] = [
            'name'    => PasswordStrength::class,
            'options' => [
                'threshold' => $thresholds['ok'],
                'messages'  => [
                    PasswordStrength::TOO_WEAK => 'validate.user.password.tooWeak',
                ],
            ],
        ];

        $repeatSpec = [
            'name'     => 'passwordRepeat',
            'required' => true,
            'filters'  => [
                ['name' => 'StringTrim'],
            ],
            'validators' => [
                $ur->getFormHelper()->getNotEmptyValidatorSpecification(),
                [
                    'name'    => 'Identical',
                    'options' => [
                        'token'    => 'newPassword',
                        'messages' => [
                            \Zend\Validator\Identical::NOT_SAME => 'validate.user.passwordRepeat.notSame',
                        ],
                    ],
                ],
            ],
        ];

        return [
            $oldPasswordSpec,
            $newPasswordSpec,
            $repeatSpec,
        ];
    }
}
