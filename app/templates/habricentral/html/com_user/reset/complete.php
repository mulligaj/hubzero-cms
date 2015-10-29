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
	<h2><?php echo Lang::txt('Reset your Password'); ?></h2>
</div>

<form action="<?php echo Route::url( 'index.php?option=com_user&task=completereset' ); ?>" method="post" class="josForm form-validate" name="hubForm" id="hubForm">
	<fieldset>
		<h3><?php echo Lang::txt('New Password'); ?></h3>

		<p><?php echo Lang::txt('RESET_PASSWORD_COMPLETE_DESCRIPTION'); ?></p>

		<label for="password1" class="hasTip" title="<?php echo Lang::txt('RESET_PASSWORD_PASSWORD1_TIP_TITLE'); ?>::<?php echo Lang::txt('RESET_PASSWORD_PASSWORD1_TIP_TEXT'); ?>"><?php echo Lang::txt('Password'); ?>:</label>
		<input id="password1" name="password1" type="password" class="required validate-password" />

		<label for="password2" class="hasTip" title="<?php echo Lang::txt('RESET_PASSWORD_PASSWORD2_TIP_TITLE'); ?>::<?php echo Lang::txt('RESET_PASSWORD_PASSWORD2_TIP_TEXT'); ?>"><?php echo Lang::txt('Verify Password'); ?>:</label>
		<input id="password2" name="password2" type="password" class="required validate-password" />
	</fieldset>
	<div class="clear"></div>

	<p class="submit"><button type="submit" class="validate"><?php echo Lang::txt('Submit'); ?></button></p>
	<?php echo html::input('token'); ?>
</form>