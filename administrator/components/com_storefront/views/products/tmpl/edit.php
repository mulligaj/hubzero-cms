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
 * @copyright Copyright 2005-2011 Purdue University. All rights reserved.
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

$canDo = StorefrontHelperPermissions::getActions('product');

$text = ($this->task == 'edit' ? JText::_('COM_STOREFRONT_EDIT') : JText::_('COM_STOREFRONT_NEW'));

JToolBarHelper::title(JText::_('COM_STOREFRONT') . ': ' . JText::_('COM_STOREFRONT_PRODUCT') . ': ' . $text, 'kb.png');
if ($canDo->get('core.edit'))
{
	JToolBarHelper::apply();
	JToolBarHelper::save();
}
JToolBarHelper::cancel();
JToolBarHelper::spacer();
JToolBarHelper::help('category');

$this->css();
?>
<script type="text/javascript">
function submitbutton(pressbutton)
{
	if (pressbutton == 'cancel') {
		submitform(pressbutton);
		return;
	}

	// do field validation
	if (document.getElementById('field-title').value == ''){
		alert("<?php echo 'Title cannot be empty' ?>");
	}
	else if (document.getElementById('field-description').value == ''){
		alert("<?php echo 'Description cannot be empty' ?>");
	}
	else {
		submitform(pressbutton);
	}
}
</script>

