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

<?php App::redirect( base64_decode($this->return) ); ?>

<?php /** @todo Should this be routed */ ?>
<form action="<?php echo Route::url( 'index.php' ); ?>" method="post" name="login" id="login">
	<?php if ( $this->params->get( 'show_logout_title' ) ) : ?>
	<div class="componentheading<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>">
		<?php echo $this->escape($this->params->get( 'header_logout' )); ?>
	</div>
	<?php endif; ?>
	<table border="0" align="center" cellpadding="4" cellspacing="0" class="contentpane<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>" width="100%">
	<tr>
		<td valign="top">
			<div>
			<?php echo $this->image; ?>
			<?php
				if ($this->params->get('description_logout')) :
					echo $this->escape($this->params->get('description_logout_text'));
				endif;
			?>
			</div>
		</td>
	</tr>
	<tr>
		<td align="center">
			<div align="center">
				<input type="submit" name="Submit" class="button" value="<?php echo Lang::txt( 'Logout' ); ?>" />
			</div>
		</td>
	</tr>
	</table>

	<br /><br />

	<input type="hidden" name="option" value="com_user" />
	<input type="hidden" name="task" value="logout" />
	<input type="hidden" name="return" value="<?php echo $this->return; ?>" />
</form>
