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
?>
<div id="content-header">
	<h2><?php echo $this->title; ?></h2>
</div>
<div id="content-header-extra">
	<p>
		<a class="icon-main main-page btn" href="<?php echo JRoute::_('index.php?option=' . $this->option); ?>"><?php echo JText::_('COM_KB_MAIN'); ?></a>
	</p>
</div>
<div class="main section">
<?php if ($this->getError()) { ?>
	<p class="error"><?php echo implode("\n", $this->getErrors()); ?></p>
<?php } ?>
	<div class="aside">
		<div class="container">
			<h3><?php echo JText::_('COM_KB_CATEGORIES'); ?></h3>
			<ul class="categories">
				<li>
					<a <?php if ($this->get('catid') == 0) { echo ' class="active"'; } ?> href="<?php echo JRoute::_('index.php?option=' . $this->option . '&section=all'); ?>">
						<?php echo JText::_('COM_KB_ALL_ARTICLES'); ?>
					</a>
				</li>
			<?php foreach ($this->categories as $row) { ?>
				<li>
					<a <?php if ($this->catid == $row->get('id')) { echo 'class="active" '; } ?> href="<?php echo JRoute::_($row->link()); ?>">
						<?php echo $this->escape(stripslashes($row->get('title'))); ?> <span class="item-count"><?php echo $row->get('articles', 0); ?></span>
					</a>
				<?php if (count($this->subcategories) > 0 && $this->get('catid') == $row->get('id')) { ?>
					<ul class="categories">
					<?php foreach ($this->subcategories as $cat) { ?>
						<li>
							<a <?php if ($this->article->get('category') == $cat->get('id')) { echo 'class="active" '; } ?> href="<?php echo JRoute::_($cat->link()); ?>">
								<?php echo $this->escape(stripslashes($cat->get('title'))); ?> <span class="item-count"><?php echo $cat->get('articles', 0); ?></span>
							</a>
						</li>
					<?php } ?>
					</ul>
				<?php } ?>
				</li>
			<?php } ?>
			</ul>
		</div><!-- / .container -->
	</div><!-- / .aside -->
	<div class="subject">
		<div class="container" id="entry-<?php echo $this->article->get('id'); ?>">
			<div class="container-block">
				<h3><?php echo $this->escape(stripslashes($this->article->get('title'))); ?></h3>
				<div class="entry-content">
					<?php echo stripslashes($this->article->content()); ?>
				</div>
			<?php if ($tags = $this->article->tags('cloud')) { ?>
				<div class="entry-tags">
					<p><?php echo JText::_('COM_KB_TAGS'); ?></p>
					<?php echo $tags; ?>
				</div><!-- / .entry-tags -->
			<?php } ?>

				<p class="entry-voting voting">
					<?php 
						$view = new JView(array(
							'name'   => $this->controller,
							'layout' => '_vote'
						));
						$view->option = $this->option;
						$view->item   = $this->article;
						$view->type   = 'entry';
						$view->vote   = $this->vote;
						$view->id     = $this->article->get('id');
						$view->display();
					?>
				</p>

				<p class="entry-details">
					<?php echo JText::_('COM_KB_LAST_MODIFIED'); ?> 
					<span class="entry-date-at"><?php echo JText::_('COM_KB_DATETIME_AT'); ?></span>
					<span class="entry-time"><time datetime="<?php echo $this->article->modified(); ?>"><?php echo $this->article->modified('time'); ?></time></span> 
					<span class="entry-date-on"><?php echo JText::_('COM_KB_DATETIME_ON'); ?></span> 
					<span class="entry-date"><time datetime="<?php echo $this->article->modified(); ?>"><?php echo $this->article->modified('date'); ?></time></span>
				</p>

				<div class="clearfix"></div>
			</div><!-- / .container-block -->
		</div><!-- / .container -->
	</div><!-- / .subject -->
</div><!-- / .main section -->

