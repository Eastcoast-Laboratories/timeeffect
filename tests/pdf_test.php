<?php
// Simple test to identify headers issue in PDF generation
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Starting PDF test...\n";

// Test 1: Check if bootstrap loads without output
ob_start();
require_once(__DIR__ . "/../bootstrap.php");
$bootstrap_output = ob_get_contents();
ob_end_clean();

if (!empty($bootstrap_output)) {
    echo "ISSUE: Bootstrap produces output: " . var_export($bootstrap_output, true) . "\n";
} else {
    echo "OK: Bootstrap loads cleanly\n";
}

// Test 2: Check config loading
ob_start();
include_once(__DIR__ . "/../include/config.inc.php");
$config_output = ob_get_contents();
ob_end_clean();

if (!empty($config_output)) {
    echo "ISSUE: Config produces output: " . var_export($config_output, true) . "\n";
} else {
    echo "OK: Config loads cleanly\n";
}

// Test 3: Check scripts.inc.php
ob_start();
include_once($_PJ_include_path . '/scripts.inc.php');
$scripts_output = ob_get_contents();
ob_end_clean();

if (!empty($scripts_output)) {
    echo "ISSUE: Scripts produces output: " . var_export($scripts_output, true) . "\n";
} else {
    echo "OK: Scripts loads cleanly\n";
}

// Test 4: Check class loading
ob_start();
require_once(__DIR__ . '/../include/invoice.class.php');
require_once(__DIR__ . '/../include/pdf_generator.class.php');
$classes_output = ob_get_contents();
ob_end_clean();

if (!empty($classes_output)) {
    echo "ISSUE: Classes produce output: " . var_export($classes_output, true) . "\n";
} else {
    echo "OK: Classes load cleanly\n";
}

echo "Test completed.\n";
