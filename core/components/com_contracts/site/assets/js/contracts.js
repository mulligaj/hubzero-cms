/**
 * @package     hubzero-cms
 * @file        components/com_blog/assets/js/blog.jquery.js
 * @copyright   Copyright 2005-2015 HUBzero Foundation, LLC.
 * @license     http://opensource.org/licenses/MIT MIT
 */

$(function(){
	$('input[name="authority"]').on('click', function(e){
		if ($(this).val() == 1){
			var parentForm = $(this).closest('form');
			$.ajax({
				url: parentForm.attr('action'),
				data: parentForm.serialize() + '&no_html=1',
				method: 'POST',
				success: function(response){
					if (response.showDocument == true){
						$('#contract-section').html(response.html);
						var topPos = $('#contract-section').offset().top - 110;
						$(window).scrollTop(topPos);
					}
					else{
						$('#authorized-none').prop('checked', true);
						parentForm.submit();
					}
				}
			});
		}
		else{
			$('#contract-section').html('');
		}
	});
});