<form action="index.php" method="post" name="adminForm" id="item-form">
	<div class="col width-60 fltlft">
		<fieldset class="adminform">
			<legend><span><?php echo JText::_('COM_STOREFRONT_DETAILS'); ?></span></legend>

			<div class="input-wrap">
				<label for="field-title"><?php echo JText::_('COM_STOREFRONT_TITLE'); ?>: <span class="required"><?php echo JText::_('JOPTION_REQUIRED'); ?></span></label><br />
				<input type="text" name="fields[pName]" id="field-title" size="30" maxlength="100" value="<?php echo $this->escape(stripslashes($this->row->getName())); ?>" />
			</div>

			<div class="input-wrap">
				<label for="field-alais"><?php echo JText::_('Alias'); ?>:</label><br />
				<input type="text" name="fields[pAlias]" id="field-alais" size="30" maxlength="100" value="<?php echo $this->escape(stripslashes($this->row->getAlias())); ?>" />
			</div>

			<div class="input-wrap">
				<label for="field-pTagline"><?php echo JText::_('COM_STOREFRONT_TAGLINE'); ?>: <span class="required"><?php echo JText::_('JOPTION_REQUIRED'); ?></span></label><br />
				<input type="text" name="fields[pTagline]" id="field-pTagline" size="30" maxlength="100" value="<?php echo $this->escape(stripslashes($this->row->getTagline())); ?>" />
			</div>

			<div class="input-wrap">
				<label for="field-description"><?php echo JText::_('COM_STOREFRONT_DESCRIPTION'); ?>: <span class="required"><?php echo JText::_('JOPTION_REQUIRED'); ?></span></label><br />
				<?php echo JFactory::getEditor()->display('fields[pDescription]', $this->escape(stripslashes($this->row->getDescription())), '', '', 50, 10, false, 'field-description'); ?>
			</div>

			<div class="input-wrap">
				<label for="field-features"><?php echo JText::_('COM_STOREFRONT_FEATURES'); ?>:</label><br />
				<?php echo JFactory::getEditor()->display('fields[pFeatures]', $this->escape(stripslashes($this->row->getFeatures())), '', '', 50, 10, false, 'field-features'); ?>
			</div>
		</fieldset>
	</div>
	<div class="col width-40 fltrt">
		<table class="meta">
			<tbody>
				<tr>
					<th class="key"><?php echo JText::_('COM_STOREFRONT_ID'); ?>:</th>
					<td>
						<?php echo $this->row->getId(); ?>
						<input type="hidden" name="fields[pId]" id="field-id" value="<?php echo $this->escape($this->row->getId()); ?>" />
					</td>
				</tr>
			</tbody>
		</table>

		<fieldset class="adminform">
			<legend><span><?php echo JText::_('COM_STOREFRONT_OPTIONS'); ?></span></legend>

			<div class="input-wrap">
				<label for="field-state"><?php echo JText::_('COM_STOREFRONT_TYPE'); ?>:</label>
				<select name="fields[ptId]" id="field-state">
					<?php

					foreach ($this->types as $type)
					{
						?>
						<option value="<?php echo $type->ptId; ?>"<?php if ($this->row->getType() == $type->ptId) { echo ' selected="selected"'; } ?>><?php echo $type->ptName; ?></option>
						<?php
					}

					?>
				</select>
			</div>

			<?php
			if ($this->metaNeeded) {
			?>
			<p>
				<a href="<?php echo 'index.php?option=' . $this->option . '&controller=meta&task=edit&id=' . $this->row->getId(); ?>">Edit
					type-related options</a> (save product first if you updated the type)</p>
			<?php
			}
			?>

			<div class="input-wrap">
				<label for="field-state"><?php echo JText::_('COM_STOREFRONT_ALLOW_MULTIPLE'); ?>:</label>
				<select name="fields[pAllowMultiple]" id="field-state">
					<option value="0"<?php if ($this->row->getAllowMultiple() == 0) { echo ' selected="selected"'; } ?>><?php echo JText::_('COM_STOREFRONT_NO'); ?></option>
					<option value="1"<?php if ($this->row->getAllowMultiple() == 1) { echo ' selected="selected"'; } ?>><?php echo JText::_('COM_STOREFRONT_YES'); ?></option>
				</select>
			</div>
		</fieldset>

		<fieldset class="adminform">
			<legend><span><?php echo JText::_('COM_STOREFRONT_PARAMETERS'); ?></span></legend>

			<div class="input-wrap">
				<label for="field-state"><?php echo JText::_('COM_STOREFRONT_PUBLISH'); ?>:</label>
				<select name="fields[state]" id="field-state">
					<option value="0"<?php if ($this->row->getActiveStatus() == 0) { echo ' selected="selected"'; } ?>><?php echo JText::_('JUNPUBLISHED'); ?></option>
					<option value="1"<?php if ($this->row->getActiveStatus() == 1) { echo ' selected="selected"'; } ?>><?php echo JText::_('JPUBLISHED'); ?></option>
					<option value="2"<?php if ($this->row->getActiveStatus() == 2) { echo ' selected="selected"'; } ?>><?php echo JText::_('JTRASHED'); ?></option>
				</select>
			</div>
			<div class="input-wrap">
				<label for="field-access"><?php echo JText::_('COM_STOREFRONT_ACCESS_LEVEL'); ?>:</label>
				<?php
				echo JHtml::_('access.level', 'fields[access]', $this->row->getAccessLevel());

				?>
			</div>
		</fieldset>


		<?php
		if ($this->collections->total())
		{
		?>
		<fieldset class="adminform">
			<legend><span><?php echo 'Collections'; ?></span></legend>

			<div class="input-wrap">
				<ul class="checklist catgories">
					<?php
					$collections = $this->row->getCollections();

					foreach ($this->collections as $cat)
					{
					?>
						<?php
						if ($cat->cActive || in_array($cat->cId, $collections))
						{
						?>
						<li>
							<input type="checkbox" name="fields[collections][]" <?php if (in_array($cat->cId, $collections)) { echo 'checked';} ?> value="<?php echo $cat->cId; ?>"
								   id="collection_<?php echo $cat->cId; ?>">
							<label for="collection_<?php echo $cat->cId; ?>">
								<?php echo $cat->cName; ?>
							</label>
						</li>
						<?php
						}
						?>
					<?php
					}
					?>
				</ul>
			</div>
		</fieldset>
		<?php
		}
		?>

		<?php
		if ($this->optionGroups->total())
		{
		?>

			<fieldset class="adminform">
				<legend><span><?php echo 'Product option groups'; ?></span></legend>

				<div class="input-wrap">
					<ul class="checklist optionGroups">
						<?php
						foreach ($this->optionGroups as $og)
						{
						?>
							<?php
							if ($og->ogActive || in_array($og->ogId, $this->productOptionGroups))
							{
							?>
							<li>
								<input type="checkbox"
									   name="fields[optionGroups][]" <?php if (in_array($og->ogId, $this->productOptionGroups)) {
									echo 'checked';
								} ?> value="<?php echo $og->ogId; ?>"
									   id="optionGroup_<?php echo $og->ogId; ?>">
								<label for="optionGroup_<?php echo $og->ogId; ?>">
									<?php echo $og->ogName; ?>
								</label>
							</li>
							<?php
							}
							?>
						<?php
						}
						?>
					</ul>
				</div>
			</fieldset>
		<?php
		}
		?>

		<fieldset class="adminform">
			<legend><span><?php echo JText::_('Image'); ?></span></legend>

			<?php
			if ($this->row->getId()) {
				$image = $this->row->getImage();
				if (!empty($image))
				{
					$image = stripslashes($this->row->getImage()->imgName);
					$pics = explode(DS, $image);
					$file = end($pics);
				}
				?>
				<div style="padding-top: 2.5em">
					<div id="ajax-uploader" data-action="index.php?option=<?php echo $this->option; ?>&amp;controller=images&amp;task=upload&amp;type=product&amp;id=<?php echo $this->row->getId(); ?>&amp;no_html=1&amp;<?php echo JUtility::getToken(); ?>=1">
						<noscript>
							<iframe height="350" name="filer" id="filer" src="index.php?option=<?php echo $this->option; ?>&amp;controller=images&amp;tmpl=component&amp;file=<?php echo $file; ?>&amp;type=product&amp;id=<?php echo $this->row->getId(); ?>"></iframe>
						</noscript>
					</div>
				</div>
			<?php
			$width = 0;
			$height = 0;
			$this_size = 0;
			if ($image)
			{
				$path = DS . trim($this->config->get('imagesFolder', '/site/storefront/products'), DS) . DS . $this->row->getId();

				$this_size = filesize(JPATH_ROOT . $path . DS . $file);
				list($width, $height, $type, $attr) = getimagesize(JPATH_ROOT . $path . DS . $file);
				$pic = $this->row->getImage()->imgName;
			}
			else
			{
				$pic = 'noimage.png';
				$path = $this->config->get('imagesFolder', '/site/storefront/products');
			}
			?>
				<div id="img-container">
					<img id="img-display" src="<?php echo '..' . $path . DS . $pic; ?>" alt="<?php echo JText::_('COM_STOREFRONT_LOGO'); ?>" />
					<input type="hidden" name="currentfile" id="currentfile" value="<?php echo $this->escape($image); ?>" />
				</div>
				<table class="formed">
					<tbody>
					<tr>
						<th><?php echo JText::_('COM_STOREFRONT_FILE'); ?>:</th>
						<td>
							<span id="img-name"><?php echo $image; ?></span>
						</td>
						<td>
							<a id="img-delete" <?php echo $image ? '' : 'style="display: none;"'; ?> href="index.php?option=<?php echo $this->option; ?>&amp;controller=images&amp;tmpl=component&amp;task=remove&amp;currentfile=<?php echo $this->row->getImage()->imgId; ?>&amp;type=product&amp;id=<?php echo $this->row->getId(); ?>&amp;<?php echo JUtility::getToken(); ?>=1" title="<?php echo JText::_('Delete'); ?>">[ x ]</a>
						</td>
					</tr>
					<tr>
						<th><?php echo JText::_('COM_STOREFRONT_PICTURE_SIZE'); ?>:</th>
						<td><span id="img-size"><?php echo \Hubzero\Utility\Number::formatBytes($this_size); ?></span></td>
						<td></td>
					</tr>
					<tr>
						<th><?php echo JText::_('COM_STOREFRONT_PICTURE_WIDTH'); ?>:</th>
						<td><span id="img-width"><?php echo $width; ?></span> px</td>
						<td></td>
					</tr>
					<tr>
						<th><?php echo JText::_('COM_STOREFRONT_PICTURE_HEIGHT'); ?>:</th>
						<td><span id="img-height"><?php echo $height; ?></span> px</td>
						<td></td>
					</tr>
					</tbody>
				</table>

				<script type="text/javascript" src="../media/system/js/jquery.fileuploader.js"></script>
				<script type="text/javascript">
					String.prototype.nohtml = function () {
						if (this.indexOf('?') == -1) {
							return this + '?no_html=1';
						} else {
							return this + '&no_html=1';
						}
					};
					jQuery(document).ready(function($){
						if ($("#ajax-uploader").length) {
							var uploader = new qq.FileUploader({
								element: $("#ajax-uploader")[0],
								action: $("#ajax-uploader").attr("data-action"),
								multiple: true,
								debug: true,
								template: '<div class="qq-uploader">' +
								'<div class="qq-upload-button"><span><?php echo JText::_('COM_STOREFRONT_UPLOAD_CLICK_OR_DROP'); ?></span></div>' +
								'<div class="qq-upload-drop-area"><span><?php echo JText::_('COM_STOREFRONT_UPLOAD_CLICK_OR_DROP'); ?></span></div>' +
								'<ul class="qq-upload-list"></ul>' +
								'</div>',
								onComplete: function(id, file, response) {
									if (response.success) {
										$('#img-display').attr('src', '..' + response.directory + '/' + response.file);
										$('#img-name').text(response.file);
										$('#img-size').text(response.size);
										$('#img-width').text(response.width);
										$('#img-height').text(response.height);

										var newHref = "index.php?option=<?php echo $this->option; ?>&controller=images&tmpl=component&task=remove&currentfile=" + response.imgId + "&type=product&id=<?php echo $this->row->getId(); ?>&<?php echo JUtility::getToken(); ?>=1";

										$('#img-delete').attr('href', newHref);

										$('#img-delete').show();
									}
								}
							});
						}
						$('#img-delete').on('click', function (e) {
							e.preventDefault();
							var el = $(this);
							$.getJSON(el.attr('href').nohtml(), {}, function(response) {
								if (response.success) {
									$('#img-display').attr('src', '../administrator/components/com_courses/assets/img/blank.png');
									$('#img-name').text('[ none ]');
									$('#img-size').text('0');
									$('#img-width').text('0');
									$('#img-height').text('0');
								}
								el.hide();
							});
						});
					});
				</script>
				<?php
			} else {
				echo '<p class="warning">'.JText::_('COM_STOREFRONT_PICTURE_ADDED_LATER').'</p>';
			}
			?>
		</fieldset>

	</div>
	<div class="clr"></div>

	<?php /*
		<?php if ($canDo->get('core.admin')): ?>
			<div class="col width-100 fltlft">
				<fieldset class="panelform">
					<?php echo $this->form->getLabel('rules'); ?>
					<?php echo $this->form->getInput('rules'); ?>
				</fieldset>
			</div>
			<div class="clr"></div>
		<?php endif; ?>
	*/ ?>

	<input type="hidden" name="option" value="<?php echo $this->option; ?>" />
	<input type="hidden" name="controller" value="<?php echo $this->controller; ?>" />
	<input type="hidden" name="task" value="save" />

	<?php echo JHTML::_('form.token'); ?>
</form>
