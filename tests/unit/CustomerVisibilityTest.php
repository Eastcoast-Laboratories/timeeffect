<?php
/**
 * Unit Test: Customer Visibility Investigation
 * 
 * This test investigates why customer ID 1 is not visible in the customer overview
 * at http://localhost/inventory/customer.php?list=1&cid=&pid=
 * 
 * Potential causes to investigate:
 * 1. ACL (Access Control List) restrictions
 * 2. Customer active status (active='no')
 * 3. User permissions and group membership
 * 4. Database integrity issues
 */

require_once(__DIR__ . '/../../bootstrap.php');
require_once(__DIR__ . '/../../include/config.inc.php');

class CustomerVisibilityTest {
    private $db;
    private $test_results = [];
    
    public function __construct() {
        $this->db = new Database();
        $this->db->connect();
    }
    
    /**
     * Run all diagnostic tests
     */
    public function runAllTests() {
        echo "<h2>🔍 Customer ID 1 Visibility Investigation</h2>\n";
        echo "<div style='font-family: monospace; background: #f8f9fa; padding: 15px; border-radius: 8px;'>\n";
        
        $this->testCustomerExists();
        $this->testCustomerActiveStatus();
        $this->testCustomerAccessPermissions();
        $this->testUserAuthentication();
        $this->testAclQuery();
        $this->testCustomerListQuery();
        $this->simulateCustomerListLoad();
        
        echo "</div>\n";
        $this->printSummary();
    }
    
    /**
     * Test 1: Check if customer ID 1 exists in database
     */
    private function testCustomerExists() {
        echo "<h3>📋 Test 1: Customer Existence Check</h3>\n";
        
        global $_PJ_customer_table;
        $query = "SELECT id, customer_name, active, user, gid, access FROM $_PJ_customer_table WHERE id = 1";
        
        debugLog("CUSTOMER_TEST", "Checking customer existence: $query");
        
        $this->db->query($query);
        if ($this->db->next_record()) {
            $customer_data = $this->db->Record;
            echo "✅ <strong>Customer ID 1 EXISTS</strong><br>\n";
            echo "   - Name: " . htmlspecialchars($customer_data['customer_name']) . "<br>\n";
            echo "   - Active: " . htmlspecialchars($customer_data['active']) . "<br>\n";
            echo "   - Owner User ID: " . htmlspecialchars($customer_data['user']) . "<br>\n";
            echo "   - Group ID: " . htmlspecialchars($customer_data['gid']) . "<br>\n";
            echo "   - Access: " . htmlspecialchars($customer_data['access']) . "<br>\n";
            
            $this->test_results['customer_exists'] = true;
            $this->test_results['customer_data'] = $customer_data;
        } else {
            echo "❌ <strong>Customer ID 1 NOT FOUND</strong><br>\n";
            $this->test_results['customer_exists'] = false;
        }
        echo "<br>\n";
    }
    
    /**
     * Test 2: Check customer active status
     */
    private function testCustomerActiveStatus() {
        echo "<h3>🔄 Test 2: Active Status Check</h3>\n";
        
        if (!$this->test_results['customer_exists']) {
            echo "⏭️ Skipped (customer doesn't exist)<br><br>\n";
            return;
        }
        
        $active_status = $this->test_results['customer_data']['active'];
        if ($active_status === 'yes') {
            echo "✅ <strong>Customer is ACTIVE</strong> (active='yes')<br>\n";
            $this->test_results['is_active'] = true;
        } else {
            echo "❌ <strong>Customer is INACTIVE</strong> (active='$active_status')<br>\n";
            echo "   🔍 <em>This could be why customer ID 1 is not visible!</em><br>\n";
            $this->test_results['is_active'] = false;
        }
        echo "<br>\n";
    }
    
    /**
     * Test 3: Check customer access permissions
     */
    private function testCustomerAccessPermissions() {
        echo "<h3>🔐 Test 3: Access Permissions Analysis</h3>\n";
        
        if (!$this->test_results['customer_exists']) {
            echo "⏭️ Skipped (customer doesn't exist)<br><br>\n";
            return;
        }
        
        $access = $this->test_results['customer_data']['access'];
        $owner_user = $this->test_results['customer_data']['user'];
        $group_id = $this->test_results['customer_data']['gid'];
        
        echo "Access String: <strong>$access</strong><br>\n";
        echo "Format: <code>rwxrwxrwx</code> (owner|group|world)<br>\n";
        
        // Parse access permissions
        $owner_read = substr($access, 0, 1) === 'r';
        $group_read = substr($access, 3, 1) === 'r';
        $world_read = substr($access, 6, 1) === 'r';
        
        echo "   - Owner (User $owner_user): " . ($owner_read ? "✅ READ" : "❌ NO READ") . "<br>\n";
        echo "   - Group ($group_id): " . ($group_read ? "✅ READ" : "❌ NO READ") . "<br>\n";
        echo "   - World: " . ($world_read ? "✅ READ" : "❌ NO READ") . "<br>\n";
        
        $this->test_results['access_permissions'] = [
            'owner_read' => $owner_read,
            'group_read' => $group_read,
            'world_read' => $world_read,
            'owner_user' => $owner_user,
            'group_id' => $group_id
        ];
        echo "<br>\n";
    }
    
