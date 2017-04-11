<?php
// No direct access
defined('_HZEXEC_') or die();

// Get the permissions helper
$canDo = Components\PressForward\Helpers\Permissions::getActions('feed');

// Toolbar is a helper class to simplify the creation of Toolbar 
// titles, buttons, spacers and dividers in the Admin Interface.
//
// Here we'll had the title of the component and options
// for saving based on if the user has permission to
// perform such actions. Everyone gets a cancel button.
$text = ($this->task == 'edit' ? Lang::txt('JACTION_EDIT') : Lang::txt('JACTION_CREATE'));

Toolbar::title(Lang::txt('COM_PRESSFORWARD') . ': ' . Lang::txt('PF_FEEDS') . ': ' . $text);
if ($canDo->get('core.edit'))
{
	Toolbar::apply();
	Toolbar::save();
	Toolbar::spacer();
}
Toolbar::cancel();
Toolbar::spacer();
Toolbar::appendButton('Link', 'help', 'help', 'https://github.com/PressForward/pressforward/wiki');

$this->css();
?>
<script type="text/javascript">
Joomla.submitbutton = function(pressbutton) {
	var form = document.adminForm;

	if (pressbutton == 'cancel') {
		Joomla.submitform(pressbutton, document.getElementById('item-form'));
		return;
	}

	<?php echo $this->editor()->save('text'); ?>

	// do field validation
	if ($('#field-title').val() == ''){
		alert("<?php echo Lang::txt('PF_ERROR_MISSING_TITLE'); ?>");
	} else {
		Joomla.submitform(pressbutton, document.getElementById('item-form'));
	}
}

jQuery(document).ready(function($){
	$('#pf_feed_category-add-toggle').on('click', function(e){
		e.preventDefault();

		$($(this).attr('href')).toggleClass('hide');
	});

	$('#pf_feed_category-add-submit').on('click', function(e){
		e.preventDefault();

		var title = $('#newpf_feed_category');
		if (!title.val()) {
			title.focus();
			return false;
		}

		var parent = $('#newpf_feed_category_parent');

		$.post($(this).attr('data-action'), {
			'folder[name]': title.val(),
			'folder[term_id]': 0,
			'taxonomy[parent]': parent.val(),
			'taxonomy[term_taxonomy_id]': 0,
			'no_html': 1,
			task: 'save',
			'<?php echo Session::getFormToken(); ?>': 1
		}, function(data){
			//var response = jQuery.parseJSON(data);
			var response = $(data);

			if (parent.val()) {
				$('#all-pf_feed_category-' + parent.val() + ' > ul').append(data);
			} else {
				$('#pf_feed_categorychecklist').append(data);
			}
		});
	});

	$('#meta-submit').on('click', function(e){
		e.preventDefault();

		var tr = $('#list-table').find('tr').last();
		if (!tr.length) {
			return;
		}

		var tr2 = tr.clone();

		tr2.find('input[type=text]').val($('#meta-key').val());
		tr2.find('input[type=hidden]').val(0);
		tr2.find('textarea').val($('#meta-value').val());

		tr2.insertAfter(tr);

		$('#meta-key').val('');
		$('#meta-value').val('');
	});

	$('#item-form')
		.on('click', '.deletemeta', function(e){
			e.preventDefault();

			$(this).closest('tr').remove();
		});
});
</script>

