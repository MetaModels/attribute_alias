<?php

if (!defined('TL_ROOT'))
{
	die('You cannot access this file directly!');
}

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['typeOptions']['alias'] = 'Alias';
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['alias_fields']         = array('Alias Felder', 'Bitte wählen Sie ein oder mehrere Felder aus denen der Alias erstellt werden soll.');
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['field_attribute']      = 'Attribute';
$GLOBALS['TL_LANG']['tl_metamodel_attribute']['force_alias']          = array('Alias Neuerstellung erzwingen', 'Erzwingt die Neuerstellung des Alias, wenn sich eines der abhängigen Felder ändert. Alte URLs, die auf dem Alias basieren, werden dadurch ungültig.');
