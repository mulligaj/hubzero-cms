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

if (!isset( $_SERVER['HTTPS'] ) || $_SERVER['HTTPS'] == 'off'):
	App::redirect( 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
	App::abort( 403, 'Forbidden: SSL is required to view this resource' );
endif;

Hubzero\Document\Assets::addComponentStylesheet('com_user');

$sitename = Config::get('sitename');
?>

<form action="<?php echo Route::url( 'index.php', true ); ?>"  method="post" name="com-login" id="hubForm">
	<div class="explaination">
	<?php
		$usersConfig = Component::params( 'com_users' );
		if ($usersConfig->get('allowUserRegistration')) : ?>
			<h4>No account?</h4><p><a href="/register">
			<?php echo Lang::txt('REGISTER'); ?></a>. It's free!</p>

			<h4>Is this really free?</h4>
			<p>Yes! Use of <?php echo $sitename; ?> resources and tools is <em>free</em> for registered users. There are no hidden costs or fees.</p>

			<h4>Why is registration required for parts of <?php echo $sitename; ?>?</h4>

			<p>Our sponsors ask us who uses <?php echo $sitename; ?> and what they use it for. Registration
			helps us answer these questions. Usage statistics also focus our attention on improvements, making the
			<?php echo $sitename; ?> experience better for <em>you</em>.</p>
		<?php endif; ?>
	</div>
	<fieldset>
		<h3 class="componentheading">Log in with your Hub account.</h3>

		<label>
			<?php echo Lang::txt('Username'); ?>:
			<input name="username" id="username" type="text" tabindex="1" class="inputbox" alt="username" size="18" />
		</label>

		<p class="hint">
			<a href="/user/remind"><?php echo Lang::txt('FORGOT_YOUR_USERNAME'); ?></a>
		</p>

		<label>
			<?php echo Lang::txt('_PASSWORD'); ?>:
			<input type="password" tabindex="2" name="passwd" id="passwd" />
		</label>

		<p class="hint">
			<a href="/user/reset"><?php echo Lang::txt('FORGOT_YOUR_PASSWORD'); ?></a>
		</p>

		<?php if (Plugin::isEnabled('system', 'remember')) : ?>
			<label>
					<input type="checkbox" class="option" name="remember" id="remember" value="yes" alt="Remember Me" />
					<?php echo Lang::txt('Remember me'); ?>
			</label>
		<?php endif; ?>

		<input type="hidden" name="option" value="com_user" />
		<input type="hidden" name="task" value="login" />
		<input type="hidden" name="return" value="<?php echo $return; ?>" />
		<input type="hidden" name="freturn" value="<?php echo  base64_encode(  $_SERVER['REQUEST_URI'] ); ?>" />
		<?php echo Html::input('token'); ?>
	</fieldset>
	<div class="clear"></div>
	<p class="submit"><input type="submit" name="Submit" class="button" value="<?php echo Lang::txt('LOGIN'); ?>" /></p>
</form>
