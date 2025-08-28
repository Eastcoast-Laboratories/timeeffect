<?php
/**
 * Standard Layout Template for TimeEffect Pages
 * Usage: Include this at the end of your PHP page after setting $center_template and $center_title
 */

// Ensure required variables are set
if (!isset($center_template)) {
    die('Error: $center_template must be set before including layout template');
}

if (!isset($center_title)) {
    $center_title = 'TimeEffect';
}

// Include the unified layout system
include("$_PJ_root/templates/list.ihtml.php");
include_once("$_PJ_include_path/degestiv.inc.php");
?>
