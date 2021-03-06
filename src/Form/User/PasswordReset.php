<?php

/**
 * @copyright   (c) 2014-16, Vrok
 * @license     MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @author      Jakob Schumann <schumann@vrok.de>
 */

namespace AppBase\Form\User;

use Vrok\Form\Form;
use Vrok\Service\UserManager;
use Vrok\Validator\PasswordStrength;
use Zend\InputFilter\InputFilterProviderInterface;

/**
 * Asks the user to enter his new password and repeat it to avoid typos.
 * Used after a passwordRequest was confirmed.
 */
class PasswordReset extends Form implements InputFilterProviderInterface
{
    /**
     * @var UserManager
     */
    protected $userManager = null;

    /**
     * Sets the UM instance to use.
     *
     * @param UserManager $um
     */
    public function setUserManager(UserManager $um)
    {
        $this->userManager = $um;
    }

    /**
     * {@inheritDoc}
     */
    public function init()
    {
        // csrf first so error message appears above the form
        $this->addCsrfElement('csrfPasswordReset');

        $ur = $this->getEntityManager()
                ->getRepository('Vrok\Entity\User');

        $passwordDefinition                     = $ur->getFormElementDefinition('password');
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

        $thresholds = $this->userManager->getPasswordStrengthThresholds();

        $newPasswordSpec = $ur->getInputSpecification('password');
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
            $newPasswordSpec,
            $repeatSpec,
        ];
    }
}
