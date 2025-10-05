<?php
namespace viablecube\viasuzen\Admin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Comments_Settings
 *
 * Handles comment-related admin settings for Zendesk Connect WP plugin.
 *
 * @package viablecube\viasuzen\Admin
 */
class Comments_Settings {

    /**
     * Option name used for storing comment plugin settings.
     *
     * @var string
     */
	private $option_name = 'viasuzen_comments_settings';

    /**
     * Constructor.
     * Adds WordPress hooks for comment settings.
     */
    public function __construct() {

    }

	/**
	 * Register the comment settings, sections, and fields.
	 *
	 * @return void
	 * @author Ahsan Amin
	 * @version 1.0.0
	 */
	public function register_settings() {

		register_setting(
			'viasuzen_comments_settings_group',
			'viasuzen_comments_settings',
			array( $this, 'sanitize_settings' )
		);

		add_settings_section(
			'viasuzen_comments_main_section',
			'',
			array( $this, 'section_callback' ),
			'viasuzen_comments_settings'
		);

		$fields = array(
			array(
				'id'       => 'enable_comments',
				'title'    => __( 'Enable Comments Sync', 'viable-support-for-zendesk' ),
				'callback' => array( $this, 'field_enable_comments' ),
				'page'     => 'viasuzen_comments_settings',
				'section'  => 'viasuzen_comments_main_section',
			),
			array(
				'id'       => 'auto_approve_comments',
				'title'    => __( 'Auto Approve Comments', 'viable-support-for-zendesk' ),
				'callback' => array( $this, 'field_auto_approve_comments' ),
				'page'     => 'viasuzen_comments_settings',
				'section'  => 'viasuzen_comments_main_section',
			),
			array(
				'id'       => 'comments_role_restriction',
				'title'    => __( 'Restrict Comments by User Role', 'viable-support-for-zendesk' ),
				'callback' => array( $this, 'field_comments_role_restriction' ),
				'page'     => 'viasuzen_comments_settings',
				'section'  => 'viasuzen_comments_main_section',
			),
			array(
				'id'       => 'limit_post_types',
				'title'    => __( 'Allowed Post Types', 'viable-support-for-zendesk' ),
				'callback' => array( $this, 'field_limit_post_types' ),
				'page'     => 'viasuzen_comments_settings',
				'section'  => 'viasuzen_comments_main_section',
			),
			array(
				'id'       => 'ticket_subject_template',
				'title'    => __( 'Zendesk Ticket Subject', 'viable-support-for-zendesk' ),
				'callback' => array( $this, 'field_ticket_subject_template' ),
				'page'     => 'viasuzen_comments_settings',
				'section'  => 'viasuzen_comments_main_section',
			),
			array(
				'id'       => 'include_comment_meta',
				   'title'    => __( 'Include Comment Meta', 'viable-support-for-zendesk' ),
				'callback' => array( $this, 'field_include_comment_meta' ),
				'page'     => 'viasuzen_comments_settings',
				'section'  => 'viasuzen_comments_main_section',
			),
			array(
				'id'       => 'internal_tags',
				'title'    => __( 'Internal Tags', 'viable-support-for-zendesk' ),
				'callback' => array( $this, 'field_internal_tags' ),
				'page'     => 'viasuzen_comments_settings',
				'section'  => 'viasuzen_comments_main_section',
			),
		);

		foreach ( $fields as $field ) {
			add_settings_field(
				$field['id'],
				$field['title'],
				$field['callback'],
				$field['page'],
				$field['section']
			);
		}
	}

	 /**
	 * Render the section description.
	 *
	 * @return void
	 * @author Ahsan Amin
	 * @version 1.0.0
	 */
	public function section_callback() {
		echo '<div class="zc-form-settings-section-wrapper">';

			echo '<div class="zc-form-description">';
				   echo '<h2>' . esc_html__( 'Comment Settings', 'viable-support-for-zendesk' ) . '</h2>';
				   echo '<p>' . esc_html__( 'Manage how comments work across your site.', 'viable-support-for-zendesk' ) . '</p>';
			echo '</div>';

			echo '<div class="zc-form-submit-button">';
				submit_button();
			echo '</div>';

		echo '</div>';
	}


