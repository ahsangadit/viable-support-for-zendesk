<?php
/**
 * Admin menu handler for Viable Support For Zendesk plugin.
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
     * Constructor.
     * Initializes admin hooks.
     *
     * @since 1.0.0
     */
    public function __construct() {
        add_action( 'admin_init', array( $this, 'remove_other_plugins_notices' ), 1 );
    }

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

    /**
     * Checks if we're on one of this plugin's admin pages.
     *
     * @since 1.0.0
     * @return bool
     */
    private function is_plugin_admin_page() {
        $current_page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
        $plugin_page_slugs = array(
            'viasuzen-settings',
            'viasuzen-form-settings',
            'viasuzen-comments-settings',
        );

        return in_array( $current_page, $plugin_page_slugs, true );
    }

    /**
     * Removes admin notices from other plugins using WordPress hooks.
     *
     * @since 1.0.0
     * @return void
     */
    public function remove_other_plugins_notices() {
        if ( ! $this->is_plugin_admin_page() ) {
            return;
        }

        global $wp_filter;

        if ( ! isset( $wp_filter['admin_notices'] ) ) {
            return;
        }

        $callbacks = $wp_filter['admin_notices']->callbacks;

        if ( empty( $callbacks ) ) {
            return;
        }

        // Remove notices from other plugins
        foreach ( $callbacks as $priority => $hooks ) {
            foreach ( $hooks as $hook_key => $hook ) {
                if ( ! isset( $hook['function'] ) ) {
                    continue;
                }

                $function = $hook['function'];
                $is_plugin_notice = false;

                // Check if it's a class method
                if ( is_array( $function ) && isset( $function[0] ) ) {
                    // Check if it's an object before calling get_class()
                    if ( is_object( $function[0] ) ) {
                        $class_name = get_class( $function[0] );
                        // Check if it belongs to this plugin
                        if ( strpos( $class_name, 'viablecube\\viasuzen' ) !== false ) {
                            $is_plugin_notice = true;
                        }
                    } elseif ( is_string( $function[0] ) ) {
                        // If it's a string (class name), check directly
                        if ( strpos( $function[0], 'viablecube\\viasuzen' ) !== false || 
                             strpos( $function[0], 'viasuzen' ) !== false || 
                             strpos( $function[0], 'viablecube' ) !== false ) {
                            $is_plugin_notice = true;
                        }
                    }
                } elseif ( is_string( $function ) ) {
                    // Check if function name belongs to this plugin
                    if ( strpos( $function, 'viasuzen' ) !== false || strpos( $function, 'viablecube' ) !== false ) {
                        $is_plugin_notice = true;
                    }
                }

                // Remove if it doesn't belong to this plugin
                if ( ! $is_plugin_notice ) {
                    unset( $wp_filter['admin_notices']->callbacks[ $priority ][ $hook_key ] );
                }
            }
        }
    }
}
