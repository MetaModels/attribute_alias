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
 * @package    MetaModels/attribute_alias
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2018 The MetaModels team.
 * @license    https://github.com/MetaModels/attribute_alias/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\AttributeAliasBundle\EventListener;

use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use MenAtWork\MultiColumnWizardBundle\Event\GetOptionsEvent;
use MetaModels\IFactory;

/**
 * Handle events for tl_metamodel_attribute.alias_fields.attr_id.
 */
class GetOptionsListener
{
    /**
     * Request scope determinator.
     *
     * @var RequestScopeDeterminator
     */
    private $scopeDeterminator;

    /**
     * Metamodels factory.
     *
     * @var IFactory
     */
    private $factory;

    /**
     * GetOptionsListener constructor.
     *
     * @param RequestScopeDeterminator $scopeDeterminator Request scope determinator.
     * @param IFactory                 $factory           Metamodels factory.
     */
    public function __construct(RequestScopeDeterminator $scopeDeterminator, IFactory $factory)
    {
        $this->scopeDeterminator = $scopeDeterminator;
        $this->factory           = $factory;
    }

    /**
     * Check if the event is intended for us.
     *
     * @param GetOptionsEvent $event The event to test.
     *
     * @return bool
     */
    private function isEventForMe(GetOptionsEvent $event)
    {
        if (false === $this->scopeDeterminator->currentScopeIsBackend()) {
            return false;
        }

        $input = $event->getEnvironment()->getInputProvider();
        $type  = $event->getModel()->getProperty('type');

        if ($input->hasValue('type')) {
            $type = $input->getValue('type');
        }

        if (empty($type)) {
            $type = $event->getModel()->getProperty('type');
        }

        return
            ($event->getEnvironment()->getDataDefinition()->getName() === 'tl_metamodel_attribute')
            && ($type === 'alias')
            && ($event->getPropertyName() === 'alias_fields')
            && ($event->getSubPropertyName() === 'field_attribute');
    }

    /**
     * Retrieve the options for the attributes.
     *
     * @param GetOptionsEvent $event The event.
     *
     * @return void
     */
    public function getOptions(GetOptionsEvent $event)
    {
        if (false === self::isEventForMe($event)) {
            return;
        }

        $model       = $event->getModel();
        $metaModelId = $model->getProperty('pid');
        if (!$metaModelId) {
            $metaModelId = ModelId::fromSerialized(
                $event->getEnvironment()->getInputProvider()->getValue('pid')
            )->getId();
        }

        $metaModelName = $this->factory->translateIdToMetaModelName($metaModelId);
        $metaModel     = $this->factory->getMetaModel($metaModelName);

        if (!$metaModel) {
            return;
        }

        $result = [];

        // Add meta fields.
        $result['meta'] = self::getMetaModelsSystemColumns();

        // Fetch all attributes except for the current attribute.
        foreach ($metaModel->getAttributes() as $attribute) {
            if ($attribute->get('id') === $model->getId()) {
                continue;
            }

            $result['attributes'][$attribute->getColName()] = \sprintf(
                '%s [%s]',
                $attribute->getName(),
                $attribute->get('type')
            );
        }

        $event->setOptions($result);
    }

    /**
     * Returns the global MetaModels System Columns (replacement for super global access).
     *
     * @return mixed Global MetaModels System Columns
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected static function getMetaModelsSystemColumns()
    {
        return $GLOBALS['METAMODELS_SYSTEM_COLUMNS'];
    }
}
