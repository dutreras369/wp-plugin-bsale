<?php

class WP_Bsale_Settings_Page {

    public static function render_page() {
        // Procesar el formulario si se ha enviado
        if ( isset( $_POST['bsale_token'] ) ) {
            update_option( 'wp_bsale_api_token', sanitize_text_field( $_POST['bsale_token'] ) );
            echo '<div class="updated"><p>' . __( 'Settings saved.', 'wp-bsale-integration' ) . '</p></div>';
        }

        // Obtener el token actual
        $token = get_option( 'wp_bsale_api_token', '' );

        // Renderizar el formulario
        ?>
        <div class="wrap">
            <h1><?php _e( 'Bsale Integration Settings', 'wp-bsale-integration' ); ?></h1>
            <form method="post" action="">
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e( 'API Token', 'wp-bsale-integration' ); ?></th>
                        <td>
                            <input type="text" name="bsale_token" value="<?php echo esc_attr( $token ); ?>" class="regular-text">
                            <p class="description"><?php _e( 'Enter your Bsale API token.', 'wp-bsale-integration' ); ?></p>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}
