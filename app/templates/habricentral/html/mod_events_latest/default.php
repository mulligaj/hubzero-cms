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
		$database = App::get('db');
		$cls = 'even';
		foreach ($this->events as $dayEvent)
		{
			//reset($daysEvents);

			// Get all of the events for this day
			//foreach ($daysEvents as $dayEvent)
			//{
				// Get the title and start time
				/*$startDate = $dayEvent->publish_up;
				$eventDate = mktime(substr($startDate, 11, 2), substr($startDate, 14, 2), substr($startDate, 17, 2), date('m'), date('d') + $relDay, date('Y'));
				$startDate = mktime(substr($startDate, 11, 2), substr($startDate, 14, 2), substr($startDate, 17, 2), substr($startDate, 5, 2), substr($startDate, 8, 2), substr($startDate, 0, 4));
				$endDate = $dayEvent->publish_down;
				$endDate = mktime(substr($endDate, 11, 2), substr($endDate, 14, 2), substr($endDate, 17, 2), substr($endDate, 5, 2), substr($endDate, 8, 2), substr($endDate, 0, 4));

				$year  = date('Y', $startDate);
				$month = date('m', $startDate);
				$day   = date('d', $startDate);*/

				$cls = ($cls == 'even') ? 'odd' : 'even';

				/*if ($dayEvent->announcement == 1)
				{
					$cls .= ' announcement';
				}*/
				if (!isset($dayEvent->category) || !$dayEvent->category)
				{
					$database->setQuery("SELECT title FROM `#__categories` WHERE id=" . $dayEvent->catid);
					$dayEvent->category = $database->loadResult();
				}
	?>
			<li class="<?php echo $cls; ?>">
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
			//}
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
