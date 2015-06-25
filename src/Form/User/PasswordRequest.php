<?php
/**
 * @copyright   (c) 2014, Vrok
 * @license     http://customlicense CustomLicense
 * @author      Jakob Schumann <schumann@vrok.de>
 */

namespace AppBase\Form\User;

use \Vrok\Form\Form;
use \Zend\InputFilter\InputFilterProviderInterface;

/**
 * Asks the user for his username (or email) to reset his password.
 */
class PasswordRequest extends Form implements InputFilterProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function init()
    {
        // csrf first so error message appears above the form
        $this->addCsrfElement('csrfPasswordRequest');

        $ur = $this->getEntityManager()
                ->getRepository('Vrok\Entity\User');

        $this->add($ur->getFormElementDefinition('username'));

        $this->add(array(
            'name'       => 'request-password',
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

        return array(
            $ur->getInputSpecification('username'),
        );
    }
}
