<?php

/**
 * @copyright   (c) 2014, Vrok
 * @license     http://customlicense CustomLicense
 * @author      Jakob Schumann <schumann@vrok.de>
 */

namespace AppBase\Form\User;

use Vrok\Form\Form;

/**
 * Form to create or edit a user group.
 */
class Group extends Form
{
    /**
     * {@inheritDoc}
     */
    public function init()
    {
        $this->add([
            'type'    => 'AppBase\Form\User\GroupFieldset',
            'options' => [
                'use_as_base_fieldset' => true,
            ],
        ]);

        $this->add([
            'name'       => 'submit',
            'attributes' => [
                'type'  => 'submit',
                'value' => 'form.submit',
                'id'    => 'submit',
            ],
        ]);
    }
}
