<?php
// No direct access
defined('_HZEXEC_') or die();

// Get the permissions helper
$canDo = \Components\Contracts\Helpers\Permissions::getActions('character');

// Toolbar is a helper class to simplify the creation of Toolbar 
// titles, buttons, spacers and dividers in the Admin Interface.
//
// Here we'll had the title of the component and options
// for saving based on if the user has permission to
// perform such actions. Everyone gets a cancel button.
$text = ($this->task == 'edit' ? Lang::txt('JACTION_EDIT') : Lang::txt('JACTION_CREATE'));

Toolbar::title(Lang::txt('COM_CONTRACTS') . ': ' . $text);
if ($canDo->get('core.edit'))
{
	Toolbar::apply();
	Toolbar::save();
	Toolbar::spacer();
}
Toolbar::cancel();
Toolbar::spacer();
Toolbar::help('character');
$this->css('contracts');
?>
<script type="text/javascript">
function submitbutton(pressbutton)
{
	var form = document.adminForm;

	if (pressbutton == 'cancel') {
		submitform(pressbutton);
		return;
	}

	// do field validation
	if ($('#field-title').val() == ''){
		alert("<?php echo Lang::txt('COM_CONTRACTS_ERROR_MISSING_NAME'); ?>");
	} else {
		<?php echo $this->editor()->save('text'); ?>

		submitform(pressbutton);
	}
}
</script>

