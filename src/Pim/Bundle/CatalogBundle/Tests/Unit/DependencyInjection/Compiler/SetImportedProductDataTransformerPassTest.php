<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\DependencyInjection\Compiler;

use Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\SetImportedProductDataTransformerPass;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SetImportedProductDataTransformerPassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->pass = new SetImportedProductDataTransformerPass();
    }

    /**
     * Test related method
     */
    public function testInstanceOfCompilerPassInterface()
    {
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface', $this->pass);
    }

    /**
     * Test related method
     */
    public function testProcessWithOverridedTransformer()
    {
        $productType = new Definition();
        $productType->setArguments(array('arg1', 'arg2', 'arg3', 'arg4'));
        $container = $this->getContainerBuilderMock($productType);
        $container->expects($this->any())
            ->method('getParameter')
            ->with('pim_catalog.imported_product_data_transformer')
            ->will($this->returnValue('the_foo_transformer'));

        $this->pass->process($container);

        $arguments = $productType->getArguments();
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Reference', $arguments[3]);
        $this->assertAttributeEquals('the_foo_transformer', 'id', $arguments[3]);
    }

    /**
     * Test related method
     */
    public function testProcessWithNonOverridedTransformer()
    {
        $productType = new Definition();
        $productType->setArguments(array('arg1', 'arg2', 'arg3', 'arg4'));
        $container = $this->getContainerBuilderMock($productType);
        $container->expects($this->any())
            ->method('getParameter')
            ->with('pim_catalog.imported_product_data_transformer')
            ->will($this->returnValue(null));

        $this->pass->process($container);

        $arguments = $productType->getArguments();
        $this->assertEquals('arg4', $arguments[3]);
    }

    /**
     * Test related method
     */
    public function testProcessWithUnavailableProductType()
    {
        $container = $this->getContainerBuilderMock();
        $container->expects($this->never())
            ->method('getParameter');

        $this->pass->process($container);
    }

    /**
     * @param mixed $definition
     *
     * @return \Symfony\Component\DependencyInjection\ContainerBuilder
     */
    private function getContainerBuilderMock($definition = null)
    {
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder');

        $container->expects($this->any())
            ->method('hasDefinition')
            ->with('pim_catalog.form.type.product')
            ->will($this->returnValue(null !== $definition));

        if ($definition) {
            $container->expects($this->any())
                ->method('getDefinition')
                ->will($this->returnValue($definition));
        } else {
            $container->expects($this->never())
                ->method('getDefinition');
        }

        return $container;
    }
}
