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
        $this->add(array(
            'type'    => 'AppBase\Form\User\GroupFieldset',
            'options' => array(
                'use_as_base_fieldset' => true
            )
        ));

        $this->add(array(
            'name'       => 'submit',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'form.submit',
                'id'    => 'submit',
            ),
        ));
    }
}