<form action="<?php echo Route::url('index.php?option=' . $this->option . '&controller=' . $this->controller); ?>" method="post" name="adminForm" class="editform" id="item-form">
	<div class="grid">
		<div class="col span7">
			<fieldset class="adminform">
				<legend><span><?php echo Lang::txt('JDETAILS'); ?></span></legend>

				<div class="input-wrap">
					<label for="field-title"><?php echo Lang::txt('COM_CONTRACTS_FIELD_TITLE'); ?> <span class="required"><?php echo Lang::txt('JOPTION_REQUIRED'); ?></span></label>
					<input type="text" name="fields[title]" id="field-title" size="35" value="<?php echo $this->escape($this->row->get('title')); ?>" />
				</div>
				<div class="input-wrap">
					<label for="field-alias"><?php echo Lang::txt('COM_CONTRACTS_FIELD_ALIAS'); ?> <span class="required"><?php echo Lang::txt('JOPTION_REQUIRED'); ?></span></label>
					<input type="text" name="fields[alias]" id="field-alias" size="35" value="<?php echo $this->escape($this->row->get('alias')); ?>" />
				</div>

			</fieldset>
			<fieldset class="adminform">
				<legend><span><?php echo Lang::txt('COM_CONTRACTS_MESSAGES'); ?></span></legend>
				<div class="input-wrap">
					<label for="field-accepted-message"><?php echo Lang::txt('COM_CONTRACTS_FIELD_ACCEPTED_MESSAGE'); ?></label>
					<textarea name="fields[accepted_message]" id="field-accepted-message" rows="15"><?php echo $this->escape($this->row->get('accepted_message')); ?></textarea>
				</div>
				<div class="input-wrap">
					<label for="field-manual-message"><?php echo Lang::txt('COM_CONTRACTS_FIELD_MANUAL_MESSAGE'); ?></label>
					<textarea name="fields[manual_message]" id="field-manual-message" rows="15"><?php echo $this->escape($this->row->get('manual_message')); ?></textarea>
				</div>
				
			</fieldset>
		</div>
		<div class="col span5">
			<table class="meta">
				<tbody>
					<tr>
						<th><?php echo Lang::txt('COM_CONTRACTS_FIELD_ID'); ?>:</th>
						<td>
							<?php echo $this->row->get('id', 0); ?>
							<input type="hidden" name="fields[id]" id="field-id" value="<?php echo $this->escape($this->row->get('id')); ?>" />
						</td>
					</tr>
					<?php if ($this->row->get('state')) { ?>
						<tr>
							<th><?php echo Lang::txt('COM_CONTRACTS_FIELD_CREATOR'); ?>:</th>
							<td>
								<?php
								$editor = User::getInstance($this->row->get('created_by'));
								echo $this->escape($editor->get('name'));
								?>
								<input type="hidden" name="fields[created_by]" id="field-created_by" value="<?php echo $this->escape($this->row->get('created_by')); ?>" />
							</td>
						</tr>
						<tr>
							<th><?php echo Lang::txt('COM_CONTRACTS_FIELD_CREATED'); ?>:</th>
							<td>
								<?php echo $this->row->get('created'); ?>
								<input type="hidden" name="fields[created]" id="field-created" value="<?php echo $this->escape($this->row->get('created')); ?>" />
							</td>
						</tr>
					<?php } ?>
				</tbody>
			</table>
			<fieldset class="editform">
				<div class="input-wrap">
					<label for="acusers">Primary Contact Person</label>
					<?php $userSelect = Event::trigger('hubzero.onGetSingleEntryWithSelect', array(array('members', 'fields[contact_id]', 'acusers','', $this->row->contact->get('name',''), '', 'owner'))); ?>
					<?php if (count($userSelect) > 0): ?>
						<?php echo $userSelect[0]; ?>
					<?php else: ?>
					<input type="text" name="fields[contact_id]" value="<?php echo $this->escape($this->row->get('contact_id')); ?>" id="acusers" value="" size="30" autocomplete="off" />
					<?php endif; ?>
				</div>
			</fieldset>
		</div>
	</div>
	<input type="hidden" name="option" value="<?php echo $this->option; ?>" />
	<input type="hidden" name="controller" value="<?php echo $this->controller; ?>" />
	<input type="hidden" name="task" value="save" />
	<?php echo Html::input('token'); ?>
	</form>
	<div class="editform">
		<div class="grid">
			<div class="col span12">
				<fieldset class="adminform">
					<legend><span><?php echo Lang::txt('Pages'); ?></span></legend>
					<?php if ($this->row->isNew()): ?>
						<div class="message warning">You must first save this Contract before being able to add pages below.</div>
					<?php else: ?>
						<nav class="sub-navigation">
							<ul>
								<li class="button"><a href="<?php echo Route::url('index.php?option=' . $this->option . '&controller=pages&task=add&contract_id=' . $this->row->get('id', 0)); ?>" id="add-page">Add New Page</a></li>
							</ul>
						</nav>
						<?php $count = 1; ?>
						<section id="pages-section" data-order-url="<?php echo Route::url('index.php?option=' . $this->option . '&controller=pages&task=order');?>">
						<?php foreach ($this->row->pages as $page): ?>
							<?php $this->view('_page', 'pages')
									->set('pageNum', $count)
									->set('page', $page)
									->display(); ?>
							<?php $count++; ?>
						<?php endforeach; ?>
						</section>
					<?php endif; ?>
				</fieldset>
			</div>
		</div>
	</div>

	<script type="text/javascript">
		$(function(){
			var tokenInput = $('#item-form input[type="hidden"]:last');
			$('#pages-section').on('click', '.edit-item', function(e){
				e.preventDefault();
				e.stopPropagation();
				var pageContainer = $(this).closest('.page-item');
				pageContainer.addClass('active');
				pageContainer.addClass('editing');
				var url = $(this).attr('href');
				var pageContent = pageContainer.children('.page-content');
				var textArea = $(this).find('textarea').text();
				$.ajax({
					url: url,
					data: {"text" : textArea},
					method: "POST",
					success: function(response){
						pageContent.html(response.content);
						pageContent.siblings('.edit-item-buttons').hide();
						pageContent.siblings('.save-item-buttons').show();
					}
				});
			});

			$('#pages-section').on('click', '.page-item', function(e){
				if (!$(this).hasClass('editing') || !$(this).hasClass('active')){
					$(this).siblings().removeClass('active');
					$(this).toggleClass('active');
				}
			});

			$('#pages-section').sortable({
				update: function(event, ui){reorderPages()}
			});
			$('#pages-section').on('submit', '.save-item',  function(e){
				e.preventDefault();
				$(this).append(tokenInput.clone());
				var url = $(this).attr('action');
				var data = $(this).serialize();
				var container = $(this).parent('.page-content');
				$.ajax({
					url: url,
					data: data,
					method: "POST",
					success: function(response){
						container.html(response.content);
						container.parent('.page-item').removeClass('editing');
						container.siblings('.save-item-buttons').hide();
						container.siblings('.edit-item-buttons').show();
					}
				});
			});
			$('#add-page').on('click', function(e){
				e.preventDefault();
				var url = $(this).attr('href');
				$.ajax({
					url: url,
					method: "POST",
					success: function(response){
						$('#pages-section').prepend(response.content);
						reorderPages();
					}
				});
			});

			var reorderPages = function(){
				var orderUrl = $('#pages-section').data('orderUrl');
				var pageIds = [];
				$('.page-item').each(function(index){
					pageIds.push($(this).data('pageId'));
				});
				$.ajax({
					url: orderUrl,
					data: {"orderedItems" : pageIds},
					method: "POST",
					success: function(response){
						$('.page-item').each(function(index){
							var pageNum = index + 1;
							$(this).find('label').text('Page ' + pageNum);
						});
					}
				});
			};
			var userSearch = $('#user-search').autocomplete({
				source: function(request, response) {
					$.ajax({
						url: 'https://localhost/api/members/list', 
						data: {search: request.term}, 
						success: function(myResponse){
							var members = myResponse.members;
							var results = [];
							$.each(members, function(index, member){
								memberItem = {
									label: member.name,
									value: member.id,
									username: member.usernmae
								};
								results.push(memberItem);
							});
							response(results);
						}
					});
				},
				select: function(event, ui){
					$('input[name="fields[contact_id]"]').val(ui.item.id);
				}
			}).autocomplete("instance");
			userSearch._renderItem = function( ul, item ){
				return $('<li>').append(item.label).appendTo(ul);
			};
			userSearch._renderMenu = function (ul, items){
				var that = this;
				$.each(items, function(index, item){
					that._renderItemData(ul, item);
				});
				$(ul).find("li:even").css('background', 'red');
			};
		});
	</script>
