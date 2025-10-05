/**
 * VIASUZEN Plugin AJAX Actions
 * Handles: 
 *  - Removing API authorization
 *  - Triggering API authorization
 * Uses WordPress localized variables: viasuzen_ajax.ajax_url and nonces
 */

 jQuery(document).ready(function(jQuery) {

	/**
	 * Remove Zendesk Authorization via AJAX
	 * -------------------------------------
	 * When the "Remove Authorization" button (#zcw-remove-auth-btn) is clicked:
	 * - Prevents default form behavior.
	 * - Disables the button and shows a "Removing..." text to indicate action in progress.
	 * - Sends an AJAX POST request to trigger `viasuzen_remove_authorization` action in WordPress.
	 * - On success: reloads the page.
	 * - On failure: displays an error message in #zcw-remove-auth-status and re-enables the button.
	 * 
	 * Requires:
	 * - `viasuzen_ajax.ajax_url` — the admin-ajax.php URL.
	 * - `viasuzen_ajax.nonce` — nonce for security validation.
	 */

	 jQuery('#zcw-remove-auth-btn').on('click', function(e) {
		 e.preventDefault();
		 var $btn = jQuery(this);
		 var $status = jQuery('#zcw-remove-auth-status');

		$btn.prop('disabled', true).text('Removing...');
		$status.text('');

			 jQuery.ajax({
				 url: viasuzen_ajax.ajax_url,
				 type: 'POST',
				 data: {
					 action: 'viasuzen_remove_authorization',
					 nonce: viasuzen_ajax.nonce
				 },
				 success: function(response) {
					 if (response.success) {
						 location.reload();
					 } else {
						 const toast = new Toast({
							 message: 'Remove Authorization.',
							 type: 'danger',
							 gravity: 'top',
							 position: 'right'
						 });
						 setTimeout(() => toast.hide(), 2000);
					 }
					 $btn.prop('disabled', false).text('Disconnect Zendesk');
				 },
				 error: function() {
					 $status.text('AJAX error.').css('color', 'red');
					 $btn.prop('disabled', false).text('Disconnect Zendesk');
				 }
			 });
	 });


  /**
	 * Authorize Zendesk API via AJAX
	 * ------------------------------
	 * Triggered when the "Authorize API" button (#zcw-authorize-btn) is clicked:
	 * 
	 * - Prevents default button behavior.
	 * - Disables the button and updates text to "Authorizing...".
	 * - Collects user input values for subdomain, email, and API token.
	 * - Sends an AJAX POST request to the server with the values and nonce.
	 * - On success: reloads the page if authorization is successful.
	 * - On failure: shows an error message from response in #zcw-authorize-status.
	 * - Re-enables the button after the AJAX call finishes.
	 * 
	 * Requirements:
	 * - `viasuzen_ajax.ajax_url`: URL to WordPress AJAX handler (admin-ajax.php).
	 * - `viasuzen_ajax.auth_nonce`: Nonce for verifying the request.
	 */

	jQuery('#zcw-authorize-btn').on('click', function(e) {
		e.preventDefault();

		var $btn = jQuery(this);
		var $status = jQuery('#zcw-authorize-status');
		var $removeBtn = jQuery('#zcw-remove-auth-btn');

		// Disable the button and show loading state
		$btn.prop('disabled', true).text('Authorizing...');
		$status.text('');
		var subdomain = jQuery('input[name="viasuzen_settings[subdomain]"]').val();
		var email     = jQuery('input[name="viasuzen_settings[email]"]').val();
		var api_token = jQuery('input[name="viasuzen_settings[api_token]"]').val();

		// Send AJAX request to authorize API
		jQuery.ajax({
			url: viasuzen_ajax.ajax_url,
			nonce: viasuzen_ajax.auth_nonce,
			type: 'POST',
			data: {
				action: 'viasuzen_authorize_api',
				nonce: viasuzen_ajax.auth_nonce,
				subdomain: subdomain,
				email: email,
				api_token: api_token
			},
			success: function(response) {
				if (response.success) {
					location.reload();
				} else {
					const toast = new Toast({
						message: response.data.message,
						type: 'danger',
						gravity: 'top',
						position: 'right'
					});
					setTimeout(() => toast.hide(), 2000);
				}
				$btn.prop('disabled', false).text('Connect to Zendesk');
			},
			error: function() {
				const toast = new Toast({
						message: 'AJAX error.',
						type: 'danger',
						gravity: 'top',
						position: 'right'
					});
				setTimeout(() => toast.hide(), 2000);
			   $btn.prop('disabled', false).text('Disconnect Zendesk');
			}
		});
	});


	/**
	 * Handle Custom Fields Sync Button Click
	 * --------------------------------------
	 * Triggered when the user clicks the "Synchronize Zendesk Custom Fields" button.
	 *
	 * - Disables the button and shows a "Syncing..." loading message.
	 * - Sends an AJAX POST request to fetch custom fields from Zendesk.
	 * - If the response is successful and contains a list of fields, it opens a popup UI (via showFieldsPopup).
	 * - If there's an error, it shows an alert with a relevant message.
	 * - After AJAX completes (success or failure), the button is re-enabled with its original label.
	 *
	 * - `viasuzen_ajax.ajax_url`: WordPress AJAX handler URL.
	 * - `viasuzen_ajax.aync_nonce`: Nonce for verifying the custom fields request.
	 * - `viasuzen_ajax.aync_nonce`: Nonce for verifying the custom fields request.
	 */

	jQuery('.zcw-sync-button').on('click', function(e) {
		e.preventDefault();

		const $button = jQuery(this);

		// Disable button and show loading state
		$button.prop('disabled', true).text('Syncing...');

		// Send AJAX request to sync custom fields
		jQuery.ajax({
			url: viasuzen_ajax.ajax_url,
			method: 'POST',
			data: {
				action: 'viasuzen_sync_custom_fields',
				nonce: viasuzen_ajax.aync_nonce
			},
			success: function(response) {
				// If response is successful and contains an array of fields
				if (response.success && Array.isArray(response.data.fields)) {
					// Launch popup for selecting fields
					showFieldsPopup(response.data.fields);
				} else {
					const errorMessage = response.data?.message || 'Unknown error';
					const toast = new Toast({
						message: errorMessage,
						type: 'danger',
						gravity: 'top',
						position: 'right'
					});
					setTimeout(() => toast.hide(), 2000);
				}
			},
			error: function() {
				const toast = new Toast({
						message: 'AJAX error.',
						type: 'danger',
						gravity: 'top',
						position: 'right'
					});
				setTimeout(() => toast.hide(), 2000);
			},
			complete: function() {
				// Re-enable button and restore label
				$button.prop('disabled', false).text('Sync Zendesk Custom Fields');
			}
		});
	});


	 /**
	 * Display a popup modal showing Zendesk custom fields with checkboxes.
	 * Allows the user to select which text fields to include in the shortcode.
	 * Sends selected fields back to the server for storage.
	 *
	 * @param {Array} fields - Array of Zendesk field objects (should contain `id` and `title`).
	 */
    function showFieldsPopup(fields) {

        if (fields.length === 0) {
            const toast = new Toast({
				message: 'No text fields found to display.',
				type: 'error',
				gravity: 'top',
				position: 'right'
			});

			// Manually hide after 2 seconds
			setTimeout(() => {
				toast.hide();
			}, 2000);

			return;
        }

        // Create modal HTML
        const $modal = jQuery(`
            <div id="zcw-fields-modal" style="position:fixed;top:0;left:0;width:100%;height:100%;
            background:rgba(0,0,0,0.6);z-index:10000;display:flex;align-items:center;justify-content:center;">
                <div style="background:#fff;padding:20px;border-radius:8px;width:400px;max-height:80%;overflow:auto;position:relative;">
                    <h3>Choose Fields to Display via Shortcode</h3>
                    <form id="zcw-fields-form">
                        ${fields.map(field => `
                            <div>
                                <label>
                                    <input type="checkbox" name="fields[]" value="${field.id}" />
                                    ${field.title}
                                </label>
                            </div>
                        `).join('')}
                        <button type="submit" style="margin-top:15px;">Save Selection</button>
                    </form>
                    <button id="zcw-close-modal" style="position:absolute;top:10px;right:10px;">X</button>
                </div>
            </div>
        `);

        jQuery('body').append($modal);

        // Handle modal close
        jQuery('#zcw-close-modal').on('click', function() {
            jQuery('#zcw-fields-modal').remove();
        });

        // Handle field selection save
        jQuery('#zcw-fields-form').on('submit', function(e) {
            e.preventDefault();

            const selectedIds = jQuery(this).serializeArray().map(f => f.value);

            if (selectedIds.length === 0) {
                const toast = new Toast({
					message: 'Please select at least one field.',
					type: 'danger',
					gravity: 'top',
					position: 'right'
				});
				setTimeout(() => toast.hide(), 2000);
				jQuery('#zcw-fields-modal').remove();
				return;
            }
			
			// Match selected field objects by ID
			const selectedFields = fields.filter(field =>
				selectedIds.includes(field.id.toString())
			);

            // Send selected field IDs to backend for storage
			jQuery.ajax({
				url: viasuzen_ajax.ajax_url,
				method: 'POST',
				data: {
					action: 'viasuzen_save_selected_fields',
					nonce: viasuzen_ajax.aync_nonce,
					selected_fields: selectedFields
				},
                success: function(res) {
					const toast = new Toast({
						message: res.success ? 'Fields saved successfully!' : 'Failed to save fields.',
						type: res.success ? 'success' : 'danger',
						gravity: 'top',
						position: 'right'
					});
					setTimeout(() => toast.hide(), 2000);

					if (res.success) {
						jQuery('#zcw-fields-modal').remove();
						location.reload();
					}
                },
                error: function() {
            	 const toast = new Toast({
                    message: 'AJAX error while saving fields.',
                    type: 'danger',
                    gravity: 'top',
                    position: 'right'
                });
                setTimeout(() => toast.hide(), 2000);
                }
            });
        });
    }

	/**
	 * Copy Shortcode to Clipboard
	 * ---------------------------
	 * This function handles copying the shortcode from the input field (#zc-shortcode)
	 * to the user's clipboard when the copy button (.zc-copy-btn) is clicked.
	 *
	 * - It selects the input field content.
	 * - Uses the Clipboard API to copy the text.
	 * - Shows the "Copied!" feedback (#zc-copy-feedback) briefly (2 seconds).
	 */

	function copyZCShortcode(event) {
		event.preventDefault();

		const $input = jQuery('#zc-shortcode');
		const $feedback = jQuery('#zc-copy-feedback');

		if (!$input.length || !$feedback.length) return;

		$input.trigger('select');

		const textToCopy = $input.val();

		if (!navigator.clipboard) return;

		navigator.clipboard.writeText(textToCopy).then(() => {
			$feedback.stop(true, true).fadeIn(100);

			setTimeout(() => {
				$feedback.fadeOut(300);
			}, 2000);
		});
	}

	// Attach the copy function to the copy button
	jQuery(document).on('click', '.viasuzen-copy-btn', copyZCShortcode);

		
	/**
	 * Handle Sync Button Click to Show Preloader
	 * ------------------------------------------
	 * This code listens for clicks on the "Sync" button (.zcw-sync-button).
	 * When clicked:
	 * - It prevents the default action.
	 * - Shows the preloader element (#zcw-preloader) with "flex" styling.
	 * - Hides the preloader after 2 seconds using a timeout.
	 */

	const $syncBtn = jQuery(".viasuzen-sync-button");
	const $preloader = jQuery("#zcw-preloader");

	if ($syncBtn.length && $preloader.length) {
		$syncBtn.on("click", function (e) {
			e.preventDefault();
			$preloader.css("display", "flex");

			setTimeout(function () {
				$preloader.css("display", "none");
			}, 2000);
		});
	}

});

	 
 
 
