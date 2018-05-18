<?php
	$this->js('customfields');
	$xml = Components\Groups\Models\Orm\Field::toXml($this->customFields);
	$form = new Hubzero\Form\Form('application', array('control' => 'customfields'));
	$form->load($xml);
	$form->bind($this->customAnswers);
	foreach ($this->customFields as $field)
	{
		$formfield = $form->getField($field->get('name'));
		if (strtolower($formfield->type) != 'paragraph')
		{
			echo $formfield->label;
		}
		echo $formfield->input;
		if ($formfield->description && strtolower($formfield->type) != 'paragraph')
		{
			echo '<span class="hint">' . $formfield->description . '</span>';
		}
	}
