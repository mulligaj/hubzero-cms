<?php
/**
 * HUBzero CMS
 *
 * Copyright 2005-2014 Purdue University. All rights reserved.
 *
 * This file is part of: The HUBzero(R) Platform for Scientific Collaboration
 *
 * The HUBzero(R) Platform for Scientific Collaboration (HUBzero) is free
 * software: you can redistribute it and/or modify it under the terms of
 * the GNU Lesser General Public License as published by the Free Software
 * Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 * HUBzero is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * HUBzero is a registered trademark of Purdue University.
 *
 * @package   hubzero-cms
 * @copyright Copyright 2005-2014 Purdue University. All rights reserved.
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

$this->css('form.css');

$pdf     = $this->pdf;
$dep     = $this->dep;
$resp    = $this->resp;
$record  = $resp->getAnswers();
$layout  = $pdf->getPageLayout($record['summary']['version']);
$version = $record['summary']['version'];
?>

<header id="content-header">
	<h2>Results: <?php echo $this->title ?></h2>

	<div id="content-header-extra">
		<p>
			<a class="icon-prev back btn" href="<?php echo JRoute::_($this->base); ?>">
				<?php echo JText::_('Back to course'); ?>
			</a>
		</p>
	</div>
</header>

<section class="main section">
	<p>Completed <?php echo JHTML::_('date', $resp->getEndTime(), 'r'); ?></p>
	<p>Score <strong><?php echo $record['summary']['score'] ?>%</strong></p>

	<?php if ($this->dep->getAllowedAttempts() > 1) : ?>
		<?php $attempt = $resp->getAttemptNumber(); ?>
		<p>
			You are allowed <strong><?php echo $this->dep->getAllowedAttempts() ?></strong> attempts.
			This was your <strong><?php echo FormHelper::toOrdinal((int)$attempt) ?></strong> attempt.
		</p>
		<form action="<?php echo JRoute::_($this->base . '&task=form.complete') ?>">
			<input type="hidden" name="crumb" value="<?php echo $this->dep->getCrumb() ?>" />
			<?php $completedAttempts = $resp->getCompletedAttempts(); ?>
			<?php if ($completedAttempts && count($completedAttempts) > 0) : ?>
				<p>
					View another completed attempt:
					<select name="attempt">
						<?php foreach ($completedAttempts as $completedAttempt) : ?>
							<option value="<?php echo $completedAttempt ?>"<?php echo ($completedAttempt == $attempt) ? ' selected="selected"' : ''; ?>><?php echo FormHelper::toOrdinal($completedAttempt) ?> attempt</option>
						<?php endforeach; ?>
					</select>
					<input class="btn btn-secondary" type="submit" value="GO" />
				</p>

				<?php $nextAttempt = (count($completedAttempts) < $dep->getAllowedAttempts()) ? (count($completedAttempts)+1) : null; ?>
			<?php endif; ?>

			<?php if ($dep->getState() == 'active' && isset($nextAttempt)) : ?>
				<p>
					<a href="<?php echo JRoute::_($this->base . '&task=form.complete&crumb=' . $this->dep->getCrumb() . '&attempt=' . $nextAttempt) ?>">
						<button type="button" class="btn btn-warning">Take your next attampt!</button>
					</a>
				</p>
			<?php endif; ?>
		</form>
	<?php endif; ?>

	<ol id="pages" class="complete">
	<?php $pdf->eachPage(function($url, $idx) use($layout, $record) { ?>
		<li>
			<img src="<?php echo $url ?>" />
			<?php
			if (isset($layout[$idx - 1])):
				$qidx = 0;
				foreach ($layout[$idx - 1] as $qid=>$group):
					foreach ($group['answers'] as $aidx=>$ans):
						if (!isset($record['detail'][$qid]) || $record['detail'][$qid]['answer_id'] == 0) :
							echo '<div class="no-answer" style="top: '.($ans['top'] - 4).'px; left: '.$ans['left'].'px">No answer provided</div>';
							continue 2;
						elseif ($record['detail'][$qid]['correct_answer_id'] == $ans['id']):
							echo '<div name="question-'.$qid.'" value="'.$ans['id'].'" class="answer-marker correct" type="radio" style="top: '.($ans['top'] - 4).'px; left: '.$ans['left'].'px">&#10004;</div>';
						elseif ($record['detail'][$qid]['answer_id'] == $ans['id']):
							echo '<div name="question-'.$qid.'" value="'.$ans['id'].'" class="answer-marker incorrect" type="radio" style="top: '.($ans['top'] - 4).'px; left: '.$ans['left'].'px">&#10008;</div>';
						endif;
					endforeach;
					++$qidx;
				endforeach;
			endif; ?>
		</li>
	<?php }, $version); ?>
	</ol>
</section>