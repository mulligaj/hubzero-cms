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

// No direct access.
defined('_HZEXEC_') or die();

$pagetitle = $this->params->get('page_title');
$pagetitle = ($pagetitle) ? $pagetitle : Lang::txt('Polls');

if (Pathway::count() <= 0)
{
	Pathway::append($this->escape($pagetitle),'index.php?option=com_polls');
}
if (trim($this->poll->title) != '')
{
	Pathway::append($this->poll->title,'index.php?option=com_polls&id='.$this->poll->id);
}

$this->css('poll_bars.css');
?>

<form action="index.php" method="post" name="poll" id="poll">
<?php if ($this->params->get( 'show_page_title')) : ?>
	<div id="content-header" class="componentheading<?php echo $this->params->get( 'pageclass_sfx' ) ?>">
		<h2><?php echo $this->poll->title ? $this->escape($this->poll->title) : $pagetitle; ?></h2>
	</div>
<?php endif; ?>
	<div class="main section">
		<div class="contentpane<?php echo $this->params->get( 'pageclass_sfx' ) ?>">
			<label for="id">
				<?php echo Lang::txt('Select Poll'); ?>
				<?php echo $this->lists['polls']; ?>
			</label>
		</div>

		<div class="contentpane<?php echo $this->params->get( 'pageclass_sfx' ) ?>">
			<?php echo $this->loadTemplate('graph'); ?>
		</div>
	</div><!-- / .main section -->
</form>