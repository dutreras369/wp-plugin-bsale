<?php

class Product_Sync {

    private $client;

    public function __construct() {
        $this->client = new Bsale_Client();
    }

    public function sync_products() {
        $products = $this->client->get( 'v1/products.json' );

        if ( isset( $products['error'] ) ) {
            return $products['error'];
        }

        foreach ( $products['items'] as $product ) {
            if ( isset( $product['variations'] ) && ! empty( $product['variations'] ) ) {
                // Producto con variaciones
                $this->create_or_update_variable_product( $product );
            } else {
                // Producto simple
                $this->create_or_update_simple_product( $product );
            }
        }
    }

    private function create_or_update_simple_product( $product_data ) {
        $product_id = wc_get_product_id_by_sku( $product_data['code'] );

        if ( $product_id ) {
            $product = wc_get_product( $product_id );
        } else {
            $product = new WC_Product();
        }

        $product->set_name( $product_data['name'] );
        $product->set_sku( $product_data['code'] );
        $product->set_regular_price( $product_data['price'] );
        $product->set_stock_quantity( $product_data['stock'] );
        $product->save();
    }

    private function create_or_update_variable_product( $product_data ) {
        $product_id = wc_get_product_id_by_sku( $product_data['code'] );

        if ( $product_id ) {
            $product = wc_get_product( $product_id );
        } else {
            $product = new WC_Product_Variable();
        }

        $product->set_name( $product_data['name'] );
        $product->set_sku( $product_data['code'] );
        $product->save();

        // Sincronizar las variaciones
        if ( isset( $product_data['variations'] ) ) {
            $this->sync_variations( $product->get_id(), $product_data['variations'] );
        }
    }

    private function sync_variations( $parent_id, $variations ) {
        foreach ( $variations as $variation_data ) {
            $variation_id = $this->get_variation_by_sku( $variation_data['code'], $parent_id );

            if ( $variation_id ) {
                $variation = new WC_Product_Variation( $variation_id );
            } else {
                $variation = new WC_Product_Variation();
                $variation->set_parent_id( $parent_id );
            }

            // Setear los atributos y propiedades de la variación
            $variation->set_sku( $variation_data['code'] );
            $variation->set_regular_price( $variation_data['price'] );
            $variation->set_stock_quantity( $variation_data['stock'] );

            // Asignar atributos de la variación
            if ( isset( $variation_data['attributes'] ) ) {
                $this->set_variation_attributes( $variation, $variation_data['attributes'] );
            }

            $variation->save();
        }
    }

    private function get_variation_by_sku( $sku, $parent_id ) {
        global $wpdb;

        $variation_id = $wpdb->get_var( $wpdb->prepare("
            SELECT ID FROM {$wpdb->posts} 
            WHERE post_parent = %d AND post_type = 'product_variation' 
            AND meta_key = '_sku' AND meta_value = %s
        ", $parent_id, $sku ) );

        return $variation_id;
    }

    private function set_variation_attributes( $variation, $attributes ) {
        $attr_data = [];

        foreach ( $attributes as $attr_name => $attr_value ) {
            $taxonomy = 'pa_' . sanitize_title( $attr_name );

            // Registrar el atributo si no existe
            if ( ! taxonomy_exists( $taxonomy ) ) {
                $this->register_product_attribute( $attr_name );
            }

            // Asegurarse de que el valor del atributo exista
            if ( ! term_exists( $attr_value, $taxonomy ) ) {
                wp_insert_term( $attr_value, $taxonomy );
            }

            $attr_data[ $taxonomy ] = $attr_value;
        }

        $variation->set_attributes( $attr_data );
    }

    private function register_product_attribute( $name ) {
        global $wpdb;

        $attribute_name = sanitize_title( $name );

        if ( $wpdb->get_var( "SELECT attribute_id FROM {$wpdb->prefix}woocommerce_attribute_taxonomies WHERE attribute_name = '$attribute_name'" ) === null ) {
            $wpdb->insert(
                "{$wpdb->prefix}woocommerce_attribute_taxonomies",
                array(
                    'attribute_name' => $attribute_name,
                    'attribute_label' => $name,
                    'attribute_type' => 'select',
                    'attribute_orderby' => 'menu_order',
                    'attribute_public' => 0,
                )
            );

            // Regenerar caché de atributos
            wp_cache_delete( 'woocommerce-attributes', 'woocommerce' );
        }
    }
}
