var TwitterField = $('#form_twitter').val();
var LinkedinField = $('#form_linkedin').val();
var FacebookField = $('#form_facebook').val();
var TwitterOk = false;
var LinkedinOk = false;
var FacebookOk = false;

/**
 * Converts social URLs to username and checks if such Facebook profile exists
 * If it exists fills in other fields if they are empty
 */
function CheckFieldsChanges() {
	/* processing twitter field*/
	var newField = $('#form_twitter').val();
	var ok = false;

	if (newField && TwitterField != newField) {
		/* remove domain part and parameters from pasted URL */
		TwitterField = newField;
		TwitterField = TwitterField.replace(/(\w{1,5}\:\/\/)?((\w*\.){1,3}\w*\/)?(\#\!\/)?/,'');
		TwitterField = TwitterField.replace(/(\/|\?).*/,'');
		$('#form_twitter').val(TwitterField);

		/* check if Twitter account exists */
		ok = $.ajax({
			url: window.BaseURL + 'i/check_social/twitter',
			type: 'POST',
			data: {twitter: TwitterField },
			success: function(response) {
				if (response == '1') {
					$('#twitter-ok').show();
					$('#twitter-wrong').hide();
					TwitterOk = true;
				} else {
					$('#twitter-ok').hide();
					$('#twitter-wrong').show();
					TwitterOk = false;
				}
				$('#twitter-ajax').hide();
			}
		});

		if (ok) {
			$('#twitter-ok').hide();
			$('#twitter-wrong').hide();
			$('#twitter-ajax').show();
		}

	} else if ( ! newField) {
		$('#twitter-ok').hide();
		$('#twitter-wrong').hide();
		$('#twitter-ajax').hide();
		TwitterOk = false;
	}

	/* processing linkedin field */
	newField = $('#form_linkedin').val();

	if (newField && LinkedinField != newField) {
		/* remove domain part and parameters from pasted URL */
		LinkedinField = newField;
		LinkedinField = LinkedinField.replace(/(\w{1,5}\:\/\/)?((\w*\.){1,3}\w*\/)?/,'');
		LinkedinField = LinkedinField.replace(/(\?.*)/,'');
		$('#form_linkedin').val(LinkedinField);

		/* check if LinkedIn account exists */
		ok = $.ajax({
			url: window.BaseURL + 'i/check_social/linkedin',
			type: 'POST',
			data: {linkedin: LinkedinField },
			success: function(response) {
				if (response == '1') {
					$('#linkedin-ok').show();
					$('#linkedin-wrong').hide();
					LinkedinOk = true;
				} else {
					$('#linkedin-ok').hide();
					$('#linkedin-wrong').show();
					LinkedinOk = false;
				}
				$('#linkedin-ajax').hide();
			}
		});

		if (ok) {
			$('#linkedin-ok').hide();
			$('#linkedin-wrong').hide();
			$('#linkedin-ajax').show();
		}

	} else if ( ! newField) {
		$('#linkedin-ok').hide();
		$('#linkedin-wrong').hide();
		$('#linkedin-ajax').hide();
		LinkedinOk = false;
	}

	/* processing Facebook field */
	newField = $('#form_facebook').val();

	if (newField && FacebookField != newField) {
		FacebookField = newField;
		FacebookField = FacebookField.replace(/(\w{1,5}\:\/\/)?((\w*\.){1,3}\w*\/)?/,'');
		FacebookField = FacebookField.replace(/(\/|\?).*/,'');
		$('#form_facebook').val(FacebookField);

		/* check if Facebook account exists */
		ok = $.ajax({
			url: window.BaseURL + 'i/check_social/facebook',
			type: 'POST',
			data: {facebook: FacebookField },
			success: function(response) {
				if (response == '1') {
					$('#facebook-ok').show();
					$('#facebook-wrong').hide();
					FacebookOk = true;
				} else {
					$('#facebook-ok').hide();
					$('#facebook-wrong').show();
					FacebookOk = false;
				}
				$('#facebook-ajax').hide();
			}
		});

		if (ok) {
			$('#facebook-ok').hide();
			$('#facebook-wrong').hide();
			$('#facebook-ajax').show();
		}

	} else if ( ! newField) {
		$('#facebook-ok').hide();
		$('#facebook-wrong').hide();
		$('#facebook-ajax').hide();
		FacebookOk = false;
	}


	/* Disable/enable "add person" button */
	if (LinkedinOk || TwitterOk || FacebookOk) {
		$('#add_button').removeClass('disabled')
		$('#add_button').removeAttr('disabled');
	} else {
		$('#add_button').addClass('disabled')
		$('#add_button').attr('disabled', 'disabled')
	}
}

