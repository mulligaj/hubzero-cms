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

$type = 'folder';
$cls  = 'feed_folder';
if (!$this->folder->get('term_taxonomy_id'))
{
	$type = 'feed';
	$cls  = 'feed';
}
?>
	<li class="feed_folder" id="<?php echo $type; ?>-<?php echo $this->folder->get('term_id'); ?>">
		<a href="<?php echo Route::url('index.php?option=' . $this->option . '&controller=' . $this->controller . '&' . $type . '=' . $this->folder->get('term_id')); ?>" data-id="<?php echo $this->folder->get('term_id'); ?>" class="folder<?php if ($this->active == $this->folder->get('term_id')) { echo ' active'; } ?>" title="<?php echo $this->folder->get('name'); ?>"><?php echo $this->folder->get('name'); ?></a>
		<?php
		$folders = $this->folder->get('children');

		if (count($folders))
		{
			$this->view('_folders')
				->set('folders', $folders)
				->set('active', $this->active)
				->set('depth', $this->depth)
				->display();
		}
		?>
	</li>