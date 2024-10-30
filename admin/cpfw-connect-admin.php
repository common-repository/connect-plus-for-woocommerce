<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link      https://website.olivery.app/
 * @since      1.0.0
 *
 * @package    Connect_Plus
 * @subpackage Connect_Plus/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Connect_Plus
 * @subpackage Connect_Plus/admin
 * @author     Olivery dev
 */
class CPFW_Admin
{

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      CPFW_Loader    $cpfw_loader    Maintains and registers all hooks for the plugin.
	 */
	protected $cpfw_loader;

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $cpfw_plugin_name    The ID of this plugin.
	 */
	private $cpfw_plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $cpfw_version    The current version of this plugin.
	 */
	private $cpfw_version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $cpfw_plugin_name       The name of this plugin.
	 * @param      string    $cpfw_version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{

		$this->cpfw_plugin_name = $plugin_name;
		$this->cpfw_version = $version;

		$this->cpfw_loader = new CPFW_Loader();
	}

	/**
	 * Run the loader to execute all of the hooks for WordPress Admin Area.
	 *
	 * @since    1.0.0
	 */
	public function cpfw_run()
	{
		$this->define_admin_actions();

		$this->define_admin_filters();

		$this->cpfw_loader->cpfw_run();
	}

	/**
	 * Register all of the actions related to the admin area functionality of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_actions()
	{
		// Add the custom script 
		$this->cpfw_loader->add_action('admin_enqueue_scripts', $this, 'cpfw_enqueue_styles');
		$this->cpfw_loader->add_action('admin_enqueue_scripts', $this, 'cpfw_enqueue_scripts');

		// Add the send button to order page.
		$this->cpfw_loader->add_action('woocommerce_admin_order_data_after_order_details', $this, 'cpfw_register_send_order_btn');

		// Register the Links and Pages for the admin area.
		$this->cpfw_loader->add_action('admin_menu', $this, 'cpfw_register_links_pages');

		// Register the plugin Options.
		$this->cpfw_loader->add_action('admin_init', $this, 'cpfw_register_options');

		// Add custom sequence column in orders table.
		$this->cpfw_loader->add_filter('manage_woocommerce_page_wc-orders_columns', $this, 'cpfw_add_sequence_column_to_orders_table');

		// Set the custom sequence column data in orders table.
		$this->cpfw_loader->add_action('woocommerce_shop_order_list_table_custom_column', $this, 'cpfw_add_sequence_column_inner_data_to_orders_table', 10, 2);

		// Register the bulk send button.
		$this->cpfw_loader->add_action('bulk_actions-edit-shop_order', $this, 'cpfw_register_bulk_send_btn');

		// Register the autosend action.
		if ($the_status =  esc_attr(get_option('olivery_connect_auto_send'))) {
			$this->cpfw_loader->add_action('woocommerce_order_status_' . str_replace('wc-', '', $the_status), $this, 'cpfw_handle_order_status_change');
			if ($the_status === 'wc-pending') {
				// Hook into the order creation for the initial status 
				$this->cpfw_loader->add_action('woocommerce_new_order', $this, 'cpfw_handle_initial_order_status');
			}
				
		}

	}
	/**    
	 * Register all of the filters related to the admin area functionality of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_filters()
	{
		// add olivery connect status in orders status
		$this->cpfw_loader->add_filter( 'woocommerce_register_shop_order_post_statuses', $this, 'cpfw_register_custom_order_status' );
		// add olivery connect in dropdown list
		$this->cpfw_loader->add_filter( 'wc_order_statuses', $this,'cpfw_show_custom_order_status_single_order_dropdown' );
		// Adding custom status to admin order list bulk actions dropdown
		$this->cpfw_loader->add_filter( 'bulk_actions-woocommerce_page_wc-orders',$this, 'cpfw_custom_dropdown_bulk_actions_shop_order', 20, 1 );
		// Adding custom status to admin order list bulk actions dropdown
		$this->cpfw_loader->add_filter( 'handle_bulk_actions-woocommerce_page_send-oc',$this, 'cpfw_custom_dropdown_bulk_actions_shop_order', 20, 1 );
		// Set settings Button Link of the plugin in plugins page.
		$this->cpfw_loader->add_filter('plugin_action_links_olivery-connect/olivery-connect.php', $this, 'cpfw_settings_link');
	}

	/**
	 * Set settings Button Link of the plugin in plugins page.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	public function cpfw_settings_link($links)
	{
		// Build and escape the URL.
		$url = esc_url(add_query_arg(
			'page',
			'connect-plus-for-woocommerce',
			get_admin_url() . 'admin.php'
		));
		// Create the link.
		$settings_link = "<a href='$url' aria-label='" . esc_html__('View Connect Plus settings', 'connect-plus-for-woocommerce') . "'>" . esc_html__('Settings', 'connect-plus-for-woocommerce') . "</a>";
		// Adds the link to the end of the array.
		array_push(
			$links,
			$settings_link
		);
		return $links;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function cpfw_enqueue_styles()
	{
		wp_enqueue_style($this->cpfw_plugin_name, plugin_dir_url(__FILE__) . 'css/cpfw-connect-admin.css', array(), $this->cpfw_version, 'all');
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function cpfw_enqueue_scripts()
	{
		wp_enqueue_script($this->cpfw_plugin_name, plugin_dir_url(__FILE__) . 'js/cpfw-connect-admin.js', array('jquery'), $this->cpfw_version, false);
		wp_localize_script($this->cpfw_plugin_name, 'olivery_ajax_object', array(
			'ajax_url' => admin_url('admin-ajax.php'),
			'texts' => [
				'confirm_send_to_oc' => esc_html__('Are you sure to send this/these order/s to Olivery Connect?', 'connect-plus-for-woocommerce'),
				'done_send_to_oc' => esc_html__('Done Send To Olivery Connect, check table for results.', 'connect-plus-for-woocommerce'),
				'error_send_to_oc' =>esc_html__('Error Send To Olivery Connect, check the filed.', 'connect-plus-for-woocommerce'),
				'auto_send_status' =>  esc_attr(get_option('olivery_connect_auto_send')),
			],
			'nonce'    => wp_create_nonce('my-ajax-nonce') // Generate a nonce
		));
	}

	/**
	 * Register the plugin Options.
	 *
	 * @since    1.0.0
	 */
	public function cpfw_register_options()
	{
		register_setting('olivery_options_group', 'olivery_connect_token', [
			'type' => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'default' => '',
		]);

		register_setting('olivery_options_group', 'olivery_connect_auto_send', [
			'type' => 'string',
			'sanitize_callback' => 'sanitize_text_field',
		]);

	}

