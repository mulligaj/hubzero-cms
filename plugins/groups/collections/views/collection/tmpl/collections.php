<?php
/**
 * HUBzero CMS
 *
 * Copyright 2005-2011 Purdue University. All rights reserved.
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
 * @author    Shawn Rice <zooley@purdue.edu>
 * @copyright Copyright 2005-2011 Purdue University. All rights reserved.
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

$base = 'index.php?option=' . $this->option . '&cn=' . $this->group->get('cn') . '&active=' . $this->name;
?>

<?php if (!$this->juser->get('guest')) { // && !$this->params->get('access-create-collection')) { ?>
<ul id="page_options">
	<li>
		<?php if ($this->model->isFollowing()) { ?>
			<a class="icon-unfollow unfollow btn" data-text-follow="<?php echo JText::_('Follow All'); ?>" data-text-unfollow="<?php echo JText::_('Unfollow All'); ?>" href="<?php echo JRoute::_($base . '&scope=unfollow'); ?>">
				<span><?php echo JText::_('Unfollow All'); ?></span>
			</a>
		<?php } else { ?>
			<a class="icon-follow follow btn" data-text-follow="<?php echo JText::_('Follow All'); ?>" data-text-unfollow="<?php echo JText::_('Unfollow All'); ?>" href="<?php echo JRoute::_($base . '&scope=follow'); ?>">
				<span><?php echo JText::_('Follow All'); ?></span>
			</a>
		<?php } ?>
	</li>
</ul>
<?php } ?>

<form method="get" action="<?php echo JRoute::_($base); ?>" id="collections">
	<fieldset class="filters">
		<ul>
			<li>
				<a class="collections count active" href="<?php echo JRoute::_($base . '&scope=all'); ?>">
					<span><?php echo JText::sprintf('<strong>%s</strong> collections', $this->rows->total()); ?></span>
				</a>
			</li>
			<li>
				<a class="posts count" href="<?php echo JRoute::_($base . '&scope=posts'); ?>">
					<span><?php echo JText::sprintf('<strong>%s</strong> posts', $this->posts); ?></span>
				</a>
			</li>
			<li>
				<a class="followers count" href="<?php echo JRoute::_($base . '&scope=followers'); ?>">
					<span><?php echo JText::sprintf('<strong>%s</strong> followers', $this->followers); ?></span>
				</a>
			</li>
		<?php if ($this->params->get('access-can-follow')) { ?>
			<li>
				<a class="following count" href="<?php echo JRoute::_($base . '&scope=following'); ?>">
					<span><?php echo JText::sprintf('<strong>%s</strong> following', $this->following); ?></span>
				</a>
			</li>
		<?php } ?>
		</ul>
	<?php if (!$this->juser->get('guest')) { ?>
		<p>
		<?php if ($this->params->get('access-create-collection')) { ?>
			<a class="icon-add add btn" href="<?php echo JRoute::_($base . '&scope=new'); ?>">
				<span><?php echo JText::_('New collection'); ?></span>
			</a>
		<?php } //else { ?>
			<?php if ($this->params->get('access-manage-collection')) { ?>
			<a class="icon-config config btn tooltips" href="<?php echo JText::_($base . '&scope=settings'); ?>" title="<?php echo JText::_('Manage content creation settings'); ?>">
				<span><?php echo JText::_('Settings'); ?></span>
			</a>
			<?php } ?>
		<?php //} ?>
		</p>
	<?php } ?>
		<div class="clear"></div>
	</fieldset>

<?php if ($this->rows->total() > 0) { ?>
	<div id="posts">
	<?php 
	foreach ($this->rows as $row) 
	{
		?>
		<div class="post collection <?php echo ($row->get('access') == 4) ? 'private' : 'public'; ?>" id="b<?php echo $row->get('id'); ?>" data-id="<?php echo $row->get('id'); ?>">
			<div class="content">
				<?php
						$view = new \Hubzero\Plugin\View(
							array(
								'folder'  => 'groups',
								'element' => $this->name,
								'name'    => 'post',
								'layout'  => 'default_collection'
							)
						);
						$view->row        = $row;
						$view->collection = $row;
						$view->display();
				?>
				<div class="meta">
					<p class="stats">
						<span class="likes">
							<?php echo JText::sprintf('%s likes', $row->get('positive', 0)); ?>
						</span>
						<span class="reposts">
							<?php echo JText::sprintf('%s posts', $row->get('posts', 0)); ?>
						</span>
					</p>
				<?php if (!$this->juser->get('guest')) { ?>
					<div class="actions">
						<?php if ($row->isFollowing()) { ?>
							<a class="unfollow" data-id="<?php echo $row->get('id'); ?>" data-text-follow="<?php echo JText::_('Follow'); ?>" data-text-unfollow="<?php echo JText::_('Unfollow'); ?>" href="<?php echo JRoute::_($base . '&scope=' . $row->get('alias') . '/unfollow'); ?>">
								<span><?php echo JText::_('Unfollow'); ?></span>
							</a>
						<?php } else { ?>
							<a class="follow" data-id="<?php echo $row->get('id'); ?>" data-text-follow="<?php echo JText::_('Follow'); ?>" data-text-unfollow="<?php echo JText::_('Unfollow'); ?>" href="<?php echo JRoute::_($base . '&scope=' . $row->get('alias') . '/follow'); ?>">
								<span><?php echo JText::_('Follow'); ?></span>
							</a>
						<?php } ?>
					<?php if ($this->params->get('access-manage-collection')) { ?>
						<?php if ($this->params->get('access-edit-collection')) { ?>
							<a class="edit" data-id="<?php echo $row->get('id'); ?>" href="<?php echo JRoute::_($base . '&scope=' . $row->get('alias') . '/edit'); ?>" title="<?php echo JText::_('Edit'); ?>">
								<span><?php echo JText::_('Edit'); ?></span>
							</a>
						<?php } ?>
						<?php if ($this->params->get('access-delete-collection')) { ?>
							<a class="delete" data-id="<?php echo $row->get('id'); ?>" href="<?php echo JRoute::_($base . '&scope=' . $row->get('alias') . '/delete'); ?>" title="<?php echo JText::_('Delete'); ?>">
								<span><?php echo JText::_('Delete'); ?></span>
							</a>
						<?php } ?>
					<?php } else { ?>
							<a class="repost" data-id="<?php echo $row->get('id'); ?>" href="<?php echo JRoute::_($base . '&scope=' . $row->get('alias') . '/collect'); ?>">
								<span><?php echo JText::_('Collect'); ?></span>
							</a>
					<?php } ?>
					</div><!-- / .actions -->
				<?php } ?>
				</div><!-- / .meta -->
			</div><!-- / .content -->
		</div><!-- / .post -->
	<?php } ?>
	</div><!-- / #posts -->
	<?php if ($this->total > $this->filters['limit']) { echo $this->pageNav->getListFooter(); } ?>
	<div class="clear"></div>
<?php } else { ?>
		<div id="collection-introduction">
		<?php if ($this->params->get('access-create-collection')) { ?>
			<div class="instructions">
				<ol>
					<li><?php echo JText::_('Click on the "new collection" button.'); ?></li>
					<li><?php echo JText::_('Add a title and maybe a description.'); ?></li>
					<li><?php echo JText::_('Start adding content!'); ?></li>
				</ol>
			</div><!-- / .instructions -->
			<div class="questions">
				<p><strong><?php echo JText::_('What is a collection?'); ?></strong></p>
				<p><?php echo JText::_('A collection is where you organize posts by topic. For example, you could collect diagrams, files, resources, or wiki pages about physics for your Physics 101 collection. Collections can be private or public.'); ?></p>
			</div>
		<?php } else { ?>
			<div class="instructions">
				<p><?php echo JText::_('No collections available.'); ?></p>
			</div><!-- / .instructions -->
		<?php } ?>
		</div><!-- / #collection-introduction -->
<?php } ?>
</form>