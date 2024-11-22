<div class="wrap">
    <h1><?php _e('Bsale Synchronization Results', 'wp-bsale-integration'); ?></h1>

    <!-- Mostrar resultados -->
    <?php if (isset($results['error'])): ?>
        <div class="notice notice-error is-dismissible">
            <p><?php echo esc_html($results['error']); ?></p>
        </div>
    <?php endif; ?>

    <h2><?php _e('Synchronization Summary', 'wp-bsale-integration'); ?></h2>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php _e('Product Name', 'wp-bsale-integration'); ?></th>
                <th><?php _e('Status', 'wp-bsale-integration'); ?></th>
                <th><?php _e('Details', 'wp-bsale-integration'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($results['success'])): ?>
                <?php foreach ($results['success'] as $product_name): ?>
                    <tr>
                        <td><?php echo esc_html($product_name); ?></td>
                        <td><?php _e('Success', 'wp-bsale-integration'); ?></td>
                        <td>-</td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>

            <?php if (!empty($results['failed'])): ?>
                <?php foreach ($results['failed'] as $failed_product): ?>
                    <tr>
                        <td><?php echo esc_html($failed_product['name'] ?? __('Unknown Product', 'wp-bsale-integration')); ?></td>
                        <td><?php _e('Failed', 'wp-bsale-integration'); ?></td>
                        <td><?php echo esc_html($failed_product['error'] ?? __('Unknown Error', 'wp-bsale-integration')); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>

            <?php if (empty($results['success']) && empty($results['failed'])): ?>
                <tr>
                    <td colspan="3"><?php _e('No results to display.', 'wp-bsale-integration'); ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Mostrar logs de depuración -->
    <h2><?php _e('Debug Logs', 'wp-bsale-integration'); ?></h2>
    <pre style="background: #f5f5f5; padding: 10px; border: 1px solid #ccc; overflow-x: auto;">
        <?php echo esc_html($debug_logs); ?>
    </pre>
</div>
