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
    return true;
}

// Try to load from Composer if available
$composer_autoload = HO_TRACKING_PLUGIN_DIR . 'vendor/autoload.php';
if (file_exists($composer_autoload)) {
    try {
        require_once $composer_autoload;
        
        // Verify the library loaded successfully
        if (class_exists('PhpOffice\PhpSpreadsheet\IOFactory')) {
            return true;
        }
    } catch (Exception $e) {
        error_log('HO Tracking: Failed to load PhpSpreadsheet - ' . $e->getMessage());
        return false;
    }
}

// If PhpSpreadsheet is not available, we'll fall back to CSV only
// This is handled in the main plugin file
return false;
