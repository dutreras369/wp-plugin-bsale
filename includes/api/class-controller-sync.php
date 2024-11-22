<?php
class Sync_Controller
{
    public static function render_sync_results()
    {
        // Validar permisos de usuario
        if (!current_user_can('manage_options')) {
            echo '<div class="notice notice-error"><p>' . __('You do not have sufficient permissions to access this page.', 'wp-bsale-integration') . '</p></div>';
            return;
        }

        // Recuperar resultados y logs desde la URL
        $results = isset($_GET['results']) ? json_decode(stripslashes($_GET['results']), true) : [
            'success' => [],
            'failed' => [],
        ];

        $debug_logs = isset($_GET['debug_logs']) ? stripslashes($_GET['debug_logs']) : 'No debug logs available.';

        // Validar estructura de los resultados
        if (!is_array($results) || !isset($results['success'], $results['failed'])) {
            $results = [
                'success' => [],
                'failed' => [],
                'debug' => __('Invalid results format.', 'wp-bsale-integration'),
            ];
        }

        // Renderizar resultados en el DOM
        echo '<div class="wrap">';
        echo '<h1>' . __('Bsale Synchronization Results', 'wp-bsale-integration') . '</h1>';

        // Mostrar resumen de sincronización
        echo '<h2>' . __('Synchronization Summary', 'wp-bsale-integration') . '</h2>';
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead>
            <tr>
                <th>' . __('Product Name', 'wp-bsale-integration') . '</th>
                <th>' . __('Status', 'wp-bsale-integration') . '</th>
                <th>' . __('Details', 'wp-bsale-integration') . '</th>
            </tr>
          </thead>';
        echo '<tbody>';

        // Productos sincronizados con éxito
        foreach ($results['success'] as $product_name) {
            echo '<tr>
                <td>' . esc_html($product_name) . '</td>
                <td>' . __('Success', 'wp-bsale-integration') . '</td>
                <td>-</td>
              </tr>';
        }

        // Productos con errores
        foreach ($results['failed'] as $failed_product) {
            echo '<tr>
                <td>' . esc_html($failed_product['name'] ?? __('Unknown Product', 'wp-bsale-integration')) . '</td>
                <td>' . __('Failed', 'wp-bsale-integration') . '</td>
                <td>' . esc_html($failed_product['error'] ?? __('Unknown Error', 'wp-bsale-integration')) . '</td>
              </tr>';
        }

        if (empty($results['success']) && empty($results['failed'])) {
            echo '<tr>
                <td colspan="3">' . __('No results to display.', 'wp-bsale-integration') . '</td>
              </tr>';
        }

        echo '</tbody></table>';

        // Mostrar logs de depuración
        echo '<h2>' . __('Debug Logs', 'wp-bsale-integration') . '</h2>';
        echo '<pre style="background: #f5f5f5; padding: 10px; border: 1px solid #ccc; overflow-x: auto;">';
        echo esc_html($debug_logs);
        echo '</pre>';
        echo '</div>';
    }
}
