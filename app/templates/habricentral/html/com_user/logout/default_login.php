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
<?php if (Plugin::isEnabled('authentication', 'openid')) :
		Lang::load('plg_authentication_openid');

		$langScript = 	'var JLanguage = {};'.
						' JLanguage.WHAT_IS_OPENID = \''.Lang::txt( 'WHAT_IS_OPENID' ).'\';'.
						' JLanguage.LOGIN_WITH_OPENID = \''.Lang::txt( 'LOGIN_WITH_OPENID' ).'\';'.
						' JLanguage.NORMAL_LOGIN = \''.Lang::txt( 'NORMAL_LOGIN' ).'\';'.
						' var comlogin = 1;';
		Document::addScriptDeclaration( $langScript );
		Html::script('openid.js');
endif; ?>
<form action="<?php echo Route::url( 'index.php', true, $this->params->get('usesecure')); ?>" method="post" name="com-login" id="com-form-login">
	<table width="100%" border="0" align="center" cellpadding="4" cellspacing="0" class="contentpane<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>">
		<tbody>
			<tr>
				<td colspan="2">
					<?php if ( $this->params->get( 'show_login_title' ) ) : ?>
					<div class="componentheading<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>">
						<?php echo $this->params->get( 'header_login' ); ?>
					</div>
					<?php endif; ?>
					<div>
						<?php echo $this->image; ?>
						<?php if ( $this->params->get( 'description_login' ) ) : ?>
							<?php echo $this->params->get( 'description_login_text' ); ?>
							<br /><br />
						<?php endif; ?>
					</div>
				</td>
			</tr>
		</tbody>
	</table>
	<fieldset class="input">
		<p id="com-form-login-username">
			<label for="username"><?php echo Lang::txt('Username') ?></label><br />
			<input name="username" id="username" type="text" class="inputbox" alt="username" size="18" />
		</p>
		<p id="com-form-login-password">
			<label for="passwd"><?php echo Lang::txt('Password') ?></label><br />
			<input type="password" id="passwd" name="passwd" class="inputbox" size="18" alt="password" />
		</p>
		<?php if(JPluginHelper::isEnabled('system', 'remember')) : ?>
		<p id="com-form-login-remember">
			<label for="remember"><?php echo Lang::txt('Remember me') ?></label>
			<input type="checkbox" id="remember" name="remember" class="inputbox" value="yes" alt="Remember Me" />
		</p>
		<?php endif; ?>
		<input type="submit" name="Submit" class="button" value="<?php echo Lang::txt('LOGIN') ?>" />
	</fieldset>
	<ul>
		<li>
			<a href="<?php echo Route::url( 'index.php?option=com_user&view=reset' ); ?>">
			<?php echo Lang::txt('FORGOT_YOUR_PASSWORD'); ?></a>
		</li>
		<li>
			<a href="<?php echo Route::url( 'index.php?option=com_user&view=remind' ); ?>">
			<?php echo Lang::txt('FORGOT_YOUR_USERNAME'); ?></a>
		</li>
		<?php
		$usersConfig = Component::params( 'com_users' );
		if ($usersConfig->get('allowUserRegistration')) : ?>
		<li>
			<a href="<?php echo Route::url( 'index.php?option=com_user&view=register' ); ?>">
				<?php echo Lang::txt('REGISTER'); ?></a>
		</li>
		<?php endif; ?>
	</ul>

	<input type="hidden" name="option" value="com_user" />
	<input type="hidden" name="task" value="login" />
	<input type="hidden" name="return" value="<?php echo $this->return; ?>" />
	<?php echo Html::input('token'); ?>
</form>
