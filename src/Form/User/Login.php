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
 * Asks the user for his username (or email) and his password.
 */
class Login extends Form implements InputFilterProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function init()
    {
        // csrf first so error message appears above the form
        $this->addCsrfElement('csrfLogin');

        $ur = $this->getEntityManager()
                ->getRepository('Vrok\Entity\User');

        // we use a custom label here to allow sites that do not support a
        // username to translate it as "Email" or sites that allow both to
        // translate as "Email or Username".
        $username = $ur->getFormElementDefinition('username');
        $username['options']['label'] = 'form.user.loginname.label';
        $this->add($username);

        $this->add($ur->getFormElementDefinition('password'));

        $this->add(array(
            'name'       => 'login',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'form.user.login.label',
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

        // do not use the authValidator here as he logs the user in even if the
        // form is not valid, e.g. the CSRF fails
        //$userManager = $this->getServiceLocator()->getServiceLocator()
        //        ->get('UserManager');
        //$passwordSpec = $ur->getInputSpecification('password');
        //$passwordSpec['validators']['auth'] = $userManager->getAuthValidator();

        return array(
            $ur->getInputSpecification('username'),
            $ur->getInputSpecification('password'),
        );
    }
}
