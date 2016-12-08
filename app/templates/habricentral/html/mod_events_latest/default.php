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

if (isset($this->error) && $this->error)
{
	?>
	<p class="error"><?php echo $this->error; ?></p>
	<?php
}
else
{
	?>
	<ul class="new latest_events_tbl">
		<?php
		if ($this->events)
		{
			$c = 0;
			$database = App::get('db');
			$cls = 'even';
			foreach ($this->events as $dayEvent)
			{
				$cls = ($cls == 'even') ? 'odd' : 'even';

				if (!isset($dayEvent->category) || !$dayEvent->category)
				{
					$database->setQuery("SELECT title FROM `#__categories` WHERE id=" . $dayEvent->catid);
					$dayEvent->category = $database->loadResult();
				}
				?>
				<li class="<?php echo $cls; ?>"<?php if ($c > 0) { echo ' style="display:none;"'; } ?>>
					<div class="item">
						<h4 class="event-title">
							<a href="<?php echo Route::url('index.php?option=com_events&task=details&id=' . $dayEvent->id); ?>"><?php echo stripslashes($dayEvent->title); ?></a>
						</h4>
						<p>
							<span class="event-date"><span class="month"><?php echo Date::of($dayEvent->publish_up)->toLocal('M'); ?></span><span class="day"><?php echo Date::of($dayEvent->publish_up)->toLocal('d'); ?></span></span>
							<span class="category"><strong>Category</strong><br /><?php echo $dayEvent->category; ?></span>
						</p>
					</div>
				</li>
				<?php
				$c++;
			}
		}
		else
		{
			?>
			<li class="odd">
				<p class="mod_events_latest_noevents"><?php echo Lang::txt('MOD_EVENTS_LATEST_NONE_FOUND'); ?></p>
			</li>
			<?php
		}
		?>
	</ul>
	<?php
}
