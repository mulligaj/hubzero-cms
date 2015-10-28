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
		<div class="imgOutline">
			<div class="imgTotal">
				<div align="center" class="imgBorder">
					<a class="folder-item" href="<?php echo Route::url('index.php?option=com_media&view=mediaList&tmpl=component&folder=' . $this->_tmp_folder->path_relative); ?>" target="folderframe">
						<?php echo Lang::txt('COM_MEDIA_FOLDER'); ?>
					</a>
				</div>
			</div>
			<div class="controls">
			<?php if (User::authorise('core.delete', 'com_media')):?>
				<a class="delete-item" target="_top" href="<?php echo Route::url('index.php?option=com_media&task=folder.delete&tmpl=index&' . Session::getFormToken() . '=1&folder=' . $this->state->folder . '&rm[]=' . $this->_tmp_folder->name); ?>" rel="<?php echo $this->_tmp_folder->name; ?> :: <?php echo $this->_tmp_folder->files+$this->_tmp_folder->folders; ?>">
					<?php echo Lang::txt('JACTION_DELETE'); ?>
				</a>
				<input type="checkbox" name="rm[]" value="<?php echo $this->_tmp_folder->name; ?>" />
			<?php endif;?>
			</div>
			<div class="imginfoBorder">
				<a href="<?php echo Route::url('index.php?option=com_media&view=mediaList&tmpl=component&folder=' . $this->_tmp_folder->path_relative); ?>" target="folderframe"><?php echo substr($this->_tmp_folder->name, 0, 10) . (strlen($this->_tmp_folder->name) > 10 ? '...' : ''); ?></a>
			</div>
		</div>
