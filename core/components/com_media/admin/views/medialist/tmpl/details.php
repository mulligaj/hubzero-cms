<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	com_media
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_HZEXEC_') or die();

?>
<form target="_parent" action="<?php echo Route::url('index.php?option=com_media&tmpl=index&folder=' . $this->state->folder); ?>" method="post" id="mediamanager-form" name="mediamanager-form">
	<div class="manager">
		<table>
			<thead>
				<tr>
					<th><?php echo Lang::txt('JGLOBAL_PREVIEW'); ?></th>
					<th><?php echo Lang::txt('COM_MEDIA_NAME'); ?></th>
					<th><?php echo Lang::txt('COM_MEDIA_PIXEL_DIMENSIONS'); ?></th>
					<th><?php echo Lang::txt('COM_MEDIA_FILESIZE'); ?></th>
					<?php if (User::authorise('core.delete', 'com_media')): ?>
						<th><?php echo Lang::txt('JACTION_DELETE'); ?></th>
					<?php endif; ?>
				</tr>
			</thead>
			<tbody>
				<?php echo $this->loadTemplate('up'); ?>

				<?php for ($i=0, $n=count($this->folders); $i<$n; $i++) :
					$this->setFolder($i);
					echo $this->loadTemplate('folder');
				endfor; ?>

				<?php for ($i=0, $n=count($this->documents); $i<$n; $i++) :
					$this->setDoc($i);
					echo $this->loadTemplate('doc');
				endfor; ?>

				<?php for ($i=0, $n=count($this->images); $i<$n; $i++) :
					$this->setImage($i);
					echo $this->loadTemplate('img');
				endfor; ?>

			</tbody>
		</table>
		<input type="hidden" name="task" value="list" />
		<input type="hidden" name="username" value="" />
		<input type="hidden" name="password" value="" />
		<?php echo Html::input('token'); ?>
	</div>
</form>
