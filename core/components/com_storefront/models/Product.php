<?php
/**
 * HUBzero CMS
 *
 * Copyright 2005-2015 HUBzero Foundation, LLC.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * HUBzero is a registered trademark of Purdue University.
 *
 * @package   hubzero-cms
 * @author    Hubzero
 * @copyright Copyright 2005-2015 HUBzero Foundation, LLC.
 * @license   http://opensource.org/licenses/MIT MIT
 */

// No direct access.
defined('_HZEXEC_') or die();

include_once(__DIR__ . DS . 'Warehouse.php');
include_once(__DIR__ . DS . 'Sku.php');

/**
 *
 * Storefront product class
 *
 */
class StorefrontModelProduct
{
	// Product data container
	var $data;

	// Product SKUs
	var $skus = array();

	/**
	 * Constructor
	 *
	 * @param  void
	 * @return void
	 */
	public function __construct()
	{
		// Load language file
		Lang::load('com_storefront');
	}

	/**
	 * Set product type
	 *
	 * @param	string		Product type
	 * @return	bool		true on success, exception otherwise
	 */
	public function setType($productType)
	{
		if (is_numeric($productType))
		{
			$this->data->type = $productType;
			return true;
		}

		switch (strtolower($productType))
		{
			case 'course':
				$this->data->type = 20;
				return true;
				break;
			case 'product':
				$this->data->type = 1;
				return true;
				break;
		}

		throw new Exception(Lang::txt('COM_STOREFRONT_INVALID_PRODUCT_TYPE'));
	}

	/**
	 * Get product type
	 *
	 * @param	void
	 * @return	int		Product type
	 */
	public function getType()
	{
		return $this->data->type;
	}

	/**
	 * Add product to collection
	 *
	 * @param	int		collection ID
	 * @return	bool	true
	 */
	public function addToCollection($cId)
	{
		$this->data->collections[] = $cId;
		return true;
	}

	/**
	 * Get product collections
	 *
	 * @param	void
	 * @return	array		collection IDs
	 */
	public function getCollections()
	{
		return $this->data->collections;
	}

	public function addSku($sku)
	{
		if (!($sku instanceof StorefrontModelSku))
		{
			throw new Exception(Lang::txt('Bad SKU. Unable to add.'));
		}

		$sku->verify();

		$this->skus[] = $sku;
	}

	/**
	 * Sets a new SKU for the product, used by single SKU products
	 *
	 * @param	StorefrontModelSku
	 * @return	void
	 */
	protected function setSku($sku)
	{
		if (!($sku instanceof StorefrontModelSku))
		{
			throw new Exception(Lang::txt('Bad SKU. Unable to add.'));
		}

		// Overwrite the existing SKU(s)
		$this->skus = array($sku);
	}

	/**
	 * Get product skus
	 *
	 * @param	void
	 * @return	array		product SKUs
	 */
	public function getSkus()
	{
		return $this->skus;
	}

	/**
	 * Set product id (used to update product or to create a product with given ID)
	 *
	 * @param	int			product ID
	 * @return	bool		true
	 */
	public function setId($pId)
	{
		$this->data->id = $pId;
		return true;
	}

	/**
	 * Get product id (if set)
	 *
	 * @param	void
	 * @return	int		product ID
	 */
	public function getId()
	{
		if (!empty($this->data->id))
		{
			return $this->data->id;
		}
		return false;
	}

	/**
	 * Set product name
	 *
	 * @param	string		Product name
	 * @return	bool		true
	 */
	public function setName($productName)
	{
		$this->data->name = $productName;
		return true;
	}

	/**
	 * Get product name
	 *
	 * @param	void
	 * @return	string		Product name
	 */
	public function getName()
	{
		return $this->data->name;
	}

	/**
	 * Set product description
	 *
	 * @param	string		Product description
	 * @return	bool		true
	 */
	public function setDescription($productDescription)
	{
		$this->data->description = $productDescription;
		return true;
	}

	/**
	 * Get product description
	 *
	 * @param	void
	 * @return	string		Product description
	 */
	public function getDescription()
	{
		return $this->data->description;
	}

	/**
	 * Set product tagline
	 *
	 * @param	string		Product tagline
	 * @return	bool		true
	 */
	public function setTagline($productTagline)
	{
		$this->data->tagline = $productTagline;
		return true;
	}

	/**
	 * Get product tagline
	 *
	 * @param	void
	 * @return	string		Product tagline
	 */
	public function getTagline()
	{
		if (empty($this->data->tagline))
		{
			return NULL;
		}
		return $this->data->tagline;
	}

	/**
	 * Set product active status
	 *
	 * @param	bool		Product status
	 * @return	bool		true
	 */
	public function setActiveStatus($activeStatus)
	{
		if ($activeStatus)
		{
			$this->data->activeStatus = 1;
		}
		else
		{
			$this->data->activeStatus = 0;
		}
		return true;
	}

	/**
	 * Get product active status
	 *
	 * @param	void
	 * @return	bool		Product status
	 */
	public function getActiveStatus()
	{
		if (!isset($this->data->activeStatus))
		{
			return 'DEFAULT';
		}
		return $this->data->activeStatus;
	}

	/**
	 * Check if everything checks out and the product is ready to go
	 *
	 * @param  void
	 * @return bool		true on sucess, throws exception on failure
	 */
	public function verify()
	{
		if (empty($this->data->name))
		{
			throw new Exception(Lang::txt('No product name set'));
		}
		if (empty($this->data->description))
		{
			//throw new Exception(Lang::txt('No product description set'));
		}

		foreach ($this->skus as $sku)
		{
			$sku->verify();
		}

		return true;
	}

	/**
	 * Add product to the warehouse
	 *
	 * @param  void
	 * @return object	info
	 */
	public function add()
	{
		$this->verify();

		include_once(__DIR__ . DS . 'Warehouse.php');
		$warehouse = new StorefrontModelWarehouse();

		return($warehouse->addProduct($this));
	}

	/**
	 * Update product info
	 *
	 * @param  void
	 * @return object	info
	 */
	public function update()
	{
		include_once(__DIR__ . DS . 'Warehouse.php');
		$warehouse = new StorefrontModelWarehouse();

		return($warehouse->updateProduct($this));
	}

	/* ************************************* Static functions ***************************************************/

	/**
	 * Get product meta value by name or all product meta values if $metaKey is false
	 *
	 * @param  	int		Product ID
	 * @param	String	Optional: Meta key to get a certain value, if empty returns all product meta
	 * @return 	mixed	Product meta
	 */
	public static function getMeta($pId, $metaKey = false)
	{
		$db = App::get('db');

		$sql  = 'SELECT ';
		if (!$metaKey)
		{
			$sql .= '`pmKey`, ';
		}
		$sql .= '`pmValue` FROM `#__storefront_product_meta` WHERE `pId` = ' . $db->quote($pId);
		if ($metaKey)
		{
			$sql .= ' AND `pmKey` = ' . $db->quote($metaKey);
		}
		$db->setQuery($sql);
		if ($metaKey)
		{
			$meta = $db->loadResult();
		}
		else
		{
			$meta = $db->loadObjectList();
		}
		return $meta;
	}

}