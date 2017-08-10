<?php $pageId = 'page-' . $this->page->get('id') . '-content'; ?>
<div class="input-wrap page-item" data-page-id="<?php echo $this->page->get('id');?>">
	<ul class="subnav edit-item-buttons">
		<li><a href="<?php echo Route::url('index.php?option=' . $this->option . '&controller=pages&task=edit&id=' . $this->page->get('id')); ?>" class="edit-item button">Edit</a></li>
	</ul>
	<ul class="subnav save-item-buttons">
		<li><button type="submit" form="<?php echo 'save-item-' . $this->page->get('id'); ?>" class="button">Save</button></li>
		<li><button type="reset" form="<?php echo 'save-item-' . $this->page->get('id'); ?>" class="button cancel">Cancel</button></li>
	</ul>
	<label for="<?php echo $pageId; ?>" class="page-label">Page <?php echo $this->pageNum; ?></label>
	<div class="page-content">
		<?php if ($this->task == 'add'): ?>
			<?php $this->setLayout('_form')->display(); ?>
		<?php else: ?>
			<?php echo $this->escape($this->page->get('content')); ?>
		<?php endif; ?>
	</div>
</div>
