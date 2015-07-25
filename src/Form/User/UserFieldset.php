<?php

/**
 * @copyright   (c) 2014, Vrok
 * @license     http://customlicense CustomLicense
 * @author      Jakob Schumann <schumann@vrok.de>
 */

namespace AppBase\Form\User;

use Vrok\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;

/**
 * Form to create or edit an user.
 */
class UserFieldset extends Fieldset implements InputFilterProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function init()
    {
        $this->setName('user');

        $ur = $this->getEntityManager()
                ->getRepository('Vrok\Entity\User');

        $this->add($ur->getFormElementDefinition('email'));

        // the username is not required for admins, it defaults to the email if empty
        $username = $ur->getFormElementDefinition('username');
        unset($username['attributes']['required']);
        $username['options']['description'] =
                \Vrok\Doctrine\Common::getEntityTranslationString('Vrok\Entity\User',
                        'username').'.description';
        $this->add($username);

        // the displayName is not required for admins, it defaults to the username if empty
        $displayName                           = $ur->getFormElementDefinition('displayName');
        $displayName['options']['description'] =
                \Vrok\Doctrine\Common::getEntityTranslationString('Vrok\Entity\User',
                        'displayName').'.description';
        $this->add($displayName);

        // Admin sees the password he enters for the user,
        // it is not required - to allow setting a random password
        $password         = $ur->getFormElementDefinition('password');
        $password['type'] = 'Zend\Form\Element\Text';
        unset($password['attributes']['required']);
        $this->add($password);

        $this->add([
            'type'    => 'Zend\Form\Element\Checkbox',
            'name'    => 'setRandomPassword',
            'options' => [
                'label'           => 'form.user.setRandomPassword.label',
                'unchecked_value' => 0,
                'checked_value'   => 1,
            ],
        ]);

        $this->add($ur->getFormElementDefinition('groups'));

        // Admin created users are active per default
        $this->add($ur->getFormElementDefinition('isActive'));
        $this->get('isActive')->setValue(true);

        // Admin created users are validated per default
        $this->add($ur->getFormElementDefinition('isValidated'));
        $this->get('isValidated')->setValue(true);

        $this->add($ur->getFormElementDefinition('createdAt'));
        $this->add($ur->getFormElementDefinition('lastLogin'));
        $this->add($ur->getFormElementDefinition('lastSession'));

        // the ID field is hidden, we need it for the UniqueObject validator
        $this->add($ur->getFormElementDefinition('id'));
    }

    /**
     * {@inheritDoc}
     */
    public function getInputFilterSpecification()
    {
        $ur = $this->getEntityManager()
                ->getRepository('Vrok\Entity\User');

        $spec = $ur->getInputFilterSpecification();

        // this is not added per default in the repository class as other forms like the
        // login or passwordRequest form don't need it
        $spec['email']['validators']['uniqueObject'] = [
            'name'    => 'DoctrineModule\Validator\UniqueObject',
            'options' => [
                'use_context'       => true,
                'object_repository' => $ur,
                'fields'            => 'email',
                'object_manager'    => $this->getEntityManager(),
                'messages'          => [
                    \DoctrineModule\Validator\UniqueObject::ERROR_OBJECT_NOT_UNIQUE => \Vrok\Doctrine\Common::getEntityTranslationString('Vrok\User\Entity', 'email').'.notUnique',
                ],
            ],
        ];

        // this is not added per default in the repository class as other forms like the
        // login or passwordRequest form don't need it
        $spec['username']['validators']['uniqueObject'] = [
            'name'    => 'DoctrineModule\Validator\UniqueObject',
            'options' => [
                'use_context'       => true,
                'object_repository' => $ur,
                'fields'            => 'username',
                'object_manager'    => $this->getEntityManager(),
                'messages'          => [
                    \DoctrineModule\Validator\UniqueObject::ERROR_OBJECT_NOT_UNIQUE => \Vrok\Doctrine\Common::getEntityTranslationString('Vrok\User\Entity', 'username').'.notUnique',
                ],
            ],
        ];

        // allow empty usernames for admin as it defaults to the email
        $spec['username']['required']   = false;
        $spec['username']['allowEmpty'] = true;
        unset($spec['username']['validators']['notEmpty']);

        // allow empty passwords for admin as the can set a random password
        $spec['password']['required']   = false;
        $spec['password']['allowEmpty'] = true;
        unset($spec['password']['validators']['notEmpty']);

        return $spec;
    }
}
