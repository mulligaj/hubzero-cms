<?php
JToolBarHelper::title(JText::_('Focus Areas'));
JToolBarHelper::custom('index', 'edit', ' ', 'Tag Relationships', false);

$doc =& JFactory::getDocument();

$doc->addScript('/administrator/components/com_tag_graph/resources/jquery.js');
$doc->addScript('/administrator/components/com_tag_graph/resources/jquery-ui.js');
$doc->addScript('/administrator/components/com_tag_graph/resources/tag_graph.js');

$dbh =& JFactory::getDBO();
$dbh->setQuery(
	'SELECT *, (SELECT group_concat(resource_type_id) FROM #__focus_area_resource_type_rel WHERE focus_area_id = fa.id) AS types 
	FROM #__tags t
	INNER JOIN #__focus_areas fa ON fa.tag_id = t.id
	ORDER BY raw_tag'
);
$fas = $dbh->loadAssocList();
$dbh->setQuery(
	'SELECT DISTINCT id, type FROM #__resource_types WHERE category = (SELECT id FROM #__resource_types WHERE type = \'Main Types\') AND contributable ORDER BY type'
);
$types = $dbh->loadAssocList('id');
?>
<script type="text/javascript">
window.resourceTypes = <?php echo json_encode(array_values($types)); ?>;
</script>
<p>By default, there is one focus area group, named "focus area", that is shown for every resource on the site during the contribution process, prompting the user to select from the available areas to categorize their submission.</p>
<p>Here, you can change the properties of this group or add additional groups of focus areas.</p>
<p>For each group, you may choose which types of resources are valid for the group, and at what depth to consider the group mandatory. (Tags that are nested more deeply than the mandatory level will be presented as a box of optional, multiple-selection choices).</p>

<form action="index.php?option=com_tag_graph" method="post">
<ul id="fas">
<?php 
foreach ($fas as $fa): 
	$type_ids = array_flip(explode(',', $fa['types']));
?>
	<li>
		<h3>Group name: <input type="text" name="name-<?php echo $fa['id']; ?>" value="<?php echo str_replace('"', '&quot;', $fa['raw_tag']); ?>" /><button class="delete-group">Delete group</button></h3>
		<fieldset>
			<p><label for="types-<?php echo $fa['id']; ?>[]">Show for resource types:</label></p>
			<select id="types-<?php echo $fa['id']; ?>" name="types-<?php echo $fa['id']; ?>[]" multiple="multiple" size="<?php echo count($types); ?>">
				<?php foreach ($types as $type): ?>
					<option value="<?php echo $type['id']; ?>" <?php if (isset($type_ids[$type['id']])) echo 'selected="selected" '; ?>><?php echo $type['type']; ?></option>
				<?php endforeach; ?>
			</select>
		</fieldset>
		<fieldset>
			<input type="radio" name="mandatory-<?php echo $fa['id']; ?>" value="mandatory" <?php if (!is_null($fa['mandatory_depth']) && $fa['mandatory_depth'] < 2) echo 'checked="checked" '; ?>/> mandatory
			<input type="radio" name="mandatory-<?php echo $fa['id']; ?>" value="optional" <?php if (is_null($fa['mandatory_depth'])) echo 'checked="checked" '; ?>/> optional
			<input type="radio" name="mandatory-<?php echo $fa['id']; ?>" value="depth" <?php if ($fa['mandatory_depth'] > 1) echo 'checked="checked" '; ?>/> mandatory until depth: <input type="text" name="mandatory-depth-<?php echo $fa['id']; ?>" value="<?php if ($fa['mandatory_depth'] > 1) echo $fa['mandatory_depth']; ?>" />
		</fieldset>

		<fieldset>
			<input type="radio" name="multiple-<?php echo $fa['id']; ?>" value="single" <?php if (is_null($fa['multiple_depth'])) echo 'checked="checked" '; ?>/> single-select (radio) 
			<input type="radio" name="multiple-<?php echo $fa['id']; ?>" value="multiple" <?php if (!is_null($fa['multiple_depth']) && $fa['multiple_depth'] < 2) echo 'checked="checked" '; ?>/> multiple-select (checkbox)
			<input type="radio" name="multiple-<?php echo $fa['id']; ?>" value="depth" <?php if ($fa['multiple_depth'] > 1) echo 'checked="checked" '; ?>/> single-select until depth: <input type="text" name="multiple-depth-<?php echo $fa['id']; ?>" value="<?php if ($fa['multiple_depth'] > 1) echo $fa['multiple_depth']; ?>" />
		</fieldset>
	</li>
<?php 
endforeach;
$fill_new = !isset($added_new_focus_area);
$type_ids = $fill_new && isset($_POST['types-new']) ? array_flip($_POST['types-new']) : array();
?>
</ul>
<p><button id="add_group">Add group</button></p>
<p>
	<input type="hidden" name="task" value="update_focus_areas" />
	<button type="submit">Save</button>
</p>
</form>

<form name="adminForm" method="get" action="index.php">
	<input type="hidden" value="com_tag_graph" name="option" />
	<input type="hidden" value="" name="task" />
	<input type="hidden" value="0" name="boxchecked" />
</form>
