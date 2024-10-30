<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://website.olivery.app/
 * @since      1.0.0
 *
 * @package    Connect_Plus
 * @subpackage Connect_Plus/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Connect_Plus
 * @subpackage Connect_Plus/public
 * @author     Olivery dev
 */
class CPFW_Public {

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
	 * @param      string    $cpfw_plugin_name       The name of the plugin.
	 * @param      string    $cpfw_version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{

		$this->cpfw_plugin_name = $plugin_name;
		$this->cpfw_version = $version;

		$this->cpfw_loader = new CPFW_Loader();
	}

	/**
	 * Run the loader to execute all of the hooks for WordPress Public Area.
	 *
	 * @since    1.0.0
	 */
	public function cpfw_run()
	{
		$this->define_public_actions();

		$this->define_public_filters();

		$this->cpfw_loader->cpfw_run();
	}

	/**
	 * Register all of the actions related to the public area functionality of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_actions()
	{
		$this->cpfw_loader->add_action('wp_enqueue_scripts', $this, 'enqueue_styles');
		$this->cpfw_loader->add_action('wp_enqueue_scripts', $this, 'enqueue_scripts');
	}

	/**
	 * Register all of the filters related to the public area functionality of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_filters()
	{
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{
		
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{
		
	}
}
