<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$settings     = get_option( 'viasuzen_form_settings' );
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Used only for safe UI tab highlighting
$current_page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
?>

<div class="wrap">
    <div class="zc-admin-header">

        <!-- Logo -->
        <div class="zc-logo">
			<?php // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage -- Safe: using static plugin image, not media attachment ?>
            <img src="<?php echo esc_url( VIASUZEN_URL . 'assets/images/logo_icon.png' ); ?>" alt="<?php echo esc_attr__( 'Z Logo', 'viable-support-for-zendesk' ); ?>" />
        </div>

        <!-- Admin Navigation -->
        <nav class="zc-admin-nav">
            <a href="admin.php?page=viasuzen-settings" class="<?php echo $current_page === 'viasuzen-settings' ? 'active' : ''; ?>">
                <?php esc_html_e( 'Dashboard', 'viable-support-for-zendesk' ); ?>
            </a>
            <a href="admin.php?page=viasuzen-form-settings" class="<?php echo $current_page === 'viasuzen-form-settings' ? 'active' : ''; ?>">
                <?php esc_html_e( 'Form Settings', 'viable-support-for-zendesk' ); ?>
            </a>
            <a href="admin.php?page=viasuzen-comments-settings" class="<?php echo $current_page === 'viasuzen-comments-settings' ? 'active' : ''; ?>">
                <?php esc_html_e( 'Comment Settings', 'viable-support-for-zendesk' ); ?>
            </a>
        </nav>

    </div>

<?php settings_errors( 'viasuzen_form_settings_group' ); ?>
<form method="post" action="options.php">
    <div class="zc-form-wrapper">
    <?php settings_fields( 'viasuzen_form_settings_group' ); ?>

        <div class="zc-section zc-form-main-section">
            <div class="zc-form-settings-section-wrapper">
                <div class="zc-form-description">
                    <h2><?php esc_html_e( 'Form Settings', 'viable-support-for-zendesk' ); ?></h2>
                    <p><?php esc_html_e( 'Customize your Zendesk ticket submission form labels below.', 'viable-support-for-zendesk' ); ?></p>
                </div>
                <div class="zc-form-submit-button">
                    <?php submit_button(); ?>
                </div>
            </div>

            <!-- First Two Fields: Shortcode + Sync Button -->
            <div class="zc-top-fields-wrapper">
                <table class="form-table">
                    <tbody>
                        <?php
                        global $wp_settings_fields;

                        if ( isset( $wp_settings_fields['viasuzen-form-settings']['viasuzen_form_main_section'] ) ) {
                            $counter = 0;
                            foreach ( $wp_settings_fields['viasuzen-form-settings']['viasuzen_form_main_section'] as $key => $field ) {
                                if ( $counter < 2 ) {
                                    echo '<tr>';
                                    echo '<th scope="row">' . ( ! empty( $field['title'] ) ? esc_html( $field['title'] ) : '' ) . '</th>';
                                    echo '<td>';
                                    call_user_func( $field['callback'], $field['args'] );
                                    echo '</td>';
                                    echo '</tr>';
                                }
                                $counter++;
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <!-- Remaining Fields -->
            <div class="zc-form-fields-group">
                <table class="form-table">
                    <tbody>
                        <?php
                        $counter = 0;
                        if ( isset( $wp_settings_fields['viasuzen-form-settings']['viasuzen_form_main_section'] ) ) {
                            foreach ( $wp_settings_fields['viasuzen-form-settings']['viasuzen_form_main_section'] as $key => $field ) {
                                if ( $counter >= 2 ) {
                                    echo '<tr class="zc-individual-field-wrapper">';
                                    echo '<th scope="row">' . ( ! empty( $field['title'] ) ? esc_html( $field['title'] ) : '' ) . '</th>';
                                    echo '<td>';
                                    call_user_func( $field['callback'], $field['args'] );
                                    echo '</td>';
                                    echo '</tr>';
                                }
                                $counter++;
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</form>

</div>
