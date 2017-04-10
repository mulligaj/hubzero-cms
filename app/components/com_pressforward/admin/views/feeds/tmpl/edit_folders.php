<?php
// No direct access
defined('_HZEXEC_') or die();
?>
<script type="text/javascript">
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

		$.post($(this).attr('data-action'), {
			'folder[name]': title.val(),
			'taxonomy[parent]': 0,
			'no_html': 1,
			task: 'save'
		}, function(data){
			var response = jQuery.parseJSON(data);
		});
	});
});
</script>

		<fieldset class="adminform">
			<legend><span><?php echo Lang::txt('PF_FOLDERS'); ?></span></legend>

			<?php echo Html::tabs('start', 'pf_feed_category-tabs'); ?>
				<?php echo Html::tabs('panel', Lang::txt('All Folders'), 'all-details'); ?>
				<?php
				$this->view('_folders')
					->set('folders', $this->folders)
					->set('depth', 0)
					->set('id', 'pf_feed_categorychecklist')
					->display();
				?>

				<?php echo Html::tabs('panel', Lang::txt('Most Used'), 'pop-details'); ?>
				<?php
				$this->view('_folders')
					->set('folders', $this->folders)
					->set('depth', 0)
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
								<option value="<?php echo $folder->get('id'); ?>"><?php echo $folder->get('treename') . $folder->get('name'); ?></option>
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