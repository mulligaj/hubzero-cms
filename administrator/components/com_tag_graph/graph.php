<?php
$doc =& JFactory::getDocument();

$doc->addScript('/administrator/components/com_tag_graph/resources/jquery.js');
$doc->addScript('/administrator/components/com_tag_graph/resources/jquery-ui.js');
$doc->addScript('/administrator/components/com_tag_graph/resources/d3/d3.min.js');
$doc->addScript('/administrator/components/com_tag_graph/resources/d3/d3.layout.min.js');
$doc->addScript('/administrator/components/com_tag_graph/resources/d3/d3.geom.min.js');
$doc->addScript('/administrator/components/com_tag_graph/resources/tag_graph.js');
$doc->addScript('/plugins/hubzero/autocompleter/autocompleter.jquery.js');
$doc->addStyleSheet('/plugins/hubzero/autocompleter/autocompleter.css');
$doc->addStyleSheet('/administrator/components/com_tag_graph/resources/tag_graph.css');

JToolBarHelper::title(JText::_('Tag Management'));
JToolBarHelper::custom('meta', 'edit', ' ', 'Focus Areas', false);
?>
<form id="tag-sel" action="" method="get">
	<p>Tag: <input type="text" id="center-node" class="tag-entry" value="<?php echo $preload; ?>" /><button type="submit" id="center">Lookup</button></p>
	<p>Show relationships: <input type="radio" name="relationship" id="hierarchical" checked="checked" /> labels and hierarchy <input type="radio" name="relationship" id="implicit" /> implicit </p>
</form>
<div id="graph"></div>
<div id="metadata-cont">
	<h3>Metadata</h3>
	<form id="metadata" action="index.php?option=com_tag_graph" method="post">
		<p>Description: <textarea cols="100" rows="4" id="description" name="description"></textarea></p>
		<p>Labeled: <ul id="labeled" class="textboxlist-holder act"></ul>
		<p>Labels: <ul id="labels" class="textboxlist-holder act"></ul>
		<p>Parents: <ul id="parents" class="textboxlist-holder act"></ul>
		<p>Children: <ul id="children" class="textboxlist-holder act"></ul>
		<p>
			<input type="hidden" class="tag-id" name="tag" value="" />
			<input type="hidden" value="update" name="task" />
			<button type="submit">Update</button>
		</p>
	</form>
	<h3>Delete or merge</h3>
	<form action="index.php?option=com_tag_graph" method="post">
		<p id="merge-par">
			<input type="checkbox" name="do_merge" /> Merge <span class="tag-count"></span> tagged items into another tag: <input type="text" id="merge-tag" name="merge_tag" class="tag-entry" />
		</p>
		<p>
			<input type="checkbox" name="really" /> I understand that this operation is irreversible and I am prepared to live with its consequences
		</p>
		<p>
			<input type="hidden" class="tag-id" name="tag" value="" />
			<input type="hidden" value="delete" name="task" />
			<button type="submit">Delete</button>
		</p>
	</form>
</div>

<form name="adminForm" method="get" action="index.php">
	<input type="hidden" value="com_tag_graph" name="option" />
	<input type="hidden" value="" name="task" />
	<input type="hidden" value="0" name="boxchecked" />
</form>
