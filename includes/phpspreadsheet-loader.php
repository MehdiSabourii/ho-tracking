<?php
/**
 * PhpSpreadsheet loader
 * 
 * This file attempts to load PhpSpreadsheet library for Excel file support
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Check if PhpSpreadsheet is already loaded
if (class_exists('PhpOffice\PhpSpreadsheet\IOFactory')) {
    return;
}

// Try to load from Composer if available
$composer_autoload = HO_TRACKING_PLUGIN_DIR . 'vendor/autoload.php';
if (file_exists($composer_autoload)) {
    require_once $composer_autoload;
    return;
}

// If PhpSpreadsheet is not available, we'll fall back to CSV only
// This is handled in the main plugin file
