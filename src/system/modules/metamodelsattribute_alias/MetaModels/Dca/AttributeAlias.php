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
 * @copyright   The MetaModels team.
 * @license     LGPL.
 * @filesource
 */

namespace MetaModels\Dca;

use MetaModels\Factory;

/**
 * This class is used from DCA tl_metamodel_attribute for various callbacks.
 *
 * @package	   MetaModels
 * @subpackage AttributeAlias
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class AttributeAlias
{

	/**
	 * Fetch all attributes from the parenting MetaModel. Called as options_callback.
	 * User the oncreate_callback.
	 *
	 * @return array
	 */
	public function getAllAttributes()
	{
		$intID = \Input::getInstance()->get('id');
		$intPID = \Input::getInstance()->get('pid');

		$arrReturn = array();

		if (empty($intPID))
		{
			$objResult = \Database::getInstance()
				->prepare('SELECT pid FROM tl_metamodel_attribute WHERE id=?')
				->limit(1)
				->execute($intID);

			if ($objResult->numRows == 0)
			{
				return $arrReturn;
			}
			$objMetaModel = Factory::byId($objResult->pid);
		} else {
			$objMetaModel = Factory::byId($intPID);
		}

		foreach ($objMetaModel->getAttributes() as $objAttribute)
		{
			$arrReturn[$objAttribute->getColName()] = $objAttribute->getName();
		}
		return $arrReturn;
	}
}

?>
