<?php

class Bsale_Client {

    private $api_url = 'https://api.bsale.cl/';
    private $token;

    public function __construct() {
        $this->token = get_option( 'wp_bsale_api_token', '' );
    }

    // Método para realizar una solicitud GET
    public function get( $endpoint ) {
        $response = wp_remote_get( $this->api_url . $endpoint, array(
            'headers' => array(
                'access_token' => $this->token
            )
        ));

        if ( is_wp_error( $response ) ) {
            return $response->get_error_message();
        }

        return json_decode( wp_remote_retrieve_body( $response ), true );
    }
}
