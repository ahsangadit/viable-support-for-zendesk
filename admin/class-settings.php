<?php
namespace viablecube\viasuzen\Admin;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Settings
 *
 * Handles the admin settings for Zendesk Connect WP plugin.
 *
 * @package viablecube\viasuzen\Admin
 */
class Settings
{

    /**
     * Option name used for storing plugin settings.
     *
     * @var string
     */
	private $option_name = 'viasuzen_settings';

    /**
     * Constructor.
     * Adds WordPress hooks for admin settings and AJAX handlers.
     */
    public function __construct()
    {
		add_action( 'admin_post_viasuzen_remove_authorization', array( $this, 'viasuzen_remove_authorization' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'viasuzen_enqueue_admin_scripts' ) );
		add_action( 'wp_ajax_viasuzen_authorize_api', array( $this, 'viasuzen_authorize_api' ) );
		add_action( 'wp_ajax_viasuzen_remove_authorization', array( $this, 'viasuzen_remove_authorization' ) );
    }

    /**
     * Enqueue admin scripts and localize AJAX data.
     *
     * @return void
     * @author Ahsan
	 * @since  1.0.0
     */
	public function viasuzen_enqueue_admin_scripts()
    {
		wp_register_script(
			'viasuzen-admin-js',
			VIASUZEN_URL . 'assets/js/admin.js',
			array('jquery'),
			VIASUZEN_VERSION,
			true
		);
		wp_enqueue_script('viasuzen-admin-js');

		wp_register_script(
			'viasuzen-toast-js',
			VIASUZEN_URL . 'assets/js/Toast.js',
			array('jquery'),
			VIASUZEN_VERSION,
			true
		);
		wp_enqueue_script('viasuzen-toast-js');

		wp_register_style(
			'viasuzen-admin-css',
			VIASUZEN_URL . 'assets/css/admin.css',
			array(),
			VIASUZEN_VERSION
		);
		wp_enqueue_style('viasuzen-admin-css');

		wp_register_style(
			'viasuzen-toast-css',
			VIASUZEN_URL . 'assets/css/Toast.css',
			array(),
			VIASUZEN_VERSION
		);
		wp_enqueue_style('viasuzen-toast-css');

		wp_localize_script('viasuzen-admin-js', 'viasuzen_ajax', array(
			'ajax_url' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('viasuzen_remove_auth_nonce'),
			'auth_nonce' => wp_create_nonce('viasuzen_auth_nonce'),
			'aync_nonce' => wp_create_nonce('viasuzen_sync_fields')
		));
    }

	/**
	 * Register plugin settings, sections, and fields.
	 *
	 * Handles registration of all Zendesk Connect plugin options in the WordPress Settings API.
	 * Dynamically renders fields based on authorization status.
	 *
	 * @return void
	 * @author Ahsan
	 * @since  1.1.0
	 */
	public function register_settings() {
		// Register main option with sanitization callback
		register_setting(
			'viasuzen_settings_group',
			$this->option_name,
			array( $this, 'sanitize_settings' )
		);

		// Add Zendesk API section
		add_settings_section(
			'viasuzen_section_api',
			__( 'Zendesk API Settings', 'viable-support-for-zendesk' ),
			null,
			'zc-settings'
		);

		// Define core API fields
		$api_fields = array(
			'subdomain'  => array( __( 'Zendesk Subdomain', 'viable-support-for-zendesk' ), 'field_subdomain' ),
			'email'      => array( __( 'Zendesk Email', 'viable-support-for-zendesk' ), 'field_email' ),
			'api_token'  => array( __( 'Zendesk API Token', 'viable-support-for-zendesk' ), 'field_api_token' ),
		);

		// Register core API fields
		foreach ( $api_fields as $key => $field ) {
			add_settings_field(
				$key,
				$field[0],
				array( $this, $field[1] ),
				'zc-settings',
				'viasuzen_section_api'
			);
		}

		// Check authorization status
		$is_authorized = get_option( 'viasuzen_authorization_status' ) === '1';

		// Register either "Authorize" or "Remove Authorization" button
		// Register either "Authorize" or "Remove Authorization" button
		add_settings_field(
			$is_authorized ? 'remove_authorization' : 'authorize_api',
			$is_authorized ? __( 'Test', 'viable-support-for-zendesk' ) : "Test",
			$is_authorized ? array( $this, 'field_remove_authorization' ) : array( $this, 'field_authorize_button' ),
			'zc-settings',
			'viasuzen_section_api',
			array(
				'class'     => 'zcw-authorize-label'
			)
		);

		// If authorized, register additional widget settings
		if ( $is_authorized ) {
			add_settings_section(
				'viasuzen_section_widget',
				__( 'Widget Configuration', 'viable-support-for-zendesk' ),
				null,
				'zc-settings'
			);

			$widget_fields = array(
				'webwidget_display' => array( __( 'Widget Visibility', 'viable-support-for-zendesk' ), 'field_webwidget_display' ),
				'widget_code'       => array( __( 'Widget Embed Code', 'viable-support-for-zendesk' ), 'field_widget_code' ),
			);

			foreach ( $widget_fields as $key => $field ) {
				add_settings_field(
					$key,
					$field[0],
					array( $this, $field[1] ),
					'zc-settings',
					'viasuzen_section_widget'
				);
			}
		}
	}

