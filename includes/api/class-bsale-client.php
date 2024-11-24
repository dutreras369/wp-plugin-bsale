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
     * @param string $endpoint Endpoint o URL completa de la API.
     * @return array|string Respuesta decodificada o mensaje de error.
     */
    public function get($endpoint) {
        // Manejar URLs absolutas o relativas
        $url = (strpos($endpoint, 'http') === 0) ? $endpoint : $this->api_url . ltrim($endpoint, '/');

        if (empty($this->token)) {
            return [
                'error' => __('API Token is missing. Please configure it in settings.', 'wp-bsale-integration'),
            ];
        }

        $response = wp_remote_get($url, [
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

    /**
     * Obtener la URL base de la API.
     *
     * @return string URL base de la API.
     */
    public function get_api_url() {
        return $this->api_url;
    }
}

