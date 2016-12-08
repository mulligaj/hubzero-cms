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

$rows = $this->rows;

$option = Request::getCmd('option');
$c = 0;
?>

<?php if (count($rows) > 0) : ?>
	<ul class="new">
		<?php foreach ($rows as $row) : ?>
			<li<?php if ($option == 'com_content' && $c > 0) { echo ' style="display:none;"'; } ?>>
				<div class="item">
					<h4 class="new-title">
						<a href="<?php echo Route::url($row->href); ?>"><?php echo Hubzero\Utility\String::truncate(stripslashes($row->title), 200); ?></a>
					</h4>
					<p>
						<span class="new-details">
							<?php 
							if ($row->area) {
								echo "<a href=\"" . Route::url('index.php?option=com_resources&type=' . str_replace(' ', '', strtolower($row->area))) . "\">" . Lang::txt($row->area) . "</a>";
							} else {
								echo Lang::txt(strtoupper($row->section));
							}
							?>
						</span>
						<span class="new-date"><?php echo date("F jS, Y", strtotime($row->publish_up)); ?></span>
					</p>
				</div>
			</li>
			<?php $c++; ?>
		<?php endforeach; ?>
	</ul>
<?php else : ?>
	<p>
		<?php echo Lang::txt('MOD_WHATSNEW_NO_RESULTS'); ?>
	</p>
<?php endif; ?>

<?php /*
<p class="more">
	<a href="<?php echo Route::url('index.php?option=com_whatsnew&period='.$this->area.':'.$this->period); ?>">More Resources &rsaquo;</a>
</p>

<?php if ($this->feed) : ?>
	<a class="newsfeed" href="<?php echo $this->feedlink; ?>" title="<?php echo Lang::txt('MOD_WHATSNEW_SUBSCRIBE'); ?>">
		<?php echo Lang::txt('MOD_WHATSNEW_NEWS_FEED'); ?>
	</a>
<?php endif;*/ ?>
