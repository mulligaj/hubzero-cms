<?php 
defined('_JEXEC') or die('Restricted access');

$juser =& JFactory::getUser();

$dateFormat = '%d %b, %Y';
$timeFormat = '%I:%M %p';
$tz = 0;
if (version_compare(JVERSION, '1.6', 'ge'))
{
	$dateFormat = 'd M, Y';
	$timeFormat = 'h:m a';
	$tz = true;
}
?>

<div id="content-header">
	<h2><?php echo $this->escape($this->title); ?></h2>
</div>
<div id="content-header-extra">
	<p><a href="<?php echo JRoute::_('index.php?option=' . $this->option); ?>"><?php echo JText::_('&larr; All categories'); ?></a></p>
</div>
<div class="clear"></div>

<?php foreach ($this->notifications as $notification) { ?>
<p class="<?php echo $notification['type']; ?>"><?php echo $this->escape($notification['message']); ?></p>
<?php } ?>

<div class="main section">
	<div class="aside">
		<div class="container">
			<h3><?php echo JText::_('Last Post'); ?><span class="starter-point"></span></h3>
			<p>
<?php
	if (is_object($this->lastpost)) 
	{
		$lname = JText::_('Anonymous');
		$lastposter = JUser::getInstance($this->lastpost->created_by);
		if (is_object($lastposter) && !$this->lastpost->anonymous) 
		{
			$lname = '<a href="' . JRoute::_('index.php?option=com_members&id=' . $lastposter->get('id')) . '">' . $this->escape(stripslashes($lastposter->get('name'))) . '</a>';
		}
?>
				<span class="entry-date">
					@
					<span class="time"><?php echo JHTML::_('date', $this->lastpost->created, $timeFormat, $tz); ?></span> <?php echo JText::_('COM_FORUM_ON'); ?> 
					<span class="date"><?php echo JHTML::_('date', $this->lastpost->created, $dateFormat, $tz); ?></span>
				</span>
				<span class="entry-author">
					<?php echo JText::_('by'); ?>
					<?php echo $lname; ?>
				</span>
<?php } else { ?>
				<?php echo JText::_('none'); ?>
<?php } ?>
			</p>
		</div><!-- / .container -->
<?php if ($this->config->get('access-create-thread')) { ?>
		<div class="container">
			<h3><?php echo JText::_('Start Your Own'); ?><span class="starter-point"></span></h3>
<?php if (!$this->category->closed) { ?>
			<p>
				<?php echo JText::_('Create your own discussion where you and other users can discuss related topics.'); ?>
			</p>
			<p class="add">
				<a href="<?php echo JRoute::_('index.php?option='.$this->option . '&section=' . $this->filters['section'] . '&category=' . $this->filters['category'] . '&task=new'); ?>"><?php echo JText::_('Add Discussion'); ?></a>
			</p>
<?php } else { ?>
			<p class="warning">
				<?php echo JText::_('This category is closed and no new discussions may be created.'); ?>
			</p>
<?php } ?>
		</div><!-- / .container -->
<?php } ?>
	</div><!-- / .aside -->

	<div class="subject">
		<form action="<?php echo JRoute::_('index.php?option=' . $this->option); ?>" method="post">
			<div class="container data-entry">
				<input class="entry-search-submit" type="submit" value="<?php echo JText::_('Search'); ?>" />
				<fieldset class="entry-search">
					<legend><?php echo JText::_('Search for articles'); ?></legend>				
					<label for="entry-search-field"><?php echo JText::_('Enter keyword or phrase'); ?></label>
					<input type="text" name="q" id="entry-search-field" value="<?php echo $this->escape($this->filters['search']); ?>" />
					<input type="hidden" name="option" value="<?php echo $this->option; ?>" />
					<input type="hidden" name="controller" value="categories" />
					<input type="hidden" name="task" value="search" />
				</fieldset>
			</div><!-- / .container -->
			
			<div class="container">
				<table class="entries">
					<caption>
<?php
			if ($this->filters['search']) {
				if ($this->category->title) {
					echo JText::sprintf('Search for "%s" in "%s"', $this->escape($this->filters['search']), $this->escape(stripslashes($this->category->title)));
				} else {
					echo JText::sprintf('Search for "%s"', $this->escape($this->filters['search']));
				}
			} else {
				echo JText::sprintf('Discussions in "%s"', $this->escape(stripslashes($this->category->title)));
			}
?>
					</caption>
					<tbody>
