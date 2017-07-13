jQuery(document).ready(function($) {
	var initial_theme = $( '#theme' ).val();

	$( '#theme' ).on( 'change', function() {
		if ($(this).val() != initial_theme) {
			$( '#canvas_theme_warning' ).show();
			$( '#canvas_theme_link' ).hide();
		} else {
			$( '#canvas_theme_warning' ).hide();
			$( '#canvas_theme_link' ).show();
		}
	})

	$( '#canvas_different_theme' ).on( 'change', function() {
		if ($(this).is(':checked')) {
			$( '#theme_choice_block' ).show();
		} else {
			$( '#theme_choice_block' ).hide();
		}
	})

	$( '.canvas-other-options-checkbox' ).on( 'change', function() {
		if ($(this).is(':checked')) {
			$( '.canvas-other-options' ).show();
		} else {
			$( '.canvas-other-options' ).hide();
		}
	})

	$( '#canvas_push_log_enable' ).on( 'change', function() {
		if ($(this).is( ':checked' )) {
			$( '#canvas_push_log_name_block' ).show();
		} else {
			$( '#canvas_push_log_name_block' ).hide();
		}
	})

	$(".canvas-chosen-select").chosen();

	$( '#form_editor' ).areYouSure();

	/* Manual notifications */
	if ($('#canvas_notification_data_id').length) {

		var canvasLimitChars = function (txtMsg, CharLength, indicator) {
			chars = txtMsg.value.length;
			var chars_left  = CharLength - chars;
			document.getElementById(indicator).innerHTML = chars_left + " character" + (chars_left != 1 ? 's' : '') + " left.";
			if (chars > CharLength) {
				txtMsg.value = txtMsg.value.substring(0, CharLength);
				document.getElementById(indicator).innerHTML = "0 characters left.";
			}
		}

		var canvasCheckDuplicateNotification = function () {
			var data = {
				action: 'canvas_notification_check_duplicate',
				msg: $("#canvas_message").val(),
				data_id: $("#canvas_notification_data_id").val(),
				post_id: $("#canvas_post_id").val(),
				url: $("#canvas_url").val(),
				os: $("input[name='canvas_os']:checked").val()
			};
			var duplicate = false;
			$.ajax({
				url: ajaxurl,
				data: data,
				type: 'POST',
				async: false,
				success: function (response) {
					if ($.trim(response).length > 0) {
						duplicate = true;
					}
				}
			});
			return duplicate;
		};

		var isUrlValid = function (url) {
			return /^(https?|s?ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test(url);
		}

		var canvasValidateNotification = function () {
			var errors = [];

			var message = $.trim($("#canvas_message").val());
			if (message.length === 0) {
				errors.push('Message cannot be blank');
			}

			var attach = $("#canvas_notification_data_id").val();
			if (attach === 'custom') {
				var customPostID = $.trim($("#canvas_post_id").val());
				if (customPostID.length === 0) {
					errors.push('Custom ID cannot be blank');
				} else if (!$.isNumeric(customPostID)) {
					errors.push('Custom ID must be a number');
				}
			}
			if (attach === 'url') {
				var customUrl = $.trim($("#canvas_url").val());
				if (customUrl.length === 0) {
					errors.push('URL cannot be blank');
				} else if (!isUrlValid(customUrl)) {
					errors.push('You must enter a valid URL');
				}
			}

			if (errors.length > 0) {
				$("#error-message").html(errors.join("<br/>")).show();
				return false;
			} else {
				$("#error-message").hide();
				return true;
			}
		};

		var canvasLoadNotificationHistory = function () {
			var data = {
				action: 'canvas_notification_history',
				async: true
			};
			$("#canvas_notification_history").css("display", "none");

			$.post(ajaxurl, data, function (response) {
				//saving the result and reloading the div
				$("#canvas_notification_history").html(response).show();
			});
		};

		$("#canvas_message").on("input", function () {
			canvasLimitChars(this, 107, 'canvas_message_chars');
		});

		$.post(ajaxurl, {action: 'canvas_attachment_content', async: true}, function(response) {
			if (response.search('<option') > -1) {
				$("#canvas_notification_data_id").html(response).val('').trigger('change');
			}
		});

		$('#canvas_notification_data_id').on('change', function() {
			var value = $(this).val();
			switch (value) {
				case 'url':
					$('#canvas_post_id_block').hide();
					$('#canvas_url_block').show();
					break;
				case 'custom':
					$('#canvas_post_id_block').show();
					$('#canvas_url_block').hide();
					break;
				default:
					$('#canvas_post_id_block, #canvas_url_block').hide();
			}
		})

		$("#canvas_notification_manual_send_submit").click(function () {

			if (canvasValidateNotification()) {
				var checkDuplicate = canvasCheckDuplicateNotification();

				var cont = true;
				if (checkDuplicate) {
					cont = confirm('It seems that you have sent this exact message already, are you sure you wish to send it again?');
				}

				if (cont) {
					$("#canvas_notification_manual_send_submit").val($("#canvas_notification_manual_send_submit").data('sending'));
					$("#canvas_notification_manual_send_submit").attr("disabled", true);

					$("#canvas_notification_manual_send_submit").css("opacity", "0.5");

					var data = {
						action: 'canvas_notification_manual_send',
						msg: $("#canvas_message").val(),
						data_id: $("#canvas_notification_data_id").val(),
						post_id: $("#canvas_post_id").val(),
						url: $("#canvas_url").val(),
						os: $("input[name='canvas_os']:checked").val(),
						category_as_tag: $("input[name='category_as_tag']:checked").val() || '',
						tags_list: $("#canvas_tags_list").val(),
					};

					$.post(ajaxurl, data, function (response) {
						// update history
						$("#canvas_notification_manual_send_submit").val($("#canvas_notification_manual_send_submit").data('send'));
						$("#canvas_notification_manual_send_submit").attr("disabled", false);
						$("#canvas_notification_manual_send_submit").css("opacity", "1.0");
						if (true === response) {
							canvasLoadNotificationHistory();
							$("#success-message").show();
							setTimeout(function () {
								$("#success-message").fadeOut();
								}, 2000);
						} else {
							if (false === response) {
								response = "There was an error sending this notification";
							} else {
								response = "There was an error sending this notification:<br>" + response;
							}
							$('#error-message').html(response).show();
							setTimeout(function () {
								$("#error-message").fadeOut();
								}, 20000);
						}
					});
				}
				return true;
			} else {
				return false;
			}
		});

		$('#canvas_manual_message input:not([type="submit"]), #canvas_manual_message select').on('click.clear-error, input.clear-error, change.clear-error', function() {
			$('#error-message').hide();
		})

		canvasLoadNotificationHistory();
	}
	/* Manual notifications - end */

});