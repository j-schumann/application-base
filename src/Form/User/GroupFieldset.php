<?php

/**
 * @copyright   (c) 2014-16, Vrok
 * @license     MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @author      Jakob Schumann <schumann@vrok.de>
 */

namespace AppBase\Form\User;

use Vrok\Entity\Group;
use Vrok\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;

/**
 * Form to create or edit an user group.
 */
class GroupFieldset extends Fieldset implements InputFilterProviderInterface
{
    /**
     * The repository class could not know which group we want to update
     * so we need to set it here to only get allowed parent groups.
     *
     * @param array $data
     */
    public function populateValues($data)
    {
        parent::populateValues($data);

        if (isset($data['id'])) {
            $parent                          = $this->get('parent');
            $findMethod                      = $parent->getOption('find_method');
            $findMethod['params']['groupId'] = $data['id'];
            $parent->setOptions(['find_method' => $findMethod]);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function init()
    {
        $this->setName('group');

        $repository = $this->getEntityManager()->getRepository(Group::class);

        // the ID field is hidden, we need it for the UniqueObject validator
        $this->add($repository->getFormElementDefinition('id'));
        $this->add($repository->getFormElementDefinition('name'));
        $this->add($repository->getFormElementDefinition('description'));
        $this->add($repository->getFormElementDefinition('parent'));
    }

    /**
     * {@inheritDoc}
     */
    public function getInputFilterSpecification()
    {
        $repository = $this->getEntityManager()->getRepository(Group::class);

        return $repository->getInputFilterSpecification();
    }
}
