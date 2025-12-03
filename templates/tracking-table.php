<?php
/**
 * Frontend tracking table template
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="ho-tracking-wrapper">
    <div class="ho-tracking-search">
        <h3><?php _e('Track Your Package', 'ho-tracking'); ?></h3>
        <form id="ho-tracking-search-form">
            <div class="search-box">
                <input type="text" 
                       id="tracking-search-input" 
                       name="search" 
                       placeholder="<?php _e('Enter tracking code or recipient name...', 'ho-tracking'); ?>"
                       autocomplete="off">
                <button type="submit" class="search-button">
                    <?php _e('Search', 'ho-tracking'); ?>
                </button>
            </div>
        </form>
    </div>
    
    <div class="ho-tracking-results">
        <div id="tracking-loading" style="display: none;">
            <p><?php _e('Searching...', 'ho-tracking'); ?></p>
        </div>
        
        <div id="tracking-no-results" style="display: none;">
            <p><?php _e('No tracking information found. Please check your tracking code and try again.', 'ho-tracking'); ?></p>
        </div>
        
        <div id="tracking-table-container">
            <!-- Results will be loaded here via AJAX -->
        </div>
    </div>
</div>
