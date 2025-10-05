<?php
namespace viablecube\viasuzen\Frontend;

use viablecube\viasuzen\Integrations\API;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class Contact_Form
 *
 * Renders a frontend contact form using a shortcode.
 * Dynamically includes Zendesk custom text fields in the form.
 *
 * @package viablecube\viasuzen\Frontend
 * @author  Ahsan
 * @since   1.0.0
 */
class Contact_Form {

    /**
     * Renders the contact form HTML via a shortcode.
     *
     * This form includes:
     * - Name (required)
     * - Email (required)
     * - Message (required)
     * - Dynamic custom text fields fetched from Zendesk
     *
     * Custom fields are retrieved via the API class and included
     * as additional text inputs.
     *
     * @return string The HTML output of the contact form.
     *
     * @since 1.0.0
     */
    public function render_form_shortcode() {
        ob_start();

        $api     = new API();
        $fields  = $api->get_custom_fields();
        ?>

        <form method="post" action="">
            <p>
                <label for="zcw_name">Name</label>
                <input type="text" name="zcw_name" required>
            </p>

            <p>
                <label for="zcw_email">Email</label>
                <input type="email" name="zcw_email" required>
            </p>

            <?php foreach ( $fields as $field ): ?>
                <p>
                    <label for="custom_<?php echo esc_attr( $field['id'] ); ?>">
                        <?php echo esc_html( $field['title'] ); ?>
                    </label>
                    <input type="text" name="custom_<?php echo esc_attr( $field['id'] ); ?>">
                </p>
            <?php endforeach; ?>

            <p>
                <label for="zcw_message">Message</label>
                <textarea name="zcw_message" required></textarea>
            </p>

            <p><input type="submit" value="Submit Ticket"></p>
        </form>

        <?php
        return ob_get_clean();
    }
}
