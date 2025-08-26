<?php
/**
 * Unit test for bulk edit efforts functionality
 * Tests the bulk edit form display and parameter handling
 */

require_once(__DIR__ . '/../../bootstrap.php');

class BulkEditTest extends PHPUnit\Framework\TestCase {
    
    private $auth;
    private $customer;
    private $project;
    private $effort_ids;
    
    protected function setUp(): void {
        // Enable debug logging for this test
        $GLOBALS['_PJ_debug'] = true;
        
        // Create test auth object
        $this->auth = new PJAuth();
        
        // Create test customer
        $this->customer = new Customer($this->auth);
        $this->customer->data['customer_name'] = 'Test Customer for Bulk Edit';
        $this->customer->data['active'] = 'yes';
        $this->customer->save();
        
        // Create test project
        $this->project = new Project($this->customer, $this->auth);
        $this->project->data['project_name'] = 'Test Project for Bulk Edit';
        $this->project->data['customer_id'] = $this->customer->giveValue('id');
        $this->project->save();
        
        // Create test efforts
        $this->effort_ids = [];
        for($i = 0; $i < 3; $i++) {
            $effort = new Effort($this->customer, $this->auth);
            $effort->data['project_id'] = $this->project->giveValue('id');
            $effort->data['user'] = $this->auth->giveValue('id');
            $effort->data['description'] = "Test Effort $i for Bulk Edit";
            $effort->data['date'] = date('Y-m-d');
            $effort->data['begin'] = '09:00:00';
            $effort->data['end'] = '10:00:00';
            $effort->data['access'] = 'rw-r-----';
            $effort->save();
            $this->effort_ids[] = $effort->giveValue('id');
        }
        
        debugLog('BULK_EDIT_TEST', 'Test setup completed with effort IDs: ' . implode(', ', $this->effort_ids));
    }
    
    protected function tearDown(): void {
        // Clean up test data
        foreach($this->effort_ids as $eid) {
            $effort = new Effort($this->customer, $this->auth, $eid);
            if($effort->giveValue('id')) {
                $effort->delete();
            }
        }
        
        if($this->project && $this->project->giveValue('id')) {
            $this->project->delete();
        }
        
        if($this->customer && $this->customer->giveValue('id')) {
            $this->customer->delete();
        }
        
        debugLog('BULK_EDIT_TEST', 'Test cleanup completed');
    }
    
    public function testBulkEditFormGeneration() {
        debugLog('BULK_EDIT_TEST', 'Testing bulk edit form generation');
        
        // Simulate bulk edit request parameters
        $_REQUEST['bulk_edit'] = '1';
        $_REQUEST['effort_ids'] = $this->effort_ids;
        
        // Capture output from bulk edit form
        ob_start();
        
        // Set required variables for the template
        $customer = $this->customer;
        $_PJ_auth = $this->auth;
        
        // Prepare current values array (simulate what efforts.php would do)
        $current_values = [
            'access' => ['rw-r-----', 'rw-r-----', 'rw-r-----'],
            'billed' => ['Not billed', 'Not billed', 'Not billed'],
            'project_id' => [$this->project->giveValue('id'), $this->project->giveValue('id'), $this->project->giveValue('id')],
            'user' => [$this->auth->giveValue('id'), $this->auth->giveValue('id'), $this->auth->giveValue('id')],
            'rate' => ['0.00', '0.00', '0.00']
        ];
        
        // Include the bulk edit form template
        include(__DIR__ . '/../../templates/inventory/effort/bulk_edit_form.ihtml.php');
        
        $output = ob_get_clean();
        
        // Test that form contains expected elements
        $this->assertStringContainsString('bulk_edit_form', $output, 'Form should have bulk_edit_form name');
        $this->assertStringContainsString('Update access permissions', $output, 'Form should contain access permissions option');
        $this->assertStringContainsString('Update billing status', $output, 'Form should contain billing status option');
        $this->assertStringContainsString('Change project assignment', $output, 'Form should contain project assignment option');
        $this->assertStringContainsString('Change user assignment', $output, 'Form should contain user assignment option');
        $this->assertStringContainsString('Apply new hourly rate', $output, 'Form should contain rate override option');
        
        // Test that current values are displayed with larger font
        $this->assertStringContainsString('font-size: 14px', $output, 'Current values should have larger font size');
        $this->assertStringContainsString('Current values:', $output, 'Current values should be displayed');
        
        debugLog('BULK_EDIT_TEST', 'Form generation test passed');
        
        // Clean up request variables
        unset($_REQUEST['bulk_edit']);
        unset($_REQUEST['effort_ids']);
    }
    
    public function testProjectListConstructor() {
        debugLog('BULK_EDIT_TEST', 'Testing ProjectList constructor with proper parameters');
        
        // Test that ProjectList can be instantiated with correct parameters
        $projects = new ProjectList($this->customer, $this->auth);
        $this->assertInstanceOf('ProjectList', $projects, 'ProjectList should be instantiated successfully');
        
        debugLog('BULK_EDIT_TEST', 'ProjectList constructor test passed');
    }
    
    public function testBulkEditParameterValidation() {
        debugLog('BULK_EDIT_TEST', 'Testing bulk edit parameter validation');
        
        // Test with valid effort IDs
        $valid_ids = $this->effort_ids;
        $accessible_efforts = [];
        
        foreach($valid_ids as $eid) {
            $effort = new Effort($this->customer, $this->auth, $eid);
            if($effort->checkUserAccess('write')) {
                $accessible_efforts[] = $eid;
            }
        }
        
        $this->assertCount(3, $accessible_efforts, 'All test efforts should be accessible for writing');
        
        // Test with invalid effort ID
        $invalid_ids = ['999999'];
        $accessible_invalid = [];
        
        foreach($invalid_ids as $eid) {
            $effort = new Effort($this->customer, $this->auth, $eid);
            if($effort->checkUserAccess('write')) {
                $accessible_invalid[] = $eid;
            }
        }
        
        $this->assertCount(0, $accessible_invalid, 'Invalid effort IDs should not be accessible');
        
        debugLog('BULK_EDIT_TEST', 'Parameter validation test passed');
    }
}
