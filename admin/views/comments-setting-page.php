<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Used only for safe UI tab highlighting
$current_page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
?>
<div class="wrap">
    <div class="zc-admin-header">

		<!-- Plugin Logo -->
		<div class="zc-logo">
			<?php // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage -- Plugin asset, not media library image ?>
            <img src="<?php echo esc_url( VIASUZEN_URL . 'assets/images/logo_icon.png' ); ?>" alt="<?php echo esc_attr__( 'Z Logo', 'viable-support-for-zendesk' ); ?>" />
		</div>

        <!-- Admin Navigation -->
        <nav class="zc-admin-nav">
            <a href="admin.php?page=viasuzen-settings" class="<?php echo ( $current_page === 'viasuzen-settings' ) ? 'active' : ''; ?>">
                Dashboard
            </a>
            <a href="admin.php?page=viasuzen-form-settings" class="<?php echo ( $current_page === 'viasuzen-form-settings' ) ? 'active' : ''; ?>">
                Form Settings
            </a>
            <a href="admin.php?page=viasuzen-comments-settings" class="<?php echo ( $current_page === 'viasuzen-comments-settings' ) ? 'active' : ''; ?>">
                Comments Settings
            </a>
        </nav>

    </div>

    <!-- Comments Settings Form -->
    <form method="post" action="options.php">
        <div class="zendex-connects-wp-main-comments-setting">
            <?php
                settings_fields( 'viasuzen_comments_settings_group' );
                do_settings_sections( 'viasuzen_comments_settings' );
            ?>
        </div>
    </form>
</div>
