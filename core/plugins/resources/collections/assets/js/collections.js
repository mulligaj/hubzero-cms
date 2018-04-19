$(function(){
	$('#add-collection').fancybox();
	$('body').on('change', '#collectionForm #pid', function(e){
		var selected = $(this).val();
		if (selected.length)
		{
			$('#new-series-add').hide();
			$('#new-series-add').prev('.col').find('.or').hide();
			$('input[name="resource-title"]').val('');
		}		
		else
		{
			$('#new-series-add').prev('.col').find('.or').show();
			$('#new-series-add').show();
		}
	});
});
