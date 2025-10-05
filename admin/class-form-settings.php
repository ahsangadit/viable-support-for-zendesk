<?php
namespace viablecube\viasuzen\Admin;
use viablecube\viasuzen\Integrations\API;

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

class Form_Settings
{

    private $option_name = 'viasuzen_form_settings';

    public function __construct()
    {
	
		// Hook to initialize settings page fields
        add_action( 'admin_init', array( $this, 'viasuzen_register_settings' ) );
        add_shortcode( 'viasuzen_ticket_form', array( $this, 'viasuzen_render_ticket_form_shortcode' ) );
        add_action( 'init', array( $this, 'viasuzen_handle_form_submission' ) );
        add_action( 'wp_ajax_viasuzen_sync_custom_fields', array( $this, 'viasuzen_handle_sync_custom_fields_ajax' ) );
        add_action( 'wp_ajax_nopriv_viasuzen_sync_custom_fields', array( $this, 'viasuzen_handle_sync_custom_fields_ajax' ) );
        add_action( 'wp_ajax_viasuzen_save_selected_fields', array( $this, 'viasuzen_save_selected_fields_callback' ) );
        add_action( 'wp_ajax_nopriv_viasuzen_save_selected_fields', array( $this, 'viasuzen_save_selected_fields_callback' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'viasuzen_enqueue_frontend_scripts' ) );
    }

	/**
	 * Enqueue frontend stylesheet for the Zendesk Connect plugin.
	 *
	 * @author Ahsan
	 * @since  1.0.0
	 *
	 * @return void
	 */
    public function viasuzen_enqueue_frontend_scripts() {
        wp_enqueue_style(
            'viasuzen-front-css',
            VIASUZEN_URL . 'assets/css/style.css',
            array(),
            VIASUZEN_VERSION
        );
    }
   
	/**
     * Register settings, sections, and fields for the form settings page.
     *
     * @author Ahsan Amin
     * @version 1.0.0
     */
    public function viasuzen_register_settings() {

        register_setting(
            'viasuzen_form_settings_group',
            $this->option_name,
            array( $this, 'sanitize_settings' )
        );

        // Add main section
        add_settings_section(
            'viasuzen_form_main_section',
            '',
            function () {
                echo '<div class="viasuzen-form-settings-section-wrapper">';
                echo '<div class="viasuzen-form-description">';
                echo '<h2>' . esc_html__( 'Form Settings', 'viable-support-for-zendesk' ) . '</h2>';
                echo '<p>' . esc_html__( 'Customize your Viable Support For Zendesk ticket submission form labels below.', 'viable-support-for-zendesk' ) . '</p>';
                echo '</div>';
                echo '<div class="viasuzen-form-submit-button">';
                submit_button();
                echo '</div>';
                echo '</div>';
            },
            'viasuzen-form-settings'
        );

        // Display shortcode and sync button field
        add_settings_field(
            'viasuzen_shortcode_sync_block',
            '',
            array( $this, 'field_shortcode_display_callback' ),
            'viasuzen-form-settings',
            'viasuzen_form_main_section'
        );

        // Standard form fields
        $fields = array(
            'form_title'          => __( 'Form Title', 'viable-support-for-zendesk' ),
            'field_name_label'    => __( 'Name Field Label', 'viable-support-for-zendesk' ),
            'field_email_label'   => __( 'Email Field Label', 'viable-support-for-zendesk' ),
            'field_subject_label' => __( 'Subject Field Label', 'viable-support-for-zendesk' ),
            'field_message_label' => __( 'Message Field Label', 'viable-support-for-zendesk' ),
        );

        foreach ( $fields as $key => $label ) {
            add_settings_field(
                $key,
                $label,
                array( $this, 'field_text_callback' ),
                'viasuzen-form-settings',
                'viasuzen_form_main_section',
                array(
                    'label_for'   => $key,
                    'option_key'  => $key,
                    'placeholder' => $label,
                )
            );
        }

        // Render Zendesk custom fields if any exist
        $custom_fields = get_option( 'viasuzen_custom_fields', array() );

        if ( ! empty( $custom_fields ) && is_array( $custom_fields ) ) {
            foreach ( $custom_fields as $field ) {
                if ( empty( $field['id'] ) || empty( $field['title'] ) || empty( $field['type'] ) ) {
                    continue;
                }

                $field_key_label   = 'custom_field_' . sanitize_key( $field['id'] ) . '_label';
                $field_key_enabled = 'custom_field_' . sanitize_key( $field['id'] ) . '_enabled';

                add_settings_field(
                    'custom_field_group_' . sanitize_key( $field['id'] ),
                    esc_html( $field['title'] . ' Field Label' ),
                    function () use ( $field_key_label, $field_key_enabled, $field ) {
                        $options          = get_option( $this->option_name );
                        $label_value      = isset( $options[ $field_key_label ] ) ? esc_attr( $options[ $field_key_label ] ) : '';
                        $checkbox_checked = isset( $options[ $field_key_enabled ] ) ? checked( 1, $options[ $field_key_enabled ], false ) : '';

                        echo '<div class="zc-form-row" style="display: flex; align-items: center; gap: 20px;">';

                        echo '<input type="text" name="' . esc_attr( $this->option_name ) . '[' . esc_attr( $field_key_label ) . ']" value="' . esc_attr( $label_value ) . '" placeholder="' . esc_attr( $field['title'] ) . '" class="regular-text zc-input" />';

                        echo '<div class="zc-toggle-switch-wrapper">';
                        echo '<label class="zc-toggle-switch">';
                        echo '<input type="checkbox" name="' . esc_attr( $this->option_name ) . '[' . esc_attr( $field_key_enabled ) . ']" value="1" ' . esc_attr( $checkbox_checked ) . ' />';
                        echo '<span class="zc-slider"></span>';
                        echo '</label>';
                        echo '</div>';

                        echo '</div>';
                    },
                    'viasuzen-form-settings',
                    'viasuzen_form_main_section'
                );
            }
        }
    }
	
