<?php

/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 * @package     MetaModels
 * @subpackage  AttributeAlias
 * @author      Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author      Andreas Isaak <info@andreas-isaak.de>
 * @copyright   The MetaModels team.
 * @license     LGPL.
 * @filesource
 */

/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
	'MetaModels\Attribute\Alias\Alias'     => 'system/modules/metamodelsattribute_alias/MetaModels/Attribute/Alias/Alias.php',
	'MetaModels\Dca\AttributeAlias'        => 'system/modules/metamodelsattribute_alias/MetaModels/Dca/AttributeAlias.php',

	'MetaModelAttributeAlias'              => 'system/modules/metamodelsattribute_alias/deprecated/MetaModelAttributeAlias.php',
	'TableMetaModelsAttributeAlias'        => 'system/modules/metamodelsattribute_alias/deprecated/TableMetaModelsAttributeAlias.php',
));


/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
	'mm_attr_alias'              => 'system/modules/metamodelsattribute_alias/templates',
));
