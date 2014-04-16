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
 * @author    Sam Wilson <samwilson@purdue.edu>
 * @copyright Copyright 2005-2011 Purdue University. All rights reserved.
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

\Hubzero\Document\Assets::addSystemScript('jquery.fancyselect.min');
\Hubzero\Document\Assets::addSystemStylesheet('jquery.fancyselect.css');

// Get our asset model
$asset = new CoursesModelAsset(JRequest::getInt('asset_id', null));

// Get the asset groups
$assetgroups = array();
foreach ($this->course->offering()->units() as $unit) :
	foreach ($unit->assetgroups() as $agt) :
		foreach ($agt->children() as $ag) :
			$assetgroups[] = array('id'=>$ag->get('id'), 'title'=>$ag->get('title'));
		endforeach;
	endforeach;
endforeach;

require_once(JPATH_ROOT . DS . 'components' . DS . 'com_tools' . DS . 'models' . DS . 'tool.php');

$tools     = ToolsModelTool::getMyTools();
$config    = JComponentHelper::getParams('com_courses');
$tool_path = $config->get('tool_path');
?>

<div class="edit-asset">
	<h3>Edit Asset</h3>

	<form action="<?php echo JURI::base(true); ?>/api/courses/asset/save" method="POST" class="edit-form">

		<p>
			<label for="title">Title:</label>
			<input type="text" name="title" value="<?= $asset->get('title') ?>" placeholder="Asset Title" />
		</p>
		<p>
			<label for="title">URL:</label>
			<input type="text" name="url" value="<?= $asset->get('url') ?>" placeholder="Asset URL" />
		</p>
		<p>
			<label for="type">Type:</label>
			<select name="type">
				<option value="video"<?php if ($asset->get('type') == 'video') { echo ' selected="selected"'; } ?>><?php echo JText::_('Video'); ?></option>
				<option value="file"<?php if ($asset->get('type') == 'file') { echo ' selected="selected"'; } ?>><?php echo JText::_('File'); ?></option>
				<option value="form"<?php if ($asset->get('type') == 'form') { echo ' selected="selected"'; } ?>><?php echo JText::_('Form'); ?></option>
				<option value="text"<?php if ($asset->get('type') == 'text') { echo ' selected="selected"'; } ?>><?php echo JText::_('Text'); ?></option>
				<option value="url"<?php if ($asset->get('type') == 'url') { echo ' selected="selected"'; } ?>><?php echo JText::_('URL'); ?></option>
			</select>
		</p>
		<p>
			<label for="subtype">Subtype:</label>
			<select name="subtype">
				<option value="video"<?php if ($asset->get('subtype') == 'video') { echo ' selected="selected"'; } ?>><?php echo JText::_('Video'); ?></option>
				<option value="embedded"<?php if ($asset->get('subtype') == 'embedded') { echo ' selected="selected"'; } ?>><?php echo JText::_('Embedded'); ?></option>
				<option value="file"<?php if ($asset->get('subtype') == 'file') { echo ' selected="selected"'; } ?>><?php echo JText::_('File'); ?></option>
				<option value="exam"<?php if ($asset->get('subtype') == 'exam') { echo ' selected="selected"'; } ?>><?php echo JText::_('Exam'); ?></option>
				<option value="quiz"<?php if ($asset->get('subtype') == 'quiz') { echo ' selected="selected"'; } ?>><?php echo JText::_('Quiz'); ?></option>
				<option value="homework"<?php if ($asset->get('subtype') == 'homework') { echo ' selected="selected"'; } ?>><?php echo JText::_('Homework'); ?></option>
				<option value="note"<?php if ($asset->get('subtype') == 'note') { echo ' selected="selected"'; } ?>><?php echo JText::_('Note'); ?></option>
				<option value="wiki"<?php if ($asset->get('subtype') == 'wiki') { echo ' selected="selected"'; } ?>><?php echo JText::_('Wiki'); ?></option>
				<option value="link"<?php if ($asset->get('subtype') == 'link') { echo ' selected="selected"'; } ?>><?php echo JText::_('Link'); ?></option>
				<option value="tool"<?php if ($asset->get('subtype') == 'tool') { echo ' selected="selected"'; } ?>><?php echo JText::_('Tool'); ?></option>
			</select>
		</p>
		<p>
			<label for="scope_id">Attach to:</label>
			<select name="scope_id">
				<? foreach ($assetgroups as $assetgroup) : ?>
					<? $selected = ($assetgroup['id'] == $this->scope_id) ? 'selected' : ''; ?>
					<option value="<?= $assetgroup['id'] ?>" <?= $selected ?>><?= $assetgroup['title'] ?></option>
				<? endforeach; ?>
			</select>
		</p>

		<p>
			<label for="graded">Create a gradebook entry for this item?</label>
			<input name="graded" type="checkbox" value="1" <?php echo ($asset->get('graded')) ? 'checked="checked"' : ''; ?>/>
			<input type="hidden" name="edit_graded" value="1" />
		</p>

		<?php if ($tool_path
				&& $tools
				&& count($tools) > 0
				&& (($asset->get('type') == 'file' && $asset->get('subtype') == 'file')
					|| ($asset->get('type') == 'url' && $asset->get('subtype') == 'tool'))) : ?>
			<p>
				<label for="tool_param">Launch a tool with this file?</label>
				<input name="tool_param" class="tool-param" type="checkbox" value="1" <?php echo ($asset->get('type') == 'url' && $asset->get('subtype') == 'tool') ? 'checked="checked"' : ''; ?>/>
				<input type="hidden" name="edit_tool_param" value="1" />

				<select class="tool-list" name="tool_alias">
					<? foreach ($tools as $tool) : ?>
						<? preg_match('/\/tools\/([0-9a-z]+)\//', $asset->get('url'), $substr); ?>
						<? $selected = ($substr && isset($substr[1]) && $substr[1] == $tool->alias) ? 'selected="selected"' : ''; ?>
						<option value="<?= $tool->alias ?>" <?= $selected ?>><?= $tool->title ?></option>
					<? endforeach; ?>
				</select>
			</p>
		<?php endif; ?>

		<input type="hidden" name="course_id" value="<?= $this->course->get('id') ?>" />
		<input type="hidden" name="original_scope_id" value="<?= $this->scope_id ?>" />
		<input type="hidden" name="offering" value="<?= $this->course->offering()->alias(); ?>" />
		<input type="hidden" name="id" value="<?= $asset->get('id') ?>" />

		<input type="button" value="Cancel" class="cancel" />
		<input type="submit" value="Submit" class="submit" />
	</form>
</div>