    /**
     * Sanitize input fields.
     *
     * @author Ahsan Amin
     * @version 1.0.0
     *
     * @param array $input Raw input from settings form.
     * @return array Sanitized settings array.
     */
    public function sanitize_settings( $input ) {

        $sanitized = array();

        $fields = array(
            'form_title',
            'field_name_label',
            'field_email_label',
            'field_subject_label',
            'field_message_label',
        );

        foreach ( $fields as $field ) {
            if ( isset( $input[ $field ] ) ) {
                $sanitized[ $field ] = sanitize_text_field( $input[ $field ] );
            }
        }

        // Handle dynamic custom fields
        $custom_fields = get_option( 'viasuzen_custom_fields', array() );

        if ( ! empty( $custom_fields ) && is_array( $custom_fields ) ) {
            foreach ( $custom_fields as $field ) {
                if ( empty( $field['id'] ) ) {
                    continue;
                }

                $key_label   = 'custom_field_' . sanitize_key( $field['id'] ) . '_label';
                $key_enabled = 'custom_field_' . sanitize_key( $field['id'] ) . '_enabled';

                if ( isset( $input[ $key_label ] ) ) {
                    $sanitized[ $key_label ] = sanitize_text_field( $input[ $key_label ] );
                }

                $sanitized[ $key_enabled ] = isset( $input[ $key_enabled ] ) ? 1 : 0;
            }
        }

        return $sanitized;
    }


    /**
     * Callback for text input fields
     * @author Ahsan Amin
     * @version 1.0.0
     */
    public function field_text_callback($args)
    {
        $options = get_option($this->option_name);
        $value = isset($options[$args['option_key']]) ? esc_attr($options[$args['option_key']]) : '';

        printf(
            '<input type="text" id="%1$s" name="%2$s[%1$s]" value="%3$s" placeholder="%4$s" class="regular-text" />',
            esc_attr($args['label_for']),
            esc_attr($this->option_name),
            esc_attr($value),
            esc_attr($args['placeholder'])
        );
    }
 	
	/**
     * Callback to display Checkbox field.
     * @author Ahsan Amin
     * @version 1.0.0
     */
    public function field_checkbox_callback($args)
    {
        $options = get_option($this->option_name);
        $checked = isset($options[$args['option_key']]) ? (bool) $options[$args['option_key']] : false;

        printf(
            '<input type="checkbox" id="%1$s" name="%2$s[%1$s]" value="1" %3$s />',
            esc_attr($args['label_for']),
            esc_attr($this->option_name),
            checked($checked, true, false)
        );
    }