<?php
			if ($this->rows) {
				foreach ($this->rows as $row) 
				{
					$name = JText::_('Anonymous');
					if (!$row->anonymous)
					{
						$creator =& JUser::getInstance($row->created_by);
						if (is_object($creator)) 
						{
							$name = '<a href="' . JRoute::_('index.php?option=com_members&id=' . $creator->get('id')) . '">' . $this->escape(stripslashes($creator->get('name'))) . '</a>';
						}
					}
?>
						<tr<?php if ($row->sticky) { echo ' class="sticky"'; } ?>>
							<th>
								<span class="entry-id"><?php echo $this->escape($row->id); ?></span>
							</th>
							<td>
								<a class="entry-title" href="<?php echo JRoute::_('index.php?option=' . $this->option . '&section=' . $this->filters['section'] . '&category=' . $this->filters['category'] . '&thread=' . $row->id); ?>">
									<span><?php echo $this->escape(stripslashes($row->title)); ?></span>
								</a>
								<span class="entry-details">
									<span class="entry-date">
										<?php echo JHTML::_('date', $row->created, $dateFormat, $tz); ?>
									</span>
									<?php echo JText::_('by'); ?>
									<span class="entry-author">
										<?php echo $name; ?>
									</span>
								</span>
							</td>
							<td>
								<span><?php echo ($row->replies + 1); ?></span>
								<span class="entry-details">
									<?php echo JText::_('Comments'); ?>
								</span>
							</td>
							<td>
								<span><?php echo JText::_('Last Post:'); ?></span>
								<span class="entry-details">
<?php 
									/*$lastpost = null;
									if ($row->last_activity != '0000-00-00 00:00:00')
									{*/
										$lastpost = $this->forum->getLastPost($row->id);
									//}
									if (is_object($lastpost)) 
									{
										$lname = JText::_('Anonymous');
										$lastposter =& JUser::getInstance($lastpost->created_by);
										if (is_object($lastposter)) 
										{
											$lname = '<a href="' . JRoute::_('index.php?option=com_members&id=' . $lastposter->get('id')) . '">' . $this->escape(stripslashes($lastposter->get('name'))) . '</a>';
										}
?>
									<span class="entry-date">
										<?php echo JHTML::_('date', $lastpost->created, $dateFormat, $tz); ?>
									</span>
									<?php echo JText::_('by'); ?>
									<span class="entry-author">
										<?php echo $lname; ?>
									</span>
							<?php } else { ?>
									<?php echo JText::_('none'); ?>
							<?php } ?>
								</span>
							</td>
						<?php if ($this->config->get('access-manage-thread') || $this->config->get('access-edit-thread') || $this->config->get('access-delete-thread')) { ?>
							<td class="entry-options">
								<?php if ($this->config->get('access-manage-thread') || ($this->config->get('access-edit-thread') && $row->created_by == $juser->get('id'))) { ?>
									<a class="edit" href="<?php echo JRoute::_('index.php?option=' . $this->option . '&section=' . $this->filters['section'] . '&category=' . $this->filters['category'] . '&thread=' . $row->id . '&task=edit'); ?>">
										<?php echo JText::_('COM_FORUM_EDIT'); ?>
									</a>
								<?php } ?>
								<?php if ($this->config->get('access-manage-thread') || ($this->config->get('access-delete-thread') && $row->created_by == $juser->get('id'))) { ?>
									<a class="delete" href="<?php echo JRoute::_('index.php?option=' . $this->option . '&section=' . $this->filters['section'] . '&category=' . $this->filters['category'] . '&thread=' . $row->id . '&task=delete'); ?>">
										<?php echo JText::_('COM_FORUM_DELETE'); ?>
									</a>
								<?php } ?>
							</td>
						<?php } ?>
						</tr>
<?php 
				}
			} else { ?>
						<tr>
							<td><?php echo JText::_('There are currently no discussions.'); ?></td>
						</tr>
<?php 		} ?>
					</tbody>
				</table>
<?php 
			if ($this->pageNav) {
				// @FIXME: Nick's Fix Based on Resources View
				$pf = $this->pageNav->getListFooter();
				//var_dump($pf);
				$nm = str_replace('com_', '', $this->option);
				//$pf = str_replace($nm.'/?',$nm.'/'.$this->group->get('cn').'/'.$this->_element.'/?',$pf);
				echo $pf;
				//echo $this->pageNav->getListFooter();
				// @FIXME: End Nick's Fix
			}
?>
				<div class="clear"></div>
			</div><!-- / .container -->
		</form>
	</div><!-- /.subject -->
</div><!-- /.main -->