<?php

/**
 * @copyright   (c) 2014-16, Vrok
 * @license     MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @author      Jakob Schumann <schumann@vrok.de>
 */

namespace AppBase\SlmQueue;

use Vrok\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;

/**
 * Form to enter the max execution time to recover jobs in the queue.
 */
class RecoverForm extends Form implements InputFilterProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->addCsrfElement('csrfRecover');

        $this->add([
            'type'    => 'Zend\Form\Element\Text',
            'name'    => 'executionTime',
            'options' => [
                'label' => 'Maximum execution time in minutes:',
            ],
            'attributes' => [
                'maxlength' => 3,
                'value'     => 30,
            ],
        ]);

        $this->add([
            'name'       => 'confirm',
            'attributes' => [
                'type'  => 'submit',
                'value' => 'Submit',
            ],
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function getInputFilterSpecification()
    {
        return [
            'executionTime' => [
                'required'   => true,
                'allowEmpty' => false,
                'filters'    => [
                    ['name' => 'Zend\Filter\StringTrim'],
                ],
                'validators' => [
                    [
                        'name'    => 'Zend\Validator\Digits',
                        'options' => [
                            'messages' => [
                                \Zend\Validator\Digits::NOT_DIGITS => \Vrok\Doctrine\FormHelper::ERROR_NOTINT,
                            ],
                        ],
                    ],
                    [
                        'name'    => 'Zend\Validator\StringLength',
                        'options' => [
                            'max'      => 3,
                            'messages' => [
                                \Zend\Validator\StringLength::TOO_LONG => \Vrok\Doctrine\FormHelper::ERROR_TOOLONG,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
