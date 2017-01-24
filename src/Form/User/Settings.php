<?php

/**
 * @copyright   (c) 2014-17, Vrok
 * @license     MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @author      Jakob Schumann <schumann@vrok.de>
 */

namespace AppBase\Form\User;

use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Vrok\Form\Form;

/**
 * Allows the user to edit settings for his account.
 */
class Settings extends Form
{
    /**
     * {@inheritDoc}
     */
    public function init()
    {
        $this->setName('account-settings');
        $this->setHydrator(new DoctrineHydrator($this->getEntityManager()));

        // csrf first so error message appears above the form
        $this->addCsrfElement('csrfSettings');

        $this->add([
            'type'    => SettingsFieldset::class,
            'options' => [
                'use_as_base_fieldset' => true,
            ],
        ]);

        $this->add([
            'name'       => 'save',
            'attributes' => [
                'type'  => 'submit',
                'value' => 'form.save',
            ],
        ]);
    }
}
