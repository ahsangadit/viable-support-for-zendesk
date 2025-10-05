<?php
/**
 * Plugin Name: Viable Support For Zendesk
 * Plugin URI:  https://viablecube.com/viable-support-for-zendesk
 * Description: Seamlessly integrate Zendesk support features with WordPress – including dynamic contact forms, comment ticketing, and Web Widget. (Formerly Zendesk Connect)
 * Version:     1.0.0
 * Author:      Ahsan Amin
 * Author URI:  https://viablecube.com
 * License:     GPL2+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: viable-support-for-zendesk
 * Domain Path: /languages/
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

// Define plugin constants
define( 'VIASUZEN_VERSION', '1.0' );
define( 'VIASUZEN_DIR', plugin_dir_path( __FILE__ ) );
define( 'VIASUZEN_URL', plugin_dir_url( __FILE__ ) );
define( 'VIASUZEN_FILE', __FILE__ );

// Load core loader
require_once VIASUZEN_DIR . 'core/class-loader.php';

// Initialize plugin
function viasuzen_run_plugin() {
    $loader = new \viablecube\viasuzen\Core\Loader();
    $loader->run();
}
add_action( 'plugins_loaded', 'viasuzen_run_plugin' );