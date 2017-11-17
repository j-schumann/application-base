<?php

/**
 * @copyright   (c) 2014-16, Vrok
 * @license     MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @author      Jakob Schumann <schumann@vrok.de>
 */

namespace AppBase\Form\Validation;

use Vrok\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;

/**
 * Shows inputs for validation ID and token, used if the confirmation via URL failed,
 * e.g. parameters were incomplete.
 */
class ConfirmationForm extends Form implements InputFilterProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function init()
    {
        // csrf first so error message appears above the form
        $this->addCsrfElement('csrfValidation');

        $vr = $this->getEntityManager()
                ->getRepository('Vrok\Entity\Validation');

        // the autoincrement is not required by default
        $id                           = $vr->getFormElementDefinition('id');
        $id['attributes']['required'] = 'required';
        $this->add($id);

        $this->add($vr->getFormElementDefinition('token'));

        $this->add([
            'name'       => 'confirm',
            'attributes' => [
                'type'  => 'submit',
                'value' => 'form.validation.confirm.label',
            ],
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function getInputFilterSpecification()
    {
        $vr = $this->getEntityManager()
                ->getRepository('Vrok\Entity\Validation');

        // the autoincrement is not required by default
        $id             = $vr->getInputSpecification('id');
        $id['required'] = true;
        \Vrok\Stdlib\ArrayUtils::array_unshift_assoc(
            $id['validators'],
            'notEmpty',
            $vr->getFormHelper()->getNotEmptyValidatorSpecification()
        );

        return [
            $id,
            $vr->getInputSpecification('token'),
        ];
    }
}
