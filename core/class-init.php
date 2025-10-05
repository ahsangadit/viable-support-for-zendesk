<?php
namespace viablecube\viasuzen\Core;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class Init
 *
 * Core initializer for the Zendesk Connect plugin.
 * Responsible for loading translations and providing plugin-level utilities.
 *
 * @package viablecube\viasuzen\Core
 * @author  Ahsan
 * @since   1.0.0
 */
class Init {

    /**
     * Init constructor.
     *
     * Registers core plugin actions.
     *
     * Hooks:
     * - plugins_loaded — Triggers loading of the plugin's textdomain.
    * - viasuzen_load_textdomain — Custom action after textdomain is loaded.
     *
     * @author Ahsan
     * @since  1.0.0
     */
    public function __construct() {
    }

    /**
     * Check if Zendesk API authorization has been completed.
     *
     * Used throughout the plugin to determine if authenticated actions can be performed.
     *
     * @author Ahsan
     * @since  1.0.0
     *
     * @return bool True if authorized, false otherwise.
     */
    public static function is_authorized() {
        return (bool) get_option( 'viasuzen_authorization_status', false );
    }

}
