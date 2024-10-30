(function ($) {
	'use strict';

	$(document).ready(function ($) {
		//Initiate Color Picker
		$('.wp-color-picker-field').wpColorPicker();
		//add chooser
		$(".chosen-select").chosen();

		// Switches option sections
		$('.cbxwpslack_group').hide();
		var activetab = '';
		if (typeof(localStorage) != 'undefined') {
			//get
			activetab = localStorage.getItem("cbxwpslackactivetab");
		}

		//if url has section id as hash then set it as active or override the current local storage value
		if (window.location.hash) {
			activetab = window.location.hash;
			if (typeof(localStorage) != 'undefined') {
				localStorage.setItem("cbxwpslackactivetab", activetab);
			}
		}

		if (activetab != '' && $(activetab).length) {
			$(activetab).fadeIn();
		} else {
			$('.cbxwpslack_group:first').fadeIn();
		}
		$('.cbxwpslack_group .collapsed').each(function () {
			$(this).find('input:checked').parent().parent().parent().nextAll().each(
				function () {
					if ($(this).hasClass('last')) {
						$(this).removeClass('hidden');
						return false;
					}
					$(this).filter('.hidden').removeClass('hidden');
				});
		});

		if (activetab != '' && $(activetab + '-tab').length) {
			$(activetab + '-tab').addClass('nav-tab-active');
		}
		else {
			$('.nav-tab-wrapper a:first').addClass('nav-tab-active');
		}

		$('.nav-tab-wrapper a').click(function (evt) {
			evt.preventDefault();

			$('.nav-tab-wrapper a').removeClass('nav-tab-active');
			$(this).addClass('nav-tab-active').blur();
			var clicked_group = $(this).attr('href');
			if (typeof(localStorage) != 'undefined') {
				//set
				localStorage.setItem("cbxwpslackactivetab", $(this).attr('href'));
			}
			$('.cbxwpslack_group').hide();
			$(clicked_group).fadeIn();

		});

		$('.wpsa-browse').on('click', function (event) {
			event.preventDefault();

			var self = $(this);

			// Create the media frame.
			var file_frame = wp.media.frames.file_frame = wp.media({
				title   : self.data('uploader_title'),
				button  : {
					text: self.data('uploader_button_text')
				},
				multiple: false
			});

			file_frame.on('select', function () {
				var attachment = file_frame.state().get('selection').first().toJSON();

				self.prev('.wpsa-url').val(attachment.url);
			});

			// Finally, open the modal
			file_frame.open();
		});


	});


})(jQuery);