<?php
/**
 * HUBzero CMS
 *
 * Copyright 2005-2011 Purdue University. All rights reserved.
 *
 * This file is part of: The HUBzero(R) Platform for Scientific Collaboration
 *
 * The HUBzero(R) Platform for Scientific Collaboration (HUBzero) is free
 * software: you can redistribute it and/or modify it under the terms of
 * the GNU Lesser General Public License as published by the Free Software
 * Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 * HUBzero is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * HUBzero is a registered trademark of Purdue University.
 *
 * @package   hubzero-cms
 * @author    Ilya Shunko <ishunko@purdue.edu>
 * @copyright Copyright 2005-2011 Purdue University. All rights reserved.
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
 */

require_once(JPATH_ROOT . DS . 'components' . DS . 'com_storefront' . DS . 'models' . DS . 'Warehouse.php');

class Integrity
{
	public static function skuIntegrityCheck($sku)
	{
		$return = new stdClass();
		$return->status = 'ok';
		$return->errors = array();

		$options = $sku->getOptions();

		// Check if there are other SKUs that have the same set of options
		$warehouse = new StorefrontModelWarehouse();
		$skuMatch = $warehouse->mapSku($sku->getProductId(), $options, false);

		if ($skuMatch && $skuMatch != $sku->getId())
		{
			$return->status = 'error';
			// If there are no options, no multiple SKUs can be published
			if (empty($options))
			{
				$return->errors[] = 'There is already another SKU published for this product. A product without product options can only have one SKU.';
			}
			else
			{
				$return->errors[] = 'There is already a SKU with the identical set of options. Each SKU must have a unique set of options.';
			}
		}

		return $return;
	}

	public static function collectionIntegrityCheck($collection)
	{
		$return = new stdClass();
		$return->status = 'ok';
		$return->errors = array();

		// Check if there are other collections that have the same alias
		try
		{
			$conflictingCollectionId = StorefrontModelCollection::findActiveCollectionByAlias($collection->getAlias());
			if ($conflictingCollectionId && $conflictingCollectionId != $collection->getId())
			{
				$return->status = 'error';
				$return->errors[] = 'There is already another collection published with the same alias. Alias must be unique.';
			}
		}
		catch (Exception $e)
		{
			// No conflicting product found (hence, the Exception), good to go.
			return $return;
		}

		return $return;
	}
}