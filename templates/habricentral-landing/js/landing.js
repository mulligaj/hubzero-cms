$(document).ready( function() {
	var default_email_val = "e-mail@address.com";
	 
	$("#emailaddress").val( default_email_val );

	$("#emailaddress").bind({
		focus: function() {
			if(this.value == default_email_val) {
				this.value = "";
			}
		},
		blur: function() {
			if(this.value == "") {
				this.value = default_email_val;
			}
		}
	})
});