      /**
     * Callback to display shortcode field.
     *
     * @author Ahsan Amin
     * @version 1.0.0
     */
    public function field_shortcode_display_callback() {

        $shortcode = '[viasuzen_ticket_form]';

        echo '<div class="zc-shortcode-sync-wrapper">';

        echo '<div class="zc-shortcode-field">';
        echo '<div class="zc-shortcode-field-title">';
        echo '<h2>' . esc_html__( 'Form Shortcode', 'viable-support-for-zendesk' ) . '</h2>';

        echo '<div class="zc-shortcode-input-container">';
        printf(
            '<input type="text" id="zc-shortcode" readonly value="%s" onclick="this.select();" class="regular-text zc-copy-input" />',
            esc_attr( $shortcode )
        );

        echo '<button type="button" class="viasuzen-copy-btn" title="' . esc_attr__( 'Copy Shortcode', 'viable-support-for-zendesk' ) . '">';
        echo '<i class="dashicons dashicons-admin-page"></i>';
        echo '</button>';
		
		echo '<span id="zc-copy-feedback" style="color: black; display: none;">Copied!</span>';
        echo '</div>';

        echo '<p class="description">';
        $alt = get_option( 'viasuzen_logo_alt', 'Viable Support Logo' );
        // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage, WordPress.Security.EscapeOutput.OutputNotEscaped -- Plugin asset image with proper escaping
        echo '<img src="' . esc_url( VIASUZEN_URL . 'assets/images/icon.svg' ) . '" alt="' . esc_attr( $alt ) . '" />';


        echo esc_html__( 'Copy and paste this shortcode into any page or post to display your Viable Support For Zendesk ticket submission form.', 'viable-support-for-zendesk' );
        echo '</p>';
        echo '</div>';

        echo '<div class="zc-sync-button-field">';
         echo '<p class="description">' . esc_html__( 'Sync the latest custom fields from your Zendesk account.', 'viable-support-for-zendesk' ) . '</p>';

        echo '<div id="zcw-preloader" class="zc-preloader">';
        echo '<div class="zc-spinner"></div>';
        echo '</div>';

        echo '<button type="button" class="button zcw-sync-button">' . esc_html__( 'Synchronize Zendesk Custom Fields', 'viable-support-for-zendesk' ) . '</button>';
        echo '</div>';

        echo '</div>';
        echo '</div>';
    }

    /**
     * Shortcode handler - outputs the ticket form.
     *
     * @author Ahsan Amin
     * @version 1.0.0
     * @return string HTML form output
     */
    public function viasuzen_render_ticket_form_shortcode() {

        ob_start();

        $options         = get_option( $this->option_name );
        $zendesk_fields  = get_option( 'viasuzen_custom_fields' );

        $title           = ! empty( $options['form_title'] ) ? esc_html( $options['form_title'] ) : __( 'Submit a Support Ticket', 'viable-support-for-zendesk' );
        $name_label      = ! empty( $options['field_name_label'] ) ? esc_html( $options['field_name_label'] ) : __( 'Your Name', 'viable-support-for-zendesk' );
        $email_label     = ! empty( $options['field_email_label'] ) ? esc_html( $options['field_email_label'] ) : __( 'Your Email', 'viable-support-for-zendesk' );
        $subject_label   = ! empty( $options['field_subject_label'] ) ? esc_html( $options['field_subject_label'] ) : __( 'Subject', 'viable-support-for-zendesk' );
        $message_label   = ! empty( $options['field_message_label'] ) ? esc_html( $options['field_message_label'] ) : __( 'Your Message', 'viable-support-for-zendesk' );

        ?>

    <form method="post" class="zcw_main_form_frontend" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">

            <h2><?php echo esc_html( $title ); ?></h2>

            <p>
                <label for="zcw_name"><?php echo esc_html( $name_label ); ?> <span style="color: red;">*</span></label><br />
                <input type="text" id="zcw_name" name="zcw_name" required />
            </p>

            <p>
                <label for="zcw_email"><?php echo esc_html( $email_label ); ?> <span style="color: red;">*</span></label><br />
                <input type="email" id="zcw_email" name="zcw_email" required />
            </p>

            <p>
                <label for="zcw_subject"><?php echo esc_html( $subject_label ); ?> <span style="color: red;">*</span></label><br />
                <input type="text" id="zcw_subject" name="zcw_subject" required />
            </p>

            <?php
            // Render dynamic custom fields (before message field)
            if ( is_array( $zendesk_fields ) ) {
                foreach ( $zendesk_fields as $field ) {
                    $field_id     = $field['id'];
                    $label_key    = 'custom_field_' . $field_id . '_label';
                    $enabled_key  = 'custom_field_' . $field_id . '_enabled';

                    $enabled      = ! empty( $options[ $enabled_key ] );
                    $label        = ! empty( $options[ $label_key ] ) ? esc_html( $options[ $label_key ] ) : ( isset( $field['title'] ) ? esc_html( $field['title'] ) : '' );

                    if ( $enabled && $label ) {
                        $input_name = 'custom_field_' . $field_id;
                        ?>
                        <p>
                            <label for="<?php echo esc_attr( $input_name ); ?>"><?php echo esc_html( $label ); ?></label><br />
                            <input type="text" id="<?php echo esc_attr( $input_name ); ?>" name="<?php echo esc_attr( $input_name ); ?>" class="regular-text" />
                        </p>
                        <?php
                    }
                }
            }
            ?>

            <!-- Message field now comes after custom fields -->
            <p>
                <label for="zcw_message"><?php echo esc_html( $message_label ); ?> <span style="color: red;">*</span></label><br />
                <textarea id="zcw_message" name="zcw_message" rows="5" required></textarea>
            </p>

            <?php wp_nonce_field( 'viasuzen_ticket_form_submit', 'viasuzen_ticket_form_nonce' ); ?>
            <input type="hidden" name="action" value="viasuzen_submit_ticket" />

            <p>
                <input type="submit" value="<?php esc_attr_e( 'Submit Ticket', 'viable-support-for-zendesk' ); ?>" class="button button-primary" />
            </p>
        </form>

        <?php
        // Display success or error messages
        if (
            isset( $_GET['viasuzen_form_success'], $_GET['_viasuzen_nonce'] ) &&
            wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_viasuzen_nonce'] ) ), 'viasuzen_form_message' )
        ) {
            echo '<div class="notice notice-success">' . esc_html__( 'Ticket submitted successfully!', 'viable-support-for-zendesk' ) . '</div>';
        } elseif (
            isset( $_GET['viasuzen_form_error'], $_GET['_viasuzen_nonce'] ) &&
            wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_viasuzen_nonce'] ) ), 'viasuzen_form_message' )
        ) {
            echo '<div class="notice notice-error">' . esc_html__( 'There was an error submitting your ticket. Please try again.', 'viable-support-for-zendesk' ) . '</div>';
        }


        return ob_get_clean();
    }