   /**
	 * Sanitize and validate comment settings input.
	 *
	 * @param array $input Input values from settings form.
	 * @return array Sanitized settings.
	 *
	 * @author Ahsan Amin
	 * @version 1.0.0
	 */
	public function sanitize_settings( $input ) {

		$output = array();

		// ✅ Sanitize "Enable Comments" checkbox.
		$output['enable_comments'] = (
			isset( $input['enable_comments'] ) && $input['enable_comments'] === '1'
		) ? '1' : '0';

		// ✅ Sanitize "Auto Approve Comments" checkbox.
		$output['auto_approve_comments'] = (
			isset( $input['auto_approve_comments'] ) && $input['auto_approve_comments'] === '1'
		) ? '1' : '0';

		// ✅ Sanitize "Restrict by User Role" multi-select.
		if ( isset( $input['comments_role_restriction'] ) && is_array( $input['comments_role_restriction'] ) ) {
			$output['comments_role_restriction'] = array_map( 'sanitize_text_field', $input['comments_role_restriction'] );
		} else {
			$output['comments_role_restriction'] = array();
		}

		// ✅ Sanitize "Limit by Post Types" checkboxes.
		if ( isset( $input['limit_post_types'] ) && is_array( $input['limit_post_types'] ) ) {
			$output['limit_post_types'] = array_map( 'sanitize_text_field', $input['limit_post_types'] );
		} else {
			$output['limit_post_types'] = array();
		}

		// ✅ Sanitize subject template field (string).
		$output['ticket_subject_template'] = isset( $input['ticket_subject_template'] )
			? sanitize_text_field( $input['ticket_subject_template'] )
			: '';

		// ✅ Sanitize "Include Comment Meta" checkbox.
		$output['include_comment_meta'] = (
			isset( $input['include_comment_meta'] ) && $input['include_comment_meta'] === '1'
		) ? '1' : '0';

		// ✅ Sanitize internal tags (comma-separated).
		$output['internal_tags'] = isset( $input['internal_tags'] )
			? sanitize_text_field( $input['internal_tags'] )
			: '';

		return $output;
	}


	  /**
	 * Render the "Enable Comments Sync" checkbox field.
	 *
	 * Displays a toggle switch to enable or disable syncing WordPress comments with Zendesk tickets.
	 *
	 * @author Ahsan Amin
	 * @version 1.0.0
	 */
	public function field_enable_comments() {

		$settings = get_option( $this->option_name );
		$checked  = ( isset( $settings['enable_comments'] ) && $settings['enable_comments'] === '1' ) ? 'checked' : '';

		echo '<div class="zc-toggle-switch-comment-setting">';

			echo '<label for="enable_comments" class="zc-toggle-label">';
			echo esc_html__( 'Sync WordPress comments with Zendesk tickets.', 'viable-support-for-zendesk' );
			echo '</label>';

			echo '<label class="zc-toggle-switch">';
				printf(
					'<input type="checkbox" id="enable_comments" name="%1$s[enable_comments]" value="1" %2$s />',
					esc_attr( $this->option_name ),
					esc_attr( $checked )
				);
				echo '<span class="zc-slider"></span>';
			echo '</label>';

		echo '</div>';
	}
	
	/**
	 * Render the "Auto Approve Comments" checkbox field.
	 *
	 * @return void
	 * @author Ahsan Amin
	 * @version 1.0.0
	 */
	public function field_auto_approve_comments() {
		$settings = get_option( $this->option_name );
		$checked = ( isset( $settings['auto_approve_comments'] ) && $settings['auto_approve_comments'] === '1' ) ? 'checked' : '';

		echo '<div class="zc-toggle-switch-comment-setting">';

			echo '<label for="auto_approve_comments" class="zc-toggle-label">';
		echo esc_html__( 'Create a Zendesk ticket for each new comment.', 'viable-support-for-zendesk' );
			echo '</label>';

			echo '<label class="zc-toggle-switch">';
				printf(
					'<input type="checkbox" id="auto_approve_comments" name="%1$s[auto_approve_comments]" value="1" %2$s />',
					esc_attr( $this->option_name ),
					esc_attr( $checked )
				);
				echo '<span class="zc-slider"></span>';
			echo '</label>';

		echo '</div>';
	}


	/**
	 * Render the "Restrict Comments to User Roles" multi-select field.
	 *
	 * @author Ahsan Amin
	 * @version 1.0.0
	 */
	public function field_comments_role_restriction() {

		$settings = get_option( $this->option_name );
		$selected_roles = isset( $settings['comments_role_restriction'] ) ? (array) $settings['comments_role_restriction'] : array();

		$roles = array_keys( get_editable_roles() );

		echo '<div class="zc-field-wrapper" style="margin-top: 20px;" >';
		echo '<label for="comments_role_restriction" style="font-weight: 600; display: block; margin-bottom: 8px; font-size: 14px;">';
		echo '</label>';
		echo '<select  id="comments_role_restriction" name="' . esc_attr( $this->option_name ) . '[comments_role_restriction][]" style="margin-top: 12px; width: 100%; max-width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px;" >';

		foreach ( $roles as $role ) {
			printf(
				'<option value="%1$s" %2$s>%1$s</option>',
				esc_attr( $role ),
				selected( in_array( $role, $selected_roles, true ), true, false )
			);
		}

		echo '</select>';

			echo '<p class="description zc-role-restrict-description">';
				echo '<span class="zc-info-icon">ℹ️</span>';
				echo esc_html__( 'Only selected roles can manually convert comments into Zendesk tickets.', 'viable-support-for-zendesk' );
			echo '</p>';

		echo '</div>';
	}

	
	/**
	 * Render checkboxes for limiting ticket conversion by post type.
	 *
	 * Displays a list of public post types with checkboxes,
	 * allowing users to select which post types should be eligible
	 * for comment-to-ticket conversion.
	 *
	 * @author Ahsan Amin
	 * @version 1.0.0
	 */
	public function field_limit_post_types() {

		// Retrieve plugin settings from the database
		$options = get_option( $this->option_name );

		// Get the array of post types already selected
		$selected_post_types = isset( $options['limit_post_types'] ) ? (array) $options['limit_post_types'] : array();

		// Fetch all public post types (posts, pages, etc.)
		$post_types = get_post_types( array( 'public' => true ), 'objects' );

		// Start wrapper
		echo '<div class="zc-post-types-wrapper-option">';

		foreach ( $post_types as $post_type => $details ) {

			$checked = in_array( $post_type, $selected_post_types, true ) ? 'checked' : '';

			echo '<div class="zc-post-type-item">';
				echo '<label>';

					printf(
						'<input type="checkbox" name="%1$s[limit_post_types][]" value="%2$s" %3$s /> %4$s',
						esc_attr( $this->option_name ),
						esc_attr( $post_type ),
						esc_attr( $checked ),
						esc_html( $details->labels->name )
					);

				echo '</label>';
			echo '</div>';
		}

		echo '</div>';
	}

