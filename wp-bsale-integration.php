<?php
/*
Plugin Name: WP Bsale Integration
Description: Integración de Bsale con WordPress y WooCommerce.
Version: 1.0.0
Author: David
Text Domain: wp-bsale-integration
Domain Path: /languages
*/

// Evitar acceso directo
if ( ! defined( 'ABSPATH' ) ) exit;

// Definir constantes del plugin
define( 'WP_BSALE_DIR', plugin_dir_path( __FILE__ ) );
define( 'WP_BSALE_URL', plugin_dir_url( __FILE__ ) );
define( 'WP_BSALE_TEMPLATES', WP_BSALE_DIR . 'templates/' );


// Incluir archivos principales
require_once WP_BSALE_DIR . 'includes/class-main.php';
require_once WP_BSALE_DIR . 'includes/functions.php';


// Inicializar el plugin
function wp_bsale_init() {
    new WP_Bsale_Main();
}
add_action( 'plugins_loaded', 'wp_bsale_init' );

// Activación y desactivación del plugin
register_activation_hook( __FILE__, 'wp_bsale_activate' );
register_deactivation_hook( __FILE__, 'wp_bsale_deactivate' );

function wp_bsale_activate() {
    // Lógica de inicialización, como crear opciones o roles
}

function wp_bsale_deactivate() {
    // Lógica para desactivar, como limpiar cron jobs
}
