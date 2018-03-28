<?php

/**
 * This file is part of MetaModels/attribute_alias.
 *
 * (c) 2012-2018 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage AttributeAlias
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Andreas Isaak <info@andreas-isaak.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2018 The MetaModels team.
 * @license    https://github.com/MetaModels/attribute_alias/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

$GLOBALS['TL_DCA']['tl_metamodel_attribute']['metapalettes']['alias extends _simpleattribute_'] = [
    '+advanced' => ['force_alias'],
    '+display'  => ['alias_prefix', 'alias_postfix', 'alias_fields after description']
];

$GLOBALS['TL_DCA']['tl_metamodel_attribute']['fields']['alias_prefix'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['alias_prefix'],
    'exclude'   => true,
    'inputType' => 'text',
    'sql'       => 'varchar(255) NOT NULL default \'\'',
    'eval'      => [
        'rgxp'     => 'alpha',
        'tl_class' => 'clr w50'
    ],
];

$GLOBALS['TL_DCA']['tl_metamodel_attribute']['fields']['alias_postfix'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['alias_postfix'],
    'exclude'   => true,
    'inputType' => 'text',
    'sql'       => 'varchar(255) NOT NULL default \'\'',
    'eval'      => [
        'tl_class' => 'w50'
    ],
];

$GLOBALS['TL_DCA']['tl_metamodel_attribute']['fields']['alias_fields'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['alias_fields'],
    'exclude'   => true,
    'inputType' => 'multiColumnWizard',
    'sql'       => 'blob NULL',
    'eval'      => [
        'tl_class'     => 'clr',
        'columnFields' => [
            'field_attribute' => [
                'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['field_attribute'],
                'exclude'   => true,
                'inputType' => 'select',
                'reference' => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['select_values'],
                'eval'      => [
                    'style'  => 'width:600px',
                    'chosen' => 'true'
                ]
            ],
        ],
    ],
];

$GLOBALS['TL_DCA']['tl_metamodel_attribute']['fields']['force_alias'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['force_alias'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'sql'       => 'char(1) NOT NULL default \'\'',
    'eval'      => [
        'tl_class' => 'cbx w50'
    ],
];
