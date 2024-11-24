<?php

class Product_Sync
{
    private $client;

    public function __construct()
    {
        $this->client = new Bsale_Client();
    }

    /**
     * Sincronizar productos desde la API de Bsale a WooCommerce.
     */
    public function sync_products()
    {
        $results = [
            'success' => [],
            'failed' => [],
        ];

        $response = $this->client->get('v1/products.json');

        if ($this->has_api_error($response)) {
            $results['failed'][] = [
                'name' => __('API Error', 'wp-bsale-integration'),
                'error' => $response['error'] ?? __('Unknown error', 'wp-bsale-integration'),
            ];
            return $results;
        }

        foreach ($response['items'] as $product) {
            try {
                $this->process_product($product);
                $results['success'][] = $product['name'];
            } catch (Exception $e) {
                $results['failed'][] = [
                    'name' => $product['name'] ?? __('Unknown Product', 'wp-bsale-integration'),
                    'error' => $e->getMessage(),
                ];
                error_log('Error syncing product: ' . $e->getMessage());
            }
        }

        return $results;
    }

    /**
     * Procesar un producto individual.
     */
    private function process_product($product_data)
    {
        if (!isset($product_data['id'], $product_data['name'])) {
            throw new Exception(__('Invalid product data.', 'wp-bsale-integration'));
        }

        // Crear o actualizar el producto
        $product_id = wc_get_product_id_by_sku($product_data['id']);
        $product = $product_id ? wc_get_product($product_id) : new WC_Product_Variable();

        $product->set_name($product_data['name']);
        $product->set_sku($product_data['id']);
        $product->save();

        // Sincronizar variaciones
        if (!empty($product_data['variants']['href'])) {
            $this->sync_variations($product->get_id(), $product_data['variants']['href']);
        }
    }

    /**
     * Sincronizar variaciones con WooCommerce.
     */
    private function sync_variations($parent_id, $variants_href)
    {
        $response = $this->client->get($variants_href);

        if ($this->has_api_error($response)) {
            throw new Exception(__('Failed to fetch variants.', 'wp-bsale-integration'));
        }

        foreach ($response['items'] as $variation_data) {
            try {
                $variation_id = $this->get_variation_by_sku($variation_data['id'], $parent_id);
                $variation = $variation_id ? new WC_Product_Variation($variation_id) : new WC_Product_Variation();

                $variation->set_parent_id($parent_id);
                $variation->set_sku($variation_data['id']);
                $variation->set_regular_price($this->get_variant_cost($variation_data['costs']['href']));
                $variation->set_stock_quantity($variation_data['unlimitedStock'] ? null : ($variation_data['availableStock'] ?? 0));

                $attributes = $this->parse_attributes_from_description($variation_data['description']);
                $this->set_variation_attributes($variation, $attributes);

                $variation->save();
            } catch (Exception $e) {
                error_log('Error processing variation: ' . $e->getMessage());
            }
        }
    }

    /**
     * Obtener el costo promedio de una variación.
     */
    private function get_variant_cost($costs_href)
    {
        $response = $this->client->get($costs_href);
        return $response['averageCost'] ?? '0';
    }

    /**
     * Parsear atributos desde la descripción.
     */
    private function parse_attributes_from_description($description)
    {
        $attributes = [];
        $parts = explode('/', $description);
        if (count($parts) === 2) {
            $attributes['Color'] = trim($parts[0]);
            $attributes['Talla'] = trim($parts[1]);
        }
        return $attributes;
    }

    /**
     * Asignar atributos a una variación.
     */
    private function set_variation_attributes($variation, $attributes)
    {
        $attr_data = [];

        foreach ($attributes as $attr_name => $attr_value) {
            $taxonomy = $this->ensure_attribute_registered($attr_name);

            if (!term_exists($attr_value, $taxonomy)) {
                wp_insert_term($attr_value, $taxonomy);
            }

            $attr_data[$taxonomy] = $attr_value;
        }

        $variation->set_attributes($attr_data);
    }

    /**
     * Registrar atributos en WooCommerce.
     */
    private function ensure_attribute_registered($attribute_name)
    {
        global $wpdb;

        $taxonomy = 'pa_' . sanitize_title($attribute_name);

        if (taxonomy_exists($taxonomy)) {
            return $taxonomy;
        }

        $wpdb->insert("{$wpdb->prefix}woocommerce_attribute_taxonomies", [
            'attribute_name' => sanitize_title($attribute_name),
            'attribute_label' => $attribute_name,
            'attribute_type' => 'select',
            'attribute_orderby' => 'menu_order',
            'attribute_public' => 0,
        ]);

        wp_cache_delete('woocommerce-attributes', 'woocommerce');
        register_taxonomy($taxonomy, 'product', [
            'hierarchical' => false,
            'labels' => ['name' => $attribute_name],
            'show_ui' => false,
            'query_var' => true,
            'rewrite' => false,
        ]);

        return $taxonomy;
    }

    /**
     * Verificar errores en la respuesta de la API.
     */
    private function has_api_error($response)
    {
        return !is_array($response) || isset($response['error']);
    }

    /**
     * Obtener una variación por SKU.
     */
    private function get_variation_by_sku($sku, $parent_id)
    {
        global $wpdb;

        $query = $wpdb->prepare("
            SELECT post_id
            FROM {$wpdb->postmeta}
            WHERE meta_key = '_sku' AND meta_value = %s
            AND post_id IN (
                SELECT ID FROM {$wpdb->posts}
                WHERE post_parent = %d AND post_type = 'product_variation'
            )
        ", $sku, $parent_id);

        return $wpdb->get_var($query);
    }
}
