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

/**
 *
 * Coupons lookup and management
 *
 */
class StorefrontModelCoupons
{
	// Database instance
	var $db = NULL;

	/**
	 * Contructor
	 *
	 * @param  void
	 * @return void
	 */
	public function __construct()
	{
		$this->_db = App::get('db');

		// Load language file
		Lang::load('com_storefront');
	}

	/**
	 * Get coupons info orderd by coupon type with item coupons first then ordered in the order of applied time
	 *
	 * @param  array		$cnIds coupon ids
	 * @return array		coupons info
	 */
	public function getCouponsInfo($cnIds)
	{
		$sqlCnIds = '0';

		foreach ($cnIds as $cnId)
		{
			$sqlCnIds .= ',' . $cnId;
		}

		$sql = "SELECT `cnId`, `cnCode`, `cnDescription`, `cnObject`,
				IF(`cnExpires` < NOW(), 1, 0) AS `cnExpired`,
				IF(`cnObject` = 'sku' OR `cnObject` = 'product', 1, 0) AS `itemCoupon`
				FROM `#__storefront_coupons` cn WHERE `cnId` IN (" . $sqlCnIds . ") ORDER BY `itemCoupon` DESC";
		$this->_db->setQuery($sql);
		//echo $this->_db->_sql;
		$couponsInfo = $this->_db->loadObjectList('cnId');

		// rearrange coupons in the order of applied within coupon types time keeping the item coupons on top (so that it is ordered by itemCoupon, dateAdded)

		// Initialize temp storage arrays
		$temp = new stdClass();
		$temp->itemCoupons = array();
		$temp->genericCoupons = array();

		// $cnIds are orderd by time applied
		foreach ($cnIds as $cnId)
		{
			// Skip deleted or inactive coupons
			if (empty($couponsInfo[$cnId]))
			{
				continue;
			}

			if ($couponsInfo[$cnId]->itemCoupon)
			{
				$temp->itemCoupons[] = $couponsInfo[$cnId];
			}
			else {
				$temp->genericCoupons[] = $couponsInfo[$cnId];
			}
		}

		$couponsInfo = array_merge($temp->itemCoupons, $temp->genericCoupons);
		unset($temp);

		return $couponsInfo;
	}

	/**
	 * Get complete info for a coupon
	 *
	 * @param 	int 		$cnId coupon ID
	 * @param 	bool 		$returnObjects flag wheter to query/return  coupon objects
	 * @param 	bool 		$returnConditions flag wheter to query/return  coupon conditions
	 * @param 	bool 		$returnAction flag wheter to query/return  coupon action
	 * @param 	bool 		$returnInfo flag wheter to query/return  coupon generic info
	 * @return 	object		coupon info
	 */
	public function getCouponInfo($cnId, $returnObjects = true, $returnConditions = true, $returnAction = true, $returnInfo = false)
	{
		$couponInfo = new stdClass();

		// Get objects
		if ($returnObjects)
		{
			$sql = "SELECT * FROM `#__storefront_coupon_objects` WHERE cnId = " . $this->_db->quote($cnId);

			$this->_db->setQuery($sql);
			$this->_db->query();
			$objects = $this->_db->loadObjectList();
			$couponInfo->objects = $objects;
		}

		// Get conditions
		if ($returnConditions)
		{
			$sql = "SELECT * FROM `#__storefront_coupon_conditions` WHERE cnId = " . $this->_db->quote($cnId);

			$this->_db->setQuery($sql);
			$this->_db->query();
			$conditions = $this->_db->loadObjectList();
			$couponInfo->conditions = $conditions;
		}

		// Get action
		if ($returnAction)
		{
			$sql = "SELECT * FROM `#__storefront_coupon_actions` WHERE cnId = " . $this->_db->quote($cnId);

			$this->_db->setQuery($sql);
			$this->_db->query();
			$action = $this->_db->loadObject();
			$couponInfo->action = $action;
		}

		// Get generic coupon info
		if ($returnInfo)
		{
			$sql = "SELECT cn.*,
					IF(`cnObject` = 'sku' OR `cnObject` = 'product', 1, 0) AS `itemCoupon`
					FROM `#__storefront_coupons` cn WHERE cnId = " . $this->_db->quote($cnId);

			$this->_db->setQuery($sql);
			$this->_db->query();
			$info = $this->_db->loadObject();
			$couponInfo->info = $info;
		}

		return($couponInfo);
	}

	/**
	 * Check if coupon is valid
	 *
	 * @param 	string		$couponCode coupon code
	 * @return 	int			coupon id if the code is valid
	 */
	public function isValid($couponCode)
	{
		// Check if the code is valid
		$sql = 	"SELECT cn.`cnId`,
				IF(cn.`cnUseLimit` IS NULL, 'unlimited', cn.`cnUseLimit`) AS `cnUseLimit`,
				IF(cn.`cnExpires` IS NULL OR cn.`cnExpires` >= DATE(NOW()), 'valid', 'expired') AS `cnValid`"
				. ' FROM #__storefront_coupons cn '
				. ' WHERE cn.`cnCode` = ' . $this->_db->quote($couponCode);

		$this->_db->setQuery($sql);
		//echo $this->_db->_sql;
		$this->_db->query();

		if (!$this->_db->getNumRows())
		{
			throw new Exception(Lang::txt('COM_STOREFRONT_INVALID_COUPON_CODE'));
		}

		$row = $this->_db->loadObject();

		// check if expired
		if ($row->cnValid != 'valid')
		{
			throw new Exception(Lang::txt('COM_STOREFRONT_EXPIRED_COUPON_CODE'));
		}

		if (!$row->cnUseLimit)
		{
			throw new Exception(Lang::txt('COM_STOREFRONT_COUPON_ALREADY_USED'));
		}

		return $row->cnId;
	}

	/**
	 * Use up one coupon application
	 *
	 * @param 	int			$cnId coupon ID
	 * @return 	void
	 */
	public function apply($cnId)
	{
		$sql = "UPDATE `#__storefront_coupons` SET `cnUseLimit` = (IF(`cnUseLimit` IS NULL, NULL, `cnUseLimit` - 1)) WHERE `cnId` = " . $this->_db->quote($cnId);
		$this->_db->setQuery($sql);
		//echo $this->_db->_sql;
		$this->_db->query();

		return true;
	}

	/**
	 * Return coupon back to the coupons pool to be available for future use
	 *
	 * @param 	int			$cnId coupon ID
	 * @return 	void
	 */
	public function recycle($cnId)
	{
		$sql = "UPDATE `#__storefront_coupons` SET `cnUseLimit` = (IF(`cnUseLimit` IS NULL, NULL, `cnUseLimit` + 1)) WHERE `cnId` = " . $this->_db->quote($cnId);
		$this->_db->setQuery($sql);
		//echo $this->_db->_sql;
		$this->_db->query();

		return true;
	}

}