<?php

/**
 * The ajax-endpoints-specific functionality of the plugin.
 *
 * @link       https://website.olivery.app/
 * @since      1.0.0
 *
 * @package    Connect_Plus
 * @subpackage Connect_plus/endpoints
 */

/**
 * The ajax-endpoints-specific functionality of the plugin.
 *
 *
 * @package    Connect_Plus
 * @subpackage Connect_Plus/endpoints
 * @author     Olivery dev
 */
class CPFW_Ajax_Wrapper
{
    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version)
    {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    public function cpfw_send_order_to_oc()
    {
        // Sanitize and validate nonce and order_id
        $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';

        $cpfw_order_id = isset($_POST['order_id']) ? absint($_POST['order_id']) : 0;


        try{
            $order = wc_get_order($cpfw_order_id);
            $cpfw_the_order = !is_object($order) ? wc_get_order($order) : $order;
            if (is_bool($cpfw_the_order) || $cpfw_the_order->get_status() == "auto-draft") {
                throw new Exception("Order not found, Make sure order is exist and not draft!", 1);

            }
        }catch(Exception $e){
            wp_send_json_error([
                'message' => esc_html($e->getMessage()),
                'error' => esc_html($e->getMessage()),
            ]);
            wp_die();
        }

         // Verify the nonce
        if (!wp_verify_nonce($nonce, 'my-ajax-nonce')) {
            wp_send_json_error([
                'message' => esc_html__('Invalid nonce', 'connect-plus-for-woocommerce'),
                'error' => esc_html__('Invalid nonce' , 'connect-plus-for-woocommerce'),
            ]);
            wp_die();
        }

        // Validate connect token
        if( esc_attr(get_option('olivery_connect_token')) == ''){
            wp_send_json_error([
                'message' =>  esc_html__('Not Valid', 'connect-plus-for-woocommerce'),
                'error' =>  esc_html__('Not Valid', 'connect-plus-for-woocommerce'),
                'error_link' => [
                    'link_ref'=>esc_url('https://www.youtube.com/watch?v=XQ0UGSanPTIr'), 
                    'link_text'=> esc_html__('Follow Instructions','connect-plus-for-woocommerce')],
            ]);
            wp_die();
        }

        // if already sent to oc and we have sequance number return it
        if ($cpfw_sequence = get_post_meta($cpfw_order_id, '_olivery_connect_sequence')) {
            wp_send_json_error([
                // Translators: $cpfw_sequence[0] is the sequence of the order from Connect plus API
                'message' => sprintf(esc_html__('Order Already sent to Olivery Connect Plus: [%s]', 'connect-plus-for-woocommerce'), $cpfw_sequence[0]),
                'sequence' => esc_html($cpfw_sequence[0]),
            ]);
            wp_die();
        }

        // send the order
        try {
            
            $response = (new CPFW_API())->cpfw_send_to_connect_plus($order);
        } catch (Exception $e) {
            wp_send_json_error([
                'message' => esc_html__('Error sending order to Olivery Connect', 'connect-plus-for-woocommerce'),
                'error' => esc_html($e->getMessage())
            ]);
            wp_die();
        }

        if (is_wp_error($response)) {
            wp_send_json_error([
                'message' => esc_html__('Order failed to be sent to Olivery Connect', 'connect-plus-for-woocommerce'),
                'error' => esc_html($response->get_error_message()) 
            ]);
            wp_die();
        }

        $res_body = json_decode($response['body'] ?? "");
        // handling unknow error 
        if (json_last_error() !== JSON_ERROR_NONE || empty($res_body)) {
            wp_send_json_error([
                'message' => esc_html__('Missing Billing Information', 'connect-plus-for-woocommerce'),
                'error'   => esc_html__('Missing Billing Information', 'connect-plus-for-woocommerce'),
            ]);
            wp_die();
        }
        
        // handling the error return from CPFW_API function cpfw_send_to_connect_plus
        if ((!empty($res_body->result->fail) && $res_body->result->message) || !empty($res_body->error->message)) {
            wp_send_json_error([
                'message' => esc_html($res_body->result->message),
                'error'=> esc_html($res_body->result->message)
            ]);
            wp_die();
        }

        // Success check
        if (($res_body->success)  && is_array($res_body->order_sequences) && !empty($res_body->order_references)) {
            
            update_post_meta($order->get_id(), '_olivery_connect_sequence', $res_body->order_sequences[0]);

            $order->add_order_note(
                // Translators: %sis the sequence of the order from Connect plus API
                sprintf(esc_html__('Order successfully sent to Olivery Connect. Sequence: %s', 'connect-plus-for-woocommerce'), $cpfw_sequence[0]),
                0,
                true);

            $order->update_status('wc-send-oc');

            $res_order_sequences = $res_body->order_sequences;
            wp_send_json_success([
                'message' => esc_html($res_order_sequences[0]),
            ]);
            wp_die(0);
        }

    
        $error_message  = $response['body'];
        wp_send_json_error([
            'message' => $error_message,
            'error' => $res_body ?? $error_message,
        ]);
        wp_die();
    }
}
