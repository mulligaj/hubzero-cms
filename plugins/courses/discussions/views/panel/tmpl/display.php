<?php
defined('_JEXEC') or die( 'Restricted access' );

$juser = JFactory::getUser();

$base = $this->offering->link() . '&active=discussions';

$instructors = array();

$inst = $this->course->instructors();
if (count($inst) > 0)
{
	foreach ($inst as $i)
	{
		$instructors[] = $i->get('user_id');
	}
}
?>
<?php if (!$this->course->offering()->section()->access('view')) : ?>
	<?php
		$view = new \Hubzero\Plugin\View(array(
			'folder'  => 'courses',
			'element' => 'outline',
			'name'    => 'shared',
			'layout'  => '_not_enrolled'
		));

		$view->set('course', $this->course)
		     ->set('option', $this->option)
		     ->set('message', 'You must be enrolled to utilize the discussion feature.')
		     ->display();

		return;
	?>
<?php endif; ?>
<?php if ($this->course->access('manage', 'offering')) { ?>
	<div id="manager-options">
		<p><a class="btn" href="<?php echo JRoute::_($base . '&unit=manage'); ?>"><?php echo JText::_('Manage'); ?></a></p>
	</div>
<?php } ?>
<div id="comments-container">
<?php foreach ($this->notifications as $notification) { ?>
	<p class="<?php echo $notification['type']; ?>"><?php echo $this->escape($notification['message']); ?></p>
<?php } ?>
	<div class="comments-wrap">
		<div class="comments-views">

			<div class="comments-feed">
				<div class="comments-toolbar cf">
					<p class="comment-sort-options">
						<?php echo JText::sprintf('%s Discussions', $this->stats->threads); ?>
					</p>
					<p class="comments-controls">
						<a class="add active" href="<?php echo JRoute::_($base); ?>" title="<?php echo JText::_('Start a new discussion'); ?>"><?php echo JText::_('New'); ?></a>
					</p>
				</div><!-- / .comments-toolbar -->

				<div class="comments-options-bar">
					<form class="comments-search" action="<?php echo JRoute::_($base); ?>" method="get">
						<fieldset>
							<input type="text" name="search" class="search" value="<?php echo $this->escape($this->filters['search']); ?>" placeholder="<?php echo JText::_('search ...'); ?>" />
							<input type="submit" class="submit" value="<?php echo JText::_('Go'); ?>" />

							<input type="hidden" name="option" value="<?php echo $this->option; ?>" />
							<input type="hidden" name="gid" value="<?php echo $this->course->get('alias'); ?>" />
							<input type="hidden" name="offering" value="<?php echo $this->offering->alias(); ?>" />
							<input type="hidden" name="active" value="discussions" />
							<input type="hidden" name="action" value="search" />
						</fieldset>
					</form>
				</div><!-- / .comments-options-bar -->

				<div class="comment-threads">
					<div class="category search-results hide">
						<div class="category-header">
							<span class="category-title"><?php echo JText::_('Search'); ?></span>
						</div>
						<div class="category-content">
						</div>
					</div>

					<div class="category category-results" id="ctmine">
						<?php
						$filters = array();
						$filters['scope']      = $this->filters['scope'];
						$filters['scope_id']   = $this->filters['scope_id'];
						if ($this->config->get('discussions_threads', 'all') != 'all')
						{
							$filters['scope_sub_id']   = $this->filters['scope_sub_id'];
						}
						$filters['state']      = 1;
						$filters['sort_Dir']   = 'DESC';
						$filters['limit']      = 100;
						$filters['start']      = 0;
						$filters['created_by'] = $juser->get('id');
						$filters['parent']     = 0;
						$filters['sticky']     = false;
						?>
						<div class="category-header">
							<span class="category-title"><?php echo JText::_('Mine'); ?></span>
							<span class="category-discussions count"><?php echo $this->post->getCount($filters); ?></span>
						</div><!-- / .category-header -->
						<div class="category-content">
							<?php
							$this->view('_threads', 'threads')
							     ->set('category', 'categorymine')
							     ->set('option', $this->option)
							     ->set('threads', $this->post->getRecords($filters))
							     ->set('unit', '')
							     ->set('lecture', 0)
							     ->set('config', $this->config)
							     ->set('instructors', $instructors)
							     ->set('cls', 'odd')
							     ->set('base', $base)
							     ->set('course', $this->course)
							     ->set('prfx', 'mine')
							     ->set('active', $this->thread)
							     ->display();
							?>
						</div><!-- / .category-content -->
					</div><!-- / .category -->
		<?php if (count($this->sections) > 0) { ?>
			<?php
				$tfilters = array();
				$tfilters['scope']      = $this->filters['scope'];
				$tfilters['scope_id']   = $this->filters['scope_id'];
				if ($this->config->get('discussions_threads', 'all') != 'all')
				{
					$tfilters['scope_sub_id']   = $this->filters['scope_sub_id'];
				}
				$tfilters['state']       = 1;
				//$tfilters['category_id'] = $row->id;
				$tfilters['sort_Dir']    = 'DESC';
				$tfilters['limit']       = (100 * count($this->sections));
				$tfilters['start']       = 0;
				$tfilters['parent']      = 0;
				$tfilters['sticky']     = false;

				$threads = array();
				$results = $this->post->getRecords($tfilters);
				if ($results)
				{
					foreach ($results as $thread)
					{
						if (!isset($threads[$thread->category_id]))
						{
							$threads[$thread->category_id] = array();
						}
						$threads[$thread->category_id][] = $thread;
					}
				}
			?>
			<?php foreach ($this->sections as $section) { ?>
					<div class="category category-results closed" id="sc<?php echo $section->id; ?>">
						<div class="category-header">
							<span class="category-title"><?php echo $this->escape(stripslashes($section->title)); ?></span>
							<span class="category-discussions count"><?php echo $section->threads; ?></span>
						</div><!-- / .category-header -->
						<div class="category-content">
						<?php
						if ($section->categories)
						{
							foreach ($section->categories as $row)
							{
								?>
								<div class="thread closed" id="ct<?php echo $row->id; ?>" data-category="<?php echo $row->id; ?>">
									<div class="thread-header">
										<span class="thread-title"><?php echo $this->escape(stripslashes($row->title)); ?></span>
										<span class="thread-discussions count"><?php echo $row->threads; ?></span>
									</div><!-- / .thread-header -->
									<div class="thread-content">
										<?php
											$this->view('_threads', 'threads')
											     ->set('category', 'category' . $row->id)
											     ->set('option', $this->option)
											     ->set('threads', isset($threads[$row->id]) ? $threads[$row->id] : null)
											     ->set('unit', $row->alias)
											     ->set('lecture', $row->id)
											     ->set('config', $this->config)
											     ->set('instructors', $instructors)
											     ->set('cls', 'odd')
											     ->set('base', $base)
											     ->set('course', $this->course)
											     ->set('active', $this->thread)
											     ->display();
										?>
									</div><!-- / .thread-content -->
								</div><!-- / .thread -->
								<?php
							}
							?>
						<?php } else { ?>
							<p class="instructions">
								There are no categories for this section.
							</p>
						<?php } ?>
						</div><!-- / .category-content -->
					</div><!-- / .category -->
			<?php } ?>
		<?php } ?>
				</div><!-- / .comment-threads -->

			</div><!-- / .comments-feed -->

			<div class="comments-panel">
				<div class="comments-toolbar">
					<p><span class="comments" data-comments="%s comments" data-add="<?php echo JText::_('Start a discussion'); ?>"><?php echo JText::_('Start a discussion'); ?></span></p>
				</div><!-- / .comments-toolbar -->
				<div class="comments-frame">

					<?php
					$c = 0;
					foreach ($this->sections as $section)
					{
						if ($section->categories)
						{
							$c++;
						}
					}
					if ($c) {
					?>

					<form action="<?php echo JRoute::_($base); ?>" method="post" id="commentform"<?php if ($this->data) { echo ' class="hide"'; } ?> enctype="multipart/form-data">
						<p class="comment-member-photo">
							<?php
							$anon = 1;
							if (!$juser->get('guest'))
							{
								$anon = 0;
							}
							$now = JFactory::getDate();
							?>
							<img src="<?php echo \Hubzero\User\Profile\Helper::getMemberPhoto($juser, $anon); ?>" alt="<?php echo JText::_('User photo'); ?>" />
						</p>

						<fieldset>
						<?php if ($juser->get('guest')) { ?>
							<p class="warning"><?php echo JText::_('PLG_COURSES_DISCUSSIONS_LOGIN_COMMENT_NOTICE'); ?></p>
						<?php } else { ?>
							<p class="comment-title">
								<strong>
									<a href="<?php echo JRoute::_('index.php?option=com_members&id=' . $juser->get('id')); ?>"><?php echo $this->escape($juser->get('name')); ?></a>
								</strong>
								<span class="permalink">
									<span class="comment-date-at">@</span>
									<span class="time"><time datetime="<?php echo $now; ?>"><?php echo JHTML::_('date', $now, JText::_('TIME_FORMAt_HZ1')); ?></time></span>
									<span class="comment-date-on"><?php echo JText::_('PLG_COURSES_DISCUSSIONS_ON'); ?></span>
									<span class="date"><time datetime="<?php echo $now; ?>"><?php echo JHTML::_('date', $now, JText::_('DATE_FORMAt_HZ1')); ?></time></span>
								</span>
							</p>

							<label for="field_comment">
								<span class="label-text"><?php echo JText::_('PLG_COURSES_DISCUSSIONS_FIELD_COMMENTS'); ?></span>
								<?php
								echo \JFactory::getEditor()->display('fields[comment]', '', '', '', 35, 5, false, 'field_comment', null, null, array('class' => 'minimal no-footer'));
								?>
							</label>

							<div class="grid">
								<div class="col span-half">
							<label for="field-upload" id="comment-upload">
								<span class="label-text"><?php echo JText::_('PLG_COURSES_DISCUSSIONS_LEGEND_ATTACHMENTS'); ?>:</span>
								<input type="file" name="upload" id="field-upload" />
							</label>
								</div>
								<div class="col span-half omega">
									<label for="field-category_id">
									<span class="label-text"><?php echo JText::_('PLG_COURSES_DISCUSSIONS_FIELD_CATEGORY'); ?></span>
									<select name="fields[category_id]" id="field-category_id">
										<option value="0"><?php echo JText::_('PLG_COURSES_DISCUSSIONS_FIELD_CATEGORY_SELECT'); ?></option>
				<?php
								foreach ($this->sections as $section)
								{
									if ($section->categories)
									{
				?>
										<optgroup label="<?php echo $this->escape(stripslashes($section->title)); ?>">
				<?php
										foreach ($section->categories as $category)
										{
											if ($category->closed)
											{
												continue;
											}
				?>
										<option value="<?php echo $category->id; ?>"><?php echo $this->escape(stripslashes($category->title)); ?></option>
				<?php
										}
				?>
										</optgroup>
				<?php
									}
								}
				?>
									</select>
								</label>
								</div>
							</div>

							<label for="field-anonymous" id="comment-anonymous-label">
								<input class="option" type="checkbox" name="fields[anonymous]" id="field-anonymous" value="1" />
								<?php echo JText::_('PLG_COURSES_DISCUSSIONS_FIELD_ANONYMOUS'); ?>
							</label>

							<p class="submit">
								<input type="submit" value="<?php echo JText::_('PLG_COURSES_DISCUSSIONS_SUBMIT'); ?>" />
							</p>
						<?php } ?>
						</fieldset>
						<input type="hidden" name="fields[parent]" id="field-parent" value="0" />
						<input type="hidden" name="fields[state]" id="field-state" value="1" />
						<input type="hidden" name="fields[scope]" id="field-scope" value="course" />
						<input type="hidden" name="fields[scope_id]" id="field-scope_id" value="<?php echo $this->post->get('scope_id'); ?>" />
						<input type="hidden" name="fields[scope_sub_id]" id="field-scope_sub_id" value="<?php echo $this->post->get('scope_sub_id'); ?>" />
						<input type="hidden" name="fields[id]" id="field-id" value="" />
						<input type="hidden" name="fields[object_id]" id="field-object_id" value="" />

						<input type="hidden" name="option" value="<?php echo $this->option; ?>" />
						<input type="hidden" name="gid" value="<?php echo $this->course->get('alias'); ?>" />
						<input type="hidden" name="offering" value="<?php echo $this->offering->alias(); ?>" />
						<input type="hidden" name="active" value="discussions" />
						<input type="hidden" name="action" value="savethread" />

						<?php echo JHTML::_('form.token'); ?>

						<p class="instructions">
							<?php echo JText::_('Click on a section and category to the left to view a list of comments.'); ?><br /><br />
							<?php echo JText::_('Click on a comment on the left to view a discussion or start your own above.'); ?>
						</p>
					</form>
					<?php } else { ?>
						<p class="instructions">
							<?php echo JText::_('This forum is currently empty and requires some set-up by the course managers before it can be used.'); ?>
							<?php if ($this->course->access('manage', 'offering')) { ?>
								<br /><br /><?php echo JText::_('Discussions require at least one section and category before posts can be made. Click the "manage" button to set up this forum.'); ?>
							<?php } ?>
						</p>
					<?php } ?>
					<div class="comment-thread"><?php if ($this->data) { echo $this->data->html; } ?></div>
				</div><!-- / .comments-frame -->
			</div><!-- / .comments-panel -->

		</div><!-- / .comments-views -->
	</div><!-- / .comments-wrap -->

</div><!-- / #comments-container -->
