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
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2023 The MetaModels team.
 * @license    https://github.com/MetaModels/attribute_alias/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\AttributeAliasBundle\EventListener;

use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\BuildWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\Event\ValidateModelEvent;
use ContaoCommunityAlliance\DcGeneral\InputProviderInterface;
use MetaModels\IFactory;
use MetaModels\IMetaModel;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * This class provides the attribute unique as default.
 */
class SetDefaultValuesAtCheckboxesListener
{
    /**
     * Create a new instance.
     *
     * @param RequestScopeDeterminator $scopeDeterminator The scope determinator.
     * @param IFactory                 $factory           Metamodels factory.
     */
    public function __construct(
        private readonly RequestScopeDeterminator $scopeDeterminator,
        private readonly IFactory $factory,
        private readonly TranslatorInterface $translator,
    ) {
    }

    /**
     * Enable widget isunique or widget force_alias at create.
     *
     * @param BuildWidgetEvent $event The event.
     *
     * @return void
     */
    public function buildWidget(BuildWidgetEvent $event): void
    {
        $model = $event->getModel();

        $dataDefinition = $event->getEnvironment()->getDataDefinition();
        assert($dataDefinition instanceof ContainerInterface);

        if (
            false === $this->scopeDeterminator->currentScopeIsBackend()
            || null !== $model->getId()
            || 'alias' !== $model->getProperty('type')
            || 'tl_metamodel_attribute' !== $dataDefinition->getName()
        ) {
            return;
        }

        // Set unique default as true.
        if ('isunique' === $event->getProperty()->getName()) {
            $model->setProperty('isunique', true);

            return;
        }

        // Set force alias default as true - only for not-variant models.
        if ('force_alias' === $event->getProperty()->getName()) {
            $metaModelId = $model->getProperty('pid');
            if (!$metaModelId) {
                $inputProvider = $event->getEnvironment()->getInputProvider();
                assert($inputProvider instanceof InputProviderInterface);
                $metaModelId = ModelId::fromSerialized($inputProvider->getParameter('pid'))->getId();
            }

            $metaModelName = $this->factory->translateIdToMetaModelName($metaModelId);
            $metaModel     = $this->factory->getMetaModel($metaModelName);
            assert($metaModel instanceof IMetaModel);

            if (!$metaModel->hasVariants()) {
                $model->setProperty('force_alias', true);
            }
        }
    }

    /**
     * Check valid combination of checkboxes 'isvariant' with 'isunique' and 'force_alias'.
     *
     * @param ValidateModelEvent $event The event.
     *
     * @return void
     */
    public function onValidateModel(ValidateModelEvent $event): void
    {
        $model = $event->getModel();

        $dataDefinition = $event->getEnvironment()->getDataDefinition();
        assert($dataDefinition instanceof ContainerInterface);

        // Next check only if 'isvariant' not checked.
        if (
            false === $this->scopeDeterminator->currentScopeIsBackend()
            || 'alias' !== $model->getProperty('type')
            || 'tl_metamodel_attribute' !== $dataDefinition->getName()
            || $model->getProperty('isvariant')
        ) {
            return;
        }

        if ($model->getProperty('isunique')) {
            $errorMessage = $this->translator->trans('isunique.variant_error', [], 'tl_metamodel_attribute');
            $event->getPropertyValueBag()->markPropertyValueAsInvalid('isunique', [$errorMessage]);
        }

        if ($model->getProperty('force_alias')) {
            $errorMessage = $this->translator->trans('force_alias.variant_error', [], 'tl_metamodel_attribute');
            $event->getPropertyValueBag()->markPropertyValueAsInvalid('force_alias', [$errorMessage]);
        }
    }
}
