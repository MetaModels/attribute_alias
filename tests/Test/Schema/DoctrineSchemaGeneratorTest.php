<?php

/**
 * This file is part of MetaModels/attribute_alias.
 *
 * (c) 2012-2023 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/attribute_alias
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2023 The MetaModels team.
 * @license    https://github.com/MetaModels/attribute_alias/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace MetaModels\AttributeAliasBundle\Test\Schema;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use MetaModels\AttributeAliasBundle\Schema\DoctrineSchemaGenerator;
use MetaModels\Information\AttributeInformation;
use PHPUnit\Framework\TestCase;

/**
 * This tests the schema generator.
 *
 * @covers \MetaModels\AttributeAliasBundle\Schema\DoctrineSchemaGenerator
 */
class DoctrineSchemaGeneratorTest extends TestCase
{
    /**
     * Test the generate method.
     *
     * @return void
     *
     * @throws Exception
     * @throws SchemaException
     * @throws \ReflectionException
     */
    public function testGenerate(): void
    {
        $instance   = new DoctrineSchemaGenerator();
        $reflection = new \ReflectionMethod(DoctrineSchemaGenerator::class, 'generateAttribute');
        $reflection->setAccessible(true);

        $tableSchema = new Table('mm_test');
        $attribute   = new AttributeInformation('test', 'alias', []);

        $reflection->invoke($instance, $tableSchema, $attribute);

        $this->assertTrue($tableSchema->hasColumn('test'));
        $column = $tableSchema->getColumn('test');
        $this->assertSame('test', $column->getName());
        $this->assertSame(Type::getType(Types::STRING), $column->getType());
        $this->assertSame(255, $column->getLength());
        $this->assertSame(null, $column->getDefault());
        $this->assertFalse($column->getNotnull());
    }
}
