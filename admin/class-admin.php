<?php
/**
 * Admin menu handler for Zendesk Connect plugin.
 *
 * @package     viablecube\viasuzen
 * @subpackage  Admin
 * @since       1.0.0
 * @author      Ahsan Amin
 * @copyright   Copyright (c) 2025
 * @license     GPL-2.0+
 */

namespace viablecube\viasuzen\Admin;

use viablecube\viasuzen\Core\Init;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class Admin
 *
 * Handles admin menu registration and page rendering.
 *
 * @since 1.0.0
 */
class Admin {

    /**
     * Registers the main menu and submenu pages for the plugin in the WordPress admin dashboard.
     *
     * @since 1.0.0
     * @return void
     */
    public function register_menu() {
        add_menu_page(
            __( 'Viable Support', 'viable-support-for-zendesk' ),
            'Viable Support',
            'manage_options',
            'viasuzen-settings',
            array( $this, 'render_settings_page' ),
            VIASUZEN_URL . 'assets/images/menu_icon.png',
            50
        );

        if ( Init::is_authorized() ) {
            add_submenu_page(
                'viasuzen-settings',
                __( 'Form Settings', 'viable-support-for-zendesk' ),
                __( 'Form Settings', 'viable-support-for-zendesk' ),
                'manage_options',
                'viasuzen-form-settings',
                array( $this, 'render_form_settings_page' )
            );

            add_submenu_page(
                'viasuzen-settings',
                __( 'Comments Settings', 'viable-support-for-zendesk' ),
                __( 'Comments Settings', 'viable-support-for-zendesk' ),
                'manage_options',
                'viasuzen-comments-settings',
                array( $this, 'render_comments_settings_page' )
            );
        }
    }

    /**
     * Renders the main plugin settings page.
     *
     * @since 1.0.0
     * @return void
     */
    public function render_settings_page() {
        include_once VIASUZEN_DIR . 'admin/views/settings-page.php';
    }

    /**
     * Renders the form settings page for the plugin.
     *
     * @since 1.0.0
     * @return void
     */
    public function render_form_settings_page() {
        include VIASUZEN_DIR . 'admin/views/form-settings-page.php';
    }

    /**
     * Renders the comments settings page for the plugin.
     *
     * @since 1.0.0
     * @return void
     */
    public function render_comments_settings_page() {
        include VIASUZEN_DIR . 'admin/views/comments-setting-page.php';
    }
}
