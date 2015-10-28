<?php
/**
 * @package		HUBzero CMS
 * @author		Shawn Rice <zooley@purdue.edu>
 * @copyright	Copyright 2005-2009 HUBzero Foundation, LLC.
 * @license		http://opensource.org/licenses/MIT MIT
 *
 * Copyright 2005-2009 HUBzero Foundation, LLC.
 * All rights reserved.
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
 */

// No direct access
defined('_HZEXEC_') or die();

$this->css();
?>
<header id="content-header">
	<h2><?php echo $this->title; ?></h2>

	<div id="content-header-extra">
		<p>
			<a class="main-page btn" href="<?php echo Route::url('index.php?option=' . $this->option); ?>">
				<?php echo Lang::txt('COM_FEEDBACK_MAIN'); ?>
			</a>
		</p>
	</div><!-- / #content-header-extra -->
</header><!-- / #content-header -->

<section class="main section">
	<p class="passed"><?php echo Lang::txt('COM_FEEDBACK_STORY_THANKS'); ?></p>

	<div class="quote">
		<?php if (count($this->addedPictures)) { ?>
			<?php foreach ($this->addedPictures as $img) { ?>
				<img src="<?php echo $this->path . '/' . $img; ?>" alt="" />
			<?php } ?>
		<?php } ?>

		<blockquote cite="<?php echo $this->escape($this->row->fullname); ?>">
			<?php echo $this->escape(stripslashes($this->row->quote)); ?>
		</div>
		<p class="cite">
			<?php
			$profile = \Hubzero\User\Profile::getInstance($this->row->user_id);
			if ($profile)
			{
				echo '<img src="' . $profile->getPicture() . '" alt="' . $this->escape($this->row->fullname) . '" width="30" height="30" />';
			}
			?>
			<cite><?php echo $this->escape($this->row->fullname); ?></cite><br />
			<?php echo $this->escape($this->row->org); ?>
		</p>
	</div>
</section><!-- / .main section -->
