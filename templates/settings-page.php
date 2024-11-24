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
        <button id="bsale-sync-button" class="button button-secondary">
            <?php _e('Start Synchronization', 'wp-bsale-integration'); ?>
        </button>
        <div id="bsale-sync-results" style="margin-top: 20px;">
            <!-- Los resultados se mostrarán aquí dinámicamente -->
        </div>
    </div>

</div>