<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://website.olivery.app/
 * @since             1.0.0
 * @package           Connect_Plus
 *
 * @wordpress-plugin
 * Plugin Name:       Connect Plus for WooCommerce
 * Plugin URI:        https://website.olivery.app/
 * Description:       Plugin to help you connect WooCommerce with Connect Plus application
 * Version:           1.0.9
 * Author:            Olivery dev
 * Text Domain:       connect-plus-for-woocommerce
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Domain Path:       /
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 */
    
define('CPFW_VERSION', '1.0.0');

/* Define the plugin directory */
define('CPFW_PLUGIN_DIR', __DIR__);

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/cpfw-connect-toggler.php
 */
function cpfw_activate()
{
    require_once plugin_dir_path(__FILE__) . 'includes/cpfw-connect-toggler.php';
    CPFW_Connect_Toggler::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/cpfw-connect-toggler.php
 */
function cpfw_deactivate()
{
    require_once plugin_dir_path(__FILE__) . 'includes/cpfw-connect-toggler.php';
    CPFW_Connect_Toggler::deactivate();
}

register_activation_hook(__FILE__, 'cpfw_activate');
register_deactivation_hook(__FILE__, 'cpfw_deactivate');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/cpfw-connect.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function cpfw_run()
{

    $plugin = new CPFW_Connect();
    $plugin->cpfw_run();
}
cpfw_run();
