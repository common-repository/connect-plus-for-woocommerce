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

$cpfw_sequence = get_post_meta($the_order->get_id(), '_olivery_connect_sequence');

?>

<div class="form-field form-field-wide wc-olivery-connect olivery-connect" style='margin: 25px 0 5px;'>
    <p class="send-to-olivery-status button-secondary" style='padding: 0 7px !important;white-space: normal;<?php echo empty($cpfw_sequence) ? 'display: none;' : ''; ?>'>
        <?php esc_html_e('Already Sent to Olivery Connect Plus', 'connect-plus-for-woocommerce'); ?>
        : <b class="text-success" style="color: #00b300;white-space: nowrap"><?php echo esc_html($cpfw_sequence[0] ?? ''); ?></b>
    </p>

    <a class="send-to-olivery-status button-primary" style="<?php echo !empty($cpfw_sequence) ? 'display: none;' : ''; ?>" onclick="document.getElementById('modal-urls').style.display='table-cell'">
        <?php esc_html_e('Send this order to Olivery Connect Plus', 'connect-plus-for-woocommerce'); ?>
    </a>

    <?php if (empty($cpfw_sequence)) : ?>
        <div id="modal-urls">
            <div class="olivery-modal modal-content">
                <p><?php echo esc_html(__('Are you sure to send this/these order/s to Olivery Connect Plus ?', 'connect-plus-for-woocommerce')); ?></p>
                <div class="alert" style="display: none"></div>
                <a class="button-primary modalBtn" id="submit_to_olivery" data-text="<?php echo esc_attr(__('Send', 'oconnect-plus-for-woocommerce')); ?>" data-sending="<?php echo esc_attr(__('Sending...', 'connect-plus-for-woocommerce')); ?>" data-id="<?php echo esc_attr($the_order->get_id()); ?>">
                    <?php echo esc_html(__('Send', 'connect-plus-for-woocommerce')); ?>
                </a>
                <a class="button modalBtn" onclick="document.getElementById('modal-urls').style.display='none';">
                    <?php echo esc_html(__('Cancel', 'connect-plus-for-woocommerce')); ?>
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>
