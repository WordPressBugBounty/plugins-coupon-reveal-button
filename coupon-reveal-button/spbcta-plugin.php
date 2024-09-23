<?php
/*
Plugin Name: Coupon & Discount Code Reveal Button
Description: CTA Buttons that reveal text on click. Made for affiliate & PPC websites. Reveal voucher & bonus codes. Responsive & SEO Optimized
Version: 1.2.7
Author: SuPlugins
License: GPL2
*/

if (!defined('WPINC')) {
    die;
}

require_once plugin_dir_path(__FILE__) . 'inc/spbcta-plugin.php';

function spbcta_run_plugin()
{
    $plugin_instance = new spbcta_Plugin('1.2.7', false, plugin_dir_path(__FILE__));
    register_activation_hook(__FILE__, array($plugin_instance, 'spbcta_initialize'));
    //register_uninstall_hook( __FILE__, array('spbcta_Plugin', 'spbcta_rollback') );
}

spbcta_run_plugin();

function spbcta_add_plugin_meta_links($meta_fields, $file)
{
    if (plugin_basename(__FILE__) == $file) {
        $meta_fields[] = "<a href='" . admin_url('admin.php?page=spbcta_plugin') . "'>Create Reveal Button</a>";
    }

    return $meta_fields;
}
add_filter("plugin_row_meta", 'spbcta_add_plugin_meta_links', 10, 2);
