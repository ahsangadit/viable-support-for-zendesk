<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$settings = get_option( 'viasuzen_settings' );
?>

<div class="wrap">

    <?php settings_errors( 'viasuzen_settings_group' ); ?>

    <form method="post" action="options.php">
    <?php settings_fields( 'viasuzen_settings_group' ); ?>

        <!-- Zendesk API Settings Section -->
        <div class="zc-section zc-api-settings">

            <div class="zendesk-connect-admin-wrap-main_brand_logo_zendex">
				<?php // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage -- Safe: using static plugin image, not media attachment ?>
                <img src="<?php echo esc_url( VIASUZEN_URL . 'assets/images/main_logo.png' ); ?>"
                     alt="<?php esc_attr_e( 'Brand Logo', 'viable-support-for-zendesk' ); ?>">
            </div>

            <div class="zendesk-connect-admin-wrap-main_zendex_div_cards">
                <div class="main_div_brands_section_zendex_header">
                    <h2><?php esc_html_e( 'Zendesk Authorization', 'viable-support-for-zendesk' ); ?></h2>
                    <div class="main_submit_buttons">
                        <?php submit_button(); ?>
                    </div>
                </div>

                <table class="form-table">
                    <?php do_settings_fields( 'zc-settings', 'viasuzen_section_api' ); ?>
                </table>
            </div>

        </div>

        <!-- Web Widget Settings Section -->
        <?php if ( get_option( 'viasuzen_authorization_status' ) === '1' ) : ?>
            <div class="zc-section zc-widget-settings">
                <h2><?php esc_html_e( 'Widget Configuration', 'viable-support-for-zendesk' ); ?></h2>
                <table class="form-table">
                    <?php do_settings_fields( 'zc-settings', 'viasuzen_section_widget' ); ?>
                </table>
            </div>
        <?php endif; ?>

    </form>

</div>
