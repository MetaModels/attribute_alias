<?php

/**
 * This file is part of MetaModels/attribute_alias.
 *
 * (c) 2012-2017 The MetaModels team.
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
 * @copyright  2012-2017 The MetaModels team.
 * @license    https://github.com/MetaModels/attribute_alias/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['typeOptions']['alias'] = 'Alias';
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['alias_prefix'][0]      = 'Alias prefix';
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['alias_prefix'][1]      =
    'Optionally add a prefix term.';
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['alias_postfix'][0]     = 'Alias postfix';
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['alias_postfix'][1]     =
    'Optionally add a postfix term.';
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['alias_fields'][0]      = 'Alias fields';
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['alias_fields'][1]      =
    'Please select one or more attributes to combine a alias.';
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['field_attribute']      = 'Attributes';
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['force_alias'][0]       = 'Force alias regenerating';
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['force_alias'][1]       =
    'Check this, if you want the alias to be regenerated whenever any of the dependant fields is changed. Note that ' .
    'this will invalidate old urls that are based upon the alias.';
