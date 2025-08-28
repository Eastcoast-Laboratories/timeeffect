<?php
/**
 * MySQL Query Executor for TimeEffect Development
 * Usage: php dev/mysql.php "DESCRIBE te_invoice_payments"
 */

// Skip authentication for dev tools
$no_login = true;

require_once(__DIR__ . "/../include/config.inc.php");
require_once(__DIR__ . "/../include/db_mysql.inc.php");

// Get SQL from command line argument
if ($argc < 2) {
    echo "Usage: php dev/mysql.php \"SQL_QUERY\"\n";
    echo "Example: php dev/mysql.php \"DESCRIBE te_invoice_payments\"\n";
    exit(1);
}

$sql = $argv[1];

// Initialize database connection
$db = new DB_Sql();
$db->connect();

echo "Executing SQL: $sql\n";
echo "=====================================\n";

try {
    $result = $db->query($sql);
    
    if ($db->Errno) {
        echo "❌ Error: " . $db->Error . "\n";
        exit(1);
    }
    
    // Check if it's a SELECT query
    if (stripos(trim($sql), 'SELECT') === 0 || stripos(trim($sql), 'DESCRIBE') === 0 || stripos(trim($sql), 'SHOW') === 0) {
        $row_count = 0;
        while ($db->next_record()) {
            $row_count++;
            if ($row_count === 1) {
                // Print headers
                $fields = array_keys($db->Record);
                echo implode("\t", $fields) . "\n";
                echo str_repeat("-", 50) . "\n";
            }
            echo implode("\t", $db->Record) . "\n";
        }
        
        if ($row_count === 0) {
            echo "No rows returned.\n";
        } else {
            echo "\nTotal rows: $row_count\n";
        }
    } else {
        echo "✅ Query executed successfully\n";
        if (method_exists($db, 'affected_rows')) {
            echo "Affected rows: " . $db->affected_rows() . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
    exit(1);
}

echo "✅ Query completed\n";
?>
