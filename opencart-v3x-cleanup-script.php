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

$tables_exist = [];
$missing_tables = [];
$executed = false;
$error = null;

// Define table bases (without prefix)
$table_bases = [
    // Log tables
    'log_request',

    // Customer tables
    'address', 'customer', 'customer_activity', 'customer_approval', 'customer_history',
    'customer_ip', 'customer_login', 'customer_online', 'customer_reward',
    'customer_search', 'customer_transaction', 'customer_wishlist',

    // Order tables
    'order', 'order_history', 'order_option', 'order_product', 'order_recurring',
    'order_recurring_transaction', 'order_subscription', 'order_total', 'order_voucher',
    'voucher', 'voucher_history', 'cart',

    // Catalog tables
    'product', 'product_attribute', 'product_description', 'product_discount',
    'product_filter', 'product_image', 'product_option', 'product_option_value',
    'product_recurring', 'product_related', 'product_reward', 'product_special',
    'product_to_category', 'product_to_download', 'product_to_layout', 'product_to_store',
    'product_viewed', 'category', 'category_description', 'category_filter',
    'category_path', 'category_to_layout', 'category_to_store', 'manufacturer', 'manufacturer_description',
    'manufacturer_to_store', 'attribute', 'attribute_description', 'attribute_group',
    'attribute_group_description', 'option', 'option_description', 'option_value',
    'option_value_description', 'filter', 'filter_description', 'filter_group',
    'filter_group_description', 'download', 'download_description', 'review',
    'url_alias', 'seo_url', 'coupon', 'coupon_category', 'coupon_product', 'coupon_history',
    'product_report',

    // iSenseLabs GDPR
    'isense_gdpr_deletions', 'isense_gdpr_optins', 'isense_gdpr_policies', 'isense_gdpr_policy_acceptances',
    'isense_gdpr_requests', 'isense_gdpr_submissions',

    // Custom TABLES
    'pricelist_product'
];

try {
    $db = new mysqli(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE, DB_PORT);
    if ($db->connect_errno) {
        throw new Exception("Database connection failed: " . $db->connect_error);
    }

    // Check table existence
    foreach ($table_bases as $table_base) {
        $table = DB_PREFIX . $table_base;
        $result = $db->query("SHOW TABLES LIKE '" . $db->real_escape_string($table) . "'");
        
        if ($result->num_rows > 0) {
            $tables_exist[] = $table;
        } else {
            $missing_tables[] = $table;
        }
    }

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm'])) {
        $db->query('SET FOREIGN_KEY_CHECKS = 0');
        
        foreach ($tables_exist as $table) {
            if (!$db->query("TRUNCATE TABLE `$table`")) {
                throw new Exception("Error truncating $table: " . $db->error);
            }
        }
        
        $db->query('SET FOREIGN_KEY_CHECKS = 1');
        $executed = true;
    }

    $db->close();

} catch (Exception $e) {
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>OpenCart Database Truncation Script</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 20px auto; padding: 20px; }
        .warning { background: #fff3cd; border: 1px solid #ffeeba; padding: 15px; margin: 20px 0; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; }
        .error { background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; }
        .tables { display: flex; gap: 30px; margin: 20px 0; }
        ul { list-style: none; padding: 0; margin: 0; }
        li { padding: 3px 0; }
        button { background: #dc3545; color: white; padding: 10px 20px; border: none; cursor: pointer; }
        button:hover { background: #c82333; }
        .count { font-size: 0.9em; color: #666; }
    </style>
    <script>
        function validateForm() {
            const confirmation1 = confirm("LAST WARNING: This will DELETE ALL DATA!\n\nProceed?");
            if (!confirmation1) return false;
            
            const confirmation2 = confirm("FINAL CONFIRMATION:\nThis will PERMANENTLY DELETE:\n- All products\n- All orders\n- All customer data\n\nType 'DELETE' to confirm:");
            if (confirmation2) {
                const answer = prompt("Type DELETE in uppercase to confirm:");
                return answer === 'DELETE';
            }
            return false;
        }
    </script>
</head>
<body>
    <h1>OpenCart Data Cleanup Utility</h1>
    
    <div class="warning">
        <h3>⚠️ Extreme Danger!</h3>
        <p>This script will <strong>permanently delete</strong>:</p>
        <ul>
            <li>• All products, categories and manufacturers</li>
            <li>• Every customer account and order history</li>
            <li>• All coupons, vouchers and shopping carts</li>
            <li>• Product reviews and customer activities</li>
            <li>• iSenseLabs GDPR</li>
            <li>• Custom TABLES</li>
        </ul>
        <p><strong>⚠️ Warning:</strong> This action cannot be undone! Ensure you have:</p>
        <ol>
            <li>1. Made a complete database backup</li>
            <li>2. Tested on a development environment</li>
            <li>3. Closed the store to public access</li>
        </ol>
    </div>

    <?php if ($error): ?>
        <div class="error">
            <h3>❌ Operation Failed</h3>
            <p><?php echo htmlspecialchars($error) ?></p>
        </div>
    <?php elseif ($executed): ?>
        <div class="success">
            <h3>✅ Truncation Complete</h3>
            <p>Successfully cleared <?php echo count($tables_exist) ?> tables</p>
            <?php if ($missing_tables): ?>
                <p><?php echo count($missing_tables) ?> tables not found (see below)</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="tables">
        <div>
            <h3>Tables Found <span class="count">(<?php echo count($tables_exist) ?>)</span></h3>
            <ul>
                <?php foreach ($tables_exist as $table): ?>
                    <li>• <?php echo htmlspecialchars($table) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        
        <?php if ($missing_tables): ?>
        <div>
            <h3>Tables Not Found <span class="count">(<?php echo count($missing_tables) ?>)</span></h3>
            <ul>
                <?php foreach ($missing_tables as $table): ?>
                    <li>• <?php echo htmlspecialchars($table) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
    </div>

    <?php if (!$executed): ?>
    <form method="post" onsubmit="return validateForm()">
        <input type="hidden" name="confirm" value="1">
        <p>
            <button type="submit">EXECUTE DATA DESTRUCTION</button>
            <br>
            <small>Requires three confirmations</small>
        </p>
    </form>
    <?php endif; ?>

    <div class="warning">
        <h3>🔒 Security Recommendations</h3>
        <ol>
            <li>1. Delete this file after use</li>
            <li>2. Password protect this directory</li>
            <li>3. Restrict access by IP address</li>
            <li>4. Keep database backups offline</li>
        </ol>
    </div>
</body>
</html>
