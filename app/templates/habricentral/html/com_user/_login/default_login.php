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

if (Plugin::isEnabled('authentication', 'openid')) :
	Lang::load( 'plg_authentication_openid',);
	$langScript =   'var JLanguage = {};'.
			' JLanguage.WHAT_IS_OPENID = \''.Lang::txt( 'WHAT_IS_OPENID' ).'\';'.
			' JLanguage.LOGIN_WITH_OPENID = \''.Lang::txt( 'LOGIN_WITH_OPENID' ).'\';'.
			' JLanguage.NORMAL_LOGIN = \''.Lang::txt( 'NORMAL_LOGIN' ).'\';'.
			' var comlogin = 1;';
	Document::addScriptDeclaration( $langScript );
	Html::script('openid.js');
endif; ?>

<?php
$sitename = Config::get('sitename');
echo $this->params->get('type');
?>

	<form action="<?php echo Route::url( 'index.php', true, $this->params->get('usesecure')); ?>"  method="post" name="com-login" id="hubForm<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>" >
	<div class="explaination">
		<?php
		$usersConfig = Component::params( 'com_users' );
		if ($usersConfig->get('allowUserRegistration')) : ?>
		<h4>No account?</h4><p><a href="<?php echo Route::url( 'index.php?option=com_register' ); ?>">
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
		<?php if ( $this->params->get( 'description_login' ) ) : ?>
		<h3 class="componentheading<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>"><?php echo $this->params->get( 'description_login_text' ); ?></h3>
		<?php // echo $this->image; ?>
		<?php endif; ?>

		<label>
			<?php echo Lang::txt('Username'); ?>:
			<input name="username" id="username" type="text" tabindex="1" class="inputbox" alt="username" size="18" />
		</label>

		<p class="hint">
			<a href="<?php echo Route::url( 'index.php?option=com_user&view=remind' ); ?>">
			<?php echo Lang::txt('FORGOT_YOUR_USERNAME'); ?></a>
		</p>

		<label>
			<?php echo Lang::txt('_PASSWORD'); ?>:
			<input type="password" tabindex="2" name="passwd" id="passwd" />
		</label>

		<p class="hint">
			<a href="<?php echo Route::url( 'index.php?option=com_user&view=reset' ); ?>">
			<?php echo Lang::txt('FORGOT_YOUR_PASSWORD'); ?></a>
		</p>

		<?php if (Plugin::isEnabled('system', 'remember')) : ?>
		<label>
			<input type="checkbox" class="option" name="remember" id="remember" value="yes" alt="Remember Me" />
			<?php echo Lang::txt('Remember me'); ?>
		</label>
		<?php endif; ?>

		<input type="hidden" name="option" value="com_user" />
		<input type="hidden" name="task" value="login" />
		<input type="hidden" name="freturn" value="<?php echo  base64_encode(  $_SERVER['REQUEST_URI'] ); ?>" />
		<input type="hidden" name="return" value="<?php echo $this->return; ?>" />
		<?php echo Html::input('token'); ?>
	</fieldset>
	<div class="clear"></div>
	<p class="submit"><input type="submit" name="Submit" class="button" value="<?php echo Lang::txt('LOGIN'); ?>" /></p>
</form>

<?php
	if (!empty($this->error_message))
		echo '<p class="error">'. $this->error_message . '</p>';
	if (!empty($this->login_attempts) && $this->login_attempts >= 2)
		echo '<p class="hint">Having trouble logging in? <a href="support/report_problems/">Report problems to Support</a>.</p>';

