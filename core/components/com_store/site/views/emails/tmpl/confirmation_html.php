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
 * @author    Alissa Nedossekina <alisa@purdue.edu>
 * @copyright Copyright 2005-2015 HUBzero Foundation, LLC.
 * @license   http://opensource.org/licenses/MIT MIT
 */

// No direct access.
defined('_HZEXEC_') or die();

?>
	<!-- Start Header -->
	<table class="tbl-header" width="100%" cellpadding="0" cellspacing="0" border="0">
		<tbody>
			<tr>
				<td width="10%" align="left" valign="bottom" nowrap="nowrap" class="sitename">
					<?php echo Config::get('sitename'); ?>
				</td>
				<td width="80%" align="left" valign="bottom" class="tagline mobilehide">
					<span class="home">
						<a href="<?php echo Request::base(); ?>"><?php echo Request::base(); ?></a>
					</span>
					<br />
					<span class="description"><?php echo Config::get('MetaDesc'); ?></span>
				</td>
				<td width="10%" align="right" valign="bottom" nowrap="nowrap" class="component">
					<?php echo Lang::txt('COM_STORE'); ?>
				</td>
			</tr>
		</tbody>
	</table>
	<!-- End Header -->

	<!-- Start Spacer -->
	<table class="tbl-spacer" width="100%" cellpadding="0" cellspacing="0" border="0">
		<tbody>
			<tr>
				<td height="30"></td>
			</tr>
		</tbody>
	</table>
	<!-- End Spacer -->

	<!-- Start Message -->
	<table width="100%" cellpadding="0" cellspacing="0" border="0">
		<tbody>
			<tr>
				<td align="left" valign="bottom" style="border-collapse: collapse; color: #666; line-height: 1; padding: 5px; text-align: center;">
					<?php echo Lang::txt('COM_STORE_THANKYOU') . ' ' . Lang::txt('COM_STORE_IN_THE') . ' ' . Config::get('sitename') . ' ' . Lang::txt(strtolower($this->option)); ?>!
				</td>
			</tr>
		</tbody>
	</table>
	<!-- End Message -->

	<!-- Start Spacer -->
	<table class="tbl-spacer" width="100%" cellpadding="0" cellspacing="0" border="0">
		<tbody>
			<tr>
				<td height="30"></td>
			</tr>
		</tbody>
	</table>
	<!-- End Spacer -->

	<table id="resource-info" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse: collapse; line-height: 1.6em;">
		<tbody>
			<tr>
				<td class="mobilehide" style="font-size: 2.5em; font-weight: bold; text-align: center; padding: 0 30px 8px 0; vertical-align: top;" align="center" valing="top">
					<p style="display: block; border: 1px solid #c8e3c2; background: #eafbe6; margin:0; padding: 1em;">#<?php echo $this->orderid; ?></p>
				</td>
				<td width="100%" style="padding: 18px 8px 8px 8px; border-top: 2px solid #e9e9e9;">
					<table width="100%" style="border-collapse: collapse; font-size: 0.9em;" cellpadding="0" cellspacing="0" border="0">
						<tbody>
							<tr>
								<th style="text-align: right; padding: 0 0.5em; font-weight: bold; white-space: nowrap;" align="right"><?php echo Lang::txt('COM_STORE_ORDER_NUMBER'); ?>:</th>
								<td style="text-align: left; padding: 0 0.5em;" width="100%" align="left"><?php echo $this->orderid; ?></td>
							</tr>
							<tr>
								<th style="text-align: right; padding: 0 0.5em; font-weight: bold; white-space: nowrap;" align="right"><?php echo Lang::txt('COM_STORE_ORDER') . ' ' . Lang::txt('COM_STORE_TOTAL'); ?>:</th>
								<td style="text-align: left; padding: 0 0.5em;" width="100%" align="left"><?php echo $this->cost . ' ' . Lang::txt('COM_STORE_POINTS'); ?></td>
							</tr>
							<tr>
								<th style="text-align: right; padding: 0 0.5em; font-weight: bold; white-space: nowrap;" align="right"><?php echo Lang::txt('COM_STORE_ORDER_PLACED'); ?>:</th>
								<td style="text-align: left; padding: 0 0.5em;" width="100%" align="left"><?php echo Date::of()->toLocal(Lang::txt('DATE_FORMAT_HZ1')); ?></td>
							</tr>
							<tr>
								<th style="text-align: right; padding: 0 0.5em; font-weight: bold; white-space: nowrap;" align="right"><?php echo Lang::txt('COM_STORE_STATUS'); ?>:</th>
								<td style="text-align: left; padding: 0 0.5em;" width="100%" align="left"><?php echo Lang::txt('COM_STORE_RECEIVED'); ?></td>
							</tr>
							<tr>
								<th style="text-align: right; padding: 0 0.5em; font-weight: bold; white-space: nowrap; vertical-align: top;" align="right"><?php echo Lang::txt('COM_STORE_ITEMS_ORDERED'); ?>:</th>
								<td style="text-align: left; padding: 0 0.5em; vertical-align: top;" width="100%" align="left"><?php foreach ($this->items as $o) {
								$text  = $o->title . ' (x' . $o->quantity . ')';
								$text .= ($o->selectedsize) ? ' - size ' . $o->selectedsize : '';
								echo $text . '<br />';
							} ?>
								</td>
							</tr>
							<tr>
								<th style="text-align: right; padding: 0 0.5em; font-weight: bold; white-space: nowrap; vertical-align: top;" align="right"><?php echo Lang::txt('COM_STORE_SHIP_TO'); ?>:</th>
								<td style="text-align: left; padding: 0 0.5em; vertical-align: top;" width="100%" align="left">
								<?php echo $this->shipping['name']; ?><br />
								<?php echo \Hubzero\Utility\Sanitize::stripAll($this->shipping['address']); ?><br />
								<?php echo Lang::txt('COM_STORE_COUNTRY') . ': ' . $this->shipping['country']; ?>
								</td>
							</tr>
							<tr>
								<th style="text-align: right; padding: 0 0.5em; font-weight: bold; white-space: nowrap; vertical-align: top;" align="right"><?php echo Lang::txt('COM_STORE_CONTACT'); ?>:</th>
								<td style="text-align: left; padding: 0 0.5em; vertical-align: top;" width="100%" align="left"><?php echo $this->shipping['phone']  ? 'Tel. ' . $this->shipping['phone'] . '<br />' : ''; ?>
								<?php echo 'Email: ' . $this->shipping['email']; ?></td>
							</tr>
							<?php if ($this->shipping['comments']) { ?>
							<tr>
								<th style="text-align: right; padding: 0 0.5em; font-weight: bold; white-space: nowrap; vertical-align: top;" align="right"><?php echo Lang::txt('COM_STORE_DETAILS'); ?>:</th>
								<td style="text-align: left; padding: 0 0.5em; vertical-align: top;" width="100%" align="left"><?php echo \Hubzero\Utility\Sanitize::stripAll($this->shipping['comments']); ?></td>
							</tr>
							<?php } ?>
						</tbody>
					</table>
				</td>
			</tr>
		</tbody>
	</table>

	<!-- Start Spacer -->
	<table class="tbl-spacer" width="100%" cellpadding="0" cellspacing="0" border="0">
		<tbody>
			<tr>
				<td height="30"></td>
			</tr>
		</tbody>
	</table>
	<!-- End Spacer -->

	<!-- Start Footer -->
	<table class="tbl-footer" width="100%" cellpadding="0" cellspacing="0" border="0">
		<tbody>
			<tr>
				<td align="left" valign="bottom">
					<span><?php echo Config::get('sitename'); ?> sent this email because you were added to the list of recipients. Visit our <a href="<?php echo Request::base(); ?>/legal/privacy">Privacy Policy</a> and <a href="<?php echo Request::base(); ?>/support">Support Center</a> if you have any questions.</span>
				</td>
			</tr>
		</tbody>
	</table>
	<!-- End Footer -->