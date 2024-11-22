<?php

class WP_Bsale_Settings_Page
{

    public static function render_page()
    {
        // Procesar el formulario si se ha enviado
        if (isset($_POST['bsale_token'])) {
            update_option('wp_bsale_api_token', sanitize_text_field($_POST['bsale_token']));
            echo '<div class="updated"><p>' . __('Settings saved.', 'wp-bsale-integration') . '</p></div>';
        }

        // Obtener el token actual
        $token = get_option('wp_bsale_api_token', '');

        // Renderizar el formulario
?>
        <div class="wrap bsale-integration-settings">
            <!-- Encabezado Principal -->
            <h1 class="bsale-title"><?php _e('Bsale Integration Settings', 'wp-bsale-integration'); ?></h1>

            <!-- Sección de Configuración -->
            <div class="bsale-settings-section">
                <form method="post" action="">
                    <h2 class="section-title"><?php _e('API Configuration', 'wp-bsale-integration'); ?></h2>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php _e('API Token', 'wp-bsale-integration'); ?></th>
                            <td>
                                <input type="text" name="bsale_token" value="<?php echo esc_attr($token); ?>" class="regular-text">
                                <p class="description"><?php _e('Enter your Bsale API token to enable integration.', 'wp-bsale-integration'); ?></p>
                            </td>
                        </tr>
                    </table>
                    <?php submit_button(__('Save Settings', 'wp-bsale-integration'), 'primary'); ?>
                </form>
            </div>

            <!-- Sección de Sincronización -->
            <div class="bsale-sync-section">
                <h2 class="section-title"><?php _e('Product Synchronization', 'wp-bsale-integration'); ?></h2>
                <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                    <?php wp_nonce_field('bsale_sync_action', 'bsale_sync_nonce'); ?>
                    <input type="hidden" name="action" value="bsale_sync_products">
                    <p><?php _e('Start synchronizing your products from Bsale to WooCommerce.', 'wp-bsale-integration'); ?></p>
                    <?php submit_button(__('Start Synchronization', 'wp-bsale-integration'), 'secondary'); ?>
                </form>

            </div>
        </div>
<?php
    }
}