	/**
	 * Render a text input field for the custom Zendesk ticket subject template.
	 *
	 * Allows the user to define a custom subject line for tickets generated from comments.
	 * Supports dynamic placeholders like {post_title}, {comment_author}, etc.
	 *
	 * @author Ahsan Amin
	 * @version 1.0.0
	 */
	public function field_ticket_subject_template() {

		// Get the plugin options from the database
		$options = get_option( $this->option_name );

		// Get the saved subject template or default to empty
		$value = isset( $options['ticket_subject_template'] ) ? esc_attr( $options['ticket_subject_template'] ) : '';

		echo '<div class="zc-ticket-subject-template-wrapper">';

			// Input field
			printf(
				'<input type="text" name="%1$s[ticket_subject_template]" value="%2$s" class="regular-text zc-subject-template-input" />',
				esc_attr( $this->option_name ),
				esc_attr( $value )
			);

			// Description
			echo '<p class="description zc-subject-template-description">';
				esc_html_e( 'Dynamic subject field supported tags include', 'viable-support-for-zendesk' );
				echo '<br>';
				echo '<code>{post_title}</code> <code>{comment_author}</code> <code>{comment_email}</code> <code>{comment_id}</code> <code>{post_id}</code>';
			echo '</p>';

		echo '</div>';
	}

	
	/**
	 * Render a text input field for adding internal tags to Zendesk tickets.
	 *
	 * Accepts a comma-separated list of internal tags that will be added to every generated ticket.
	 *
	 * @author Ahsan Amin
	 * @version 1.0.0
	 */
	public function field_internal_tags() {

		// Get the plugin options from the database
		$options = get_option( $this->option_name );

		// Get the saved tags or default to empty
		$value = isset( $options['internal_tags'] ) ? esc_attr( $options['internal_tags'] ) : '';

		echo '<div class="zc-form-group zc-field-wrapper zc-internal-tags-field">';

			// Label
			echo '<div class="zc-label-wrapper">';
				echo '<label for="zc_internal_tags" class="zc-label">';
				esc_html_e( 'Ticket Tags', 'viable-support-for-zendesk' );
				echo '</label>';
			echo '</div>';

			// Input
			echo '<div class="zc-input-wrapper">';
				printf(
					'<input type="text" id="zc_internal_tags" name="%1$s[internal_tags]" value="%2$s" class="regular-text zc-input zc-tags-input" />',
					esc_attr( $this->option_name ),
					esc_attr( $value )
				);
			echo '</div>';

			// Description
			echo '<p class="description zc-description">';
				   esc_html_e( 'Comma-separated list of internal tags to include in the ticket.', 'viable-support-for-zendesk' );
			echo '</p>';

		echo '</div>';
	}

	/**
	 * Render a checkbox for including comment meta data in Zendesk tickets.
	 *
	 * When enabled, extra metadata (e.g. IP address, user agent) will be added to the ticket body.
	 *
	 * @author Ahsan Amin
	 * @version 1.0.0
	 */
	public function field_include_comment_meta() {

		// Get the plugin options from the database
		$options = get_option( $this->option_name );

		// Check whether the option is enabled
		$checked = ( isset( $options['include_comment_meta'] ) && $options['include_comment_meta'] === '1' ) ? 'checked' : '';

		echo '<div class="zc-toggle-switch-comment-setting">';

			// Description Label
			echo '<label for="include_comment_meta" class="zc-comment-toggle-label">';
			   echo esc_html__( 'Include metadata like Comment ID, Post ID, and User IP in the Zendesk ticket.', 'viable-support-for-zendesk' );
			echo '</label>';

			// Toggle Switch
			echo '<label class="zc-toggle-switch">';
			printf(
				'<input type="checkbox" id="include_comment_meta" name="%1$s[include_comment_meta]" value="1" %2$s />',
				esc_attr( $this->option_name ),
				esc_attr( $checked )
			);
			echo '<span class="zc-slider"></span>';
			echo '</label>';

		echo '</div>';
	}

}