	/**
	 * Register the Links and Pages for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function cpfw_register_links_pages()
	{
		add_menu_page(
			esc_html__('Connect Plus', 'connect-plus-for-woocommerce'),
			esc_html__('Connect Plus', 'connect-plus-for-woocommerce'),
			'manage_options',
			'connect-plus-for-woocommerce',
			[$this, 'cpfw_plugin_settings']
		);
	}

	/**
	 * Register  Settings Page for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function cpfw_plugin_settings()
	{
		require_once plugin_dir_path(dirname(__FILE__)) .  'admin/views/cpfw-connect-admin-settings.php';
	}

	/**
	 * Add the send button to order page.
	 *
	 * @since    1.0.0
	 */
	public function cpfw_register_send_order_btn($the_order)
	{
		require_once plugin_dir_path(dirname(__FILE__)) .  'admin/partials/cpfw-connect-admin-send-btn.php';
	}

	/**
	 * Add the sequence column to orders table.
	 *
	 * @since    1.0.0
	 */
	public function cpfw_add_sequence_column_to_orders_table($columns)
	{
		$columns['olivery_sequence_code'] = esc_html__('Olivery Sequence code', 'connect-plus-for-woocommerce');
		return $columns;
	}

	/**
	 * Add the sequence column to orders table.
	 *
	 * @since    1.0.0
	 */
	public function cpfw_add_sequence_column_inner_data_to_orders_table($column, $order)
	{
		if ($column === 'olivery_sequence_code') {
			$oc_sequence = get_post_meta($order->get_id(), '_olivery_connect_sequence');
			
			$class = empty($oc_sequence[0]) ? 'status-processing' : ' ';
			// DON'T EDIT THESE HTML CLASSES cause we depend on it in JS code to save broccess
			echo "<mark class='order-status " . esc_attr($class) . " tips' style='white-space: break-spaces;line-height: 2;'><span>" . esc_html($oc_sequence[0] ?? '') . "</span></mark>";
		}
	}

