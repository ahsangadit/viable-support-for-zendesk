<?php
namespace viablecube\viasuzen\Integrations;

if ( ! defined( 'ABSPATH' ) ) exit;

class Comments {

    public function __construct() {
        // Hook for auto-conversion when comment is posted
        add_action( 'comment_post',  array( $this, 'maybe_auto_convert_comment_to_ticket'  ) , 20, 2 );

        // Hook for manual conversion from admin
        add_action( 'admin_post_viasuzen_convert_comment',  array( $this, 'handle_manual_conversion' ) );

        add_action( 'admin_notices', array( $this, 'display_zendesk_error_notice' ) );
    }

    
    /**
     * Automatically convert comment to Zendesk ticket on creation if settings allow.
     *
     * @param int $comment_ID
     * @param int $comment_approved
     * @author Ahsan
	 * @since  1.1.0
     */
    public function maybe_auto_convert_comment_to_ticket( $comment_ID, $comment_approved ) {
        $settings = get_option( 'viasuzen_comments_settings' );

        // Check if auto conversion is enabled
        if ( empty( $settings['auto_approve_comments'] ) || $settings['auto_approve_comments'] !== '1' ) {
            return;
        }

        $comment = get_comment( $comment_ID );
        if ( ! $comment ) {
            return;
        }

        $allowed_post_types = $settings['limit_post_types'] ?? [];
        $post_type = get_post_type( $comment->comment_post_ID );

        if ( ! in_array( $post_type, $allowed_post_types, true ) ) {
            return;
        }

        $this->convert_comment_to_ticket( $comment );
    }

