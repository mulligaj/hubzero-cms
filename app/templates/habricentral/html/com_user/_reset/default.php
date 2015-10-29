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

<?php if ( $this->params->def( 'show_page_title', 1 ) ) : ?>
	<div id="content-header" class="full">
		<h2><?php echo $this->escape($this->params->get('page_title')); ?></h2>
	</div>
<?php endif; ?>

<div class="main section">
	<form action="<?php echo Route::url( 'index.php?option=com_user&task=requestreset' ); ?>" method="post" class="josForm form-validate" name="hubForm" id="hubForm">
		<fieldset>
			<h3><?php echo Lang::txt('Email Verification Token'); ?></h3>
			
			<p><?php echo Lang::txt('RESET_PASSWORD_REQUEST_DESCRIPTION'); ?></p>
			<label for="email" class="hasTip" title="<?php echo Lang::txt('RESET_PASSWORD_EMAIL_TIP_TITLE'); ?>::<?php echo Lang::txt('RESET_PASSWORD_EMAIL_TIP_TEXT'); ?>"><?php echo Lang::txt('Email Address'); ?>:</label>
			<input id="email" name="email" type="text" class="required validate-email" size="25" />

		</fieldset>
		<div class="clear"></div>

		<p class="submit"><button type="submit" class="validate"><?php echo Lang::txt('Submit'); ?></button></p>
		<?php echo Html::input('token'); ?>
	</form>
</div>
