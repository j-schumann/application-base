<?php
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

        $this->add(array(
            'type'    => 'Fieldset',
            'name'    => 'userFilter',
            'options' => array(
                'label' => 'form.user.userFilter.label',
            ),
            'elements' => array(
                 array(
                    'spec' => array(
                        'type'    => 'Zend\Form\Element\Text',
                        'name'    => 'nameSearch',
                        'options' => array(
                            'label' => 'form.user.nameSearch.label'
                        ),
                        'attributes' => array(
                            'maxlength' => 255,
                        ),
                    ),
                ),
                array(
                    'spec' => array(
                        'type' => 'Vrok\Form\Element\ObjectSelect',
                        'name' => 'groupFilter',
                        'options' => array(
                            'object_manager'     => $this->getEntityManager(),
                            'target_class'       => 'Vrok\Entity\Group',
                            'property'           => 'name',
                            'label'              => 'form.user.groupFilter.label',

                            // display the empty element even if the relation is required
                            // to force the user to select one and not only use the first
                            // one that is automatically selected
                            'display_empty_item' => true,
                        ),
                        'attributes' => array(
                            'multiple' => false,
                        ),
                    ),
                ),
                array(
                    'spec' => array(
                        'name'       => 'submit',
                        'attributes' => array(
                            'type'  => 'submit',
                            'value' => 'form.submit',
                            'id'    => 'submit',
                        ),
                    )
                ),
            ),
        ));
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
