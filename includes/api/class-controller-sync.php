<?php
class Sync_Controller
{
    public static function render_sync_results()
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('You are not allowed to access this page.', 'wp-bsale-integration'));
        }

        // Recuperar resultados de la URL
        $results = isset($_GET['results']) ? json_decode(stripslashes($_GET['results']), true) : [
            'success' => [],
            'failed' => [],
        ];

        // Validar estructura de los resultados
        if (!is_array($results) || !isset($results['success'], $results['failed'])) {
            $results = [
                'success' => [],
                'failed' => [],
                'debug' => __('Invalid results format.', 'wp-bsale-integration'),
            ];
        }

        // Si tienes logs o información adicional que deseas mostrar
        $debug_logs = isset($_GET['debug_logs']) ? stripslashes($_GET['debug_logs']) : 'No debug logs available.';

        // Renderizar la página con resultados
        include WP_BSALE_DIR . 'templates/sync-results.php';
    }

    
}
