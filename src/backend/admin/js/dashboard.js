
jQuery(document).ready(function ($) {

	var _timeout;
	var close_toast = function () {
		_timeout = setTimeout(function () {
			$('#toast-container').hide();
		}, 1500);
	};

	var toast = function (msg, type) {
		if (_.isUndefined(type)) {
			type = 'success';
		}
		if (_timeout) {
			clearTimeout(_timeout);
		}
		if ($('#toast-container').length <= 0) {
			$('body').append('<div id="toast-container" class="toast-top-right"></div>');
		}
		const item = $('<div class="toast-message toast-' + type + '">' + msg + '<button type="button" class="toast-close-button" role="button">×</button></div>');
		$('#toast-container').html(item);
		$('#toast-container').show();
		item.on('click', function (e) {
			$('#toast-container').hide();
		});
		close_toast();
	};


	$(document).on('change', '.auto-save', function (e) {
		e.preventDefault();
		var input = $(this);
		if (!input.is(':disabled')) {
			var name = input.attr('name');
			var t = input.attr('type');

			input.attr('disabled', 'disabled');
			let value = '';
			switch (t) {
				case 'checkbox':
					value = input.is(':checked') ? 'on' : 'off';
					break;
				case 'radio':
					value = $('input[name="' + name + '"]:checked').val();
					break;
				default:
					value = input.val();
			}



			console.log('Type', value);


			toast(Customify_Dashboard.updating, 'info');
			$.ajax({
				url: ajaxurl,
				type: 'post',
				data: {
					action: 'customify_dashboard_settings',
					option: name,
					value: value,
					_nonce: Customify_Dashboard._nonce
				}
			}).done(function (data) {
				toast(Customify_Dashboard.updated, 'success');
			}).fail(function (data) {
				toast(Customify_Dashboard.error, 'warning');
			}).always(function () {
				input.removeAttr('disabled');
			});
		}

	});


});