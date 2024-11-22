<?php 

add_action( 'admin_post_bsale_sync_products', 'bsale_sync_products_action' );

function bsale_sync_products_action() {
    /* Validar Nonce
    if ( ! isset( $_POST['bsale_sync_nonce'] ) || ! wp_verify_nonce( $_POST['bsale_sync_nonce'], 'bsale_sync_action' ) ) {
        wp_die( __( 'Invalid request. Please try again.', 'wp-bsale-integration' ) );
    } */

    // Procesar lógica de sincronización
    $sync = new Product_Sync();
    $results = $sync->sync_products();

    // Redirigir con los resultados
    wp_redirect(admin_url('admin.php?page=bsale-sync&results=' . urlencode(json_encode($results))));
    exit;
}