    /**
     * Handle the form submission and create Zendesk ticket.
     *
     * @author Ahsan Amin
     * @version 1.0.0
     * @return void
     */
    public function viasuzen_handle_form_submission() {
        if ( ! isset( $_POST['action'] ) || $_POST['action'] !== 'viasuzen_submit_ticket' ) {
            return;
        }

        if ( ! isset( $_POST['viasuzen_ticket_form_nonce'] ) ) {
            wp_die( esc_html__( 'Missing nonce.', 'viable-support-for-zendesk' ) );
        }

        $raw_nonce = sanitize_text_field( wp_unslash( $_POST['viasuzen_ticket_form_nonce'] ) );

        if ( ! wp_verify_nonce( $raw_nonce, 'viasuzen_ticket_form_submit' ) ) {
            wp_die( esc_html__( 'Nonce verification failed.', 'viable-support-for-zendesk' ) );
        }

        $options         = get_option( $this->option_name );
        $zendesk_fields  = get_option( 'viasuzen_custom_fields' );

        // Sanitize basic form fields.
        $name     = isset( $_POST['zcw_name'] ) ? sanitize_text_field( wp_unslash( $_POST['zcw_name'] ) ) : '';
        $email    = isset( $_POST['zcw_email'] ) ? sanitize_email( wp_unslash( $_POST['zcw_email'] ) ) : '';
        $subject  = isset( $_POST['zcw_subject'] ) ? sanitize_text_field( wp_unslash( $_POST['zcw_subject'] ) ) : '';
        $message  = isset( $_POST['zcw_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['zcw_message'] ) ) : '';

        // Basic validation.
        if ( empty( $name ) || empty( $email ) || empty( $subject ) || empty( $message ) || ! is_email( $email ) ) {
            wp_redirect( add_query_arg( 'viasuzen_form_error', 1, wp_get_referer() ? wp_get_referer() : home_url() ) );
            exit;
        }

        // Prepare custom fields.
        $custom_fields_data = array();

        if ( is_array( $zendesk_fields ) ) {
            foreach ( $zendesk_fields as $field ) {
                if ( empty( $field['id'] ) ) {
                    continue;
                }

                $field_id     = $field['id'];
                $enabled_key  = 'custom_field_' . $field_id . '_enabled';

                if ( ! empty( $options[ $enabled_key ] ) ) {
                    $input_name = 'custom_field_' . $field_id;

                    if ( isset( $_POST[ $input_name ] ) ) {
                        $value = sanitize_text_field( wp_unslash( $_POST[ $input_name ] ) );

                        $custom_fields_data[] = array(
                            'id'    => $field_id,
                            'value' => $value,
                        );
                    }
                }
            }
        }

        // Submit the ticket via API.
        $created = $this->create_zendesk_ticket( $name, $email, $subject, $message, $custom_fields_data );

        $redirect_args = array(
            $created ? 'viasuzen_form_success' : 'viasuzen_form_error' => 1,
            '_viasuzen_nonce' => wp_create_nonce( 'viasuzen_form_message' ),
        );

        $redirect_url = add_query_arg( $redirect_args, wp_get_referer() ? wp_get_referer() : home_url() );

        wp_redirect( $redirect_url );
        exit;
    }

    /**
     * Create a Zendesk ticket using the Zendesk API.
     *
     * @author Ahsan Amin
     * @version 1.0.0
     *
     * @param string $name           Name of the requester.
     * @param string $email          Email of the requester.
     * @param string $subject        Ticket subject.
     * @param string $message        Ticket message.
     * @param array  $custom_fields  Optional array of custom fields (each with 'id' and 'value').
     *
     * @return bool  True on success, false on failure.
     */
    private function create_zendesk_ticket( $name, $email, $subject, $message, $custom_fields = array() ) {

        // Get API credentials from plugin settings
        $settings = get_option( 'viasuzen_settings' );

        if (
            empty( $settings['subdomain'] ) ||
            empty( $settings['api_token'] ) ||
            empty( $settings['email'] )
        ) {
            return false;
        }

        $subdomain   = sanitize_text_field( $settings['subdomain'] );
        $api_token   = sanitize_text_field( $settings['api_token'] );
        $user_email  = sanitize_email( $settings['email'] );

        $url = "https://{$subdomain}/api/v2/tickets.json";

        $ticket_data = array(
            'ticket' => array(
                'subject'       => $subject,
                'comment'       => array(
                    'body' => $message,
                ),
                'requester'     => array(
                    'name'  => $name,
                    'email' => $email,
                ),
                'custom_fields' => is_array( $custom_fields ) ? $custom_fields : array(),
            ),
        );

        $args = array(
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode( $user_email . '/token:' . $api_token ),
                'Content-Type'  => 'application/json',
            ),
            'body'    => wp_json_encode( $ticket_data ),
            'method'  => 'POST',
            'timeout' => 20,
        );

        $response = wp_remote_post( $url, $args );

        if ( is_wp_error( $response ) ) {
            return false;
        }

        $response_code = wp_remote_retrieve_response_code( $response );

        return ( $response_code === 201 );
    }

    /**
     * Handle the synchronization of Zendesk custom fields via AJAX.
     *
     * @author Ahsan Amin
     * @version 1.0.0
     */
    public function viasuzen_handle_sync_custom_fields_ajax() {

        // Verify the AJAX nonce for security.
        check_ajax_referer( 'viasuzen_sync_fields', 'nonce' );

        // Instantiate the API class and fetch custom fields.
        $api = new API();
        $custom_fields = $api->get_custom_fields();

        // Check if fields are valid.
        if ( ! empty( $custom_fields ) && is_array( $custom_fields ) ) {
            wp_send_json_success( array(
                'fields' => $custom_fields,
            ) );
        } else {
            wp_send_json_error( 'Failed to sync custom fields.' );
        }
    }

    /**
     * Handle AJAX request to save selected custom fields.
     * Verifies nonce, sanitizes input, and updates the WordPress option.
     *
     * @author Ahsan Amin
     * @version 1.0.0
     */
    public function viasuzen_save_selected_fields_callback() {

        // Verify AJAX nonce for security.
        check_ajax_referer( 'viasuzen_sync_fields', 'nonce' );

        // Validate and sanitize posted data.
        if ( ! isset( $_POST['selected_fields'] ) || ! is_array( $_POST['selected_fields'] ) ) {
            wp_send_json_error( array(
                'message' => 'Invalid field data.',
            ) );
        }

        $raw_fields       = wp_unslash( $_POST['selected_fields'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        $sanitized_fields = array();

        foreach ( $raw_fields as $field ) {
            $sanitized_fields[] = array(
                'id'    => sanitize_text_field( isset( $field['id'] ) ? $field['id'] : '' ),
                'title' => sanitize_text_field( isset( $field['title'] ) ? $field['title'] : '' ),
                'type'  => sanitize_text_field( isset( $field['type'] ) ? $field['type'] : '' ),
            );
        }

        // Save sanitized fields to the database.
        update_option( 'viasuzen_custom_fields', $sanitized_fields );

        // Return success response.
        wp_send_json_success( array(
            'message' => 'Fields saved successfully.',
        ) );
    }
}