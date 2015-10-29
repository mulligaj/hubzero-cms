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

<?php if ($this->params->get('show_page_title',1)) : ?>
<h2 class="componentheading<?php echo $this->escape($this->params->get('pageclass_sfx')) ?>">
	<?php echo $this->escape($this->params->get('page_title')) ?>
</h2>
<?php endif; ?>

<form action="<?php echo Route::url( 'index.php?option=com_user&task=remindusername' ); ?>" method="post" class="josForm form-validate" name="hubForm" id="hubForm">

	<div class="explaination">
		<p class="info">
			If you already know your username, and only need your password reset, <a href="<?php echo Route::url('/login/reset'); ?>">go here now</a>.
		</p>
	</div>
	<fieldset>
		<h3>Recover Username(s)</h3>

		<label for="email" class="hasTip" title="<?php echo Lang::txt('REMIND_USERNAME_EMAIL_TIP_TITLE'); ?>::<?php echo Lang::txt('REMIND_USERNAME_EMAIL_TIP_TEXT'); ?>"><?php echo Lang::txt('Email Address'); ?>:</label>
		<input id="email" name="email" type="text" size="36" class="required validate-email" />

		<p><?php echo Lang::txt('REMIND_USERNAME_DESCRIPTION'); ?></p>

		<div class="help">
		<h4>What if I have also lost my password?</h4>
		<p>
			Fill out this form to retrieve your username(s). The email you 
			receive will contain instructions on how to reset your password as well.
		</p>

		<h4>What if I have multiple accounts?</h4>
		<p>
			All accounts registered to your email address will be located, and you will be given a 
			list of all of those usernames.
		</p>

		<h4>What if this cannot find my account?</h4>
		<p>
			It is possible you registered under a different email address.  Please try any other email 
			addresses you have.
		</p>
		</div>
	</fieldset>
	<div class="clear"></div>

	<p class="submit"><button type="submit" class="validate"><?php echo Lang::txt('Submit'); ?></button></p>
	<?php echo Html::input('token'); ?>
</form>
