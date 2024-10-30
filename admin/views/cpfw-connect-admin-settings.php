<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://website.olivery.app/
 * @since      1.0.0
 *
 * @package    Connect_Plus
 * @subpackage Connect_Plus/admin/views
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if (!current_user_can('manage_options')) {
    wp_die(esc_html('You do not have sufficient permissions to access this page.'));
}

?>

<h1><?php esc_html_e('Connect Plus integration Settings', 'connect-plus-for-woocommerce'); ?></h1>

<form method="post" action="options.php" id="olivery-connect-admin-settings" class="olivery-connect">

    <?php
    settings_fields('olivery_options_group');

    do_settings_sections('olivery_options_group');
    ?>

    <table class='options-table'>
        <tr>
            <th><?php esc_html_e('Olivery Token', 'connect-plus-for-woocommerce'); ?></th>
            <td><input type="text" name='olivery_connect_token' id="olivery_connect_token" width="200%" placeholder="Ecommerce Token" value="<?php echo esc_attr(get_option('olivery_connect_token')); ?>"></td>
        </tr>
        
        <tr>
            <th><?php esc_html_e('Auto send when', 'connect-plus-for-woocommerce'); ?></th>
            <td>
                <select name="olivery_connect_auto_send">
                    <option value=""><?php esc_attr_e('Disable', 'connect-plus-for-woocommerce'); ?></option>
                    <?php
                    $order_statuses = wc_get_order_statuses();
                    $current_status =  esc_attr(get_option('olivery_connect_auto_send'));

                    foreach ($order_statuses as $status => $label) {
                        echo '<option ' . ($current_status == $status ? 'selected' : '')  . ' value="' . esc_attr($status) . '">' . esc_html($label) . '</option>';
                    }
                    ?>
                </select>
            </td>
        </tr>
    </table>

    <?php
        submit_button();
    ?>

</form>
