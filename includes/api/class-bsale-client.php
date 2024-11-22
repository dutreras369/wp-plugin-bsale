<?php
class Bsale_Client {
    private $api_url = 'https://api.bsale.io/';
    private $token;

    public function __construct() {
        // Recuperar el token almacenado en las opciones
        $this->token = get_option('wp_bsale_api_token', '');
    }

    /**
     * Método para realizar una solicitud GET.
     *
     * @param string $endpoint Endpoint de la API.
     * @return array|string Respuesta decodificada o mensaje de error.
     */
    public function get($endpoint) {
        if (empty($this->token)) {
            return [
                'error' => __('API Token is missing. Please configure it in settings.', 'wp-bsale-integration'),
            ];
        }

        $response = wp_remote_get($this->api_url . $endpoint, [
            'headers' => [
                'Access_token' => $this->token,
                'Content-Type' => 'application/json',
            ],
            'timeout' => 15,
        ]);

        // Manejo de errores de WordPress
        if (is_wp_error($response)) {
            return [
                'error' => $response->get_error_message(),
            ];
        }

        $http_code = wp_remote_retrieve_response_code($response);

        // Manejo de errores HTTP
        if ($http_code !== 200) {
            return [
                'error' => __('HTTP Error ' . $http_code . ': ' . wp_remote_retrieve_body($response), 'wp-bsale-integration'),
            ];
        }

        return json_decode(wp_remote_retrieve_body($response), true);
    }
}
