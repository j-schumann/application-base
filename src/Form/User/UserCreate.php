<?php
/**
 * @copyright   (c) 2014, Vrok
 * @license     http://customlicense CustomLicense
 * @author      Jakob Schumann <schumann@vrok.de>
 */

namespace AppBase\Form\User;

use \Vrok\Form\Form;

/**
 * Allows admins to create a new user.
 */
class UserCreate extends Form
{
    /**
     * {@inheritDoc}
     */
    public function init()
    {
        // csrf first so error message appears above the form
        $this->addCsrfElement('csrfUserCreate');

        $this->add(array(
            'type'    => 'AppBase\Form\User\UserFieldset',
            'options' => array(
                'use_as_base_fieldset' => true
            )
        ));

        // Description for this use case
        $this->get('user')->get('setRandomPassword')->setOption('description',
            'form.user.createRandomPassword.description');

        // random password is the default selection for new users, the admin should
        // probably not know the password
        $this->get('user')->get('setRandomPassword')->setValue(true);

        $this->add(array(
            'name'       => 'submit',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'form.submit',
            )
        ));

        // only validate the fields we used, else the InputFilter would return empty
        // values for the fields we have not used, e.g. createdAt, and those would
        // throw errors when given to the setters.
        $this->setValidationGroup(array(
            'user' => array(
                'email', 'username', 'displayName', 'password', 'groups',
                'isActive', 'isValidated', 'setRandomPassword'
            ),
        ));
    }
}