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
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @copyright  2012-2020 The MetaModels team.
 * @license    https://github.com/MetaModels/attribute_alias/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\AttributeAliasBundle\Attribute;

use Contao\CoreBundle\Slug\Slug as SlugGenerator;
use Contao\StringUtil;
use Contao\System;
use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\ReplaceInsertTagsEvent;
use Doctrine\DBAL\Connection;
use MetaModels\Attribute\BaseSimple;
use MetaModels\Helper\TableManipulator;
use MetaModels\IItem;
use MetaModels\IMetaModel;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * This is the MetaModelAttribute class for handling the alias field.
 */
class Alias extends BaseSimple
{

    /**
     * The Contao slug generator.
     *
     * @var SlugGenerator
     */
    private $slugGenerator;

    /**
     * The event dispatcher.
     *
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * {@inheritDoc}
     */
    public function __construct(
        IMetaModel $objMetaModel,
        $arrData = [],
        Connection $connection = null,
        TableManipulator $tableManipulator = null,
        EventDispatcherInterface $dispatcher = null,
        SlugGenerator $slugGenerator = null
    ) {
        parent::__construct($objMetaModel, $arrData, $connection, $tableManipulator);

        if (null === $slugGenerator) {
            $slugGenerator = System::getContainer()->get('contao.slug');
        }

        if (null === $dispatcher) {
            $dispatcher = System::getContainer()->get('event_dispatcher');
        }

        $this->dispatcher    = $dispatcher;
        $this->slugGenerator = $slugGenerator;
    }

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
                'noIntegerPrefix',
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
        if (!$this->get('force_alias') && $objItem->get($this->getColName())) {
            return;
        }

        // Item is a variant but no overriding allowed, get out!
        if ($objItem->isVariant() && !$this->get('isvariant')) {
            return;
        }

        $itemId = $objItem->get('id');
        $alias  = $this->generateAlias($objItem);
        $slug   = $this->generateSlug($alias, $itemId);

        $this->setDataFor([$itemId => $slug]);
        $objItem->set($this->getColName(), $slug);
    }

    /**
     * Generate a slug from the alias.
     *
     * @param string $alias  The alias.
     *
     * @param string $itemId The item id to check for duplicates.
     *
     * @return string The generated slug.
     */
    private function generateSlug(string $alias, string $itemId): string
    {
        $replaceEvent = new ReplaceInsertTagsEvent($alias);
        $this->dispatcher->dispatch(ContaoEvents::CONTROLLER_REPLACE_INSERT_TAGS, $replaceEvent);

        $slugOptions = ['locale' => $this->get('slugLocale') ?? ''];

        if ($this->get('validAliasCharacters')) {
            $slugOptions += [
                'validChars' => $this->get('validAliasCharacters')
            ];
        }

        $slug = $this->slugGenerator->generate(
            $alias,
            $slugOptions,
            function (string $alias) use ($itemId) {
                if (!$this->get('isunique')) {
                    return false;
                }

                return [] !== \array_diff($this->searchFor($alias), [$itemId]);
            },
            $this->get('noIntegerPrefix') ? '' : 'id-'
        );

        if (\is_numeric($slug[0]) && !$this->get('validAliasCharacters') && !$this->get('noIntegerPrefix')) {
            // BC mode. In prior versions, StringUtil::standardize was used to generate the alias
            // which always added an prefix for aliases starting with a number.
            $slug = 'id-' . $slug;
        }

        return $slug;
    }

    /**
     * Generate the alias.
     *
     * @param IItem $objItem The item.
     *
     * @return string
     */
    private function generateAlias(IItem $objItem): string
    {
        $parts = [];

        if (!empty($this->get('alias_prefix'))) {
            $parts[] = $this->get('alias_prefix');
        }

        foreach (StringUtil::deserialize($this->get('alias_fields'), true) as $aliasField) {
            if ($this->isMetaField($aliasField['field_attribute'])) {
                $attribute = $aliasField['field_attribute'];
                $parts[]   = $objItem->get($attribute);
            } else {
                $arrValues = $objItem->parseAttribute($aliasField['field_attribute'], 'text', null);
                $parts[]   = $arrValues['text'];
            }
        }

        if (!empty($this->get('alias_postfix'))) {
            $parts[] = $this->get('alias_postfix');
        }

        return \implode('-', $parts);
    }

    /**
     * Check if we have a meta field from metamodels.
     *
     * @param string $strField The selected value.
     *
     * @return boolean True => Yes we have | False => nope.
     */
    protected function isMetaField($strField): bool
    {
        $strField = \trim($strField);

        if (\in_array($strField, $this->getMetaModelsSystemColumns(), true)) {
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