    /**
     * Test 4: Check current user authentication
     */
    private function testUserAuthentication() {
        echo "<h3>👤 Test 4: Current User Authentication</h3>\n";
        
        global $_PJ_auth;
        
        if (!$_PJ_auth || !$_PJ_auth->giveValue('id')) {
            echo "❌ <strong>NO USER AUTHENTICATED</strong><br>\n";
            echo "   🔍 <em>This could be why no customers are visible!</em><br>\n";
            $this->test_results['user_authenticated'] = false;
            echo "<br>\n";
            return;
        }
        
        $user_id = $_PJ_auth->giveValue('id');
        $user_gids = $_PJ_auth->giveValue('gids');
        $is_admin = $_PJ_auth->checkPermission('admin');
        
        echo "✅ <strong>User authenticated</strong><br>\n";
        echo "   - User ID: $user_id<br>\n";
        echo "   - Group IDs: $user_gids<br>\n";
        echo "   - Is Admin: " . ($is_admin ? "YES" : "NO") . "<br>\n";
        
        $this->test_results['user_authenticated'] = true;
        $this->test_results['current_user'] = [
            'id' => $user_id,
            'gids' => $user_gids,
            'is_admin' => $is_admin
        ];
        echo "<br>\n";
    }
    
    /**
     * Test 5: Test ACL query generation
     */
    private function testAclQuery() {
        echo "<h3>🛡️ Test 5: ACL Query Analysis</h3>\n";
        
        global $_PJ_auth;
        
        if (!$this->test_results['user_authenticated']) {
            echo "⏭️ Skipped (no user authenticated)<br><br>\n";
            return;
        }
        
        // Test ACL query generation
        $acl_query = buildCustomerAclQuery($_PJ_auth);
        
        echo "Generated ACL Query: <code>" . htmlspecialchars($acl_query) . "</code><br>\n";
        
        if (empty($acl_query)) {
            echo "✅ <strong>Empty ACL query</strong> (user is admin - no restrictions)<br>\n";
        } else {
            echo "🔍 <strong>ACL restrictions applied</strong><br>\n";
        }
        
        $this->test_results['acl_query'] = $acl_query;
        echo "<br>\n";
    }
    
    /**
     * Test 6: Test complete CustomerList query
     */
    private function testCustomerListQuery() {
        echo "<h3>📊 Test 6: CustomerList Query Simulation</h3>\n";
        
        global $_PJ_customer_table, $_PJ_auth;
        
        if (!$this->test_results['user_authenticated']) {
            echo "⏭️ Skipped (no user authenticated)<br><br>\n";
            return;
        }
        
        // Simulate CustomerList query
        $query = "SELECT * FROM $_PJ_customer_table WHERE active='yes'";
        $access_query = buildCustomerAclQuery($_PJ_auth);
        $query .= $access_query;
        $query .= " ORDER BY customer_name";
        
        echo "Complete Query: <code>" . htmlspecialchars($query) . "</code><br>\n";
        
        debugLog("CUSTOMER_TEST", "CustomerList simulation query: $query");
        
        $this->db->query($query);
        $found_customers = [];
        $customer_1_found = false;
        
        while ($this->db->next_record()) {
            $customer_id = $this->db->Record['id'];
            $customer_name = $this->db->Record['customer_name'];
            $found_customers[] = "ID $customer_id: " . htmlspecialchars($customer_name);
            
            if ($customer_id == 1) {
                $customer_1_found = true;
            }
        }
        
        echo "Found " . count($found_customers) . " customers:<br>\n";
        foreach ($found_customers as $customer_info) {
            echo "   - $customer_info<br>\n";
        }
        
        if ($customer_1_found) {
            echo "✅ <strong>Customer ID 1 IS VISIBLE</strong> in query results<br>\n";
        } else {
            echo "❌ <strong>Customer ID 1 NOT VISIBLE</strong> in query results<br>\n";
        }
        
        $this->test_results['customer_1_in_list'] = $customer_1_found;
        $this->test_results['total_customers_found'] = count($found_customers);
        echo "<br>\n";
    }
    
