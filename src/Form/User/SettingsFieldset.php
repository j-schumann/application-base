<?php

/**
 * @copyright   (c) 2014-17, Vrok
 * @license     MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @author      Jakob Schumann <schumann@vrok.de>
 */

namespace AppBase\Form\User;

use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Vrok\Entity\User;
use Vrok\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;

/**
 * Fieldset to edit account settings
 */
class SettingsFieldset extends Fieldset implements InputFilterProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function init()
    {
        $this->setName('user');
        $this->setHydrator(new DoctrineHydrator($this->getEntityManager()));

        $ur = $this->getEntityManager()->getRepository(User::class);

        $this->add($ur->getFormElementDefinition('emailNotificationsEnabled'));
        $this->add($ur->getFormElementDefinition('httpNotificationsEnabled'));
        $this->add($ur->getFormElementDefinition('httpNotificationUrl'));
        $this->add($ur->getFormElementDefinition('httpNotificationUser'));
        $this->add($ur->getFormElementDefinition('httpNotificationPw'));
        $this->add($ur->getFormElementDefinition('httpNotificationCertCheck'));
    }

    /**
     * {@inheritDoc}
     */
    public function getInputFilterSpecification()
    {
        $ur = $this->getEntityManager()->getRepository(User::class);

        return [
            $ur->getInputSpecification('emailNotificationsEnabled'),
            $ur->getInputSpecification('httpNotificationsEnabled'),
            $ur->getInputSpecification('httpNotificationUrl'),
            $ur->getInputSpecification('httpNotificationUser'),
            $ur->getInputSpecification('httpNotificationPw'),
            $ur->getInputSpecification('httpNotificationCertCheck'),
        ];
    }
}
