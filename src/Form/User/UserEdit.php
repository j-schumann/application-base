<?php

/**
 * @copyright   (c) 2014, Vrok
 * @license     http://customlicense CustomLicense
 * @author      Jakob Schumann <schumann@vrok.de>
 */

namespace AppBase\Form\User;

use Vrok\Form\Form;

/**
 * Allows admins to edit an user.
 */
class UserEdit extends Form
{
    /**
     * {@inheritDoc}
     */
    public function init()
    {
        // csrf first so error message appears above the form
        $this->addCsrfElement('csrfUserEdit');

        $this->add([
            'type'    => 'AppBase\Form\User\UserFieldset',
            'options' => [
                'use_as_base_fieldset' => true,
            ],
        ]);

        // Description & labels for this use case
        $this->get('user')->get('password')->setLabel('form.user.newPassword.label');
        $this->get('user')->get('password')->setOption('description',
            'form.user.newPassword.description');
        $this->get('user')->get('setRandomPassword')->setOption('description',
            'form.user.setRandomPassword.description');

        $this->add([
            'name'       => 'submit',
            'attributes' => [
                'type'  => 'submit',
                'value' => 'form.submit',
            ],
        ]);

        // only validate the fields we used, else the InputFilter would return empty
        // values for the fields we have not used, e.g. createdAt, and those would
        // throw errors when given to the setters.
        // @todo necessary?
        $this->setValidationGroup([
            'user' => [
                'id', 'email', 'username', 'displayName', 'password', 'groups',
                'isActive', 'isValidated', 'setRandomPassword',
            ],
        ]);
    }
}