    /**
     * Test 7: Simulate actual CustomerList object creation
     */
    private function simulateCustomerListLoad() {
        echo "<h3>🏗️ Test 7: CustomerList Object Simulation</h3>\n";
        
        global $_PJ_auth;
        
        if (!$this->test_results['user_authenticated']) {
            echo "⏭️ Skipped (no user authenticated)<br><br>\n";
            return;
        }
        
        try {
            // Enable debug logging for this test
            $GLOBALS['_PJ_debug'] = true;
            
            echo "Creating CustomerList object...<br>\n";
            $customer_list = new CustomerList($_PJ_auth);
            
            echo "CustomerList created successfully<br>\n";
            echo "Total customers in list: " . $customer_list->customer_count . "<br>\n";
            
            // Check if customer ID 1 is in the list
            $customer_1_found = false;
            $customer_list->customer_cursor = -1; // Reset cursor
            
            while ($customer_list->nextCustomer()) {
                $customer = $customer_list->giveCustomer();
                $customer_id = $customer->giveValue('id');
                $customer_name = $customer->giveValue('customer_name');
                
                echo "   - Customer ID $customer_id: " . htmlspecialchars($customer_name) . "<br>\n";
                
                if ($customer_id == 1) {
                    $customer_1_found = true;
                }
            }
            
            if ($customer_1_found) {
                echo "✅ <strong>Customer ID 1 FOUND</strong> in CustomerList object<br>\n";
            } else {
                echo "❌ <strong>Customer ID 1 NOT FOUND</strong> in CustomerList object<br>\n";
            }
            
            $this->test_results['customerlist_object_works'] = true;
            $this->test_results['customer_1_in_object'] = $customer_1_found;
            
        } catch (Exception $e) {
            echo "❌ <strong>Error creating CustomerList:</strong> " . htmlspecialchars($e->getMessage()) . "<br>\n";
            $this->test_results['customerlist_object_works'] = false;
        }
        echo "<br>\n";
    }
    
    /**
     * Print diagnostic summary
     */
    private function printSummary() {
        echo "<h2>📋 Diagnostic Summary</h2>\n";
        echo "<div style='background: #e9ecef; padding: 15px; border-radius: 8px; font-family: monospace;'>\n";
        
        if (!$this->test_results['customer_exists']) {
            echo "🔴 <strong>ROOT CAUSE: Customer ID 1 does not exist in database</strong><br>\n";
            echo "   → Solution: Create customer ID 1 or check if it was deleted<br>\n";
        } elseif (!$this->test_results['is_active']) {
            echo "🔴 <strong>ROOT CAUSE: Customer ID 1 is inactive (active='no')</strong><br>\n";
            echo "   → Solution: Set customer ID 1 to active='yes' in database<br>\n";
        } elseif (!$this->test_results['user_authenticated']) {
            echo "🔴 <strong>ROOT CAUSE: No user is authenticated</strong><br>\n";
            echo "   → Solution: Ensure user login is working properly<br>\n";
        } elseif (isset($this->test_results['customer_1_in_list']) && !$this->test_results['customer_1_in_list']) {
            echo "🔴 <strong>ROOT CAUSE: ACL restrictions prevent access to customer ID 1</strong><br>\n";
            echo "   → Current user does not have read permissions for customer ID 1<br>\n";
            
            if (isset($this->test_results['access_permissions'])) {
                $perms = $this->test_results['access_permissions'];
                $current_user = $this->test_results['current_user'];
                
                echo "   → Customer owner: User {$perms['owner_user']}<br>\n";
                echo "   → Customer group: {$perms['group_id']}<br>\n";
                echo "   → Current user: {$current_user['id']} (groups: {$current_user['gids']})<br>\n";
                
                if (!$current_user['is_admin']) {
                    echo "   → Solution: Make user admin OR add user to customer's group OR set world-readable permissions<br>\n";
                }
            }
        } else {
            echo "🟢 <strong>Customer ID 1 should be visible - check browser/session issues</strong><br>\n";
        }
        
        echo "</div>\n";
    }
}

// Run the test if accessed directly
if (basename($_SERVER['PHP_SELF']) === 'CustomerVisibilityTest.php') {
    echo "<!DOCTYPE html>\n<html><head><title>Customer Visibility Test</title></head><body>\n";
    
    $test = new CustomerVisibilityTest();
    $test->runAllTests();
    
    echo "</body></html>\n";
}
