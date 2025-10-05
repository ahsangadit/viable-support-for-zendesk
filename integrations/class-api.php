<?php
namespace viablecube\viasuzen\Integrations;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class API
 *
 * Handles communication with the Zendesk API to retrieve custom ticket fields.
 *
 * @package viablecube\viasuzen\Integrations
 * @author  Ahsan
 * @since   1.0.0
 */
class API {

    /**
     * Zendesk subdomain (e.g., example.zendesk.com => example).
     *
     * @var string
     */
    private $subdomain;

    /**
     * Zendesk API token.
     *
     * @var string
     */
    private $token;

    /**
     * Zendesk agent email.
     *
     * @var string
     */
    private $email;

    /**
     * API constructor.
     * Initializes the class by retrieving settings from the WordPress options table.
     *
     * @since 1.0.0
     */
    public function __construct() {
        $settings = get_option( 'viasuzen_settings' );
        $this->subdomain = isset( $settings['subdomain'] ) ? trim( $settings['subdomain'] ) : '';
        $this->token     = isset( $settings['api_token'] ) ? trim( $settings['api_token'] ) : '';
        $this->email     = isset( $settings['email'] ) ? trim( $settings['email'] ) : '';
    }

    /**
     * Fetches all active, removable text custom fields from the Zendesk ticket fields API.
     *
     * Only returns fields where:
     * - 'active' is true
     * - 'removable' is true
     * - 'type' is 'text'
     *
     * @return array List of filtered custom ticket fields. Returns an empty array on failure.
     *
     * @since 1.0.0
     */
    public function get_custom_fields() {
        if ( empty( $this->subdomain ) || empty( $this->token ) ) {
            return [];
        }

        $url = "https://{$this->subdomain}/api/v2/ticket_fields.json";

        $response = wp_remote_get( $url, [
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode( "{$this->email}/token:{$this->token}" ),
                'Content-Type'  => 'application/json',
            ],
            'timeout' => 15,
        ] );

        if ( is_wp_error( $response ) ) {
            return [];
        }

        $data = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( ! isset( $data['ticket_fields'] ) ) {
            return [];
        }

        $custom_fields = array_filter($data['ticket_fields'], function ($field) {
            return isset($field['active'], $field['removable']) &&
                $field['active'] === true &&
                $field['removable'] === true &&
                $field['type'] === 'text';
        });

        return array_values( $custom_fields );
    }
}
