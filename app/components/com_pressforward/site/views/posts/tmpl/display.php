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
 * @author    Shawn Rice <zooley@purdue.edu>
 * @copyright Copyright 2005-2015 HUBzero Foundation, LLC.
 * @license   http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

$title = Lang::txt('COM_PRESSFORWARD');

if (Pathway::count() <= 0)
{
	Pathway::append(
		Lang::txt('COM_PRESSFORWARD'),
		'index.php?option=' . $this->option
	);
}
if ($year = $this->filters['year'])
{
	$title .= ': ' . $year;

	Pathway::append(
		$year,
		'index.php?option=' . $this->option . '&year=' . $year
	);
}
if ($month = $this->filters['month'])
{
	$title .= ': ' . $month;

	Pathway::append(
		sprintf("%02d", $month),
		'index.php?option=' . $this->option . '&year=' . $year . '&month=' . sprintf("%02d", $month)
	);
}

Document::setTitle($title);

$this->css()
     ->js();

$first = Components\PressForward\Models\Post::all()
	->whereEquals('post_type', 'post')
	->whereEquals('post_status', 'publish')
	->order('post_date_gmt', 'asc')
	->limit(1)
	->row();
?>
<header id="content-header">
	<h2><?php echo Lang::txt('COM_PRESSFORWARD'); ?></h2>

	<div id="content-header-extra">
		<?php
		$path  = 'index.php?option=' . $this->option . '&task=feed';
		$path .= ($this->filters['year']) ? '&year=' . $this->filters['year'] : '';
		$path .= ($this->filters['month']) ? '&month=' . $this->filters['month'] : '';
		$path .= ($this->filters['day']) ? '&day=' . $this->filters['day'] : '';

		$feed = Route::url($path);
		if (substr($feed, 0, 4) != 'http')
		{
			$live_site = rtrim(Request::base(),'/');

			$feed = rtrim($live_site, '/') . '/' . ltrim($feed, '/');
		}
		$feed = str_replace('https:://','http://', $feed);
		?>
		<p><a class="icon-feed feed btn" href="<?php echo $feed; ?>"><?php echo Lang::txt('PF_FEED'); ?></a></p>
	</div>
</header>