<form action="<?php echo Route::url('index.php?option=' . $this->option . '&controller=' . $this->controller); ?>" method="post" name="adminForm" class="editform" id="item-form">
	<div class="col width-60 fltlft">
		<fieldset class="adminform">
			<legend><span><?php echo Lang::txt('JDETAILS'); ?></span></legend>

			<div class="input-wrap">
				<label for="field-name"><?php echo Lang::txt('PF_FIELD_TITLE'); ?> <span class="required"><?php echo Lang::txt('JOPTION_REQUIRED'); ?></span></label>
				<input type="text" name="fields[post_title]" id="field-title" size="35" value="<?php echo $this->escape($this->row->get('post_title')); ?>" />
			</div>

			<div class="input-wrap">
				<label for="field-content"><?php echo Lang::txt('PF_FIELD_CONTENT'); ?>:</label>
				<?php echo $this->editor('fields[post_content]', $this->escape($this->row->get('post_content')), 50, 10, 'field-content', array('buttons' => false)); ?>
			</div>

			<div class="input-wrap" data-hint="<?php echo Lang::txt('Excerpts are optional hand-crafted summaries of your content that can be used in your theme.'); ?>">
				<label for="field-excerpt"><?php echo Lang::txt('Exceprt'); ?>:</label>
				<textarea name="fields[post_excerpt]" id="field-excerpt" cols="50" rows="3"><?php echo $this->escape($this->row->get('post_excerpt')); ?></textarea>
				<span class="hint"><?php echo Lang::txt('Excerpts are optional hand-crafted summaries of your content that can be used in your theme. <a href="https://codex.wordpress.org/Excerpt">Learn more about manual excerpts</a>.'); ?></span>
			</div>
		</fieldset>

		<fieldset class="adminform">
			<legend><span><?php echo Lang::txt('Custom Fields'); ?></span></legend>

			<table id="list-table">
				<thead>
					<tr>
						<th><?php echo Lang::txt('Name'); ?></th>
						<th><?php echo Lang::txt('Value'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php
					$i = 1;
					foreach ($this->row->meta as $meta)
					{
						?>
						<tr>
							<td>
								<div class="input-wrap custom-key">
									<input type="text" name="meta[<?php echo $i; ?>][key]" size="35" value="<?php echo $this->escape($meta->get('meta_key')); ?>" />
									<input type="hidden" name="meta[<?php echo $i; ?>][id]" value="<?php echo $this->escape($meta->get('meta_id')); ?>" />
								</div>
								<div class="submit">
									<input type="submit" name="deletemeta[<?php echo $i; ?>]" class="button deletemeta button-small" value="Delete" />
									<!-- <input type="submit" name="meta-<?php echo $i; ?>-submit" class="button updatemeta button-small" value="Update" /> -->
								</div>
							</td>
							<td>
								<div class="input-wrap custom-value">
									<textarea name="meta[<?php echo $i; ?>][value]" cols="35" rows="3"><?php echo $this->escape($meta->get('meta_value')); ?></textarea>
								</div>
							</td>
						</tr>
						<?php
						$i++;
					}
					?>
				</tbody>
			</table>

			<fieldset>
				<legend><span><?php echo Lang::txt('Add New Custom Field:'); ?></span></legend>

				<div class="grid custom">
					<div class="col span6">
						<div class="input-wrap custom-key">
							<input type="text" name="meta[0][key]" id="meta-key" size="35" value="" />
							<input type="hidden" name="meta[0][id]" id="meta-id" value="" />
						</div>
						<div class="submit">
							<input type="submit" name="meta-submit" id="meta-submit" class="button updatemeta button-small" value="Add Custom Field" />
						</div>
					</div>
					<div class="col span6">
						<div class="input-wrap custom-value">
							<textarea name="meta[0][value]" id="meta-value" cols="35" rows="3"></textarea>
						</div>
					</div>
				</div>

				<span class="hint">Custom fields can be used to add extra metadata to a post</span>
			</fieldset>
		</fieldset>

		<fieldset class="adminform">
			<legend><span><?php echo Lang::txt('Author'); ?></span></legend>

			<div class="input-wrap">
				<label for="field-post_author"><?php echo Lang::txt('PF_FIELD_AUTHOR'); ?> <span class="required"><?php echo Lang::txt('JOPTION_REQUIRED'); ?></span></label>
				<select name="fields[post_author]" id="field-post_author">
					<?php
					$users = User::all()
						->whereEquals('block', 0)
						->order('name', 'asc')
						->rows();
					foreach ($users as $user) { ?>
						<option value="<?php echo $this->escape($user->get('id')); ?>"<?php if ($user->get('id') == $this->row->get('post_author')) { echo ' selected="selected"'; } ?>><?php echo $this->escape($user->get('name')) . ' (' . $this->escape($user->get('username')) . ')'; ?></option>
					<?php } ?>
				</select>
			</div>
		</fieldset>
	</div>
	<div class="col width-40 fltrt">
		<table class="meta">
			<tbody>
				<tr>
					<th><?php echo Lang::txt('PF_FIELD_ID'); ?>:</th>
					<td>
						<?php echo $this->row->get('ID', 0); ?>
						<input type="hidden" name="fields[ID]" value="<?php echo $this->row->get('ID'); ?>" />
					</td>
				</tr>
				<?php if ($this->row->get('ID')) { ?>
					<tr>
						<th><?php echo Lang::txt('PF_FIELD_AUTHOR'); ?>:</th>
						<td>
							<?php echo $this->escape(stripslashes($this->row->author->get('name'))); ?>
							<input type="hidden" name="fields[post_author]" id="field-post_author" value="<?php echo $this->escape($this->row->get('post_author')); ?>" />
						</td>
					</tr>
					<tr>
						<th><?php echo Lang::txt('PF_FIELD_PUBLISHED_ON'); ?>:</th>
						<td>
							<?php echo ($this->row->get('post_date_gmt') != '0000-00-00 00:00:00' ? $this->escape(Date::of($this->row->get('post_date_gmt'))->toLocal('Y-m-d H:i:s')) : ''); ?>
						</td>
					</tr>
				<?php } ?>
			</tbody>
		</table>

		<fieldset class="adminform">
			<legend><span><?php echo Lang::txt('JGLOBAL_FIELDSET_PUBLISHING'); ?></span></legend>

			<div class="input-wrap misc-pub-section">
				<label for="field-post_status"><?php echo Lang::txt('PF_FIELD_STATUS'); ?>:</label>
				<select name="fields[post_status]" id="field-status">
					<option value="publish"<?php if ($this->row->get('post_status') == 'publish') { echo ' selected="selected"'; } ?>><?php echo Lang::txt('Active'); ?></option>
					<option value="pending"<?php if ($this->row->get('post_status') == 'pending') { echo ' selected="selected"'; } ?>><?php echo Lang::txt('Pending Review'); ?></option>
					<option value="draft"<?php if ($this->row->get('post_status') == 'draft') { echo ' selected="selected"'; } ?>><?php echo Lang::txt('Inactive'); ?></option>
				</select>
			</div>

			<fieldset>
				<legend><?php echo Lang::txt('PF_FIELD_VISIBILITY'); ?></legend>

				<div class="input-wrap">
					<input type="radio" name="fields[visibility]" value="public" <?php if ($this->row->get('visibility') == 'public') { echo ' checked="checked"'; } ?> /> <label for=""><?php echo Lang::txt('Public'); ?></label><br />
					<input type="radio" name="fields[visibility]" value="password" <?php if ($this->row->get('visibility') == 'password') { echo ' checked="checked"'; } ?> /> <label for=""><?php echo Lang::txt('Password protected'); ?></label><br />
					<input type="radio" name="fields[visibility]" value="private" <?php if ($this->row->get('visibility') == 'private') { echo ' checked="checked"'; } ?> /> <label for=""><?php echo Lang::txt('Private'); ?></label>
				</div>
			</fieldset>

			<div class="input-wrap misc-pub-section">
				<label for="field-post_date_gmt"><?php echo Lang::txt('PF_FIELD_PUBLISHED_ON'); ?>:</label><br />
				<?php echo Html::input('calendar', 'fields[post_date_gmt]', ($this->row->get('post_date_gmt') != '0000-00-00 00:00:00' ? $this->escape(Date::of($this->row->get('post_date_gmt'))->toLocal('Y-m-d H:i:s')) : ''), array('id' => 'field-post_date_gmt')); ?>
			</div>

			<div class="input-wrap misc-pub-section">
				<input type="checkbox" name="fields[no_feed_alert]" id="field-no_feed_alert" value="1" <?php if ($this->row->get('no_feed_alert') == 1) { echo ' checked="checked"'; } ?> />
				<label for="field-no_feed_alert">No alerts, never let feed go inactive.</label>
			</div>

			<div class="input-wrap misc-pub-section">
				<select id="pf_forward_to_origin_single" name="pf_meta[forward_to_origin]">
					<option value="forward"<?php if ($this->row->get('forward_to_origin') == 'forward') { echo ' selected="selected"'; } ?>>Forward</option>
					<option value="no-forward"<?php if ($this->row->get('forward_to_origin') == 'no-forward') { echo ' selected="selected"'; } ?>>Don't Forward</option>
				</select>
				<label for="pf_forward_to_origin_single">to item's original URL</label>
			</div>
		</fieldset>

		<fieldset class="adminform">
			<legend><span><?php echo Lang::txt('PF_TAGS'); ?></span></legend>

			<div class="input-wrap">
				<?php
				$tf = Event::trigger('hubzero.onGetMultiEntry', array(array('tags', 'tags', 'actags', '', $this->row->tags('string'))));

				if (count($tf) > 0) {
					echo implode("\n", $tf);
				} else { ?>
					<input type="text" name="tags" id="tags" value="<?php echo $this->escape($this->row->tags('string')); ?>" />
				<?php } ?>
			</div>
		</fieldset>

		<fieldset class="adminform">
			<legend><span><?php echo Lang::txt('PF_FOLDERS'); ?></span></legend>

			<?php echo Html::tabs('start', 'pf_feed_category-tabs'); ?>
				<?php echo Html::tabs('panel', Lang::txt('All Folders'), 'all-details'); ?>
				<?php
				$selected = array();
				foreach ($this->row->folders()->rows() as $f)
				{
					$selected[] = $f->get('term_taxonomy_id');
				}
				$this->view('_folders')
					->set('folders', $this->folders)
					->set('selected', $selected)
					->set('depth', 0)
					->set('prfx', 'all')
					->set('id', 'pf_feed_categorychecklist')
					->display();
				?>

				<?php echo Html::tabs('panel', Lang::txt('Most Used'), 'pop-details'); ?>
				<?php
				$record = Components\Pressforward\Models\Folder::all();

				$a = $record->getTableName();
				$b = Components\Pressforward\Models\Folder\Taxonomy::blank()->getTableName();

				$folders = $record
					->select($a . '.*,' . $b . '.*')
					->join($b, $b . '.term_id', $a . '.term_id', 'inner')
					->whereEquals($b . '.taxonomy', Components\Pressforward\Models\Folder\Taxonomy::$term_type)
					->where($b . '.count', '>', 0)
					->order($b . '.count', 'desc')
					->limit(5)
					->rows();

				$this->view('_folders')
					->set('folders', $folders)
					->set('depth', 0)
					->set('prfx', 'popular')
					->set('id', 'pf_feed_categorychecklist-pop')
					->display();
				?>
			<?php echo Html::tabs('end'); ?>

			<div id="pf_feed_category-adder" class="tab">
				<div class="input-wrap">
					<a id="pf_feed_category-add-toggle" href="#pf_feed_category-add" class="hide-if-no-js taxonomy-add-new">
						<?php echo Lang::txt('+ Add New Folder'); ?>
					</a>
				</div>
				<div id="pf_feed_category-add" class="category-add wp-hidden-child hide">
					<div class="input-wrap">
						<label class="screen-reader-text" for="newpf_feed_category"><?php echo Lang::txt('Add New Folder'); ?></label>
						<input type="text" name="newpf_feed_category" id="newpf_feed_category" class="form-required" placeholder="<?php echo Lang::txt('New Folder'); ?>" aria-required="true" />
					</div>
					<div class="input-wrap">
						<label class="screen-reader-text" for="newpf_feed_category_parent"><?php echo Lang::txt('Parent Category:'); ?></label>
						<select name="newpf_feed_category_parent" id="newpf_feed_category_parent" class="postform">
							<option value="0">— Parent Category —</option>
							<?php foreach (Components\PressForward\Models\Folder::listing() as $folder) { ?>
								<option value="<?php echo $folder->get('term_taxonomy_id'); ?>"><?php echo $folder->get('treename') . $folder->get('name'); ?></option>
							<?php } ?>
						</select>
					</div>
					<div class="input-wrap">
						<input type="button" id="pf_feed_category-add-submit" data-action="<?php echo Route::url('index.php?option=' . $this->option . '&controller=folders'); ?>" data-wp-lists="add:pf_feed_categorychecklist:pf_feed_category-add" class="button category-add-submit" value="<?php echo Lang::txt('Add New Folder'); ?>" />
					</div>
					<span id="pf_feed_category-ajax-response"></span>
				</div>
			</div>
		</fieldset>

		<fieldset class="adminform">
			<legend><span><?php echo Lang::txt('PF_ATTRIBUTES'); ?></span></legend>

			<div class="input-wrap">
				<label for="field-menu_order"><?php echo Lang::txt('PF_FIELD_MENU_ORDER'); ?></label>
				<input type="text" name="fields[menu_order]" id="field-menu_order" size="5" value="<?php echo $this->escape($this->row->get('menu_order', 0)); ?>" />
			</div>
		</fieldset>

		<fieldset class="adminform">
			<legend><span><?php echo Lang::txt('PF_FEATURED_IMAGE'); ?></span></legend>

			<div class="input-wrap">
				<a href="#"><?php echo Lang::txt('Set featured image'); ?></a>
			</div>
		</fieldset>
	</div>
	<div class="clr"></div>

	<input type="hidden" name="option" value="<?php echo $this->option; ?>" />
	<input type="hidden" name="controller" value="<?php echo $this->controller; ?>" />
	<input type="hidden" name="task" value="save" />

	<?php echo Html::input('token'); ?>
</form>