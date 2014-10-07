<?php
/**
 * @copyright   (c) 2014, Vrok
 * @license     http://customlicense CustomLicense
 * @author      Jakob Schumann <schumann@vrok.de>
 */

namespace AppBase\Form\User;

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
            $parent = $this->get('parent');
            $findMethod = $parent->getOption('find_method');
            $findMethod['params']['groupId'] = $data['id'];
            $parent->setOptions(array('find_method' => $findMethod));
        }
    }

    /**
     * {@inheritDoc}
     */
    public function init()
    {
        $this->setName('group');

        $groupRepository = $this->getEntityManager()
                ->getRepository('Vrok\Entity\Group');

        // the ID field is hidden, we need it for the UniqueObject validator
        $this->add($groupRepository->getFormElementDefinition('id'));
        $this->add($groupRepository->getFormElementDefinition('name'));
        $this->add($groupRepository->getFormElementDefinition('description'));
        $this->add($groupRepository->getFormElementDefinition('parent'));
    }

    /**
     * {@inheritDoc}
     */
    public function getInputFilterSpecification()
    {
        $repository = $this->getEntityManager()
                ->getRepository('Vrok\Entity\Group');
        return $repository->getInputFilterSpecification();
    }
}