<section class="main section">
	<form action="<?php echo Route::url('index.php?option=' . $this->option . '&task=browse'); ?>" method="get" class="section-inner">
		<div class="subject">
			<?php if ($this->getError()) { ?>
				<p class="error"><?php echo $this->getError(); ?></p>
			<?php } ?>

			<div class="container data-entry">
				<input class="entry-search-submit" type="submit" value="<?php echo Lang::txt('PF_SEARCH'); ?>" />
				<fieldset class="entry-search">
					<legend><?php echo Lang::txt('PF_SEARCH_LEGEND'); ?></legend>
					<label for="entry-search-field"><?php echo Lang::txt('PF_SEARCH_LABEL'); ?></label>
					<input type="text" name="search" id="entry-search-field" value="<?php echo $this->escape($this->filters['search']); ?>" placeholder="<?php echo Lang::txt('PF_SEARCH_PLACEHOLDER'); ?>" />
					<input type="hidden" name="option" value="<?php echo $this->option; ?>" />
				</fieldset>
			</div><!-- / .container -->

			<div class="container">
				<h3>
					<?php if (isset($this->filters['search']) && $this->filters['search']) { ?>
						<?php echo Lang::txt('PF_SEARCH_FOR', $this->escape($this->filters['search'])); ?>
					<?php } else if (!isset($this->filters['year']) || !$this->filters['year']) { ?>
						<?php echo Lang::txt('PF_LATEST_ENTRIES'); ?>
					<?php } else {
						$archiveDate  = $this->filters['year'];
						$archiveDate .= ($this->filters['month']) ? '-' . $this->filters['month'] : '-01';
						$archiveDate .= '-01 00:00:00';
						if ($this->filters['month'])
						{
							echo Date::of($archiveDate)->format('M Y');
						}
						else
						{
							echo Date::of($archiveDate)->format('Y');
						}
					} ?>
				</h3>

				<?php if ($this->rows->count() > 0) { ?>
					<ol class="pf-entries entries">
					<?php
					$cls = 'even';
					foreach ($this->rows as $row)
					{
						$cls = ($cls == 'even') ? 'odd' : 'even';
						?>
						<li class="<?php echo $cls; ?>" id="e<?php echo $row->get('id'); ?>">
							<article>
								<h4 class="entry-title">
									<a href="<?php echo Route::url($row->link()); ?>">
										<?php echo $this->escape(stripslashes($row->get('post_title'))); ?>
									</a>
									<?php if (User::authorise('core.manage', $this->option)) { ?>
										<a class="delete" data-confirm="<?php echo Lang::txt('PF_CONFIRM_DELETE'); ?>" href="<?php echo Route::url('index.php?option=' . $this->option . '&alias=' . $row->get('post_name') . '&task=delete'); ?>" title="<?php echo Lang::txt('PF_DELETE'); ?>">
											<?php echo Lang::txt('PF_DELETE'); ?>
										</a>
									<?php } ?>
								</h4>
								<dl class="entry-meta">
									<dt>
										<span>
											<?php echo Lang::txt('PF_ENTRY_NUMBER', $row->get('id')); ?>
										</span>
									</dt>
									<dd class="date">
										<time datetime="<?php echo $row->published(); ?>">
											<?php echo $row->published('date'); ?>
										</time>
									</dd>
									<dd class="time">
										<time datetime="<?php echo $row->published(); ?>">
											<?php echo $row->published('time'); ?>
										</time>
									</dd>
									<?php /*if ($this->config->get('show_authors')) { ?>
										<dd class="author">
											<?php if (in_array($row->creator->get('access'), User::getAuthorisedViewLevels())) { ?>
												<a href="<?php echo Route::url($row->creator->link()); ?>">
													<?php echo $this->escape(stripslashes($row->creator->get('name'))); ?>
												</a>
											<?php } else { ?>
												<?php echo $this->escape(stripslashes($row->creator->get('name'))); ?>
											<?php } ?>
										</dd>
									<?php }*/ ?>
									<?php if ($row->get('comment_status') == 'open') { ?>
										<dd class="comments">
											<a href="<?php echo Route::url($row->link('comments')); ?>">
												<?php echo Lang::txt('PF_NUM_COMMENTS', $row->get('comment_count')); ?>
											</a>
										</dd>
									<?php } else { ?>
										<dd class="comments">
											<span>
												<?php echo Lang::txt('PF_COMMENTS_OFF'); ?>
											</span>
										</dd>
									<?php } ?>
								</dl>
								<div class="entry-content">
									<?php if ($this->config->get('cleanintro', 1)) { ?>
										<p>
											<?php echo \Hubzero\Utility\String::truncate(strip_tags($row->get('post_content')), $this->config->get('introlength', 300)); ?>
										</p>
									<?php } else { ?>
										<?php echo \Hubzero\Utility\String::truncate($row->get('post_content'), $this->config->get('introlength', 300), array('html' => true)); ?>
									<?php } ?>
								</div>
							</article>
						</li>
					<?php } ?>
					</ol>

					<?php
					echo $this->rows
						->pagination
						->setAdditionalUrlParam('year', $this->filters['year'])
						->setAdditionalUrlParam('month', $this->filters['month'])
						->setAdditionalUrlParam('day', $this->filters['day'])
						->setAdditionalUrlParam('search', $this->filters['search']);
					?>
				<?php } else { ?>
					<p class="warning"><?php echo Lang::txt('PF_NO_ENTRIES_FOUND'); ?></p>
				<?php } ?>
				<div class="clearfix"></div>
			</div><!-- / .container -->
		</div><!-- / .subject -->

		<aside class="aside">
			<div class="container pf-entries-years">
				<h4><?php echo Lang::txt('PF_ARCHIVE'); ?></h4>
				<ol>
				<?php
				if ($first->get('ID'))
				{
					$start = intval(substr($first->get('post_date_gmt'), 0, 4));
					$now = Date::format("Y");

					for ($i=$now, $n=$start; $i >= $n; $i--)
					{
					?>
						<li>
							<a href="<?php echo Route::url('index.php?option=' . $this->option . '&year=' . $i); ?>"><?php echo $i; ?></a>
						<?php if (($this->filters['year'] && $i == $this->filters['year']) || (!$this->filters['year'] && $i == $now)) { ?>
							<ol>
							<?php
							$m = array(
								'PF_JANUARY',
								'PF_FEBRUARY',
								'PF_MARCH',
								'PF_APRIL',
								'PF_MAY',
								'PF_JUNE',
								'PF_JULY',
								'PF_AUGUST',
								'PF_SEPTEMBER',
								'PF_OCTOBER',
								'PF_NOVEMBER',
								'PF_DECEMBER'
							);
							//if (($this->year && $i == $this->year) || (!$this->year && $i == $now)) {
							if ($i == $now)
							{
								$months = Date::format("m");
							}
							else
							{
								$months = 12;
							}

							for ($k=0, $z=$months; $k < $z; $k++)
							{
							?>
								<li>
									<a<?php if ($this->filters['month'] && $this->filters['month'] == ($k+1)) { echo ' class="active"'; } ?> href="<?php echo Route::url('index.php?option=' . $this->option . '&year=' . $i . '&month=' . sprintf("%02d", ($k+1), 1)); ?>"><?php echo Lang::txt($m[$k]); ?></a>
								</li>
							<?php
							}
							?>
							</ol>
						<?php } ?>
						</li>
					<?php
					}
				} else { ?>
					<p><?php echo Lang::txt('PF_NO_ENTRIES_FOUND'); ?></p>
				<?php } ?>
				</ol>
			</div><!-- / .pf-entries-years -->

			<div class="container pf-popular-entries">
				<h4><?php echo Lang::txt('PF_RECENT_ENTRIES'); ?></h4>
				<?php
				$popular = Components\PressForward\Models\Post::all()
					->whereEquals('post_type', 'post')
					->whereEquals('post_status', 'publish')
					->order('post_date_gmt', 'desc')
					->limit(5)
					->rows();
				if ($popular->count()) { ?>
					<ol>
					<?php foreach ($popular as $row) { ?>
						<li>
							<a href="<?php echo Route::url($row->link()); ?>">
								<?php echo $this->escape(stripslashes($row->get('post_title'))); ?>
							</a>
						</li>
					<?php } ?>
					</ol>
				<?php } else { ?>
					<p><?php echo Lang::txt('PF_NO_ENTRIES_FOUND'); ?></p>
				<?php } ?>
			</div><!-- / .pf-popular-entries -->
		</aside><!-- / .aside -->
	</form>
</section><!-- / .main section -->
