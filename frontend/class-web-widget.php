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
            empty( $settings['subdomain'] ) ||
            empty( $settings['webwidget_display'] ) ||
            $settings['webwidget_display'] !== 'auto' ||
            $viasuzen_authorization_status !== '1'
        ) {
            return;
        }

        // Generate widget code dynamically from subdomain
        $js_code = $this->generate_widget_code( $settings['subdomain'] );
        
        if ( empty( $js_code ) ) {
            return;
        }
			
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

    /**
     * Generate Zendesk widget code from subdomain.
     *
     * @param string $subdomain The Zendesk subdomain.
     * @return string Generated widget code.
     * @author Ahsan
     * @since 1.1.0
     */
    private function generate_widget_code( $subdomain ) {
        if ( empty( $subdomain ) ) {
            return '';
        }

        $sanitized_subdomain = sanitize_text_field( $subdomain );
        $subdomain_escaped = addslashes( $sanitized_subdomain );
        
        return 'window.zEmbed||function(e,t){var n,o,d,i,s,a=[],r=document.createElement("iframe");window.zEmbed=function(){a.push(arguments)},window.zE=window.zE||window.zEmbed,r.src="javascript:false",r.title="",r.role="presentation",(r.frameElement||r).style.cssText="display: none",d=document.getElementsByTagName("script"),d=d[d.length-1],d.parentNode.insertBefore(r,d),i=r.contentWindow,s=i.document;try{o=s}catch(c){n=document.domain,r.src=\'javascript:var d=document.open();d.domain="' . $subdomain_escaped . '";void(0);\',o=s}o.open()._l=function(){var o=this.createElement("script");n&&(this.domain=n),o.id="js-iframe-async",o.src=e,this.t=+new Date,this.zendeskHost=t,this.zEQueue=a,this.body.appendChild(o)},o.write(\'<body onload="document._l();">\'),o.close()}("https://assets.zendesk.com/embeddable_framework/main.js","' . $subdomain_escaped . '");';
    }
}