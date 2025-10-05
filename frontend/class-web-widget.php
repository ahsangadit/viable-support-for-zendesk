<?php
namespace viablecube\viasuzen\Frontend;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Web_Widget
 *
 * Handles injection of the web widget script on the frontend based on settings.
 *
 * @package viablecube\viasuzen\Frontend
 */
class Web_Widget {

    /**
     * Web_Widget constructor.
     *
     * Hooks into wp_enqueue_scripts to conditionally inject the widget script.
     *
     * @return void
     */
    public function __construct() {
        add_action( 'wp_enqueue_scripts', array( $this, 'inject_widget' ) );

    }

    /**
     * Inject the widget JavaScript code inline if enabled in settings.
     *
     * Checks for 'zcw_settings' option, verifies if display is set to 'auto'
     * and widget code is not empty, then registers and enqueues a dummy script handle,
     * and adds the widget code as inline script.
     *
     * @return void
	 * @author Ahsan
	 * @since  1.0.0
     */
    public function inject_widget() {

        $settings = get_option( 'viasuzen_settings' );
        $viasuzen_authorization_status = get_option( 'viasuzen_authorization_status' );

        if (
            isset( $settings['webwidget_display'] ) &&
            $settings['webwidget_display'] === 'auto' &&
            ! empty( $settings['widget_code'] ) && $viasuzen_authorization_status == '1'
        ) {
            $js_code = $settings['widget_code'];
			
            wp_register_script(
                'zc-webwidget-inline',
                false,
                array(),
                VIASUZEN_VERSION,
                true
            );

            wp_enqueue_script( 'zc-webwidget-inline' );
            
            wp_add_inline_script( 'zc-webwidget-inline', $js_code );
        }
    }
}