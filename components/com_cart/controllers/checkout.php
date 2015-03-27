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
 * @copyright Copyright 2005-2012 Purdue University. All rights reserved.
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

require_once(JPATH_COMPONENT . DS . 'models' . DS . 'CurrentCart.php');

/**
 * Courses controller class
 */
class CartControllerCheckout extends ComponentController
{
	/**
	 * Execute a task
	 *
	 * @return     void
	 */
	public function execute()
	{
		// Get the task
		$this->_task  = JRequest::getVar('task', '');

		if (empty($this->_task))
		{
			$this->_task = 'checkout';
			$this->registerTask('__default', $this->_task);
		}

		$this->juser = JFactory::getUser();

		// Check if they're logged in
		if ($this->juser->get('guest'))
		{
			$this->login('Please login to continue');
			return;
		}

		parent::execute();
	}

	/**
	 * Checkout entry point. Begin checkout -- check, create, or update transaction and redirect to the next step
	 *
	 * @param	void
	 * @return	void
	 */
	public function checkoutTask()
	{
		$cart = new CartModelCurrentCart();

		// This is a starting point in checkout process. All existing transactions for this user
		// have to be removed and a new one has to be created.
		// Do the final check of the cart

		// Get the latest synced cart info, it will also enable cart syncing that was turned off before
		// (this should also kill old transaction info)
		$cart->getCartInfo(true);

		// Check if there are messages to display
		if ($cart->hasMessages())
		{
			// redirect back to cart to display all messages
			$redirect_url = JRoute::_('index.php?option=' . 'com_cart');
			$app = JFactory::getApplication();
			$app->redirect($redirect_url);
		}

		// Check/create/update transaction here
		$transactionInfo = $cart->getTransaction();

		// Redirect to cart if no transaction items (no cart items)
		if (!$transactionInfo)
		{
			$cart->redirect('home');
		}

		// Redirect to the final step if transaction is ready to go to the payment phase (???)
		$cart->redirect('continue');

		//$this->printTransaction($transactionInfo);
	}

	/**
	 * Continue checkout -- decides where to take the checkout process next
	 *
	 * @param	void
	 * @return	void
	 */
	public function continueTask()
	{
		/* Decide where to go next */
		$cart = new CartModelCurrentCart();

		// Check/create/update transaction here
		$transactionInfo = $cart->getTransaction();

		// Redirect to cart if no transaction items (no cart items)
		if (!$transactionInfo)
		{
			$cart->redirect('checkout');
		}

		// Redirect to the next step
		$nextStep = $cart->getNextCheckoutStep();
		$cart->redirect($nextStep);
	}

	/**
	 * Shipping step of the checkout
	 *
	 * @return     void
	 */
	public function shippingTask()
	{
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_cart' . DS . 'models' . DS . 'CurrentCart.php');
		$cart = new CartModelCurrentCart();

		// initialize address set var
		$addressSet = false;

		$params = $this->getParams(array('action', 'saId'));

		$errors = array();

		if (!empty($params) && !empty($params->action) && !empty($params->saId) && $params->action == 'select')
		{
			try
			{
				$this->selectSavedShippingAddress($params->saId, $cart);
				$addressSet = true;
			}
			catch (Exception $e)
			{
				$errors[] = array($e->getMessage(), 'error');
			}
		}

		$transaction = $cart->liftTransaction();

		if (!$transaction)
		{
			// Redirect to cart if transaction cannot be lifted
			$cart->redirect('home');
		}

		// handle non-ajax form submit
		$shippingInfoSubmitted = JRequest::getVar('submitShippingInfo', false, 'post');

		if ($shippingInfoSubmitted)
		{
			$res = $cart->setTransactionShippingInfo();

			if ($res->status)
			{
				$addressSet = true;
			}
			else
			{
				foreach ($res->errors as $error)
				{
					$errors[] = array($error, 'error');
				}
			}
		}

		// Calculate shipping charge
		if ($addressSet)
		{
			// TODO Calculate shipping
			$shippingCost = 22.22;
			$cart->setTransactionShippingCost($shippingCost);

			$cart->setStepStatus('shipping');

			$nextStep = $cart->getNextCheckoutStep();
			$cart->redirect($nextStep);
		}

		if (!empty($errors))
		{
			$this->view->notifications = $errors;
		}

		$savedShippingAddresses = $cart->getSavedShippingAddresses($this->juser->id);
		$this->view->savedShippingAddresses = $savedShippingAddresses;
		$this->view->display();
	}

