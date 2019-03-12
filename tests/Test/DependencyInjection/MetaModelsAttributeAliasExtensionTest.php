<?php

/**
 * This file is part of MetaModels/attribute_alias.
 *
 * (c) 2012-2019 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/attribute_alias
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/attribute_alias/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\AttributeAliasBundle\Test\DependencyInjection;

use MenAtWork\MultiColumnWizardBundle\Event\GetOptionsEvent;
use MetaModels\AttributeAliasBundle\Attribute\AttributeTypeFactory;
use MetaModels\AttributeAliasBundle\EventListener\GetOptionsListener;
use MetaModels\AttributeAliasBundle\DependencyInjection\MetaModelsAttributeAliasExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

/**
 * This test case test the extension.
 */
class MetaModelsAttributeAliasExtensionTest extends TestCase
{
    /**
     * Test that extension can be instantiated.
     *
     * @return void
     */
    public function testInstantiation()
    {
        $extension = new MetaModelsAttributeAliasExtension();

        $this->assertInstanceOf(MetaModelsAttributeAliasExtension::class, $extension);
        $this->assertInstanceOf(ExtensionInterface::class, $extension);
    }

    /**
     * Test that the services are loaded.
     *
     * @return void
     */
    public function testFactoryIsRegistered()
    {
        $container = $this->getMockBuilder(ContainerBuilder::class)->getMock();

        $container
            ->expects($this->exactly(2))
            ->method('setDefinition')
            ->withConsecutive(
                [
                    AttributeTypeFactory::class,
                    $this->callback(
                        function ($value) {
                            /** @var Definition $value */
                            $this->assertInstanceOf(Definition::class, $value);
                            $this->assertCount(1, $value->getTag('metamodels.attribute_factory'));

                            return true;
                        }
                    ),
                ],
                [
                    $this->anything(),
                    $this->anything(),
                ]
            );

        $extension = new MetaModelsAttributeAliasExtension();
        $extension->load([], $container);
    }

    /**
    * Test that the event listener is registered.
    *
    * @return void
    */
    public function testEventListenersAreRegistered()
    {
        $container = $this->getMockBuilder(ContainerBuilder::class)->getMock();

        $container
            ->expects($this->exactly(2))
            ->method('setDefinition')
            ->withConsecutive(
                [
                    $this->anything(),
                    $this->anything(),
                ],
                [
                    GetOptionsListener::class,
                    $this->callback(
                        function ($value) {
                            /** @var Definition $value */
                            $this->assertInstanceOf(Definition::class, $value);
                            $this->assertCount(1, $value->getTag('kernel.event_listener'));
                            $this->assertEventListener(
                                $value,
                                GetOptionsEvent::NAME,
                                'getOptions'
                            );

                            return true;
                        }
                    )
                ]
            );

        $extension = new MetaModelsAttributeAliasExtension();
        $extension->load([], $container);
    }

    /**
     * Assert that a definition is registered as event listener.
     *
     * @param Definition $definition The definition.
     * @param string     $eventName  The event name.
     * @param string     $methodName The method name.
     *
     * @return void
     */
    private function assertEventListener(Definition $definition, $eventName, $methodName)
    {
        $this->assertCount(1, $definition->getTag('kernel.event_listener'));
        $this->assertArrayHasKey(0, $definition->getTag('kernel.event_listener'));
        $this->assertArrayHasKey('event', $definition->getTag('kernel.event_listener')[0]);
        $this->assertArrayHasKey('method', $definition->getTag('kernel.event_listener')[0]);

        $this->assertEquals($eventName, $definition->getTag('kernel.event_listener')[0]['event']);
        $this->assertEquals($methodName, $definition->getTag('kernel.event_listener')[0]['method']);
    }
}
