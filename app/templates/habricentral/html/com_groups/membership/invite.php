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

// No direct access.
defined('_HZEXEC_') or die();

?>
<div id="content-header">
	<h2><?php echo $this->title; ?></h2>
</div>

<div id="content-header-extra">
	<ul id="useroptions">
		<li class="last">
			<a class="group btn" href="<?php echo Route::url('index.php?option='.$this->option.'&cn='.$this->group->get('cn')); ?>">
				<?php echo Lang::txt('Back to Group'); ?>
			</a>
		</li>
	</ul>
</div><!-- / #content-header-extra -->

<div class="main section">
	<?php
		foreach($this->notifications as $notification) 
		{
			echo "<p class=\"{$notification['type']}\">{$notification['message']}</p>";
		}
	?>
	<form action="index.php" method="post" id="hubForm">
		<div class="explaination">
			<br />
			<h4><?php echo Lang::txt('COM_GROUPS_INVITE_SIDEBAR_HELP_TITLE'); ?></h4>
			<p><?php echo Lang::txt('COM_GROUPS_INVITE_SIDEBAR_HELP_DESC'); ?></p>
			<p><img src="/components/com_groups/assets/img/invite_example.jpg" alt="Example Auto-Completer" width="100%" style="border:3px solid #aaa;" />
		</div>
		<fieldset>
			<legend><?php echo Lang::txt('COM_GROUPS_INVITE_SECTION_TITLE'); ?></legend>
	 		<p><?php echo Lang::txt('COM_GROUPS_INVITE_SECTION_DESC',$this->group->get('description')); ?></p>

			<label>
				<?php echo Lang::txt('COM_GROUPS_INVITE_LOGINS'); ?> <span class="required"><?php echo Lang::txt('COM_GROUPS_REQUIRED'); ?></span>
				<?php 
					$mc = Event::trigger( 'hubzero.onGetMultiEntry', array(array('members', 'logins', 'acmembers')) );
					if (count($mc) > 0) {
						echo $mc[0];
					} else { ?>
						<input type="text" name="logins" id="acmembers" value="" size="35" />
					<?php } ?>
				<span class="hint"><?php echo Lang::txt('COM_GROUPS_INVITE_LOGINS_HINT'); ?></span>
			</label>
			<label for="msg">
				<?php echo Lang::txt('COM_GROUPS_INVITE_MESSAGE'); ?>
				<textarea name="msg" id="msg" rows="12" cols="50"><?php echo $this->escape(stripslashes($this->msg)); ?></textarea>
			</label>
		</fieldset>
		<div class="clear"></div>
		<input type="hidden" name="option" value="<?php echo $this->option; ?>" />
		<input type="hidden" name="controller" value="membership" />
		<input type="hidden" name="task" value="doinvite" />
		<input type="hidden" name="cn" value="<?php echo $this->group->get('cn'); ?>" />
		<input type="hidden" name="return" value="<?php echo $this->return; ?>" />
		<p class="submit">
			<input type="submit" value="<?php echo Lang::txt('COM_GROUPS_INVITE_BTN_TEXT'); ?>" />
		</p>
	</form>
</div><!-- / .main section -->
