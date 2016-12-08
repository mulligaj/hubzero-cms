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

$c = 0;
?>
<div class="latest_discussions_module <?php echo $this->params->get('moduleclass_sfx'); ?>">
	<?php if (count($this->posts) > 0) : ?>
		<ul class="blog-entries">
		<?php 
		foreach ($this->posts as $post) 
		{ 
			if ($c < $this->limit) 
			{
				?>
				<li class="blog"<?php if ($c > 0) { echo ' style="display:none;"'; } ?>>
					<h4>
						<a href="<?php echo Route::url($post->link()); ?>">
							<?php 
								if ($this->pullout && $c == 0)
								{
									echo \Hubzero\Utility\String::truncate(strip_tags($post->content), $this->params->get('pulloutlimit', 500));
								}
								else
								{
									echo \Hubzero\Utility\String::truncate(strip_tags($post->content), $this->params->get('charlimit', 100));
								}
							?>
						</a>
					</h4>
					by
					<a href="<?php echo Route::url('index.php?option=com_members&id=' . $post->get('created_by')); ?>">
						<?php echo $this->escape(stripslashes($post->creator->get('name'))); ?>
					</a>
				</li>
				<?php 
			}
			$c++;
			//echo $c;
		}
		?>
		</ul>
	<?php else : ?>
		<p><?php echo Lang::txt('MOD_LATESTBLOG_NO_RESULTS'); ?></p>
	<?php endif; ?>

	<?php if ($more = $this->params->get('morelink', '')) : ?>
		<p class="more">
			<a href="<?php echo $more; ?>"><?php echo Lang::txt('MOD_LATESTBLOG_MORE_RESULTS'); ?></a>
		</p>
	<?php endif; ?>
</div>