    /**
     * Handle manual conversion triggered via admin post action.
     * @author Ahsan
	 * @since  1.1.0
     */
    public function handle_manual_conversion() {

		$nonce = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'convert_comment_to_ticket' ) ) {
            wp_die( esc_html__( 'Security check failed', 'viable-support-for-zendesk' ) );
		}
		// Validate comment ID
		if ( ! isset( $_GET['comment_id'] ) || ! is_numeric( $_GET['comment_id'] ) ) {
            wp_die( esc_html__( 'Invalid comment ID', 'viable-support-for-zendesk' ) );
		}
		
        $comment_id = intval( $_GET['comment_id'] );
        $comment    = get_comment( $comment_id );

        $this->convert_comment_to_ticket( $comment );

        wp_redirect( admin_url( 'edit-comments.php?zendesk_success=1' ) );
        exit;
    }

     /**
     * Core function to convert a comment to a Zendesk ticket.
     *
     * @param WP_Comment $comment
     * @author Ahsan
	 * @since  1.1.0
     */
    private function convert_comment_to_ticket( $comment ) {

        $viasuzen_settings = get_option( 'viasuzen_settings' );
        $viasuzen_comments_settings = get_option( 'viasuzen_comments_settings' );
        $subdomain    = $viasuzen_settings['subdomain'] ?? '';
        $token        = $viasuzen_settings['api_token'] ?? '';
        $user_email   = $viasuzen_settings[ 'email' ] ?? '';
        $template     = $viasuzen_comments_settings['ticket_subject_template'] ?? 'WP Comment';
        $include_meta = $viasuzen_comments_settings['include_comment_meta'] ?? '0';
        $tags         = $viasuzen_comments_settings['internal_tags'] ?? '';

        $url = "https://{$subdomain}/api/v2/tickets.json";

        $meta_string = '';
        if ( $include_meta === '1' ) {
            $meta_string = sprintf(
                "\n\n---\nComment ID: %d\nPost ID: %d\nUser IP: %s\n",
                $comment->comment_ID,
                $comment->comment_post_ID,
                $comment->comment_author_IP
            );
        }

        $subject = $this->parse_template_placeholders( $template, $comment );

        $ticket_data = array(
            'ticket' => array(
                'subject' => $subject,
                'comment' => array(
                    'body' => $comment->comment_content . $meta_string,
                ),
                'requester' => array(
                    'name' => $comment->comment_author,
                    'email' => $comment->comment_author_email,
                ),
                'tags' => array_filter( array_map( 'trim', explode( ',', $tags ) ) ),
            ),
        );
        
          $response = wp_remote_post( $url, array(
			'method'    => 'POST',
			'headers'   => array(
				'Authorization' => 'Basic ' . base64_encode( $user_email . '/token:' . $token ),
				'Content-Type'  => 'application/json',
			),
			'body'      => wp_json_encode( $ticket_data ),
			'data_format' => 'body',
			'timeout'   => 20,
		) );

		if ( is_wp_error( $response ) ) {
			set_transient( 'viasuzen_ticket_error', $response->get_error_message(), 30 );
			return false;
		}

		$code = wp_remote_retrieve_response_code( $response );
		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body );

		if ( $code !== 201 ) {
			set_transient( 'viasuzen_ticket_error', "HTTP Status Code: {$code}", 30 );
			return false;
		}

		$ticket_id = $data->ticket->id ?? '';
		$msg = $ticket_id
			? sprintf( 'Zendesk ticket #%d created successfully.', $ticket_id )
			: 'Zendesk ticket created successfully.';

		set_transient( 'viasuzen_ticket_success', $msg, 30 );
   		 return true;
    }

    /**
     * Add "Convert to Zendesk Ticket" link to comment row in the WordPress admin.
     *
     * This function checks if the comment-to-ticket conversion feature is enabled,
     * verifies the current user's role against allowed roles from settings,
     * and restricts availability based on the post type associated with the comment.
     *
     * @param array    $actions Array of existing row actions.
     * @param WP_Comment $comment The comment object.
     *
     * @return array Modified row actions with optional "Convert to Zendesk Ticket" link.
     * @author Ahsan
	 * @since  1.0.0
     */
    public function add_convert_link( $actions, $comment ) {
        // Get plugin settings
        $settings = get_option( 'viasuzen_comments_settings' );

        // Check if the comment-to-ticket conversion feature is enabled
        $conversion_enabled = isset( $settings['enable_comments'] ) && $settings['enable_comments'] === '1';
        
        // Check if the current user's role is allowed
        $allowed_roles = $settings['comments_role_restriction'] ?? [];
        $user = wp_get_current_user();
        $user_roles = (array) $user->roles;
        $has_role_permission = array_intersect( $user_roles, $allowed_roles );

        // Check if the comment is on an allowed post type
        $allowed_post_types = $settings['limit_post_types'] ?? [];
        $post_type = get_post_type( $comment->comment_post_ID );
        $post_type_allowed = in_array( $post_type, $allowed_post_types, true );

        // Show the "Convert to Zendesk Ticket" link only if all conditions are met
        if ( $conversion_enabled && ! empty( $has_role_permission ) && $post_type_allowed ) {
			$url = wp_nonce_url( admin_url( 'admin-post.php?action=viasuzen_convert_comment&comment_id=' . $comment->comment_ID ), 'convert_comment_to_ticket' );
            $actions['viasuzen_convert'] = '<a href="' . esc_url( $url ) . '">' . __( 'Convert to Zendesk Ticket', 'viable-support-for-zendesk' ) . '</a>';
        }

        return $actions;
    }
    
    /**
     * Displays an admin notice if a Zendesk ticket creation error occurred.
     * @author Ahsan
	 * @since  1.0.0
     */
    public function display_zendesk_error_notice() {
        // Success notice
        if ( $success = get_transient( 'viasuzen_ticket_success' ) ) {
            printf(
                '<div class="notice notice-success is-dismissible"><p>%s</p></div>',
                esc_html( $success )
            );
            delete_transient( 'viasuzen_ticket_success' );
        }

        // Error notice
        if ( $error = get_transient( 'viasuzen_ticket_error' ) ) {
            printf(
                '<div class="notice notice-error is-dismissible"><p><strong>%s</strong> %s</p></div>',
                esc_html__( 'Zendesk Ticket Creation Failed:', 'viable-support-for-zendesk' ),
                esc_html( $error )
            );
            delete_transient( 'viasuzen_ticket_error' );
        }
    }

    /**
     * Parses and replaces placeholders in the given template string with actual comment and post data.
     *
     * Supported placeholders:
     * - {post_title}      : The title of the post associated with the comment.
     * - {comment_author}  : The name of the comment author.
     * - {comment_email}   : The email address of the comment author.
     * - {comment_id}      : The ID of the comment.
     * - {post_id}         : The ID of the post the comment is attached to.
     *
     * @param string   $template The subject or body template containing placeholders.
     * @param WP_Comment $comment The comment object containing data to populate the placeholders.
     *
     * @return string The processed template string with placeholders replaced by actual values.
     * @author Ahsan
	 * @since  1.0.0
     */
    private function parse_template_placeholders( $template, $comment ) {
        // Get post title
        $post = get_post( $comment->comment_post_ID );
        $post_title = $post ? $post->post_title : '';

        // Placeholder replacements
        $placeholders = [
            '{post_title}'     => $post_title,
            '{comment_author}' => $comment->comment_author,
            '{comment_email}'  => $comment->comment_author_email,
            '{comment_id}'     => $comment->comment_ID,
            '{post_id}'        => $comment->comment_post_ID,
        ];

        // Replace placeholders in the template
        return strtr( $template, $placeholders );
    }

}