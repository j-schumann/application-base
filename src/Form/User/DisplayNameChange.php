<?php

/**
 * @copyright   (c) 2014-16, Vrok
 * @license     http://customlicense CustomLicense
 * @author      Jakob Schumann <schumann@vrok.de>
 */

namespace AppBase\Form\User;

use Vrok\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;

/**
 * Asks the user to enter his new displayName.
 */
class DisplayNameChange extends Form implements InputFilterProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function init()
    {
        // csrf first so error message appears above the form
        $this->addCsrfElement('csrfDisplayNameChange');

        $ur = $this->getEntityManager()
                ->getRepository('Vrok\Entity\User');

        $displayName                           = $ur->getFormElementDefinition('displayName');
        $displayName['attributes']['required'] = 'required';
        $this->add($displayName);

        // for uniqueObject validators
        $this->add($ur->getFormElementDefinition('id'));

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

        $displayName               = $ur->getInputSpecification('displayName');
        $displayName['allowEmpty'] = false;
        $displayName['required']   = true;
        $displayName['validators']['notEmpty']
                = $ur->getFormHelper()->getNotEmptyValidatorSpecification();

        return [
            $displayName,
        ];
    }
}
