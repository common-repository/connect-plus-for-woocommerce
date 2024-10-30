<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used to connect to
 * Olivery Connect Application
 *
 * @link       https://website.olivery.app/
 * @since      1.0.0
 *
 * @package    Connect_Plus
 * @subpackage Connect_Plus/includes/services
 */

/**
 * Olivery Connect Application API class.
 *
 * @since      1.0.0
 * @package    Connect_Plus
 * @subpackage Connect_Plus/includes/services
 * @author     Olivery dev
 */
class CPFW_API
{
    /**
     * Make API call to send the order.
     *
     * @since     1.0.0
     * @return    object    The API response.
     */
    public function cpfw_send_to_connect_plus($cpfw_the_order)
    {
        
        $cpfw_the_order = !is_object($cpfw_the_order) ? wc_get_order($cpfw_the_order) : $cpfw_the_order;
        $cpfw_customer_area = $cpfw_customer_name = $cpfw_customer_phone = $cpfw_customer_postcode = "";
        $cpfw_customer_address = [];


        switch (true) {
            case (bool) ($cpfw_temp = $this->cpfw_get_meta_key_from_option($cpfw_the_order, 'cpfw_customer_area', true)):
                $cpfw_customer_area = $cpfw_temp;
                break;
            case (bool) ($cpfw_temp = sanitize_text_field($cpfw_the_order->get_shipping_city())):
                $cpfw_customer_area = $cpfw_temp;
                break;
            case (bool) ($cpfw_temp = sanitize_text_field($cpfw_the_order->get_billing_city())) :
                $cpfw_customer_area = $cpfw_temp;
                break;
            default:
                $cpfw_customer_area = ' ';
                break;
        }

        switch (true) {
            case (bool) ($cpfw_temp = preg_replace('/[^0-9]/', '', $this->cpfw_get_meta_key_from_option($cpfw_the_order, 'cpfw_customer_phone', true))):
                $cpfw_customer_phone = $cpfw_temp;
                break;
            case (bool) ($cpfw_temp = preg_replace('/[^0-9]/', '', $cpfw_the_order->get_shipping_phone())):
                $cpfw_customer_phone = $cpfw_temp;
                break;
            case (bool) ($cpfw_temp = preg_replace('/[^0-9]/', '', $cpfw_the_order->get_billing_phone())):
                $cpfw_customer_phone = $cpfw_temp;
                break;
            default:
                $cpfw_customer_phone = '0';
                break;
        }

        if ($cpfw_temp = sanitize_text_field($this->cpfw_get_meta_key_from_option($cpfw_the_order, 'cpfw_customer_address', true))) {
            $cpfw_customer_address = $cpfw_temp;
        } else {
            $cpfw_customer_address[] = $cpfw_customer_area;

            $cpfw_customer_address[] = sanitize_text_field($cpfw_the_order->get_shipping_address_1() ?: $cpfw_the_order->get_billing_address_1());

            $cpfw_customer_address[] = sanitize_text_field($cpfw_the_order->get_shipping_address_2() ?: $cpfw_the_order->get_billing_address_2());

            $cpfw_customer_address[] = sanitize_text_field($cpfw_the_order->get_shipping_postcode() ?: $cpfw_the_order->get_billing_postcode());

            $cpfw_customer_address = implode(
                ', ',
                array_filter($cpfw_customer_address)
            );
        }

        switch (true) {
            case (bool) ($cpfw_temp = sanitize_text_field($this->cpfw_get_meta_key_from_option($cpfw_the_order, 'cpfw_customer_full_name', true))):
                $cpfw_customer_name = $cpfw_temp;
                break;
            case (bool) ($cpfw_temp = sanitize_text_field($cpfw_the_order->get_formatted_shipping_full_name())):
                $cpfw_customer_name = $cpfw_temp;
                break;
            case (bool) ($cpfw_temp = sanitize_text_field($cpfw_the_order->get_formatted_billing_full_name())):
                $cpfw_customer_name = $cpfw_temp;
                break;
            default:
                $cpfw_customer_name = ' ';
                break;
        }

        switch (true) {
            case (bool) ($cpfw_temp = $this->cpfw_get_meta_key_from_option($cpfw_the_order, 'cpfw_customer_postcode', true)):
                $cpfw_customer_postcode = $cpfw_temp;
                break;
            case (bool) ($cpfw_temp = $cpfw_the_order->get_shipping_postcode()):
                $cpfw_customer_postcode = $cpfw_temp;
                break;
            case (bool) ($cpfw_temp = $cpfw_the_order->get_billing_postcode()):
                $cpfw_customer_postcode = $cpfw_temp;
                break;
            default:
                $cpfw_customer_postcode = ' ';
                break;

        }

        $cpfw_products_detail = [];

        foreach ($cpfw_the_order->get_items() as $item_id => $item) {
            $product_id = $item->get_product_id();
            $product = $item->get_product();
            
            // Get product details
            $name = $product->get_name();
            $quantity = $item->get_quantity();
            $sku = $product->get_sku();
        
            // Construct the output format
            if (!empty($sku)) {
                $cpfw_products_detail[] = "Name: $name || Quantity: $quantity || Product Number: $product_id || SKU: $sku";
            } else {
                $cpfw_products_detail[] = "Name: $name || Quantity: $quantity || Product Number: $product_id";
            }

        }


        $cpfw_order_notes = wc_get_order_notes( array( 'order_id' => $cpfw_the_order->get_id() ) );
        
        $content_array = array();

        foreach ($cpfw_order_notes as $note) {
            $content_array[] = $note->content; // Access content directly from stdClass
        }

        // Add the URL for add orders 
        $cpfw_url  = "https://api.connect-plus.app/integration/add_orders";

        $cpfw_body ='{ "orders_list": [{
                    "address": "' . ($cpfw_customer_address ?: ' ') . '",
                    "customer_mobile": "' . trim($cpfw_customer_phone) . '",
                    "customer_name": "' . sanitize_text_field($cpfw_customer_name) . '",
                    "area": "' . $cpfw_customer_area . '",
                    "sub_area": "' . sanitize_text_field($cpfw_the_order->get_billing_address_1() ?: $cpfw_the_order->get_shipping_address_1()). '",
                    "country": "' .sanitize_text_field($cpfw_the_order->get_billing_country() ?: $cpfw_the_order->get_shipping_country()) . '",
                    "country_code": "",
                    "note": '.wp_json_encode($content_array, JSON_UNESCAPED_UNICODE).',
                    "order_reference": "' . $cpfw_the_order->get_id() . '",
                    "product_info": ' . wp_json_encode($cpfw_products_detail, JSON_UNESCAPED_UNICODE). ',
                    "package_cost": ' . $cpfw_the_order->get_subtotal() . ',
                    "delivery_fee": "' . intval($cpfw_the_order->get_shipping_total() ?:0). '",
                    "total_cod": "' . intval($cpfw_the_order->get_total() ?: 0) . '",
                    "payment_method": "' . sanitize_text_field($cpfw_the_order->get_payment_method_title()) . '"

                    }]
                }';
        
        $cpfw_request_args = array(
            'headers'     => [
                'Authorization' => 'Token '. esc_attr(get_option('olivery_connect_token')),
                'Content-Type' => 'application/json',
                'Expect' => ''
            ],
            'body'        => $cpfw_body,
            'method'      => 'POST',
            'timeout'     => 120
        );

        $cpfw_response = wp_remote_post($cpfw_url, $cpfw_request_args);
        
        return $cpfw_response;
    }

    /**
     * Return the API Error message.
     *
     * @since     1.0.0
     */

    public function cpfw_send_error($cpfw_message)
    {
        return [
            'body' => wp_json_encode([
                'result' => [
                    'fail' => true,
                    'message' => $cpfw_message
                ]
            ])
        ];
    }

    /**
     * Get Meta function for the order .
     *
     * @since     1.0.0
     */
    public function cpfw_get_meta_key_from_option($cpfw_the_order, $cpfw_key, $cpfw_single = false)
    {
        return get_post_meta(
            $cpfw_the_order->get_id(),
            esc_attr(get_option($cpfw_key)) ?: ' ',
            $cpfw_single
        );
    }
}
