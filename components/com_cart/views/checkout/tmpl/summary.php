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

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

setlocale(LC_MONETARY, 'en_US.UTF-8');

$this->css();
?>

<header id="content-header">
	<h2>Review your order</h2>
</header>

<?php

if (!empty($this->notifications))
{
	$view = new \Hubzero\Component\View(array('name'=>'shared', 'layout' => 'notifications'));
	$view->notifications = $this->notifications;
	$view->display();
}

?>

<section class="main section">
	<div class="section-inner">
		<?php
		$errors = $this->getError();
		if (!empty($errors))
		{
			foreach ($errors as $error)
			{
				echo '<p class="error">' . $error . '</p>';
			}
		}
		?>

		<?php

		$perks = false;
		if (!empty($this->transactionInfo->tiPerks))
		{
			$perks = $this->transactionInfo->tiPerks;
			$perks = unserialize($perks);
		}

		$membershipInfo = false;
		if (!empty($this->transactionInfo->tiMeta))
		{
			$meta = unserialize($this->transactionInfo->tiMeta);

			if (!empty($meta['membershipInfo']))
			{
				$membershipInfo = $meta['membershipInfo'];
			}
		}

		$view = new \Hubzero\Component\View(array('name'=>'shared', 'layout' => 'messages'));
		$view->setError($this->getError());
		$view->display();
		?>

		<div class="grid">
			<?php
			$view = new \Hubzero\Component\View(array('name'=>'checkout', 'layout' => 'checkout_items'));
			$view->perks = $perks;
			$view->membershipInfo = $membershipInfo;
			$view->transactionItems = $this->transactionItems;
			$view->tiShippingDiscount = $this->transactionInfo->tiShippingDiscount;

			echo '<div class="col span6">';

			$view->display();

			echo '</div>';

			echo '<div class="col span6 omega orderSummary">';

			if (!empty($this->transactionInfo))
			{
				$orderTotal = $this->transactionInfo->tiSubtotal + $this->transactionInfo->tiShipping - $this->transactionInfo->tiDiscounts - $this->transactionInfo->tiShippingDiscount;
				$discount = $this->transactionInfo->tiDiscounts + $this->transactionInfo->tiShippingDiscount;

				echo '<h2>Order summary:</h2>';

				echo '<p>Order subtotal: ' . money_format('%n', $this->transactionInfo->tiSubtotal) . '</p>';

				if ($this->transactionInfo->tiShipping > 0)
				{
					echo '<p>Shipping: ' . money_format('%n', $this->transactionInfo->tiShipping) . '</p>';
				}
				if ($discount > 0)
				{
					echo '<p>Discounts: ' . money_format('%n', $discount) . '</p>';
				}

				echo '<p class="orderTotal">Order total: ' . money_format('%n', $orderTotal) . '</p>';
			}

			echo '</div>';
			?>
		</div>
		<?php
		if (in_array('shipping', $this->transactionInfo->steps))
		{
			$view = new \Hubzero\Component\View(array('name'=>'checkout', 'layout' => 'checkout_shippinginfo'));
			$view->transactionInfo = $this->transactionInfo;
			$view->display();
		}

		$orderTotal = $this->transactionInfo->tiSubtotal + $this->transactionInfo->tiShipping - $this->transactionInfo->tiDiscounts - $this->transactionInfo->tiShippingDiscount;

		if ($orderTotal > 0)
		{
			$buttonLabel = 'Proceed to payment';
			$buttonLink = JRoute::_('index.php?option=com_cart/checkout/confirm');
		}
		else
		{
			$buttonLabel = 'Place order';
			$buttonLink = JRoute::_('index.php?option=com_cart/order/place/' . $this->token);
		}
		?>
		<p class="submit">
			<a href="<?php echo $buttonLink; ?>" class="btn"><?php echo $buttonLabel; ?></a>
		</p>
	</div>
</section>