    /**
     * Sanitize and validate plugin settings input.
     *
     * @param array $input Input values from settings form.
     * @return array Sanitized settings.
     * @author Ahsan
	 * @since  1.1.0
     */
    public function sanitize_settings($input)
    {
        return array(
            'subdomain' => sanitize_text_field( isset( $input['subdomain']) ? $input['subdomain'] : ''),
            'api_token' => sanitize_text_field( isset( $input['api_token']) ? $input['api_token'] : ''),
            'email' => sanitize_email( isset( $input['email']) ? $input['email'] : ''),
            'widget_code' => isset( $input['widget_code']) ? $input['widget_code'] : '',
            'webwidget_display' => in_array( isset( $input['webwidget_display']) ? $input['webwidget_display'] : 'auto', array('none', 'auto'), true)
                ? $input['webwidget_display']
                : 'auto',
        );
    }

    /**
     * Test Zendesk API authorization with provided credentials.
     *
     * @param string $subdomain Zendesk subdomain.
     * @param string $email     Email address.
     * @param string $api_token API token.
     * @return bool True if authorized, false otherwise.
     * @author Ahsan
	 * @since  1.1.0
     */
    private function test_zendesk_api($subdomain, $email, $api_token)
    {
        $url = "https://{$subdomain}/api/v2/users/search.json?query=email:" . urlencode( $email );
        $auth_string = base64_encode( "{$email}/token:{$api_token}" );

        $response = wp_remote_get( $url, array(
            'headers' => array(
                'Authorization' => "Basic {$auth_string}",
                'Accept' => 'application/json',
            ),
            'timeout' => 10,
        ));

        if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
            return false;
        }

        $data = json_decode( wp_remote_retrieve_body( $response ), true );