<?php if ($this->article->param('allow_comments')) { ?>
<div class="below section" id="comments">
	<h3 class="comments-title">
		<?php echo JText::_('COM_KB_COMMENTS_ON_ENTRY'); ?>
		<?php if ($this->article->param('feeds_enabled') && $this->article->comments('count') > 0) { ?>
			<a class="icon-feed feed btn" href="<?php echo $this->article->link('feed'); ?>" title="<?php echo JText::_('COM_KB_COMMENT_FEED'); ?>">
				<?php echo JText::_('COM_KB_FEED'); ?>
			</a>
		<?php } ?>
	</h3>

	<div class="aside">
	<?php if ($this->article->commentsOpen()) { ?>
		<p>
			<a class="icon-add add btn" href="#post-comment"><?php echo JText::_('COM_KB_ADD_COMMENT'); ?></a>
		</p>
	<?php } ?>
	</div>
	<div class="subject">
		<?php
		if ($this->article->comments('count') > 0)
		{
			$view = new JView(
				array(
					'name'    => $this->controller,
					'layout'  => '_list'
				)
			);
			$view->parent     = 0;
			$view->cls        = 'odd';
			$view->depth      = 0;
			$view->option     = $this->option;
			$view->article    = $this->article;
			$view->comments   = $this->article->comments('list');
			$view->base       = $this->article->link();
			$view->display();
		}
		else
		{
		?>
		<p class="no-comments">
			<?php echo JText::_('COM_KB_NO_COMMENTS'); ?>
		</p>
		<?php } ?>

		<h3 class="post-comment-title">
			<?php echo JText::_('COM_KB_POST_COMMENT'); ?>
		</h3>
		<form method="post" action="<?php echo JRoute::_($this->article->link()); ?>" id="commentform">
			<p class="comment-member-photo">
				<span class="comment-anchor"><!-- <a name="post-comment"></a> --></span>
				<img src="<?php echo Hubzero_User_Profile_Helper::getMemberPhoto($this->juser, (!$this->juser->get('guest') ? 0 : 1)); ?>" alt="" />
			</p>
			<fieldset>
			<?php
			if (!$this->juser->get('guest')) 
			{
				if ($this->replyto->get('id')) 
				{
					ximport('Hubzero_View_Helper_Html');
					$name = JText::_('COM_KB_ANONYMOUS');
					$xuser = Hubzero_User_Profile::getInstance($this->replyto->get('created_by'));
					if (!$this->replyto->get('anonymous')) 
					{
						if (is_object($xuser) && $xuser->get('name')) 
						{
							$name = '<a href="'.JRoute::_('index.php?option=com_members&id='.$this->replyto->get('created_by')).'">'.$this->escape(stripslashes($xuser->get('name'))).'</a>';
						}
					}
				?>
				<blockquote cite="c<?php echo $this->replyto->id ?>">
					<p>
						<strong><?php echo $name; ?></strong> 
						<span class="comment-date-at"><?php echo JText::_('COM_KB_AT'); ?></span> 
						<span class="time"><time datetime="<?php echo $this->replyto->created(); ?>"><?php echo JHTML::_('date', $this->replyto->created('time')); ?></time></span> 
						<span class="comment-date-on"><?php echo JText::_('COM_KB_ON'); ?></span> 
						<span class="date"><time datetime="<?php echo $this->replyto->created(); ?>"><?php echo JHTML::_('date', $this->replyto->created('date')); ?></time></span>
					</p>
					<p>
						<?php echo Hubzero_View_Helper_Html::shortenText(stripslashes($this->replyto->content('raw')), 300, 0); ?>
					</p>
				</blockquote>
				<?php
				}
			}
			?>

			<?php if ($this->article->commentsOpen()) { ?>
				<label for="commentcontent">
					<?php echo JText::_('COM_KB_YOUR_COMMENTS'); ?> <span class="required"><?php echo JText::_('COM_KB_REQUIRED'); ?></span>
				<?php
				if (!$this->juser->get('guest')) {
					ximport('Hubzero_Wiki_Editor');
					echo Hubzero_Wiki_Editor::getInstance()->display('comment[content]', 'commentcontent', '', 'minimal', '40', '15');
				} else {
					$rtrn = JRoute::_($this->article->link() . '#post-comment');
					?>
					<p class="warning">
						<?php echo JText::sprintf('COM_KB_MUST_LOG_IN', base64_encode($rtrn)); ?>
					</p>
					<?php
				}
				?>
				</label>

				<?php if (!$this->juser->get('guest')) { ?>
				<label id="comment-anonymous-label" for="comment-anonymous">
					<input class="option" type="checkbox" name="comment[anonymous]" id="comment-anonymous" value="1" />
					<?php echo JText::_('COM_KB_FIELD_ANONYMOUS'); ?>
				</label>

				<p class="submit">
					<input type="submit" name="submit" value="<?php echo JText::_('COM_KB_SUBMIT'); ?>" />
				</p>
				<?php } ?>
			<?php } else { ?>
				<p class="warning">
					<?php echo JText::_('COM_KB_COMMENTS_CLOSED'); ?>
				</p>
			<?php } ?>
				<input type="hidden" name="comment[id]" value="0" />
				<input type="hidden" name="comment[entry_id]" value="<?php echo $this->escape($this->article->get('id')); ?>" />
				<input type="hidden" name="comment[parent]" value="<?php echo $this->escape($this->replyto->get('id')); ?>" />
				<input type="hidden" name="comment[created]" value="" />
				<input type="hidden" name="comment[created_by]" value="<?php echo $this->escape($this->juser->get('id')); ?>" />
				<input type="hidden" name="option" value="<?php echo $this->option; ?>" />
				<input type="hidden" name="task" value="savecomment" />

				<?php echo JHTML::_('form.token'); ?>

				<div class="sidenote">
					<p>
						<strong><?php echo JText::_('COM_KB_COMMENT_KEEP_RELEVANT'); ?></strong>
					</p>
				</div>
			</fieldset>
		</form>
	</div><!-- / .subject -->
</div><!-- / .below -->

<?php } // if ($this->config->get('allow_comments')) ?>
