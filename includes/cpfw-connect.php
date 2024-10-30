<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://website.olivery.app/
 * @since      1.0.0
 *
 * @package    Connect_Plus
 * @subpackage Connect_Plus/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Connect_Plus
 * @subpackage Connect_Plus/includes
 * @author     Olivery dev
 */
class CPFW_Connect
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
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $cpfw_plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $cpfw_plugin_name;

	/**
	 * The current cpfw_version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $cpfw_version    The current version of the plugin.
	 */
	protected $cpfw_version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct()
	{
		if (defined('CPFW_VERSION')) {
			$this->cpfw_version = CPFW_VERSION;
		} else {
			$this->cpfw_version = '1.0.0';
		}
		$this->cpfw_plugin_name = 'connect-plus-for-woocommerce';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_endpoints_hooks();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - cpfw_loader. Orchestrates the hooks of the plugin.
	 * - CPFW_Admin. Defines all hooks for the admin area.
	 * - CPFW_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the cpfw_loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies()
	{

		/**
		 * The core plugin class that is used to deal with Connect plus API Service calls.
		 */
		require plugin_dir_path(__FILE__) . 'services/cpfw-connect-api.php';

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/cpfw-connect-loader.php';

		/**
		 * The class responsible for defining all actions that occur in the endpoints area.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'endpoints/cpfw-connect-endpoints.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/cpfw-connect-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/cpfw-connect-public.php';

		$this->cpfw_loader = new CPFW_loader();
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain()
	{

		load_plugin_textdomain(
			'connect-plus-for-woocommerce',
			false,
			dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
		);
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the load_plugin_textdomain function in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale()
	{

		$this->cpfw_loader->add_action('plugins_loaded', $this, 'load_plugin_textdomain');
	}

	/**
	 * Register all of the hooks related to the endpoints functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_endpoints_hooks()
	{

		$cpfw_plugin_admin = new CPFW_Endpoints($this->get_cpfw_plugin_name(), $this->get_cpfw_version());

		$cpfw_plugin_admin->cpfw_run();
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks()
	{

		$plugin_admin = new CPFW_Admin($this->get_cpfw_plugin_name(), $this->get_cpfw_version());

		$plugin_admin->cpfw_run();
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks()
	{

		$plugin_public = new CPFW_Public($this->get_cpfw_plugin_name(), $this->get_cpfw_version());

		$plugin_public->cpfw_run();
	}

	/**
	 * Run the cpfw_loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function cpfw_run()
	{
		$this->cpfw_loader->cpfw_run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_cpfw_plugin_name()
	{
		return $this->cpfw_plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    CPFW_loader    Orchestrates the hooks of the plugin.
	 */
	public function get_cpfw_loader()
	{
		return $this->cpfw_loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_cpfw_version()
	{
		return $this->cpfw_version;
	}
}
