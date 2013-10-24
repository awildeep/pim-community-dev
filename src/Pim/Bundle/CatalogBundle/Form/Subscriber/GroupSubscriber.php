<?php

namespace Pim\Bundle\CatalogBundle\Form\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Group subscriber used to disable some fields
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::POST_SET_DATA => 'postSetData'
        );
    }

    /**
     * Post set data event
     * Disable code and axis fields
     *
     * @param FormEvent $event
     */
    public function postSetData(FormEvent $event)
    {
        $group = $event->getData();
        if (null === $group || !$group->getId()) {
            return;
        }

        $form = $event->getForm();

        $form->add('code', 'text', array('disabled' => true));

        $form->add(
            'attributes',
            'entity',
            array(
                'disabled' => true,
                'class'    => 'Pim\Bundle\CatalogBundle\Entity\ProductAttribute',
                'multiple' => true,
                'label'    => 'Axis'
            )
        );

        $form->add(
            'type',
            'entity',
            array(
                'disabled' => true,
                'class' => 'PimCatalogBundle:GroupType',
                'multiple' => false,
                'expanded' => false
            )
        );

    }
}
