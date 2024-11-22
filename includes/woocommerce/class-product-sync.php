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
     *
     * @return array Resultados de sincronización (éxitos y errores).
     */
    public function sync_products()
    {
        $results = [
            'success' => [],
            'failed' => [],
        ];

        // Obtener productos desde la API
        $response = $this->client->get('v1/products.json');

        // Manejar errores en la API
        if (isset($response['error'])) {
            $results['failed'][] = [
                'name' => __('API Response', 'wp-bsale-integration'),
                'error' => $response['error'],
            ];
            error_log('API Error: ' . $response['error']);
            return $results;
        }

        // Validar estructura de la respuesta
        if (!isset($response['items']) || !is_array($response['items'])) {
            $results['failed'][] = [
                'name' => __('API Response', 'wp-bsale-integration'),
                'error' => __('Invalid response format from Bsale API.', 'wp-bsale-integration'),
            ];
            error_log('Invalid API Response: ' . print_r($response, true));
            return $results;
        }

        // Procesar solo el primer producto dentro del bucle
        foreach ($response['items'] as $index => $product) {
            if ($index > 0) {
                break; // Solo procesar el primer producto
            }

            error_log('Processing Product: ' . print_r($product, true)); // Log del producto

            try {
                $this->process_product($product); // Procesar y guardar el producto
                $results['success'][] = $product['name'];
            } catch (Exception $e) {
                $results['failed'][] = [
                    'name' => $product['name'] ?? __('Unknown Product', 'wp-bsale-integration'),
                    'error' => $e->getMessage(),
                ];
                error_log('Error saving product: ' . $e->getMessage());
            }
        }

        // Si no hay productos, registrar un mensaje
        if (empty($response['items'])) {
            $results['failed'][] = [
                'name' => __('No Product Found', 'wp-bsale-integration'),
                'error' => __('No products were found in the API response.', 'wp-bsale-integration'),
            ];
            error_log('No product found in API response.');
        }

        return $results;
    }


    /**
     * Función intermedia para imprimir productos antes en el log.
     *
     * @param array $response Lista de productos
     */

    private function print_logs($response)
    {

        foreach ($response['items'] as $index => $product) {
            error_log('Product: ' . print_r($product, true)); // Imprimir cada producto para depuración

            // Procesar solo el primer producto
            if ($index === 0) {
                break;
            }
        }
    }

    /**
     * Función intermedia para imprimir productos antes de procesarlos.
     *
     * @param array $products Lista de productos.
     */
    private function print_products($products)
    {
        error_log('Printing Products: ' . print_r($products, true)); // Registrar productos en el log

        // Opcional: Mostrar en pantalla (útil para depuración en navegador)
        if (defined('WP_DEBUG') && WP_DEBUG) {
            echo '<pre>';
            print_r($products);
            echo '</pre>';
        }
    }

    /**
     * Procesar un producto individual y sincronizarlo.
     */
    private function process_product($product)
    {
        if (!isset($product['id'], $product['name'])) {
            throw new Exception(__('Invalid product data.', 'wp-bsale-integration'));
        }

        // Determinar si el producto tiene variantes
        if (!empty($product['variants']['href'])) {
            $this->process_variants($product);
        } else {
            $this->create_or_update_simple_product($product);
        }
    }

    /**
     * Crear o actualizar un producto simple.
     */
    private function create_or_update_simple_product($product_data)
    {
        $product_id = wc_get_product_id_by_sku($product_data['id']);

        if ($product_id) {
            $product = wc_get_product($product_id);
        } else {
            $product = new WC_Product();
        }

        $product->set_name($product_data['name']);
        $product->set_sku($product_data['id']);
        $product->set_regular_price($product_data['price'] ?? '0');
        $product->set_stock_quantity($product_data['stock'] ?? 0);
        $product->save();
    }

    /**
     * Crear o actualizar un producto variable y sus variantes.
     */
    /**
     * Procesar las variantes de un producto.
     */
    private function process_variants($product_data)
    {
        // Obtener el ID del producto en WooCommerce por SKU
        $product_id = wc_get_product_id_by_sku($product_data['id']);

        if ($product_id) {
            $product = wc_get_product($product_id);
        } else {
            $product = new WC_Product_Variable();
        }

        $product->set_name($product_data['name']);
        $product->set_sku($product_data['id']);
        $product->save();

        // Obtener las variantes desde la API
        $variants_response = $this->client->get($product_data['variants']['href']);

        // Verificar si la respuesta es un array
        if (!is_array($variants_response) || !isset($variants_response['items'])) {
            throw new Exception(__('Invalid variants data: Missing "items".', 'wp-bsale-integration'));
        }

        // Procesar las variantes
        $this->sync_variations($product->get_id(), $variants_response['items']);
    }

    /**
     * Sincronizar las variaciones de un producto.
     */
    private function sync_variations($parent_id, $variations)
    {
        foreach ($variations as $variation_data) {
            // Validar datos de la variación
            if (!isset($variation_data['id'], $variation_data['price'])) {
                error_log('Invalid variation data: ' . print_r($variation_data, true));
                continue; // Saltar esta variación
            }

            $variation_id = $this->get_variation_by_sku($variation_data['id'], $parent_id);

            if ($variation_id) {
                $variation = new WC_Product_Variation($variation_id);
            } else {
                $variation = new WC_Product_Variation();
                $variation->set_parent_id($parent_id);
            }

            $variation->set_sku($variation_data['id']);
            $variation->set_regular_price($variation_data['price']);
            $variation->set_stock_quantity($variation_data['stock'] ?? 0);

            if (!empty($variation_data['attributes'])) {
                $this->set_variation_attributes($variation, $variation_data['attributes']);
            }

            $variation->save();
        }
    }

    /**
     * Obtener el ID de una variación existente por SKU.
     */
    private function get_variation_by_sku($sku, $parent_id)
    {
        global $wpdb;

        $variation_id = $wpdb->get_var($wpdb->prepare("
        SELECT post_id
        FROM {$wpdb->postmeta}
        WHERE meta_key = '_sku' AND meta_value = %s
        AND post_id IN (
            SELECT ID FROM {$wpdb->posts} WHERE post_parent = %d AND post_type = 'product_variation'
        )
    ", $sku, $parent_id));

        return $variation_id;
    }

    /**
     * Asignar atributos a una variación.
     */
    private function set_variation_attributes($variation, $attributes)
    {
        $attr_data = [];

        foreach ($attributes as $attr_name => $attr_value) {
            $taxonomy = 'pa_' . sanitize_title($attr_name);

            if (!taxonomy_exists($taxonomy)) {
                $this->register_product_attribute($attr_name);
            }

            $attr_data[$taxonomy] = $attr_value;
        }

        $variation->set_attributes($attr_data);
    }

    /**
     * Registrar un atributo de producto en WooCommerce.
     */
    private function register_product_attribute($name)
    {
        global $wpdb;

        $attribute_name = sanitize_title($name);

        if ($wpdb->get_var("SELECT attribute_id FROM {$wpdb->prefix}woocommerce_attribute_taxonomies WHERE attribute_name = '$attribute_name'") === null) {
            $wpdb->insert("{$wpdb->prefix}woocommerce_attribute_taxonomies", [
                'attribute_name' => $attribute_name,
                'attribute_label' => $name,
                'attribute_type' => 'select',
                'attribute_orderby' => 'menu_order',
                'attribute_public' => 0,
            ]);

            wp_cache_delete('woocommerce-attributes', 'woocommerce');
        }
    }
}
