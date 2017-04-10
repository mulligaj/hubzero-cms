/**
 * Comments
**/

jQuery(window).on('load', function() {

	$('body')
		// Toggle text and classes when clicking reply
		.on('click', 'a.reply', function (e) {
			e.preventDefault();

			var frm = $('#' + $(this).attr('rel'));

			if (frm.hasClass('hide')) {
				frm.removeClass('hide');

				$(this).hide();
					//.addClass('hide');
					//.text($(this).attr('data-txt-active'));
			} else {
				frm.addClass('hide');
				$(this).show();
					//.removeClass('hide');
					//.text($(this).attr('data-txt-inactive'));
			}
		})
		// Toggle text and classes when clicking reply
		.on('click', 'a.ef-replycancel', function (e) {
			e.preventDefault();

			var frm = $('#' + $(this).attr('rel'));

			if (!frm.hasClass('hide')) {
				frm.addClass('hide');
			}

			var rply = $($(this).attr('href'));

			/*if (rply.hasClass('hide')) {
				rply.removeClass('hide');
			}*/
			rply.show();
		})
		// Add confirm dialog to delete links
		.on('click', 'a.delete', function (e) {
			var res = confirm($(this).attr('data-confirm'));
			if (!res) {
				e.preventDefault();
			}
			return res;
		})
		// Have submit button reset the form
		.on('submit', '.ef-reply', function(e) {
			e.preventDefault();

			var frm = $(this);

			var comment = $(frm.find('.ef-replycontent')[0]);

			if (!comment.val()) {
				frm.find('.error')
					.text('Please enter a comment')
					.show();

				return false;
			}

			$.post(frm.attr('action'), frm.serialize(), function (response){
				if (response) {
					$('.fancybox-inner').html(response);
				}
			});
			/*
			$.ajax({
				type: "POST",
				url: frm.attr('action'),
				data: frm.serialize(),
				dataType: dataType,
				success: function(data) {
					if (data) {
						$('.fancybox-inner').html(data);
					}
				},
				error: function() {
					alert('error handing here');
				}
			});*/
		});
});
