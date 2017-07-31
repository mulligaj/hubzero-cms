<?php
// Push CSS to the document
//
// The css() method provides a quick and convenient way to attach stylesheets. 
// 
// 1. The name of the stylesheet to be pushed to the document (file extension is optional). 
//    If no name is provided, the name of the component or plugin will be used. For instance, 
//    if called within a view of the component com_tags, the system will look for a stylesheet named tags.css.
// 
// 2. The name of the extension to look for the stylesheet. For components, this will be 
//    the component name (e.g., com_tags). For plugins, this is the name of the plugin folder 
//    and requires the third argument be passed to the method.
//
// Method chaining is also allowed.
// $this->css()  
//      ->css('another');

$this->css();

// Similarly, a js() method is available for pushing javascript assets to the document.
// The arguments accepted are the same as the css() method described above.
//
// $this->js();

// Set the document title
//
// This sets the <title> tag of the document and will overwrite any previous
// title set. To append or modify an existing title, it must be retrieved first
// with $title = Document::getTitle();
Document::setTitle(Lang::txt('COM_DRWHO'));

// Set the pathway (breadcrumbs)
//
// Breadcrumbs are displayed via a breadcrumbs module and may or may not be enabled for
// all hubs and/or templates. In general, it's good practice to set the pathway
// even if it's unknown if hey will be displayed or not.
Pathway::append(
	Lang::txt('COM_DRWHO'),  // Text to display
	'index.php?option=' . $this->option  // Link. Route::url() not needed.
);
?>
<header id="content-header">
	<h2><?php echo Lang::txt('COM_CONTRACTS'); ?></h2>
</header>

<section class="main section">
	<form id="hubForm" action="<?php echo Route::url('index.php?option=' . $this->option . '&task=save');?>" method="post">
	<fieldset>
		<legend>Contact Information</legend>
		<div class="grid">
			<div class="col span6">
				<label for="firstname">
					First Name
					<input type="text" name="firstname" id="firstname" />
				</label>
			</div>
			<div class="col span6 omega">
				<label for="lastname">
					Last Name
					<input type="text" name="lastname" id="lastname" />
				</label>
			</div>
			<div class="col span6 omega">
				<label for="email">
					E-mail
					<input type="text" name="email" id="email" />
				</label>
			</div>
		</div>
	</fieldset>
	<fieldset>
		<legend>Organization Information</legend>
		<div class="grid">
			<div class="col span12 omega">
				<label for="orgname">
					Organization Name
					<input type="text" name="orgname" id="orgname" />
				</label>
			</div>
			<div class="col span12 omega">
				<label for="orgaddress">
					Organization Address
					<input type="text" name="orgaddress" id="orgaddress" />
				</label>
			</div>
		</div>
	</fieldset>
	<fieldset>
		<legend>Contract Agreement</legend>
		<div class="grid">
			<div class="col span12 omega radio-options">
				<p>Do you have authority to authorize contracts on behalf of this organization?</p>
				<input type="radio" class="option-hidden" name="authorized" value="" id="authorized-none" checked="checked" />
				<label for="authorized-none">No Option Selected</label>
				<input type="radio" class="option" name="authorized" value="1" id="authorized-yes" />
				<label for="authorized-yes">Yes</label>
				<input type="radio" class="option" name="authorized" value="0" id="authorized-no" />
				<label for="authorized-no">No</label>
			</div>
			<div class="col span12 omega">
				<section class="contract">
					<?php $pageCount = $this->contract->pages->count(); ?>
					<?php foreach ($this->contract->pages as $page): ?>
						<article id="<?php echo 'contract-page-' . $page->id;?>" class="article <?php echo $page->isFirst() ? 'current-article' : '';?>">
							<div class="page-content">
								<?php echo $page->content; ?>
								<?php if ($page->isLast()): ?>
									<fieldset class="radio-options">
										<input type="radio" class="option-hidden" name="acceptance" value="" id="acceptance-none" checked="checked" />
										<label for="acceptance-none">No Option Selected</label>
										<input type="radio" class="option" name="acceptance" value="1" id="acceptance-accept" />
										<label for="acceptance-accept">I Accept</label>
										<input type="radio" class="option" name="acceptance" value="-1" id="acceptance-changes-required" />
										<label for="acceptance-changes-required">I Require Changes</label>
									</fieldset>
								<?php endif; ?>
							</div>
							<footer>Page <?php echo $page->get('ordering'); ?></footer>
						</article>	
					<?php endforeach; ?>
					<nav class="pagination">
						<button id="prev">Prev</button>
						<button id="next">Next</button>
					</nav>
				</section>
			</div>
		</div>
	</fieldset>
	<p class="submit">
		<input type="submit" value="Submit" />
	</p>
	</form>
</section>
<script type="text/javascript">
	$(function(){
		$('section.contract').addClass('contract-paginated');
		if ($('#authorized-yes').prop('checked'))
		{
			$('.contract-paginated').show();
		}
		$('.contract-paginated .article').first().addClass('current-article').next('.article').addClass('next-article');
		$('#next').on('click',function(e){
			e.preventDefault();
			var currentPage = $('.current-article');
			var nextPage = $('.next-article');
			var prevPage = $('.prev-article');
			if (nextPage.length){
				nextPage.removeClass('next-article').addClass('current-article').next('.article').addClass('next-article');
				currentPage.removeClass('current-article').addClass('prev-article');
				prevPage.removeClass('prev-article');
			}
			checkPagePosition();
		});
		$('#prev').on('click',function(e){
			e.preventDefault();
			var currentPage = $('.current-article');
			var nextPage = $('.next-article');
			var prevPage = $('.prev-article');
			if (prevPage.length){
				prevPage.removeClass('prev-article').addClass('current-article').prev('.article').addClass('prev-article');
				currentPage.removeClass('current-article').addClass('next-article');
				nextPage.removeClass('next-article');
			}
			checkPagePosition();
		});

	  	$('input[name="authorized"]').on('click', function(e){
			if ($(this).val() == 1){
				$('.contract-paginated').show();
				checkPagePosition();
			}
			else{
				$('.contract-paginated').hide();
			}
		});

		var checkPagePosition = function () {
			var articleCount = $('.article').length;
			var currentIndex = $('.article').index($('.current-article'));
			if (currentIndex == 0){
				$('#prev').hide();
			}	
			else if (articleCount > 1){
				$('#prev').show();
			}
			if (currentIndex == (articleCount - 1)){
				$('#next').hide();
			}
			else if (articleCount > 1){
				$('#next').show();
			}
		
		};
	});
</script>
