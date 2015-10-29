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
 * @copyright Copyright 2005-2015 HUBzero Foundation, LLC.
 * @license   http://opensource.org/licenses/MIT MIT
 */

defined('_HZEXEC_') or die();
?>

<div class="componentheading">
	<h2><?php echo Lang::txt('Confirm your Account'); ?></h2>
</div>

<form action="<?php echo Route::url( 'index.php?option=com_user&task=confirmreset' ); ?>" method="post" class="josForm form-validate" name="hubForm" id="hubForm">
	<fieldset>
		<h3><?php echo Lang::txt('Email New Password'); ?></h3>

		<p><?php echo Lang::txt('RESET_PASSWORD_CONFIRM_DESCRIPTION'); ?></p>
		<label for="username" class="hasTip" title="<?php echo Lang::txt('RESET_PASSWORD_USERNAME_TIP_TITLE'); ?>::<?php echo Lang::txt('RESET_PASSWORD_USERNAME_TIP_TEXT'); ?>"><?php echo Lang::txt('User Name'); ?>:</label>
		<input id="username" name="username" type="text" class="required" size="36" />
		<label for="token" class="hasTip" title="<?php echo Lang::txt('RESET_PASSWORD_TOKEN_TIP_TITLE'); ?>::<?php echo Lang::txt('RESET_PASSWORD_TOKEN_TIP_TEXT'); ?>"><?php echo Lang::txt('Token'); ?>:</label>
		<input id="token" name="token" type="text" class="required" size="36" />
	</fieldset>
	<div class="clear"></div>

	<p class="submit"><button type="submit" class="validate"><?php echo Lang::txt('Submit'); ?></button></p>
	<?php echo Html::input('token'); ?>
</form>
