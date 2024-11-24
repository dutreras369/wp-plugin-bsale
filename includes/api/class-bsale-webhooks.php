<?php 
/*

add_action( 'admin_post_bsale_sync_products', 'bsale_sync_products_action' );

function bsale_sync_products_action() {
    // Validar Nonce
    if ( ! isset( $_POST['bsale_sync_nonce'] ) || ! wp_verify_nonce( $_POST['bsale_sync_nonce'], 'bsale_sync_action' ) ) {
        wp_die( __( 'Invalid request. Please try again.', 'wp-bsale-integration' ) );
    } 

    // Procesar lógica de sincronización
    $sync = new Product_Sync();
    $results = $sync->sync_products();

    // Redirigir con los resultados
    //wp_redirect(admin_url('admin.php?page=bsale-sync&results=' . urlencode(json_encode($results))));
    exit;
} */

add_action('wp_ajax_bsale_sync_products', 'bsale_sync_products_ajax_action');

function bsale_sync_products_ajax_action() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error([
            'message' => __('Permission denied.', 'wp-bsale-integration'),
            'debug' => 'User does not have sufficient permissions.',
        ]);
    }

    $start_time = microtime(true);

    try {
        $sync = new Product_Sync();
        $results = $sync->sync_products();

        $end_time = microtime(true);

        wp_send_json_success([
            'results' => $results,
            'debug' => [
                'request_time' => date('Y-m-d H:i:s'),
                'execution_time' => round($end_time - $start_time, 2) . ' seconds',
                'memory_usage' => round(memory_get_usage() / 1024 / 1024, 2) . ' MB',
            ],
        ]);
    } catch (Exception $e) {
        wp_send_json_error([
            'message' => $e->getMessage(),
            'debug' => [
                'trace' => $e->getTraceAsString(),
            ],
        ]);
    }
}
