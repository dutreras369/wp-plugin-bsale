<?php

class WP_Bsale_Main
{

    public function __construct()
    {
        // Cargar archivos necesarios
        $this->load_dependencies();

        // Inicializar configuraciones
        add_action('admin_menu', array($this, 'register_admin_menu'));
    }

    // Método para incluir clases y dependencias
    private function load_dependencies()
    {
        require_once WP_BSALE_DIR . 'includes/admin/class-settings-page.php';
        require_once WP_BSALE_DIR . 'includes/api/class-bsale-webhooks.php';
        require_once WP_BSALE_DIR . 'includes/api/class-bsale-client.php';
        require_once WP_BSALE_DIR . 'includes/api/class-controller-sync.php';
        require_once WP_BSALE_DIR . 'includes/woocommerce/class-product-sync.php';
        require_once WP_BSALE_DIR . 'includes/woocommerce/class-inventory-sync.php';
    }

    // Registrar menú en el administrador
    public function register_admin_menu()
    {
        add_menu_page(
            __('Bsale Integration', 'wp-bsale-integration'),
            __('Bsale', 'wp-bsale-integration'),
            'manage_options',
            'wp-bsale-settings',
            array('WP_Bsale_Settings_Page', 'render_page'),
            'dashicons-update',
            20
        );


        // Agregar el submenú de sincronización
        add_submenu_page(
            'wp-bsale-settings',
            __('Sync Products', 'wp-bsale-integration'),
            __('Sync Products', 'wp-bsale-integration'),
            'manage_options',
            'wp-bsale-sync',
            array('Sync_Controller', 'render_sync_results')
        );
    }
}