	/*
	 * Add the send button to order page.
	 *
	 * @since    1.0.0
	 */
	public function cpfw_register_bulk_send_btn($actions)
	{
		$actions['bulk_send_to_olivery'] = esc_html__('Send To Olivery', 'connect-plus-for-woocommerce');
		return $actions;
	}

	/*
	 * Add the send button to order page.
	 *
	 * @since    1.0.0
	 */
	public function cpfw_handle_order_status_change($order_id)
	{
		try {
			
			$order = wc_get_order($order_id);
			$response = (new CPFW_API())->cpfw_send_to_connect_plus($order);
			$res_body = json_decode($response['body'] ?? "");
			if($res_body->success){
				update_post_meta($order->get_id(), '_olivery_connect_sequence', $res_body->order_sequences[0]);
				$order->update_status('wc-send-oc');
			}

		} catch (Exception $e) {
			error_log($order_id . PHP_EOL . 'Error: ' . $e->getCode() . ': ' . $e->getMessage() . PHP_EOL, 3, __DIR__ . '/../logs/error.log');
		}

	}

	/*
	 * Handle initial order status when order is created spical case for pending.
	 *
	 * @since    1.0.0
	 */
	public function cpfw_handle_initial_order_status($order_id) {
		$order = wc_get_order($order_id);

		// Check if the initial order status matches the auto-send status
		if ($order->get_status() === 'pending') {
			$this->cpfw_handle_order_status_change($order_id);
		}
	}


	/*
	 * Register Connect Status.
	 *
	 * @since    1.0.0
	 */
	function cpfw_register_custom_order_status( $order_statuses ) {
	   // Status must start with "wc-"!
		$order_statuses['wc-send-oc'] = array(
			'label' => esc_html__('Sent to Olivery Connect Plus','connect-plus-for-woocommerce'),
			'public' => false,
			'exclude_from_search' => false,
			'show_in_admin_all_list' => true,
			'show_in_admin_status_list' => true,
			// Translators: %s is the number of items sent to Olivery Connect.
			'label_count' => _n_noop( 'Sent to Olivery Connect Plus <span class="count">(%s)</span>', 'Sent to Olivery Connect Plus <span class="count">(%s)</span>', 'connect-plus-for-woocommerce' ),
		);
		return $order_statuses;
	}

	/*
	 * Add olivery connect plus status in dropdown list.
	 *
	 * @since    1.0.0
	 */
	function cpfw_show_custom_order_status_single_order_dropdown( $order_statuses) {
		$order_statuses['wc-send-oc'] = esc_html__('Sent to Olivery Connect Plus','connect-plus-for-woocommerce');
		return $order_statuses;
	}
	
	/*
	 * Add custom status to admin order list bulk actions dropdown.
	 *
	 * @since    1.0.0
	 */
	function cpfw_custom_dropdown_bulk_actions_shop_order( $actions ) {
		$new_actions = array();
	
		// Add new custom order status after processing
		foreach ($actions as $key => $action) {
			$new_actions[$key] = $action;
			if ('mark_processing' === $key) {
				$new_actions['mark_send-oc'] = esc_html__( 'Send to Olivery Connect plus', 'connect-plus-for-woocommerce' );
			}
		}
	
		return $new_actions;
	}

}
