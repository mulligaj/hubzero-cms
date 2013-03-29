var typewatch = (function(){
	var timer = 0;
	return function(callback, ms){
		clearTimeout(timer);
		timer = setTimeout(callback, ms);
	}
})();

(function(jQuery) {

	jQuery.fn.stickyNotes = function(options) {
		jQuery.fn.stickyNotes.options = jQuery.extend({}, jQuery.fn.stickyNotes.defaults, options);
		jQuery.fn.stickyNotes.prepareContainer(this);
		jQuery.each(jQuery.fn.stickyNotes.options.notes, function(index, note){
			jQuery.fn.stickyNotes.renderNote(note);
			jQuery.fn.stickyNotes.notes.push(note);
		});
	};

	jQuery.fn.stickyNotes.getNote = function(note_id) {
		var result = null;
		jQuery.each(jQuery.fn.stickyNotes.notes, function(index, note) {
			if (note.id == note_id) {
				result = note;
				return false;
			}
		});
		return result;
	}

	jQuery.fn.stickyNotes.getNotes = function() {
		return jQuery.fn.stickyNotes.notes;
	}

	jQuery.fn.stickyNotes.removeNote = function(note_id) {
		jQuery.each(jQuery.fn.stickyNotes.notes, function(index, note) {
			if (note.id == note_id) {
				jQuery.fn.stickyNotes.notes.splice(index, 1);
				return false;
			}
		});
	}

	jQuery.fn.stickyNotes.prepareContainer = function(container) {
		jQuery.fn.stickyNotes.container = jQuery(container);

		if (jQuery.fn.stickyNotes.options.controls) {
			jQuery.fn.stickyNotes.container.append('<button id="add_note">Add Note</button>');
			jQuery("#add_note").on('click', function() {
				jQuery.fn.stickyNotes.createNote();
				return false;
			});	
		}
	};

	jQuery.fn.stickyNotes.createNote = function() {
		var pos_x = 0,
			pos_y = 0,
			note_id = jQuery.fn.stickyNotes.notes.length + 1;

		var _note_content = jQuery(document.createElement('textarea')).on('keyup', function () {
				typewatch(function (e) {
						jQuery.fn.stickyNotes.stopEditing(note_id);
					}, 500);
				})
				.on('focus', function (e){
					jQuery(this).parent().parent().css('opacity', 1);
				})
				.on('blur', function (e){
					jQuery(this).parent().parent().css('opacity', 0.85);
				});

		var _div_note 	= 	jQuery(document.createElement('div')).addClass('jStickyNote');

		var _div_header = 	jQuery(document.createElement('div')).addClass('jSticky-header');
		_div_note.append(_note_content);
		var _div_delete = 	jQuery(document.createElement('div'))
							.addClass('jSticky-delete')
							.attr('title', 'Delete note')
							.on('click', function(){jQuery.fn.stickyNotes.deleteNote(this);});

		var _div_wrap 	= 	jQuery(document.createElement('div'))
							.css({'position':'absolute','top':pos_x,'left':pos_y, 'float' : 'left'})
							.attr('id', 'note-' + note_id)
							.attr("data-id", 0)
							.append(_div_header)
							.append(_div_note)
							.append(_div_delete);

		_div_wrap.addClass('jSticky-medium');
		if (jQuery.fn.stickyNotes.options.resizable) {
			_div_wrap.resizable({stop: function(event, ui) { jQuery.fn.stickyNotes.resizedNote(note_id)}});
		}
		_div_wrap.draggable({
			containment: jQuery.fn.stickyNotes.container, 
			scroll: false, 
			handle: 'div.jSticky-header', 
			stop: function(event, ui) {
				jQuery.fn.stickyNotes.movedNote(note_id);
			}
		}); 

		jQuery.fn.stickyNotes.container.append(_div_wrap);

		jQuery("#note-" + note_id).on('click', function() {
			return false;
		})
		jQuery("#note-" + note_id).find("textarea").focus();

		var note = {
			"id": note_id,
			"dataId": 0,
			"text": "",
			"pos_x": pos_x,
			"pos_y": pos_y,	
			"width": jQuery(_div_wrap).width(),
			"height": jQuery(_div_wrap).height()
		};
		jQuery.fn.stickyNotes.notes.push(note);

		jQuery(_note_content).css('height', jQuery("#note-" + note_id).height() - 32);

		if (jQuery.fn.stickyNotes.options.createCallback) {
			jQuery.fn.stickyNotes.options.createCallback(note);
		}
	}

	jQuery.fn.stickyNotes.stopEditing = function(note_id) {
		var note = jQuery.fn.stickyNotes.getNote(note_id);
		note.text = jQuery("#note-" + note_id).find('textarea').val();

		if (jQuery.fn.stickyNotes.options.editCallback) {
			jQuery.fn.stickyNotes.options.editCallback(note);
		}
	};

	jQuery.fn.stickyNotes.deleteNote = function(delete_button) {
		var note_id = jQuery(delete_button).parent().attr("id").replace(/note-/, "");
		var note = jQuery.fn.stickyNotes.getNote(note_id);
		jQuery("#note-" + note_id).remove();

		if (jQuery.fn.stickyNotes.options.deleteCallback) {
			jQuery.fn.stickyNotes.options.deleteCallback(note);
		}

		jQuery.fn.stickyNotes.removeNote(note_id);
	}

	jQuery.fn.stickyNotes.renderNote = function(note) {
		var _p_note_text = 	jQuery(document.createElement('p')).attr("id", "p-note-" + note.id)
							.html( note.text);
		var _div_note 	= 	jQuery(document.createElement('div')).addClass('jStickyNote');

		var _div_header = 	jQuery(document.createElement('div')).addClass('jSticky-header');

		var _note_content = jQuery(document.createElement('textarea')).val(note.text).on('keyup', function (e) {
				typewatch(function () {
					jQuery.fn.stickyNotes.stopEditing(note.id);
				}, 500);
			})
			.on('focus', function (e){
				jQuery(this).parent().parent().css('opacity', 1);
			})
			.on('blur', function (e){
				jQuery(this).parent().parent().css('opacity', 0.85);
			});
		_div_note.append(_note_content);

		var _div_delete = 	jQuery(document.createElement('div'))
							.text('x')
							.addClass('jSticky-delete')
							.attr('title', 'Delete note')
							.on('click', function(){jQuery.fn.stickyNotes.deleteNote(this);});

		var _div_wrap 	= 	jQuery(document.createElement('div'))
							.css({'position':'absolute','top':note.pos_y,'left':note.pos_x, "width":note.width,"height":note.height}) //'float': 'left',
							.attr("id", "note-" + note.id)
							.attr("data-id", note.id)
							.addClass('jSticky-medium')
							.append(_div_header)
							.append(_div_note)
							.append(_div_delete);

		if (jQuery.fn.stickyNotes.options.resizable) {
			_div_wrap.resizable({stop: function(event, ui) { jQuery.fn.stickyNotes.resizedNote(note.id)}});
		}

		_div_wrap.draggable({
			containment: jQuery.fn.stickyNotes.container, 
			scroll: false, 
			handle: 'div.jSticky-header', 
			stop: function(event, ui){
				jQuery.fn.stickyNotes.movedNote(note.id);
			}
		});

		jQuery.fn.stickyNotes.container.append(_div_wrap);
		jQuery("#note-" + note.id).on('click', function() {
			return false;
		})

		jQuery(_note_content).css('height', jQuery('#note-' + note.id).height() - 32);
	}

	jQuery.fn.stickyNotes.movedNote = function(note_id) {
		var note = jQuery.fn.stickyNotes.getNote(note_id);

		note.pos_x = jQuery('#note-' + note_id).css('left').replace(/px/, '');
		note.pos_y = jQuery('#note-' + note_id).css('top').replace(/px/, '');

		if (jQuery.fn.stickyNotes.options.moveCallback) {
			jQuery.fn.stickyNotes.options.moveCallback(note);
		}
	}

	jQuery.fn.stickyNotes.resizedNote = function(note_id) {
		var note = jQuery.fn.stickyNotes.getNote(note_id);

		note.width  = jQuery("#note-" + note_id).width();
		note.height = jQuery("#note-" + note_id).height();

		if (jQuery.fn.stickyNotes.options.resizeCallback) {
			jQuery.fn.stickyNotes.options.resizeCallback(note);
		}
	}

	jQuery.fn.stickyNotes.defaults = {
		notes: [],
		resizable: true,
		controls: true,
		editCallback: false, 
		createCallback: false,
		deleteCallback: false,
		moveCallback: false,
		resizeCallback: false
	};

	jQuery.fn.stickyNotes.options = null;

	jQuery.fn.stickyNotes.notes = new Array();
})(jQuery);
