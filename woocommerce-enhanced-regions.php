<?php
/**
 * Plugin Name: WooCommerce Enhanced Regions
 * Description: Populates missing region data for WooCommerce from individual country files in languages directory.
 * Version: 1.0.0
 * URL: https://github.com/ssthormess/woocommerce-enhanced-regions
 * Author: Simon Sthormes
 * Author URL: https://github.com/ssthormess
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Load plugin text domain
add_action('plugins_loaded', function () {
    load_plugin_textdomain('woocommerce-enhanced-regions', false, dirname(plugin_basename(__FILE__)) . '/languages');
});

// Only run if WooCommerce is active
if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    add_filter('woocommerce_states', function ($states) {
        // Directory containing country-specific region files
        // Bundled inside the plugin for portability
        $regions_dir = plugin_dir_path(__FILE__) . 'regions/';
    
        if (!is_dir($regions_dir)) {
            error_log("Woo Regions Plugin: Directory not found: " . $regions_dir);
            return $states;
        } else {
             // error_log("Woo Regions Plugin: Scanning " . $regions_dir);
        }
    
        // Get all files in the directory
        $files = glob($regions_dir . '*.php');
    
        if ($files) {
            foreach ($files as $file) {
                $country_code = basename($file, '.php');
                
                // Safety check: ensure file is readable
                if (!is_readable($file)) {
                    continue;
                }

                $country_states = include $file;
                
                // error_log("Checking $country_code");

                // Only add if WooCommerce doesn't have data for this country
                if (empty($states[$country_code])) {
                     // error_log("Adding states for $country_code");
                     $states[$country_code] = $country_states;
                } else {
                     // error_log("Skipping $country_code - already has data");
                }
            }
        }
    
        return $states;
    }, 100);
}
