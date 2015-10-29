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

<?php if (is_object($this->mailinglist)) : ?>
	<div class="newsletter-subscribe">
	<!--div class="mailinglist-details">
		<span class="name">
			<?php echo $this->mailinglist->name; ?>
		</span>
		<span class="description">
			<?php echo nl2br($this->mailinglist->description); ?>
		</span>
	</div-->
	<div class="item">
		<form class="mailinglist-signup" action="index.php" method="post">
			<?php if (is_object($this->subscription)) : ?>
				<span>It seems you are already subscribed to this mailing list. <a href="<?php echo Route::url('index.php?option=com_newsletter&task=subscribe'); ?>">Click here</a> to manage your newsletter mailing list subscriptions.</span>
			<?php else : ?>
				<label>Email Address: <span class="required">Required</span>
					<input type="text" name="email_<?php echo Session::getFormToken(); ?>" id="email" value="<?php echo User::get('email'); ?>" />
					<input type="hidden" name="list_<?php echo Session::getFormToken(); ?>" value="<?php echo $this->mailinglist->id; ?>" />
				</label>
				<label id="hp1">Honey Pot: <span class="optional">Please leave blank.</span>
					<input type="text" name="hp1" value="" />
				</label>
				<input type="submit" value="Sign Up!" id="sign-up-submit" class="btn" />
				<input type="hidden" name="option" value="com_newsletter" />
				<input type="hidden" name="controller" value="mailinglist" />
				<input type="hidden" name="subscriptionid" value="<?php echo $this->subscriptionId; ?>" />
				<input type="hidden" name="task" value="dosinglesubscribe" />
				<input type="hidden" name="return" value="<?php echo base64_encode($_SERVER['REQUEST_URI']); ?>">
				<?php echo JHTML::_( 'form.token' ); ?>
			<?php endif; ?>
		</form>
<?php else : ?>
	<p class="warning">
		<?php echo Lang::txt('The newsletter mailing list module setup is not complete.'); ?>
	</p>
<?php endif; ?>
	</div>
	</div>
