jQuery.noConflict();

jQuery(function($)
{
	var w = 960,
	    h = 600;

	var tag_editors = function(json)
	{
		var blur_timeout = null;
		$(['labeled', 'labels', 'parents', 'children']).each(function(idx, type)
		{
			var par = $('#' + type);
			par.click(function(evt)
			{
				var mi = $('#maininput-actags');
				if (mi)
					mi.remove();
				var text = $('<input id="maininput-actags" class="maininput" type="text" autocomplete="off" />');
				text.autocomplete({ source: 'index.php?option=com_tag_graph&task=suggest&limit=50' });	

				par.append(text);
				text.focus();

				var add = function()
				{
					var tag = text.val().replace(/,+$/, '');
					text.val('');
					if (!tag)
					{
						text.remove();
						return;
					}

					var li = $('<li class="bit-box"></li>');
					li.text(tag);
					var inp = $('<input type="hidden" name="' + type + '[]" value="' + tag + '" />');
					li.append(inp);
					var close = $('<a class="closebutton" href="#"></a>');
					close.click(function(evt)
					{
						evt.preventDefault();
						par[0].removeChild(li[0]);
					});
					li.append(close);
					par.append(li);

					par.append(text);
					text.focus();
				};

				text.keyup(function(evt)
				{
					if ((evt.keyCode || evt.which) == 188)
						return add();
				});
				text.focus(function()
				{
					if (blur_timeout)
						clearInterval(blur_timeout);
				});
				text.blur(function()
				{
					blur_timeout = setTimeout(function()
					{
						add();
					}, 400);
				});
			});
			$(json[type]).each(function(k, v)
			{
				var li = $('<li class="bit-box"></li>');
				li.text(v);
				var inp = $('<input type="hidden" name="' + type + '[]" value="' + v + '" />');
				li.append(inp);
				var close = $('<a class="closebutton" href="#"></a>');
				li.append(close);
				par.append(li);
				close.click(function(evt)
				{
					evt.preventDefault();
					par[0].removeChild(li[0]);
				});

			});
		});
	};

	var center = function(tag)
	{
		$('#graph, #labels, #labeled, #parents, #children').empty();
		$('#metadata-cont').css('display', 'none');
		$('#graph').css('background', 'url(\'/administrator/components/com_tag_graph/resources/throbber.gif\') no-repeat top left');

		var vis = d3.select("#graph")
			.append("svg:svg")
			.attr("width", w)
			.attr("height", h);
		d3.json("/administrator/index.php?option=com_tag_graph&task=implicit&tag=" + tag, function(json) 
		{
			$('#description').val(json.description);
			$('.tag-id').val(json.id);
			$('.tag-count').text(json.count);
			tag_editors(json);
			$('#metadata-cont').css('display', 'block');
			$('#graph').css('background', '#fff');
			var force = d3.layout.force()
				.charge(-312)
				.linkDistance(250)
				.nodes(json.nodes)
				.links(json.links)
				.size([w, h - 50])
				.start();

			var link = vis.selectAll("line.link")
				.data(json.links)
				.enter().append("svg:line")
					.attr("class", "link")
					.style("stroke-width", function(d) { return Math.max(1, Math.sqrt(200 * d.value)); })
					.attr("x1", function(d) { return d.source.x; })
					.attr("y1", function(d) { return d.source.y; })
					.attr("x2", function(d) { return d.target.x; })
					.attr("y2", function(d) { return d.target.y; });

			var node = vis.selectAll("circle.node")
				.data(json.nodes)
				.enter().append("svg:ellipse")
					.attr("class", "node")
					.attr("cx", function(d) { return d.x; })
					.attr("cy", function(d) { return d.y; })
					.attr("rx", 5)
					.attr('ry', 5)
					.style("fill", function(d) { return d.tag == $('#center-node').val() || d.raw_tag == $('#center-node').val() ? '#79a' : '#cdf'; })
					.call(force.drag)
					.on('click', function(n)
					{
						$('#center-node').val(n.raw_tag);
						$('#tag-sel').submit();
					});
			
			var labels = vis.selectAll('circle.node')
			.data(json.nodes)
			.enter().append('svg:text')
				.attr('font-size', '10px')
				.text(function(d) { return d.raw_tag; });

			node.append("svg:title")
				.text(function(d) { return 'center graph on ' + d.raw_tag; });

			vis.style("opacity", 1e-6)
				.transition()
				.duration(1000)
				.style("opacity", 1);

			force.on("tick", function() 
			{
				link
					.attr("x1", function(d) { return d.source.x; })
					.attr("y1", function(d) { return d.source.y; })
					.attr("x2", function(d) { return d.target.x; })
					.attr("y2", function(d) { return d.target.y; });
		
				node
					.attr("cx", function(d) { return d.x; })
					.attr("cy", function(d) { return d.y; });

				labels	
					.attr("x", function(d) { return d.x + 7; })
					.attr("y", function(d) { return d.y + 2.5; });
			});
		});
	};

	var center_hierarchy = function(tag)
	{
		$('#graph, #labels, #labeled, #parents, #children').empty();
		$('#metadata-cont').css('display', 'none');
		$('#graph').css('background', 'url(\'/administrator/components/com_tag_graph/resources/throbber.gif\') no-repeat top left');

		var vis = d3.select("#graph")
			.append("svg:svg")
			.attr("width", w)
			.attr("height", 400);
		d3.json("/administrator/index.php?option=com_tag_graph&task=hierarchy&tag=" + tag, function(json) 
		{
			$('#description').val(json.description);
			$('.tag-id').val(json.id);
			$('.tag-count').text(json.count);
			tag_editors(json);

			$('#metadata-cont').css('display', 'block');
			$('#graph').css('background', '#fff');
			var force = d3.layout.force()
				.charge(-100)
				.linkDistance(200)
				.nodes(json.nodes)
				.links(json.links)
				.size([w, 350])
				.start();

			var link = vis.selectAll("line.link")
				.data(json.links)
				.enter().append("svg:line")
					.attr("class", "link")
					.style("stroke-width", '1')
					.attr("x1", function(d) { return d.source.x; })
					.attr("y1", function(d) { return d.source.y; })
					.attr("x2", function(d) { return d.target.x; })
					.attr("y2", function(d) { return d.target.y; });

			var node = vis.selectAll("circle.node")
				.data(json.nodes)
				.enter().append("svg:ellipse")
					.attr("class", "node")
					.attr("cx", function(d) { return d.x; })
					.attr("cy", function(d) { return d.y; })
					.attr("rx", 5)
					.attr('ry', 5)
					.style("fill", function(d) 
					{ 
						return d.tag == $('#center-node').val() || d.raw_tag == $('#center-node').val() 
							? '#79a' 
							: d.type === 'parent' 
								? '#fdc' 
								: d.type === 'label'
								 ? '#cfd'
								 : '#cdf'; 
					})
					.call(force.drag)
					.on('click', function(n)
					{
						$('#center-node').val(n.raw_tag);
						$('#tag-sel').submit();
					});
			
			var labels = vis.selectAll('circle.node')
			.data(json.nodes)
			.enter().append('svg:text')
				.attr('font-size', '10px')
				.text(function(d) { return d.raw_tag; });

			node.append("svg:title")
				.text(function(d) { return 'center graph on ' + d.raw_tag; });

			vis.style("opacity", 1e-6)
				.transition()
				.duration(1000)
				.style("opacity", 1);

			force.on("tick", function() 
			{
				link
					.attr("x1", function(d) { return d.source.x; })
					.attr("y1", function(d) { return d.source.y; })
					.attr("x2", function(d) { return d.target.x; })
					.attr("y2", function(d) { return d.target.y; });
		
				node
					.attr("cx", function(d) { return d.x; })
					.attr("cy", function(d) { return d.y; });

				labels	
					.attr("x", function(d) { return d.x + 7; })
					.attr("y", function(d) { return d.y + 2.5; });
			});
		});
	};

	$('#tag-sel').submit(function(evt)
	{
		evt.preventDefault();
		var tag = $('#center-node').val().toLowerCase().replace(/[^-_a-z0-9]/g, '');
		if ($("input[@name=relationship]:checked").attr('id') === 'implicit')
			center(tag);
		else
			center_hierarchy(tag);
	});
	if ($('#center-node').val())
		$('#tag-sel').submit();

	$.ui.autocomplete.prototype._renderItem = function(ul, item)
	{
		var term = this.term.split(' ').join('|');
		var re = new RegExp("(" + term + ")", "gi");
		return $("<li></li>")
			.data("item.autocomplete", item)
			.append("<a>" + item.label.replace(re, "<span class=\"highlight\">$1</span>") + "</a>")
			.appendTo(ul);
	};
	$(".tag-entry").autocomplete({ source: 'index.php?option=com_tag_graph&task=suggest&limit=50' });

/*
	<li>
		<h3><input type="text" name="name-new" value="<?php echo $fill_new && isset($_POST['name-new']) ? str_replace('"', '&quot;', $_POST['name-new']) : ''; ?>" /></h3>
		<fieldset>
			<p><label for="types-new">Show for resource types:</label></p>
			<select id="types-new" name="types-new[]" multiple="multiple" size="<?php echo count($types); ?>">
				<?php foreach ($types as $type): ?>
					<option value="<?php echo $type['id']; ?>" <?php if ($fill_new && isset($type_ids[$type['id']])) echo 'selected="selected" '; ?>><?php echo $type['type']; ?></option>
				<?php endforeach; ?>
			</select>
		</fieldset>
		<fieldset>
			<input type="radio" name="mandatory-new" value="mandatory" <?php if (!$fill_new || !isset($_POST['mandatory-new']) || (isset($_POST['mandatory-new']) && $_POST['mandatory-new'] == 'mandatory')) echo 'checked="checked" '; ?>/> mandatory
			<input type="radio" name="mandatory-new" value="optional" <?php if ($fill_new && isset($_POST['mandatory-new']) && $_POST['mandatory-new'] == 'optional') echo 'checked="checked" '; ?>/> optional
			<input type="radio" name="mandatory-new" value="depth" <?php if ($fill_new && isset($_POST['mandatory-new']) && $_POST['mandatory-new'] == 'depth') echo 'checked="checked" '; ?>/> mandatory until depth: <input type="text" name="mandatory-depth-new" value="<?php echo $fill_new && isset($_POST['mandatory-depth-new']) ? str_replace('"', '&quot;', $_POST['mandatory-depth-new']) : ''; ?>" />
		</fieldset>

		<fieldset>
			<input type="radio" name="multiple-new" value="single" <?php if (!$fill_new || !isset($_POST['multiple-new']) || (isset($_POST['multiple-new']) && $_POST['multiple-new'] == 'single')) echo 'checked="checked" '; ?>/> single-select (radio) 
			<input type="radio" name="multiple-new>" value="multiple" <?php if ($fill_new && isset($_POST['multiple-new']) && $_POST['multiple-new'] == 'multiple') echo 'checked="checked" '; ?>/> multiple-select (checkbox)
			<input type="radio" name="multiple-new" value="depth" <?php if ($fill_new && isset($_POST['multiple-new']) && $_POST['multiple-new'] == 'depth') echo 'checked="checked" '; ?>/> single-select until depth: <input type="text" name="multiple-depth-new" value="<?php echo $fill_new && isset($_POST['multiple-depth-new']) ? str_replace('"', '&quot;', $_POST['multiple-depth-new']) : ''; ?>" />
		</fieldset>
	</li>
*/

	var new_idx = 0;
	$('#add_group').click(function(evt)
	{
		++new_idx;
		evt.preventDefault();
		var li = $('<li></li>');
		var name = $('<input type="text" name="name-new-' + new_idx + '" />');
		li.append($('<h3>Group name: </h3>').append(name).append(
			$('<button>Delete group</button>').click(function()
			{
				li.remove();
			})
		));
		var type_fs = $('<fieldset><p><label for="types-new-' + new_idx + '">Show for resource types:</label></p></fieldset>');
		var type_sel = $('<select name="types-new-' + new_idx + '[]" multiple="multiple" size="' + window.resourceTypes.length + '"></select>');
		type_fs.append(type_sel);
		li.append(type_fs);
		$(window.resourceTypes).each(function(_idx, type)
		{
			type_sel.append($('<option value="' + type.id + '">' + type.type + '</option>'));
		});
		li.append($('<fieldset>' +
				'<input type="radio" name="mandatory-new-' + new_idx + '" value="mandatory" /> mandatory' +
				'<input type="radio" name="mandatory-new-' + new_idx + '" value="optional" /> optional' +
				'<input type="radio" name="mandatory-new-' + new_idx + '" value="depth" /> mandatory until depth: <input type="text" name="mandatory-depth-new-' + new_idx + '" /></p>' +
			'</fieldset>'
		)).append($('<fieldset>' +
				'<input type="radio" name="multiple-new-' + new_idx + '" value="single" /> single-select' +
				'<input type="radio" name="multiple-new-' + new_idx + '" value="optional" /> multiple-select' +
				'<input type="radio" name="multiple-new-' + new_idx + '" value="depth" /> single-select until depth: <input type="text" name="multiple-depth-new-' + new_idx + '" /></p>' +
			'</fieldset>'
		));
		$('#fas').append(li);
		name.focus();
	});
	$('.delete-group').click(function(evt)
	{
		$(evt.target).parent().parent().remove();
	});
});
