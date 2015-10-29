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

//get the rows
$rows = $this->rows;

//feedlink
$feedlink  = $this->feedlink;
?>

<?php if(count($rows) > 0) : ?>
	<ul class="questions">
		<?php foreach($rows as $row) :?>
			<li>
				<h4>
					<a href="<?php echo Route::url('index.php?option=com_answers&task=question&id='.$row->id); ?>" title="Question: <?php echo $row->subject; ?>">
						<?php echo $row->subject; ?>
					</a>
				</h4>

					<?php
						if ($row->anonymous) {
							$name = "<i>" . Lang::txt('MOD_RECENTQUESTIONS_ANONYMOUS') . "</i>";
						} else {
							$user = User::getInstance($row->created_by);
							if (is_object($user))
							{
								$name = '<a href="'.Route::url('index.php?option=com_members&id='.$user->get('id')).'">'.stripslashes($user->get('name')).'</a>';
							}
						}
					?>
				<span class="question-author"><?php echo $name . ",&nbsp;"; ?></span>
				<span class="question-date"><?php echo date("F jS, Y, g:ia", strtotime($row->created)); ?></span>
				<span class="question-answers">
					<?php echo $row->rcount . " Response"; if($row->rcount > 1) { echo "s"; } ?>
				</span>
				
				<span class="question-question"><?php echo substr($row->question,0, 100); if(strlen($row->question) > 100) { echo "&hellip;"; } ?></span>
			</li>
		<?php endforeach; ?>
	</ul>
<?php else : ?>
	<p><?php echo Lang::txt('MOD_RECENTQUESTIONS_NO_RESULTS'); ?></p>
<?php endif; ?>

<p class="more">
	<a href="<?php echo Route::url('index.php?option=com_answers'); ?>">More Questions &rsaquo;</a>
</p>

<?php if ($feedlink == "yes") : ?>
	<a href="<?php echo Route::url('index.php?option=com_answers&task=latest.rss&m='.$modrecentquestions->id, true, -1); ?>" class="newsfeed" title="Latest Question's Feed">Latest Question's Feed</a>
	<br class="clear" />
<?php endif; ?>




