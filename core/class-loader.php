<?php
namespace viablecube\viasuzen\Core;

use viablecube\viasuzen\Core\Init;
use viablecube\viasuzen\Admin\Admin;
use viablecube\viasuzen\Admin\Settings;
use viablecube\viasuzen\Admin\Form_Settings;
use viablecube\viasuzen\Admin\Comments_Settings;
use viablecube\viasuzen\Frontend\Web_Widget;
use viablecube\viasuzen\Integrations\API;
use viablecube\viasuzen\Integrations\Comments;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Loader {

    /**
     * Run the plugin's core processes.
     *
     * @return void
     * @author Ahsan
	 * @since  1.0.0
     */
    public function run(): void {
        $this->load_dependencies();
        $this->register_admin_hooks();
        $this->register_integrations();
    }

    /**
     * Load all required class files.
     *
     * @return void
     * @author Ahsan
	 * @since  1.0.0
     */
    private function load_dependencies(): void {
        // Core
        require_once VIASUZEN_DIR . 'core/class-init.php';

        // Admin
        require_once VIASUZEN_DIR . 'admin/class-admin.php';
        require_once VIASUZEN_DIR . 'admin/class-settings.php';
        require_once VIASUZEN_DIR . 'admin/class-form-settings.php';
        require_once VIASUZEN_DIR . 'admin/class-comment-settings.php';

        // Frontend
        require_once VIASUZEN_DIR . 'frontend/class-web-widget.php';

        // Integrations
        require_once VIASUZEN_DIR . 'integrations/class-api.php';
        require_once VIASUZEN_DIR . 'integrations/class-comments.php';
    }

    /**
     * Register admin dashboard hooks.
     *
     * @return void
     * @author Ahsan
	 * @since  1.0.0
     */
    private function register_admin_hooks(): void {
        $admin = new Admin();
        add_action( 'admin_menu', array(  $admin, 'register_menu' ) );

        $settings = new Settings();
        add_action( 'admin_init', array(  $settings, 'register_settings' ) );

        if ( Init::is_authorized() ) {
            $form_settings = new Form_Settings();
            add_action( 'admin_init', array(  $form_settings, 'viasuzen_register_settings' ) );

            $comment_settings = new Comments_Settings();
            add_action( 'admin_init', array(  $comment_settings, 'register_settings' ) );
        }

        // Always load frontend widget and API hooks in admin
        new Web_Widget();
        new API();
    }

    /**
     * Register Zendesk comment integration hooks.
     *
     * @return void
     * @author Ahsan
	 * @since  1.0.0
     */
    private function register_integrations(): void {
        if ( Init::is_authorized() ) {
            $comments = new Comments();
            add_filter( 'comment_row_actions', array( $comments, 'add_convert_link' ), 10, 2 );
            add_action( 'admin_post_viasuzen_convert_comment', array( $comments, 'convert_comment_to_ticket' ) );
        }
    }
}
