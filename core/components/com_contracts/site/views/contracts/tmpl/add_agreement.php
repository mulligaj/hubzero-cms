<article id="contract-agreement" class="article">
<?php echo $this->agreement->document(); ?>
<fieldset class="radio-options">
	<input type="radio" class="option-hidden" name="accepted" value="" id="accepted-none" checked="checked" />
	<label for="accepted-none">No Option Selected</label>
	<input type="radio" class="option" name="accepted" value="1" id="accepted-accept" />
	<label for="accepted-accept">Yes, I accept these terms and conditions on behalf of my organization</label>
	<input type="radio" class="option" name="accepted" value="-1" id="accepted-changes-required" />
	<label for="accepted-changes-required">No, Please email me a copy of the contract for further review/ changes.</label>
</fieldset>
</article>	
