<?php

namespace Pim\Bundle\CatalogBundle\Form\Type;

use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Oro\Bundle\FlexibleEntityBundle\Form\Type\FlexibleType;
use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;
use Pim\Bundle\CatalogBundle\Form\View\ProductFormView;
use Pim\Bundle\CatalogBundle\Form\Subscriber\IgnoreMissingFieldDataSubscriber;
use Pim\Bundle\CatalogBundle\Form\Subscriber\BindProductAssociationTargetsSubscriber;

/**
 * Product form type
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductType extends FlexibleType
{
    /**
     * Storage of the product form fields in order to use its frontend manipulation
     *
     * @var ProductFormView $productFormView
     */
    protected $productFormView;

    /**
     * {@inheritdoc}
     */
    public function __construct(
        FlexibleManager $flexibleManager,
        $valueFormAlias,
        ProductFormView $productFormView,
        EventSubscriberInterface $transformer
    ) {
        parent::__construct($flexibleManager, $valueFormAlias);

        $this->productFormView = $productFormView;
        $this->transformer     = $transformer;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['enable_state']) {
            $builder->add('enabled', 'checkbox');
        }

        parent::buildForm($builder, $options);

        $builder
            ->add(
                'productAssociations',
                'collection',
                array(
                    'type' => 'pim_catalog_product_association'
                )
            )
            ->get('productAssociations')
            ->addEventSubscriber(new BindProductAssociationTargetsSubscriber());

        if ($options['enable_family']) {
            $builder->add(
                'family',
                'entity',
                array(
                    'class'       => 'PimCatalogBundle:Family',
                    'empty_value' => ''
                )
            );
        }

        $builder
            ->add(
                'categories',
                'oro_entity_identifier',
                array(
                    'class'    => 'PimCatalogBundle:Category',
                    'required' => true,
                    'mapped'   => true,
                    'multiple' => true,
                )
            );

        if ($options['import_mode']) {
            // The product category converter works on a classic entity form type scheme
            $builder
                ->remove('categories')
                ->add(
                    'categories',
                    'entity',
                    array(
                        'class'        => 'PimCatalogBundle:Category',
                        'multiple'     => true,
                        'by_reference' => false
                    )
                )
                ->add(
                    'groups',
                    'entity',
                    array(
                        'class'        => 'PimCatalogBundle:Group',
                        'multiple'     => true,
                        'by_reference' => false
                    )
                )
                ->addEventSubscriber($this->transformer)
                ->addEventSubscriber(new IgnoreMissingFieldDataSubscriber());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['groups'] = $this->productFormView->getView();
    }

    /**
     * {@inheritdoc}
     */
    public function addEntityFields(FormBuilderInterface $builder)
    {
        parent::addEntityFields($builder);

        $builder->add('enabled', 'hidden');
    }

    /**
     * Add entity fieldsto form builder
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function addDynamicAttributesFields(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'values',
            'pim_catalog_localized_collection',
            array(
                'type'               => $this->valueFormAlias,
                'allow_add'          => true,
                'allow_delete'       => true,
                'by_reference'       => false,
                'cascade_validation' => true,
                'currentLocale'      => $options['currentLocale'],
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'currentLocale' => null,
                'import_mode'   => false,
                'enable_family' => true,
                'enable_state'  => true
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_product';
    }
}
