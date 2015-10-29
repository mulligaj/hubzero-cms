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

// No direct access
defined('_HZEXEC_') or die();
?>

<div class="slider">
	<div class="banner" id="<?php echo $this->collection; ?>">
		<?php foreach ($this->slides as $slide) { 
			if ($slide->learn_more_location == 'relative')
			{
				$tag = '<p class="relative">';
			}
			else
			{
				$tag = '<div class="' . $slide->learn_more_location . '">';
			}
			$closingtag = ($slide->learn_more_location == 'relative') ? '</p>' : '</div>';	?>

			<div class="slide" id="<?php echo $slide->alias; ?>">
				<h3><?php echo $slide->header; ?></h3>
					<p><?php echo $slide->text; ?></p>
						<?php echo $tag; ?>
							<a class="<?php echo $slide->learn_more_class; ?>" href="<?php echo $slide->learn_more_target; ?>">
								<?php echo $slide->learn_more_text; ?>
							</a>
						<?php echo $closingtag; ?>
			</div>
		<?php } ?>
	</div>
	<!-- @TODO: let's make this whole line an if statement -->
	<div <?php echo ($this->pager == 'null') ? '' : 'class="pager"'; ?> id="<?php echo($this->pager); ?>"></div>
</div>