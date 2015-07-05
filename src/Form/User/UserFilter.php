<?php
/**
 * @copyright   (c) 2014, Vrok
 * @license     http://customlicense CustomLicense
 * @author      Jakob Schumann <schumann@vrok.de>
 */

namespace AppBase\Form\User;

use Vrok\Form\Form;

/**
 * Form to filter / search user accounts.
 */
class UserFilter extends Form
{
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->setName('user-filter');

        $this->add([
            'type'    => 'Fieldset',
            'name'    => 'userFilter',
            'options' => [
                'label' => 'form.user.userFilter.label',
            ],
            'elements' => [
                [
                    'spec' => [
                        'type'    => 'Zend\Form\Element\Text',
                        'name'    => 'nameSearch',
                        'options' => [
                            'label' => 'form.user.nameSearch.label'
                        ],
                        'attributes' => [
                            'maxlength' => 255,
                        ],
                    ],
                ],
                [
                    'spec' => [
                        'type'    => 'Vrok\Form\Element\ObjectSelect',
                        'name'    => 'groupFilter',
                        'options' => [
                            'object_manager'     => $this->getEntityManager(),
                            'target_class'       => 'Vrok\Entity\Group',
                            'property'           => 'name',
                            'label'              => 'form.user.groupFilter.label',

                            // display the empty element even if the relation is required
                            // to force the user to select one and not only use the first
                            // one that is automatically selected
                            'display_empty_item' => true,
                        ],
                        'attributes' => [
                            'multiple' => false,
                        ],
                    ],
                ],
                [
                    'spec' => [
                        'name'       => 'submit',
                        'attributes' => [
                            'type'  => 'submit',
                            'value' => 'form.submit',
                            'id'    => 'submit',
                        ],
                    ]
                ],
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getInputFilter()
    {
        $if = parent::getInputFilter();
        $if->get('userFilter')->get('groupFilter')->setAllowEmpty(true);
        $if->get('userFilter')->get('groupFilter')->setRequired(false);
        return $if;
    }
}