	/**
	 * Select saved shipping address
	 *
	 * @return     void
	 */
	private function selectSavedShippingAddress($saId, $cart)
	{
		// ajax vs non-ajax
		$cart->setSavedShippingAddress($saId);
	}

	/**
	 * Summary step of the checkout
	 *
	 * @return     void
	 */
	public function summaryTask()
	{
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_cart' . DS . 'models' . DS . 'CurrentCart.php');
		$cart = new CartModelCurrentCart();

		$transaction = $cart->liftTransaction();

		if (!$transaction)
		{
			$cart->redirect('home');
		}

		// Generate security token
		$token = $cart->getToken();

		// Check if there are any steps missing. Redirect if needed
		$nextStep = $cart->getNextCheckoutStep();

		if ($nextStep != 'summary')
		{
			$cart->redirect($nextStep);
		}

		$cart->finalizeTransaction();

		$this->view->token = $token;
		$this->view->transactionItems = $transaction->items;
		$this->view->transactionInfo = $transaction->info;
		$this->view->display();
	}

	/**
	 * Confirm step of the checkout. Should be a pass-through page for JS-enabled browsers, requires a form submission to the payment gateway
	 *
	 * @return     void
	 */
	public function confirmTask()
	{
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_cart' . DS . 'models' . DS . 'CurrentCart.php');
		$cart = new CartModelCurrentCart();

		$transaction = $cart->liftTransaction();
		if (!$transaction)
		{
			$cart->redirect('home');
		}

		// Get security token
		$transaction->token = $cart->getToken();

		// Check if there are any steps missing. Redirect if needed
		$nextStep = $cart->getNextCheckoutStep();

		if ($nextStep != 'summary')
		{
			$cart->redirect($nextStep);
		}

		// Final step here before payment
		CartModelCart::updateTransactionStatus('awaiting payment', $transaction->info->tId);

		// Generate payment code
		$params =  JComponentHelper::getParams(JRequest::getVar('option'));
		$paymentGatewayProivder = $params->get('paymentProvider');

		include_once(JPATH_COMPONENT . DS . 'lib' . DS . 'payment' . DS . 'PaymentDispatcher.php');
		$paymentDispatcher = new PaymentDispatcher($paymentGatewayProivder);
		$pay = $paymentDispatcher->getPaymentProvider();

		$pay->setTransactionDetails($transaction);

		$error = false;
		try
		{
			$paymentCode = $pay->getPaymentCode();
			$this->view->paymentCode = $paymentCode;
		}
		catch (Exception $e)
		{
			$error = $e->getMessage();
		}

		if (!empty($error))
		{
			$this->view->setError($error);
		}

		$this->view->display();
	}

	/**
	 * Redirect to login page
	 *
	 * @return void
	 */
	private function login($message = '')
	{
		$return = base64_encode($_SERVER['REQUEST_URI']);
		$this->setRedirect(
			JRoute::_('index.php?option=com_users&view=login&return=' . $return),
			$message,
			'warning'
		);
		return;
	}

	/**
	 * Print transacttion info
	 *
	 * @return     void
	 */
	private function printTransaction($t)
	{
		echo '<div class="cartSection">';
		foreach($t as $k => $v)
		{
			echo '<p>';
			echo $v['info']->pName;
			echo ' @ ';
			echo $v['info']->sPrice;
			echo ' x';
			echo $v['transactionInfo']->qty;
			echo ' @ ';
			echo $v['transactionInfo']->tiPrice;
			echo '</p>';
		}
		echo '</div>';
	}
}

