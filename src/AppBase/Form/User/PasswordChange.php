<?php

namespace AppBase\Form\User;

use \Vrok\Form\Form;
use \Zend\InputFilter\InputFilterProviderInterface;

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

        $passwordDefinition = $ur->getFormElementDefinition('password');
        $passwordDefinition['options']['label'] = 'form.user.currentPassword.label';
        $this->add($passwordDefinition);

        $passwordDefinition['name'] = 'newPassword';
        $passwordDefinition['options']['label'] = 'form.user.newPassword.label';
        $this->add($passwordDefinition);

        $passwordDefinition['name'] = 'passwordRepeat';
        $passwordDefinition['options']['label'] = 'form.user.passwordRepeat.label';
        $this->add($passwordDefinition);

        $this->add(array(
            'name'       => 'confirm',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'form.submit',
            )
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function getInputFilterSpecification()
    {
        $ur = $this->getEntityManager()
                ->getRepository('Vrok\Entity\User');

        $oldPasswordSpec = $ur->getInputSpecification('password');

        $newPasswordSpec = $oldPasswordSpec;
        $newPasswordSpec['name'] = 'newPassword';

        $repeatSpec = array(
            'name'     => 'passwordRepeat',
            'required' => true,
            'filters'  => array(
                array('name' => 'StringTrim')
            ),
            'validators' => array(
                $ur->getFormHelper()->getNotEmptyValidatorSpecification(),
                array(
                    'name'    => 'Identical',
                    'options' => array(
                        'token'    => 'newPassword',
                        'messages' => array(
                            \Zend\Validator\Identical::NOT_SAME
                                => 'validate.user.passwordRepeat.notSame',
                        ),
                    ),
                ),
            ),
        );

        return array(
            $oldPasswordSpec,
            $newPasswordSpec,
            $repeatSpec,
        );
    }
}
