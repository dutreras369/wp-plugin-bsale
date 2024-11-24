<?php

function enqueue_bsale_admin_assets($hook) {
    // Verifica si el hook es el correcto para las páginas del plugin
    if ($hook === 'toplevel_page_wp-bsale-settings' || $hook === 'bsale_page_wp-bsale-sync') {
        // Encolar estilos
        wp_enqueue_style('bsale-admin-style', plugin_dir_url(__FILE__) . '../assets/css/admin-style.css', [], '1.0.0');

        // Encolar el script general de sincronización
        wp_enqueue_script('bsale-sync-script', plugin_dir_url(__FILE__) . '../assets/js/bsale-sync.js', ['jquery'], '1.0.0', true);

        wp_localize_script('bsale-sync-script', 'bsaleSyncAjax', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('bsale_sync_nonce'), // Este debe coincidir con el valor esperado en el servidor
            'loadingMessage' => __('Synchronizing products... Please wait.', 'wp-bsale-integration'),
        ]);
    }
}
add_action('admin_enqueue_scripts', 'enqueue_bsale_admin_assets');
