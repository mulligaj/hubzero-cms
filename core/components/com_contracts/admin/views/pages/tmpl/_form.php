<form action="<?php echo Route::url('index.php?option=' . $this->option . '&controller=pages&task=save&id=' . $this->page->get('id')); ?>" class="save-item" id="<?php echo 'save-item-' .$this->page->id;?>">
	<?php echo $this->editor('content', 
			$this->escape($this->page->get('content')), 25, 30, 'page-' . $this->page->get('id') . '-content', array('buttons' => false));?>
	<input type="submit" value="submit" />
</form>
