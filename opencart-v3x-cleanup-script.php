<?php
/**
 * OpenCart Database Truncation Script (DB_PREFIX aware)
 * Truncates catalog, order, and customer related tables
 * 
 * WARNING: This will permanently delete all data in specified tables. Use with extreme caution!
 * Recommended to backup database before running this script.
 */

// Load OpenCart configuration
require_once('config.php');

try {
    // Establish database connection
    $db = new mysqli(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE, DB_PORT);
    
    if ($db->connect_errno) {
        throw new Exception("Database connection failed: " . $db->connect_error);
    }

    // Define table bases (without prefix)
    $table_bases = [
        // Customer tables
        'customer',
        'customer_activity',
        'customer_approval',
        'customer_history',
        'customer_ip',
        'customer_login',
        'customer_online',
        'customer_reward',
        'customer_search',
        'customer_transaction',
        'customer_wishlist',

        // Order tables
        'order',
        'order_history',
        'order_option',
        'order_product',
        'order_recurring',
        'order_recurring_transaction',
        'order_subscription',
        'order_total',
        'order_voucher',
        'voucher',
        'voucher_history',
        'cart',

        // Catalog tables
        'product',
        'product_attribute',
        'product_description',
        'product_discount',
        'product_filter',
        'product_image',
        'product_option',
        'product_option_value',
        'product_recurring',
        'product_related',
        'product_reward',
        'product_special',
        'product_to_category',
        'product_to_download',
        'product_to_layout',
        'product_to_store',
        'product_viewed',
        'category',
        'category_description',
        'category_filter',
        'category_path',
        'category_to_layout',
        'category_to_store',
        'manufacturer',
        'manufacturer_to_store',
        'attribute',
        'attribute_description',
        'attribute_group',
        'attribute_group_description',
        'option',
        'option_description',
        'option_value',
        'option_value_description',
        'filter',
        'filter_description',
        'filter_group',
        'filter_group_description',
        'download',
        'download_description',
        'review',
        'url_alias',
        'coupon',
        'coupon_category',
        'coupon_product',
        'coupon_history',
        'product_report'
    ];

    // Disable foreign key checks
    $db->query('SET FOREIGN_KEY_CHECKS = 0');

    // Truncate tables with proper prefix
    foreach ($table_bases as $table_base) {
        $table = DB_PREFIX . $table_base;
        if (!$db->query("TRUNCATE TABLE `$table`")) {
            throw new Exception("Error truncating $table: " . $db->error);
        }
    }

    // Re-enable foreign key checks
    $db->query('SET FOREIGN_KEY_CHECKS = 1');

    echo "Successfully truncated all specified tables using prefix: '" . DB_PREFIX . "'\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
} finally {
    if (isset($db) && $db instanceof mysqli) {
        $db->close();
    }
}