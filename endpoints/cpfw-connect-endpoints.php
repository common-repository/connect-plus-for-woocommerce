<?php

/**
 * The endpoints-specific functionality of the plugin.
 *
 * @link       https://website.olivery.app/
 * @since      1.0.0
 *
 * @package    Connect_Plus
 * @subpackage Connect_Plus/endpoints
 */

/**
 * The endpoints-specific functionality of the plugin.
 * 
 *
 * @package    Connect_Plus
 * @subpackage Connect_Plus/endpoints
 * @author     Olivery dev
 */
class CPFW_Endpoints
{

	/**
	 * The cpfw_loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      CPFW_loader    $cpfw_loader    Maintains and registers all hooks for the plugin.
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
	 * The cpfw_version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $cpfw_version    The current cpfw_version of this plugin.
	 */
	private $cpfw_version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $cpfw_plugin_name       The name of this plugin.
	 * @param      string    $cpfw_version    The cpfw_version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{

		$this->cpfw_plugin_name = $plugin_name;
		$this->cpfw_version = $version;

		$this->cpfw_loader = new CPFW_loader();
	}

	/**
	 * Run the cpfw_loader to execute all of the hooks for WordPress endpoints Area.
	 *
	 * @since    1.0.0
	 */
	public function cpfw_run()
	{
		$this->cpfw_define_endpoints_ajax();

		$this->cpfw_define_endpoints_restapi();

		$this->cpfw_loader->cpfw_run();
	}

	/**
	 * Register all of the actions related to the endpoints area functionality of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function cpfw_define_endpoints_ajax()
	{
		require_once __DIR__ .  '/cpfw-connect-ajax-wrapper.php';

		$cpfw_ajax_wrapper = new CPFW_Ajax_Wrapper($this->cpfw_plugin_name, $this->cpfw_version);

		$this->cpfw_loader->add_action('wp_ajax_cpfw_send_order_to_oc', $cpfw_ajax_wrapper, 'cpfw_send_order_to_oc');
	}

	/**
	 * Register all of the filters related to the endpoints area functionality of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function cpfw_define_endpoints_restapi()
	{
	}
}
