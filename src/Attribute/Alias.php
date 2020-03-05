<?php

/**
 * This file is part of MetaModels/attribute_alias.
 *
 * (c) 2012-2020 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/attribute_alias
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Andreas Isaak <info@andreas-isaak.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Oliver Hoff <oliver@hofff.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2012-2020 The MetaModels team.
 * @license    https://github.com/MetaModels/attribute_alias/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\AttributeAliasBundle\Attribute;

use Ausi\SlugGenerator\SlugGenerator;
use Contao\StringUtil;
use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\ReplaceInsertTagsEvent;
use MetaModels\Attribute\BaseSimple;
use MetaModels\IItem;

/**
 * This is the MetaModelAttribute class for handling the alias field.
 */
class Alias extends BaseSimple
{
    /**
     * The alias.
     *
     * @var string
     */
    private $alias;

    /**
     * The integer prefix.
     *
     * @var string
     */
    private $integerPrefix = 'id-';

    /**
     * {@inheritDoc}
     */
    public function getSQLDataType()
    {
        return 'varchar(255) NULL';
    }

    /**
     * {@inheritDoc}
     */
    public function getAttributeSettingNames()
    {
        return \array_merge(
            parent::getAttributeSettingNames(),
            [
                'alias_fields',
                'isunique',
                'force_alias',
                'mandatory',
                'alwaysSave',
                'filterable',
                'searchable',
                'sortable',
                'validAliasCharacters',
                'slugLocale',
                'skipIntegerPrefix',
                'alias_prefix',
                'alias_postfix'
            ]
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getFieldDefinition($arrOverrides = [])
    {
        $arrFieldDef = parent::getFieldDefinition($arrOverrides);

        $arrFieldDef['inputType'] = 'text';

        // We do not need to set mandatory, as we will automatically update our value when isunique is given.
        if ($this->get('isunique')) {
            $arrFieldDef['eval']['mandatory'] = false;
        }

        // If "force_alias" is true set alwaysSave and readonly to true.
        if ($this->get('force_alias')) {
            $arrFieldDef['eval']['alwaysSave'] = true;
            $arrFieldDef['eval']['readonly']   = true;
        }

        return $arrFieldDef;
    }

    /**
     * {@inheritdoc}
     */
    public function modelSaved($objItem)
    {
        // Alias already defined and no update forced, get out!
        if ($objItem->get($this->getColName()) && (!$this->get('force_alias'))) {
            return;
        }

        // Item is a variant but no overriding allowed, get out!
        if ($objItem->isVariant() && (!$this->get('isvariant'))) {
            return;
        }

        // Generate alias string.
        $this->alias = $this->generateAlias($objItem);

        // Convert alias with Contao standardize or slug.
        if ($this->get('validAliasCharacters')) {
            $this->convertAliasBySlug();
        } else {
            $this->convertAliasByStandardize();
        }

        // We need to fetch the attribute values for all attributes in the alias_fields and update the database and the
        // model accordingly.
        if ($this->get('isunique')) {
            // Ensure uniqueness.
            $strBaseAlias = $this->alias;
            $arrIds       = [$objItem->get('id')];
            $intCount     = 2;
            while (\array_diff($this->searchFor($this->alias), $arrIds)) {
                $this->alias = $strBaseAlias . '-' . ($intCount++);
            }
        }

        $this->setDataFor([$objItem->get('id') => $this->alias]);
        $objItem->set($this->getColName(), $this->alias);
    }


    /**
     * Convert alias by standardize.
     */
    private function convertAliasByStandardize()
    {
        $dispatcher   = $this->getMetaModel()->getServiceContainer()->getEventDispatcher();
        $replaceEvent = new ReplaceInsertTagsEvent($this->alias);
        $dispatcher->dispatch(ContaoEvents::CONTROLLER_REPLACE_INSERT_TAGS, $replaceEvent);

        // Implode with '-', replace inserttags and strip HTML elements.
        $baseAlias   = $this->alias;
        $this->alias = StringUtil::standardize(\strip_tags($replaceEvent->getBuffer()));

        // Check skip integer prefix.
        if (!$this->get('skipIntegerPrefix')) {
            return;
        }

        // Skip integer prefix is added by standardize.
        if ($this->integerPrefix !== substr($baseAlias, 0, strlen($this->integerPrefix))
            && $this->integerPrefix === substr($this->alias, 0, strlen($this->integerPrefix))) {
            $this->alias = substr($this->alias, strlen($this->integerPrefix));
        }

        return;
    }

    /**
     * Convert alias by slug.
     */
    private function convertAliasBySlug()
    {
        $slugOptions = [
            'locale'     => $this->get('slugLocale'),
            'validChars' => $this->get('validAliasCharacters')
        ];

        $slugGenerator = new SlugGenerator();
        $baseAlias     = StringUtil::prepareSlug($this->alias);
        $this->alias   = $slugGenerator->generate($baseAlias, $slugOptions);

        // Add integer prefix.
        if (!$this->get('skipIntegerPrefix') && preg_match('/^[1-9]\d*$/', $this->alias)) {
            $this->alias = $this->integerPrefix . $this->alias;
        }

        return;
    }

    /**
     * Generate the alias.
     *
     * @param IItem $objItem The item.
     *
     * @return string
     */
    private function generateAlias(IItem $objItem)
    {
        $arrAlias = [];

        if (!empty($this->get('alias_prefix'))) {
            $arrAlias[] = $this->get('alias_prefix');
        }

        foreach (StringUtil::deserialize($this->get('alias_fields')) as $strAttribute) {
            if ($this->isMetaField($strAttribute['field_attribute'])) {
                $strField   = $strAttribute['field_attribute'];
                $arrAlias[] = $objItem->get($strField);
            } else {
                $arrValues  = $objItem->parseAttribute($strAttribute['field_attribute'], 'text', null);
                $arrAlias[] = $arrValues['text'];
            }
        }

        if (!empty($this->get('alias_postfix'))) {
            $arrAlias[] = $this->get('alias_postfix');
        }

        return \implode('-', $arrAlias);
    }

    /**
     * Check if we have a meta field from metamodels.
     *
     * @param string $strField The selected value.
     *
     * @return boolean True => Yes we have | False => nope.
     */
    protected function isMetaField($strField)
    {
        $strField = \trim($strField);

        if (\in_array($strField, $this->getMetaModelsSystemColumns())) {
            return true;
        }

        return false;
    }

    /**
     * Returns the global MetaModels System Columns (replacement for super global access).
     *
     * @return mixed Global MetaModels System Columns
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected function getMetaModelsSystemColumns()
    {
        return $GLOBALS['METAMODELS_SYSTEM_COLUMNS'];
    }

    /**
     * {@inheritDoc}
     *
     * This is needed for compatibility with MySQL strict mode.
     */
    public function serializeData($value)
    {
        return $value === '' ? null : $value;
    }
}