        return !empty( $data['users'][0]['email'] );
    }

    /**
     * Render Zendesk subdomain input field.
     *
     * @return void
     * @author Ahsan
	 * @since  1.1.0
     */
    public function field_subdomain()
    {
        $settings = get_option( $this->option_name );
        printf(
            '<div class="zc-field-wrapper"><input type="text" name="%1$s[subdomain]" value="%2$s" class="regular-text" placeholder="support-example.zendesk.com"></div>',
            esc_attr( $this->option_name ),
            esc_attr( isset( $settings['subdomain'] ) ? $settings['subdomain'] : '' )
        );
    }


    /**
     * Render email input field.
     *
     * @return void
     * @author Ahsan
	 * @since  1.1.0
     */
    public function field_email()
    {
        $settings = get_option( $this->option_name );
        printf(
            '<input type="email" name="%1$s[email]" value="%2$s" class="regular-text" placeholder="Enter your Zendesk email address">',
            esc_attr( $this->option_name ),
            esc_attr( isset( $settings['email'] ) ? $settings['email'] : '' )
        );
    }

    /**
     * Render API token input field.
     *
     * @return void
     * @author Ahsan
	 * @since  1.1.0
     */
    public function field_api_token()
    {
        $settings = get_option( $this->option_name );
        $token = isset( $settings['api_token'] ) ? esc_attr( $settings['api_token'] ) : '';

        printf(
            '<input type="text" name="%1$s[api_token]" data-token="%2$s" value="%3$s" class="regular-text" placeholder="Enter your Zendesk API token" autocomplete="off">',
            esc_attr( $this->option_name ),
            esc_attr( $token ),
            esc_attr( $token)
        );
    }

    /**
     * Render the authorize API button field.
     *
     * @return void
     * @author Ahsan
	 * @since  1.1.0
     */
    public function field_authorize_button()
    {
        printf(
            '<div id="zcw-authorize-wrapper">
                <button type="button" id="zcw-authorize-btn" class="button button-primary">%s</button>
                <span id="zcw-authorize-status" style="margin-left: 10px;"></span>
            </div>',
			esc_html__( 'Connect to Zendesk', 'viable-support-for-zendesk' )
        );
    }

    /**
     * Render the remove authorization button field.
     *
     * @return void
     * @author Ahsan
	 * @since  1.1.0
     */
    public function field_remove_authorization()
    {
        printf(
            '<button id="zcw-remove-auth-btn" class="button" style="background-color: #dc3232; color: #fff;">%s</button>
             <span id="zcw-remove-auth-status" style="margin-left: 10px;"></span>',
			esc_html__( 'Disconnect Zendesk', 'viable-support-for-zendesk' )
        );
    }

	/**
	 * AJAX callback to authorize Zendesk API.
	 *
	 * @return void
	 * @author Ahsan
	 * @since  1.1.0
	 */
	public function viasuzen_authorize_api() {
		check_ajax_referer( 'viasuzen_auth_nonce', 'nonce' );

		$subdomain  = sanitize_text_field( wp_unslash( $_POST['subdomain'] ?? '' ) );
		$email      = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
		$api_token  = sanitize_text_field( wp_unslash( $_POST['api_token'] ?? '' ) );

		if ( empty( $subdomain ) || empty( $email ) || empty( $api_token ) ) {
			wp_send_json_error( array( 'message' => 'All fields are required.' ) );
		}

		$success = $this->test_zendesk_api( $subdomain, $email, $api_token );

		if ( $success ) {
			
			$widget_code = 'window.zEmbed||function(e,t){var n,o,d,i,s,a=[],r=document.createElement("iframe");window.zEmbed=function(){a.push(arguments)},window.zE=window.zE||window.zEmbed,r.src="javascript:false",r.title="",r.role="presentation",(r.frameElement||r).style.cssText="display: none",d=document.getElementsByTagName("script"),d=d[d.length-1],d.parentNode.insertBefore(r,d),i=r.contentWindow,s=i.document;try{o=s}catch(c){n=document.domain,r.src=\'javascript:var d=document.open();d.domain="' . $subdomain . '";void(0);\',o=s}o.open()._l=function(){var o=this.createElement("script");n&&(this.domain=n),o.id="js-iframe-async",o.src=e,this.t=+new Date,this.zendeskHost=t,this.zEQueue=a,this.body.appendChild(o)},o.write(\'<body onload="document._l();">\'),o.close()}("https://assets.zendesk.com/embeddable_framework/main.js","' . $subdomain . '");';
			$settings = array(
				'subdomain'          => $subdomain,
				'email'              => $email,
				'api_token'          => $api_token,
				'widget_code'        => $widget_code,
				'webwidget_display'  => 'auto',
			);

			update_option( 'viasuzen_authorization_status', 1 );
			update_option( 'viasuzen_settings', $settings );

			wp_send_json_success( array( 'message' => 'API authorized successfully.' ) );
		}

		wp_send_json_error( array( 'message' => 'API authorization failed.' ) );
	}

	
	/**
	 * AJAX callback to remove Zendesk API authorization.
	 *
	 * Resets the authorization status and optionally clears related settings.
	 *
	 * @author Ahsan
	 * @since  1.1.0
	 *
	 * @return void
	 */
	public function viasuzen_remove_authorization() {
		check_ajax_referer( 'viasuzen_remove_auth_nonce', 'nonce' );

		// Mark as unauthorized
		update_option( 'viasuzen_authorization_status', 0 );

		// Optional: clear stored Viable Support For Zendesk credentials/settings
		$settings = get_option( 'viasuzen_settings', array() );

		unset( $settings['subdomain'] );
		unset( $settings['email'] );
		unset( $settings['api_token'] );
		unset( $settings['widget_code'] );
		unset( $settings['webwidget_display'] );

		update_option( 'viasuzen_settings', $settings );

		wp_send_json_success( array( 'message' => 'Authorization removed successfully.' ) );
	}


	/**
	 * Handle removal of Zendesk API authorization via admin_post hook.
	 *
	 * Triggered by a POST request from a form submission in the WordPress admin area.
	 * Verifies nonce, updates the authorization status, and redirects with a success message.
	 *
	 * @author Ahsan
	 * @since  1.1.0
	 *
	 * @return void
	 */
	public function handle_remove_authorization() {
		// Validate nonce.
		check_admin_referer( 'viasuzen_remove_auth_nonce' );

		// Mark as unauthorized.
		update_option( 'viasuzen_authorization_status', 0 );

		// Redirect with success message.
		wp_redirect( esc_url_raw( admin_url( 'options-general.php?page=zc-settings&message=authorization_removed' ) ) );
		exit;
	}

	/**
	 * Render the "Web Widget Display" select field in the settings form.
	 *
	 * Provides options to control whether the Zendesk Web Widget is shown automatically or not.
	 *
	 * @author Ahsan
	 * @since  1.1.0
	 *
	 * @return void
	 */
	public function field_webwidget_display() {
		$settings = get_option( $this->option_name );
		$current  = isset( $settings['webwidget_display'] ) ? $settings['webwidget_display'] : 'auto';

		$options = array(
			'none' => __( 'Hide widget', 'viable-support-for-zendesk' ),
			'auto' => __( 'Show Automatically', 'viable-support-for-zendesk' ),
		);

		echo '<label class="zendesk-connect-admin-wrap-main_section">';
		echo '<span>' . esc_html__( 'Select Web Widget Display Option:', 'viable-support-for-zendesk' ) . '</span>';
		echo '<div class="zendesk-connect-admin-wrap-main-option">';
		echo '<select name="' . esc_attr( $this->option_name ) . '[webwidget_display]">';

		foreach ( $options as $value => $label ) {
			printf(
				'<option value="%s" %s>%s</option>',
				esc_attr( $value ),
				selected( $current, $value, false ),
				esc_html( $label )
			);
		}

		echo '</select></div></label>';
	}

	/**
	 * Render the "Widget Code" textarea field in the settings form.
	 *
	 * Displays the Zendesk Web Widget code, typically auto-generated after successful API authorization.
	 * The field is read-only to prevent accidental edits.
	 *
	 * @author Ahsan
	 * @since  1.1.0
	 *
	 * @return void
	 */
	public function field_widget_code() {
		$settings     = get_option( $this->option_name );
		$widget_code  = isset( $settings['widget_code'] ) ? $settings['widget_code'] : '';

		printf(
			   '<textarea id="viasuzen_widget_code" name="%1$s[widget_code]" rows="16" cols="50" class="large-text code" readonly placeholder="%2$s">%3$s</textarea>',
			   esc_attr( $this->option_name ),
			   esc_attr__( 'Paste your Viable Support For Zendesk widget code here', 'viable-support-for-zendesk' ),
			   esc_textarea( $widget_code )
		);

	echo '<p class="description">' . esc_html__( 'This code is generated automatically after successful authorization and should not be edited manually.', 'viable-support-for-zendesk' ) . '</p>';
	}


}