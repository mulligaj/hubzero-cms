/**
 * @package     hubzero-cms
 * @file        components/com_blog/assets/js/blog.jquery.js
 * @copyright   Copyright 2005-2015 HUBzero Foundation, LLC.
 * @license     http://opensource.org/licenses/MIT MIT
 */

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
				pageContainer.children('.page-content').html(response.content);
				$(window).scrollTop(pageContainer.offset().top - 50);
				pageContainer.addClass('editing');
				toggleEditState(pageContainer);
			}
		});
	});
	$('#pages-section').on('click', '.delete-page', function(e){
		e.stopPropogation();
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
		var container = $(this).closest('.page-item');
		$.ajax({
			url: url,
			data: data,
			method: "POST",
			success: function(response){
				   container.children('.page-content').html(response.content);
				   container.removeClass('editing');
				   container.removeClass('active');
				   toggleEditState(container);
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
				var container = $(response.content);
				$('#pages-section').prepend(container);
				toggleEditState(container);
				container.addClass('editing');
				container.addClass('active');
				reorderPages();
				$(window).scrollTop(container.offset().top - 50);
			}
	   });
	});

	var toggleEditState = function(pageContainer){
		pageContainer.children('.save-item-buttons').toggle();
		pageContainer.children('.edit-item-buttons').toggle();
	};

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
});

